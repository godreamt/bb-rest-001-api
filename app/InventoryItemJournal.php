<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class InventoryItemJournal extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'inventoryId', 
        'description',
        'transactionType',
        'quantity',
        'pricePerUnit',
        'totalAmount',
        'updatedBy',
        'orderId'
    ];

    protected $casts = [
        // 'inventoryId' => 'int',
        // 'updatedBy' => 'int',
        // 'orderId' => 'int',
        'pricePerUnit' => 'double',
        'totalAmount' => 'double',
        'quantity' => 'double',
    ];  

    protected static function boot()
    {
        parent::boot();

        
        static::creating(function ($journal) {
            $loggedUser = \Auth::user();
            $journal->updatedBy = $loggedUser->id;
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $journal->id = IdGenerator::generate(['table' => 'inventory_item_journals', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
