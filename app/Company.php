<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Company extends Model
{
    protected $fillable = [
        'companyName',
        'companyLogo',
        'companyDetails',
        'numberOfBranchesAllowed',
        'enableAccounting',
        'enableRestaurantFunctions',
        'isActive',
        'apiKey'

    ];

    protected $casts = [
        'enableAccounting' => 'boolean',
        'enableRestaurantFunctions' => 'boolean',
        'isActive' => 'boolean',
        'numberOfBranchesAllowed' => 'int'
    ];


    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = Auth::user();
            if($user instanceof User && $user->roles != 'Super Admin') {
                // $builder->where('id',  $user->company_id);
            }
        });
    }
}
