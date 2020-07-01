<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'categoryName', 'description', 'featuredImage', 'isActive', 'branch_id'
    ];

    

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
