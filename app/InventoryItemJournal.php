<?php

namespace App;

use App\helper\Helper;
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
        'orderId',
        'isSync'
    ];

    protected $casts = [
        // 'inventoryId' => 'int',
        // 'updatedBy' => 'int',
        // 'orderId' => 'int',
        'pricePerUnit' => 'double',
        'totalAmount' => 'double',
        'isSync' => 'boolean',
        'quantity' => 'double',
    ];  

    protected static function boot()
    {
        parent::boot();

        
        static::creating(function ($journal) {
            $loggedUser = \Auth::user();
            $journal->updatedBy = $loggedUser->id;
            $journal->id = Helper::GenerateId($loggedUser, 'inventory_item_journals');
        });
    }
}
