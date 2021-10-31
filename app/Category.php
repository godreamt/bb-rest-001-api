<?php

namespace App;

use App\Branch;
use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Validation\ValidationException;

class Category extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'categoryName',
        'description',
        'featuredImage',
        'isActive',
        'branch_id',
        'isSync'
    ];


    protected $casts = [
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('categories.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('categories.branch_id',  $user->branch_id);
                }
            }
        });


        static::updating(function ($category) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $category->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $category->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($category->branch_id);
            $category->company_id = $branch->company_id;
        });

        static::creating(function ($category) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $category->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $category->branch_id = $loggedUser->branch_id;
                }
            }

            $branch = Branch::find($category->branch_id);
            // throw new ValidationException($branch);
            $category->company_id = $branch->company_id;

            if(empty($category->id)) {
                $category->id = Helper::GenerateId($loggedUser, 'categories');
            }
        });
    }


    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product', 'product_categories');

// $roleIds = [1, 2];
// $user->roles()->attach($roleIds);

// $roleIds = [1, 2];
// $user->roles()->sync($roleIds);
// $userIds = [10, 11];
// $role->users()->attach($userIds);
// $userIds = [10, 11];
// $role->users()->sync($userIds);
    }
}
