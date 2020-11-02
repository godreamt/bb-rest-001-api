<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Branch extends Model
{
    protected $fillable = [
        'branchTitle', 'description', 'branchAddress', 'branchCode', 'isActive', 'branchLogo', 'taxPercent', 'appDefaultOrderType', 'adminDefaultOrderType'
    ];

    protected $casts = [
        'isActive' => 'boolean',
    ];

        //we can also use hasManyThrough https://www.itsolutionstuff.com/post/laravel-has-many-through-eloquent-relationship-tutorialexample.html to get orders
  
    public function getBranchId($value)
    {
        return (string) $value;
    }
        
    public function users()
    {
        return $this->hasMany('App\User');
    }

    
    public function orderTypes()
    {
        return $this->hasMany('App\BranchOrderType', 'branch_id');
    }
    
    public function kitchens()
    {
        return $this->hasMany('App\BranchKitchen', 'branch_id');
    }

    
    public function categories()
    {
        return $this->hasMany('App\Category', 'branch_id');
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
            if($user instanceof User && $user->roles != 'Super Admin') {
                $builder->where('id',  $user->branch_id);
            }
        });
    }
}
