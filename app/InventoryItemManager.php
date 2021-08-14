<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class InventoryItemManager extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'inventoryId',
        'availableStock',
        'lastPurchasedPrice',
        'branch_id',
        'isSync'
    ];

    protected $casts = [
        // 'inventoryId' => 'int',
        // 'branch_id' => 'int',
        'availableStock' => 'double',
        'lastPurchasedPrice' => 'double',
        'isSync' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($orderType) {
            $loggedUser = \Auth::user();
            $prefix = Config::get('app.hosted') . substr(($loggedUser->branch_id ?? ""), -3);
            $orderType->id = IdGenerator::generate(['table' => 'inventory_item_managers', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
