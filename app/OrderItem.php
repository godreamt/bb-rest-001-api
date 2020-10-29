<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'orderId', 'quantity', 'servedQuantity', 'price', 'packagingCharges', 'productId', 'totalPrice', 'isParcel', 'productionAcceptedQuantity', 'productionReadyQuantity', 'productionRejectedQuantity'
    ];

    protected $casts = [
        'orderId' => 'int',
        'productId' => 'int'
    ];

    public function product() {
        return $this->belongsTo('App\Product', 'productId');
    }
}
