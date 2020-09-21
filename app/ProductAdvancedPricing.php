<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAdvancedPricing extends Model
{
    protected $fillable = [
        'productId', 'combinationId', 'price'
    ];
}


// Gallery photos related to individual comination can be added here.