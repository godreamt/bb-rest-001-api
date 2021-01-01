<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class LedgerAccount extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    public $timestamps = false;
    // types = Purchase Account, Sales Account, Sundry Creditors, Sundry Debitors, Duties and Taxes, Bank Account, Cash Account, Direct Expense, Indirect Expense, Direct Income, Indirect Income
    protected $fillable = [
        'ledgerName', 
        'accountType', 
        'description', 
        'isActive', 
        'isAutoCreated', 
        'company_id'
    ];


    protected $casts = [
        'company_id' => 'int',
        'isActive' => 'boolean',
        'isAutoCreated' => 'boolean'
    ];

    
    
    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }
    
    public function transactions() {
        return $this->hasMany('App\Transaction', 'accountId');
    }
    
    public function transactionOnAccount() {
        return $this->hasMany('App\TransactionOnAccount', 'accountId');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('company_id',  $user->company_id);
            }
        });

        
        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin') {
                $item->company_id = $loggedUser->company_id;
            }
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $item->id = IdGenerator::generate(['table' => 'ledger_accounts', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });

        
        static::updating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });
    }
}
