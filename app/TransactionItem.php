<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class TransactionItem extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'transactionId', 
        'itemId', 
        'quantity', 
        'amount', 
        'total',
        'isSync'
    ];

    
    protected $casts = [
        // 'transactionId' => 'int',
        // 'itemId' => 'int',
        'quantity' =>'int',
        'amount' => 'double',
        'isSync' => 'boolean',
        'total' => 'double'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            $item->id = Helper::GenerateId($loggedUser, 'transaction_items');
        });
    }

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function item() {
        return $this->belongsTo('App\InventoryItem', 'itemId');
    }
}
