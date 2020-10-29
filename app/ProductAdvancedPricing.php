<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAdvancedPricing extends Model
{
    protected $fillable = [
        'productId', 'combinationId', 'price'
    ];

    
    protected $casts = [
        'productId' => 'int',
        'combinationId' => 'int',
    ];
}


// Gallery photos related to individual comination can be added here.