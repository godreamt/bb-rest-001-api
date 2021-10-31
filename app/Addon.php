<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'featuredImage',
        'isActive',
        'company_id',
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
                    $builder->where('addons.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                    $builder->where('addons.branch_id',  $user->branch_id);
                }
            }
        });


        static::updating(function ($addon) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $addon->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $addon->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($addon->branch_id);
            $addon->company_id = $branch->company_id;
        });

        static::creating(function ($addon) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $addon->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $addon->branch_id = $loggedUser->branch_id;
                }
            }

            $branch = Branch::find($addon->branch_id);
            // throw new ValidationException($branch);
            $addon->company_id = $branch->company_id;

            if(empty($addon->id)) {
                $addon->id = Helper::GenerateId($loggedUser, 'addons');
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

    public function productAddons()
    {
        return $this->hasMany('App\ProductAddon', 'addon_id');
    }
}
