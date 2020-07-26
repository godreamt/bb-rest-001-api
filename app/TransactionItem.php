<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $fillable = [
        'transactionId', 'itemId', 'quantity', 'amount', 'total'
    ];

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function item() {
        return $this->belongsTo('App\InventoryItem', 'itemId');
    }
}
