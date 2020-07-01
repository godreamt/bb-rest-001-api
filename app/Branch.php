<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'branchTitle', 'description', 'branchAddress', 'branchCode', 'isActive'
    ];

        //we can also use hasManyThrough https://www.itsolutionstuff.com/post/laravel-has-many-through-eloquent-relationship-tutorialexample.html to get orders
    
    public function users()
    {
        return $this->hasMany('App\User');
    }

    
    public function categories()
    {
        return $this->hasMany('App\Category', 'branch_id');
    }
    
    public function orderTypes()
    {
        return $this->hasMany('App\OrderType', 'branch_id');
    }
    
    public function products()
    {
        return $this->hasMany('App\Product', 'branch_id');
    }
}
