<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPriceModelCombination extends Model
{
    protected $fillable = [
        'productId'
    ];

    
    protected $casts = [
        'productId' => 'int',
    ];
}
