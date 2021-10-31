<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FavoriteMenu extends Model
{

    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'menuTitle',
        'description',
        'startTime',
        'endTime',
        'company_id',
        'branch_id',
        'isActive',
        'isSync'
    ];


    protected $casts = [
        'isActive' => 'boolean',
        'isSync' => 'boolean'
    ];


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('favorite_menus.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                    $builder->where('favorite_menus.branch_id',  $user->branch_id);
                }
            }
        });


        static::updating(function ($menu) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $menu->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $menu->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($menu->branch_id);
            $menu->company_id = $branch->company_id;
        });

        static::creating(function ($menu) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $menu->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $menu->branch_id = $loggedUser->branch_id;
                }
                $branch = Branch::find($menu->branch_id);
                $menu->company_id = $branch->company_id;
                if(empty($menu->id)) {
                    $menu->id = Helper::GenerateId($loggedUser, 'favorite_menus');
                }
            }
        });
    }

    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function favoriteItems()
    {
        return $this->hasMany('App\FavoriteMenuItems', 'menu_id');
    }

    public function favoriteComboItems()
    {
        return $this->hasMany('App\FavoriteMenuComboItem', 'menu_id');
    }
}
