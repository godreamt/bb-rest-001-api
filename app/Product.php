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

        'isAdvancedPricing'
    ];

    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_categories');
    }

    public function orderItem() {
        return $this->hasMany('App\OrderItem', 'productId');
    }

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User && $user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        static::creating(function ($product) {
            
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $product->branch_id = $user->branch_id;
            }

            $slug = \Str::slug($product->productName);
            $count = static::whereRaw("productSlug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            $product->productSlug = $count ? "{$slug}-{$count}" : $slug;
        });
    }
}
