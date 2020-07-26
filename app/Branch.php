<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

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
    
    public function orders()
    {
        return $this->hasMany('App\Order', 'branch_id');
    }

    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('id',  $user->branch_id);
            }
        });
    }
}
