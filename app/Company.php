<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Company extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

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
                $builder->where('id',  $user->company_id);
            }
        });
        
        static::creating(function ($company) {
            $loggedUser = \Auth::user();
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $company->id = IdGenerator::generate(['table' => 'companies', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
