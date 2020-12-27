<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryItemJournal extends Model
{
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
        'inventoryId' => 'int',
        'updatedBy' => 'int',
        'orderId' => 'int',
        'pricePerUnit' => 'double',
        'totalAmount' => 'double',
        'quantity' => 'double',
    ];  
    protected static function boot()
    {
        parent::boot();

        
        static::creating(function ($item) {
            $user = \Auth::user();
            $item->updatedBy = $user->id;
        });
    }
}
