<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class TransactionOnAccount extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'transactionId', 
        'accountId', 
        'amountProcessType', 
        'amountValue', 
        'totalAmount',
        'isSync'
    ];

    
    protected $casts = [
        // 'transactionId' => 'int',
        // 'accountId' => 'int',
        'amountValue' => 'double',
        'isSync' => 'boolean',
        'totalAmount' => 'double'
    ];



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            $item->id = Helper::GenerateId($loggedUser, 'transaction_on_accounts');
        });
    }

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function account() {
        return $this->belongsTo('App\LedgerAccount', 'accountId');
    }
}
