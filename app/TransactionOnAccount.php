<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionOnAccount extends Model
{
    protected $fillable = [
        'transactionId', 'accountId', 'percentage', 'amount', 'currentBalance'
    ];

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function account() {
        return $this->belongsTo('App\LedgerAccount', 'accountId');
    }
}
