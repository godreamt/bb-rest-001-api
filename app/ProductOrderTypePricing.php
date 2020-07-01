<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductOrderTypePricing extends Model
{
    protected $fillable = [
        'price',  'taxPercent', 'packagingCharges', 'orderTypeId', 'productId'
    ];

    
    // public function branch()
    // {
    //     return $this->belongsTo('App\Branch', 'branch_id');
    // }
    
    public function product()
    {
        return $this->belongsTo('App\Product', 'productId');
    }
}
