<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customerId', 'relatedInfo', 'branch_id', 'orderTypeId', 'cgst', 'sgst', 'igst', 'orderAmount', 'packingCharge', 'extraCharge', 'excludeFromReport', 'deliverCharge', 'orderStatus'
    ];

    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customerId');
    }
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }
    
    public function orderType()
    {
        return $this->belongsTo('App\OrderType', 'orderTypeId');
    }
    
    public function orderitems()
    {
        return $this->hasMany('App\OrderItem', 'orderId');
    }
    
    public function orderTables()
    {
        return $this->hasMany('App\OrderTable', 'orderId');
    }
}
