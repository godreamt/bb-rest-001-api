<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPriceModel extends Model
{
    protected $fillable = [
        'productId', 'title', 'description'
    ];

    
    protected $casts = [
        'productId' => 'int',
    ];
}
