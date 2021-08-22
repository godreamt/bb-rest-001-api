<?php

namespace App;

use App\User;
use App\Branch;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class TransactionAccountJournal extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'description', 
        'transactionAmount',
        'transactionDate',
        'transactionId',
        'transactionAccountId', 
        'accountId', 
        'endingBalance',
        'branch_id', 
        'isSync'
    ];

    
    protected $casts = [
        'isSync' => 'boolean',
        'endingBalance' => 'decimal:2' ,
        'transactionAmount'=>'double'
    ];

    public function transaction() {
        return $this->belongsTo('App\Transaction', 'transactionId');
    }

    public function transactionAccount() {
        return $this->belongsTo('App\TransactionOnAccount', 'transactionAccountId');
    }

    protected static function boot()
    {
        parent::boot();
        

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {                
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                    $builder->where('transaction_account_journals.branch_id',  $user->branch_id);
                }
            }
        });

        
        static::updating(function ($item) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
               
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $item->branch_id = $loggedUser->branch_id;
                }
            }
        });

        static::creating(function ($item) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' ) {
                    $item->branch_id = $loggedUser->branch_id;
                }
                if(empty($item->id)) {
                    $prefix = Config::get('app.hosted') . substr(($loggedUser->branch_id ?? ""), -3);
                    $item->id = IdGenerator::generate(['table' => 'transaction_account_journals', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
                }
            }
        });
    }
}
