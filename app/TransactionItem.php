<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transactionId', 'itemId', 'quantity', 'amount', 'total'
    ];

    
    protected $casts = [
        'transactionId' => 'int',
        'itemId' => 'int',
    ];

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function item() {
        return $this->belongsTo('App\InventoryItem', 'itemId');
    }
}
