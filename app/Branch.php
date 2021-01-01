<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Branch extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'branchLogo', 
        'branchCode', 
        'branchTitle', 
        'branchAddress', 
        'description', 
        'isActive', 
        'taxPercent', 
        'appDefaultOrderType', 
        'adminDefaultOrderType',
        'company_id'
    ];

    protected $casts = [
        'isActive' => 'boolean',
        'company_id' => 'int'
    ];

        //we can also use hasManyThrough https://www.itsolutionstuff.com/post/laravel-has-many-through-eloquent-relationship-tutorialexample.html to get orders
        
    public function users()
    {
        return $this->hasMany('App\User');
    }

    
    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
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
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('id',  $user->branch_id);
                }
            }
        });
        static::creating(function ($branch) {
            $loggedUser = \Auth::user();
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $branch->id = IdGenerator::generate(['table' => 'branches', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
