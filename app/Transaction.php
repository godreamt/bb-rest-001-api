<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Transaction extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'transactionDate', 
        'transactionRefNumber', 
        'accountId', 
        'transactionType', 
        'description', 
        'grandTotal', 
        'company_id', 
        'branch_id',
        'updatedBy',
        'monthly_sheet_id'
    ];

    
    protected $casts = [
        'transactionDate' => 'date',
        'accountId' => 'int',
        'grandTotal' => 'double',
        'company_id' => 'int',
        'branch_id' => 'int',
        'monthly_sheet_id' => 'int',
        'updatedBy' => 'int'
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('company_id',  $user->company_id);
            }
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        
        static::updating(function ($transaction) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $transaction->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $transaction->branch_id = $loggedUser->branch_id;
                }
            }
            if(empty($transaction->company_id)) {
                $branch = Branch::find($transaction->branch_id);
                if($branch instanceof Branch) {
                    $transaction->company_id = $branch->company_id;
                }
            }
        });

        static::creating(function ($transaction) {

            $loggedUser = \Auth::user();
            $transaction->updatedBy = $loggedUser->id;
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $transaction->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $transaction->branch_id = $loggedUser->branch_id;
                }
            }
            if(empty($transaction->company_id)) {
                $branch = Branch::find($transaction->branch_id);
                if($branch instanceof Branch) {
                    $transaction->company_id = $branch->company_id;
                }
            }

            
            if(empty($transaction->transactionRefNumber)) {
                $ref = 1000000;
                $count = static::count();
                $transaction->transactionRefNumber = $count ? $ref + $count : $ref;
            }
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $transaction->id = IdGenerator::generate(['table' => 'transactions', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }

    /**
     * Transaction Types
     * purchase - accountId = From Account
     * sales - accountId = To Account
     * payement - accountId = From Account
     * receipt - accountId = To Account
     */

     public function ledgerAccount() {
        return $this->belongsTo('App\LedgerAccount', 'accountId');
    }

    public function company() {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function branch() {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function items() {
        return $this->hasMany('App\TransactionItem', 'transactionId');
    }

    public function accounts() {
        return $this->hasMany('App\TransactionOnAccount', 'transactionId');
    }
}

/**
 * Purchase format
 * Name Of Item ----------------------------Quantity-----------------------Rate Per----------------------Amount
 * ************************************************************************************************************
 * 
 * 
 * 
 * ************************************************************************************************************
 * Duties and taxes ------------------------------------------------------5%    -----------------------Amount
 * ************************************************************************************************************
 * Direct Income --------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * 
 * Total ================================= Total ======================================================total
 * 
 * Comment Section-------------------------------------------------------------------------------------------
 * 
 */


 
/**
 * Sales format
 * Name Of Item ----------------------------Quantity-----------------------Rate Per----------------------Amount
 * ************************************************************************************************************
 * 
 * 
 * 
 * ************************************************************************************************************
 * Duties and taxes ------------------------------------------------------5%    -----------------------Amount
 * ************************************************************************************************************
 * Direct Expense --------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * 
 * Total ================================= Total ======================================================total
 * 
 * Comment Section-------------------------------------------------------------------------------------------
 * 
 */


 
 
/**
 * Payment format
 * 
 * From Account
 * ***********
 * 
 * Particulars -----------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * To Account ------------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * 
 * Total ================================================================================================total
 * 
 * Comment Section-------------------------------------------------------------------------------------------
 * 
 */

 /**
 * Receipt format
 * 
 * To  Account
 * ***********
 * 
 * Particulars -----------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * From Account ------------------------------------------------------------------------------------------Amount
 * ************************************************************************************************************
 * 
 * Total ================================================================================================total
 * 
 * Comment Section-------------------------------------------------------------------------------------------
 * 
 */