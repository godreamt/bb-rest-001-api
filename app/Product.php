<?php

namespace App;

use App\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    /**
     * TOdo
     * Addons for products
     * consider https://slickpos.com/hardware/desktop-billing-software/
     */
    protected $fillable = [
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

        'isAdvancedPricing'
    ];

    
    protected $casts = [
        'branch_id' => 'int',
        'isActive' => 'boolean',
        'isVeg' => 'boolean',
        'isOutOfStock' => 'boolean',
        'isAdvancedPricing' => 'boolean',
        'kitchen_id' => 'int'
    ];

    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
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
                    $builder->where('company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('branch_id',  $user->branch_id);
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
            $count = static::whereRaw("productSlug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            $product->productSlug = $count ? "{$slug}-{$count}" : $slug;
        });
    }
}
