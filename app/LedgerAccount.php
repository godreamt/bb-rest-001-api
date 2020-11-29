<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class LedgerAccount extends Model
{
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

        // static::addGlobalScope('role_handler', function (Builder $builder) {
        //     $user = \Auth::user();
        //     if($user->roles != 'Super Admin') {
        //         $builder->where('branch_id',  $user->branch_id);
        //     }
        // });

        
        // static::creating(function ($item) {
        //     $user = \Auth::user();
        //     if($user->roles != 'Super Admin') {
        //         $item->branch_id = $user->branch_id;
        //     }
        // });
    }
}
