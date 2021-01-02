<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class InventoryItem extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    public $timestamps = false;
    protected $fillable = [
        'itemName', 
        'unitId', 
        'description', 
        'pricePerUnit', 
        'isActive', 
        'company_id'
    ];


    protected $casts = [
        // 'company_id' => 'int',
        // 'unitId' => 'int',
        'isActive' => 'boolean',
        'pricePerUnit' => 'double',
    ];

    public function unit() {
        return $this->belongsTo('App\MeasureUnit', 'unitId');
    }
    
    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('company_id',  $user->company_id);
            }
        });

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });

        
        static::updating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin') {
                $item->company_id = $loggedUser->company_id;
            }
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $item->id = IdGenerator::generate(['table' => 'inventory_items', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
} 
