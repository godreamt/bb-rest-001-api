<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InventoryItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'itemName', 'unitId', 'description', 'pricePerUnit', 'isActive', 'company_id'
    ];


    protected $casts = [
        'company_id' => 'int',
        'unitId' => 'int',
        'isActive' => 'boolean',
        'pricePerUnit' => 'double'
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
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });
    }
} 
