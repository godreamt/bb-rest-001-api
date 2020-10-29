<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPriceModelUnit extends Model
{
    protected $fillable = [
        'priceModelId', 'title'
    ];

    
    protected $casts = [
        'priceModelId' => 'int',
    ];
}
