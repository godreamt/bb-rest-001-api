<?php

namespace App;

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
            if($user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });
    }
}
