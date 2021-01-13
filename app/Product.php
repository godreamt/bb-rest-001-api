<?php

namespace App;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Product extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    /**
     * TOdo
     * Addons for products
     * consider https://slickpos.com/hardware/desktop-billing-software/
     */
    protected $fillable = [
        'id',
        'productNumber', 
        'productName', 
        'productSlug', 
        'description', 
        'featuredImage', 
        'price', 
        'taxPercent', 
        'packagingCharges', 
        'isActive', 
        'branch_id', 
        'isVeg',

        'kitchen_id',
        'isOutOfStock',

        'isAdvancedPricing',
        'isSync'
    ];

    
    protected $casts = [
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'isVeg' => 'boolean',
        'isOutOfStock' => 'boolean',
        'isAdvancedPricing' => 'boolean',
        // 'kitchen_id' => 'int'
    ];

    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function advancedPricing() {
        return $this->hasMany('App\ProductAdvancedPricing', 'productId');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_categories', 'product_id', 'category_id');
    }

    public function orderItem() {
        return $this->hasMany('App\OrderItem', 'productId');
    }

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('products.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('products.branch_id',  $user->branch_id);
                }
            }
        });
        
        
        static::updating(function ($product) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $product->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $product->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($product->branch_id);
            $product->company_id = $product->company_id;
        });

        static::creating(function ($product) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $product->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $product->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($product->branch_id);
            $product->company_id = $branch->company_id;

            
            $slug = \Str::slug($product->productName);
            $count = static::where("productSlug", "LIKE", $slug . '%')->count();
            $product->productSlug = $count ? "{$slug}-{$count}" : $slug;

            if(empty($product->id)) {
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $product->id = IdGenerator::generate(['table' => 'products', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
            }
           
        });
    }
}
