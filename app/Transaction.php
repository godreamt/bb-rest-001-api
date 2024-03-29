<?php

namespace App;

use App\Branch;
use App\helper\Helper;
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
        'id',
        'transactionDate', 
        'transactionRefNumber', 
        'accountId', 
        'transactionType', 
        'description', 
        'grandTotal', 
        'branch_id',
        'updatedBy',
        'monthly_sheet_id',
        'isSync'
    ];

    
    protected $casts = [
        'transactionDate' => 'date',
        'isSync' => 'boolean',
        // 'accountId' => 'int',
        'grandTotal' => 'double',
        // 'branch_id' => 'int',
        // 'monthly_sheet_id' => 'int',
        // 'updatedBy' => 'int'
    ];

    protected $appends = [
        'inventory_string'
    ];

    public function getInventoryStringAttribute() {
        $inventoryName = [];
        foreach( $this->items as $item) {
            $inventoryName[] = $item->item->itemName;
        }
        return join(", ", $inventoryName);
    }

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('transactions.branch_id',  $user->branch_id);
            }
        });

        
        static::updating(function ($transaction) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {                
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' ) {
                    $transaction->branch_id = $loggedUser->branch_id;
                }
            }
        });

        static::creating(function ($transaction) {

            $loggedUser = \Auth::user();
            $transaction->updatedBy = $loggedUser->id;
            if($loggedUser instanceof User) {                
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' ) {
                    $transaction->branch_id = $loggedUser->branch_id;
                }
            }

            
            if(empty($transaction->transactionRefNumber)) {
                $ref = 1000000;
                $count = static::where('branch_id', $transaction->branch_id)->count();
                if(($count > 0)) {
                    $transaction->transactionRefNumber = $ref + $count;
                }else {
                    
                    $transaction->transactionRefNumber =  $ref;
                }
                // \Debugger::dump($transaction->transactionRefNumber);
            }
            $branch = Branch::find($transaction->branch_id);
            $transaction->id = Helper::GenerateId($loggedUser, 'transactions', $branch->branchCode);
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