<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryItemManager extends Model
{
    protected $fillable = [
        'inventoryId',
        'availableStock',
        'lastPurchasedPrice',
        'company_id',
        'branch_id',
    ];

    protected $casts = [
        'inventoryId' => 'int',
        'company_id' => 'int',
        'branch_id' => 'int',
        'availableStock' => 'double',
        'lastPurchasedPrice' => 'double'
    ];
}
