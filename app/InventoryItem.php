<?php

namespace App;

use App\helper\Helper;
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
        'id',
        'itemName', 
        'unitId', 
        'description', 
        'pricePerUnit', 
        'availableStock', 
        'lastPurchasedPrice', 
        'isActive', 
        'branch_id',
        'isSync'
    ];


    protected $casts = [
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'pricePerUnit' => 'double',
        'availableStock' => 'double',
        'lastPurchasedPrice' => 'double',
    ];

    public function unit() {
        return $this->belongsTo('App\MeasureUnit', 'unitId');
    }
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('inventory_items.branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                $item->branch_id = $loggedUser->branch_id;
            }
            if(empty($item->id)) {
                $item->id = Helper::GenerateId($loggedUser, 'inventory_items');
            }
        });

        
        static::updating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                $item->branch_id = $loggedUser->branch_id;
            }
        });
    }
} 
