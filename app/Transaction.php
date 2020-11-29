<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    protected $fillable = [
        'transactionDate', 
        'transactionRefNumber', 
        'accountId', 
        'transactionType', 
        'description', 
        'grandTotal', 
        'company_id', 
        'branch_id',
        'monthly_sheet_id'
    ];

    
    protected $casts = [
        'transactionDate' => 'date',
        'accountId' => 'int',
        'grandTotal' => 'double',
        'company_id' => 'int',
        'branch_id' => 'int',
        'monthly_sheet_id' => 'int'
    ];

    protected static function boot() {
        parent::boot();

        // static::addGlobalScope('role_handler', function (Builder $builder) {
        //     $user = \Auth::user();
        //     if($user->roles != 'Super Admin') {
        //         $builder->where('branch_id',  $user->branch_id);
        //     }
        // });

        static::creating(function ($transaction) {

            
            // $user = \Auth::user();
            // if($user->roles != 'Super Admin') {
            //     $transaction->branch_id = $user->branch_id;
            // }


            if(empty($transaction->transactionRefNumber)) {
                $ref = 1000000;
                $count = static::count();
                $transaction->transactionRefNumber = $count ? $ref + $count : $ref;
            }
        });
    }

    /**
     * Transaction Types
     * purchase - accountId = From Account
     * sales - accountId = To Account
     * payement - accountId = From Account
     * receipt - accountId = To Account
     */

     public function account() {
        return $this->belongsTo('App\LedgerAccount', 'accountId');
    }

    public function company() {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function branch() {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

     public function items() {
         return $this->hasMany('App\TransactionItem', 'itemId');
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