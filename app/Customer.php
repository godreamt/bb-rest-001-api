<?php

namespace App;

use App\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    protected $fillable = [
        'customerName', 'mobileNumber', 'emailId', 'branch_id'
    ];


    protected $casts = [
        'branch_id' => 'int',
    ];

    public function orders()
    {
        return $this->belongsTo('App\Order', 'customerId');
    }

    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('branch_id',  $user->branch_id);
                }
            }
        });
        
        
        static::updating(function ($customer) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $customer->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $customer->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($customer->branch_id);
            $customer->company_id = $customer->company_id;
        });

        static::creating(function ($customer) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $customer->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $customer->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($customer->branch_id);
            $customer->company_id = $customer->company_id;
        });
    }
}
