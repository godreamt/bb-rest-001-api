<?php

namespace App;

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
            $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
            $item->id = IdGenerator::generate(['table' => 'transaction_on_accounts', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function account() {
        return $this->belongsTo('App\LedgerAccount', 'accountId');
    }
}
