<?php

namespace App;

use App\helper\Helper;
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
        'id',
        'companyName',
        'companyLogo',
        'companyDetails',
        'numberOfBranchesAllowed',
        'enableAccounting',
        'enableRestaurantFunctions',
        'isActive',
        'isSync',
        'apiKey'

    ];

    protected $casts = [
        'enableAccounting' => 'boolean',
        'enableRestaurantFunctions' => 'boolean',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'numberOfBranchesAllowed' => 'int'
    ];


    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = Auth::user();
            if($user instanceof User && $user->roles != 'Super Admin') {
                $builder->where('companies.id',  $user->company_id);
            }
        });
        
        static::creating(function ($company) {
            if(empty($company->id)) {
                $loggedUser = \Auth::user();
                $company->id = Helper::GenerateId($loggedUser, 'companies');
            }
        });
    }
}
