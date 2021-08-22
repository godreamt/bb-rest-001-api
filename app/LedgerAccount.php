<?php

namespace App;

use App\TransactionAccountJournal;
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
        'branch_id',
        'isSync'
    ];


    protected $casts = [
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'isAutoCreated' => 'boolean'
    ];

    protected $appends = ['ending_balance'];


    public function getEndingBalanceAttribute() {
        $lastJournal = TransactionAccountJournal::where('accountId', $this->id)->orderBy('transactionDate', 'DESC')->orderBy('id', 'DESC')->first();
        if($lastJournal instanceof TransactionAccountJournal){
            return $lastJournal->endingBalance;
        }
        return "0.00";
    }
    
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
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
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('ledger_accounts.branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin'  && $loggedUser->roles != 'Company Admin') {
                $item->branch_id = $loggedUser->branch_id;
            }
            $prefix = Config::get('app.hosted') . substr(($loggedUser->branch_id ?? ""), -3);
            $item->id = IdGenerator::generate(['table' => 'ledger_accounts', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });

        
        static::updating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin'  && $user->roles != 'Company Admin') {
                $item->branch_id = $user->branch_id;
            }
        });
    }
}
