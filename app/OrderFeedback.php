<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderFeedback extends Model
{
    protected $fillable = [
        'orderId', 'customerId', 'rating', 'comments'
    ];

    
    protected $casts = [
        'customerId' => 'int',
        'orderId' => 'int'
    ];
}
