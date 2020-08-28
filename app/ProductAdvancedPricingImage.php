<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAdvancedPricingImage extends Model
{
    protected $fillable = [
        'productId', 'advancedPricingId', 'image'
    ];
}
