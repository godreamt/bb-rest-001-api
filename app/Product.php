<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'productNumber', 'productName', 'productSlug', 'description', 'featuredImage', 'price', 'taxPercent', 'packagingCharges', 'isActive', 'branch_id', 'isOrderTypePricing', 'isVeg'
    ];

    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }
    
    public function pricings()
    {
        return $this->hasMany('App\ProductOrderTypePricing', 'productId');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'product_categories');
    }

    protected static function boot() {
        parent::boot();

        static::creating(function ($product) {
            $slug = \Str::slug($product->productName);
            $count = static::whereRaw("productSlug RLIKE '^{$slug}(-[0-9]+)?$'")->count();
            $product->productSlug = $count ? "{$slug}-{$count}" : $slug;
        });
    }
}
