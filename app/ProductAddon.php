<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAddon extends Model
{
    protected $fillable = [
        'addonTitle', 'price', 'productId'
    ];

    
    protected $casts = [
        'productId' => 'int',
    ];
}
