<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    protected $fillable = [
        'categoryName', 'description', 'featuredImage', 'isActive', 'branch_id'
    ];


    protected $casts = [
        'branch_id' => 'int',
        'isActive' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->branch_id = $user->branch_id;
            }
        });
    }
    

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
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
