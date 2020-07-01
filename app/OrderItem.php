<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'orderId', 'quantity', 'price', 'productId', 'totalPrice'
    ];
}
