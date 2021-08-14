<?php

namespace App;

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
            $prefix = Config::get('app.hosted')  . substr(($loggedUser->branch_id ?? ""), -3);
            $item->id = IdGenerator::generate(['table' => 'transaction_items', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function item() {
        return $this->belongsTo('App\InventoryItem', 'itemId');
    }
}
