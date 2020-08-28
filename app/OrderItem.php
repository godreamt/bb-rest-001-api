<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'orderId', 'quantity', 'servedQuantity', 'price', 'packagingCharges', 'productId', 'totalPrice'
    ];
}
