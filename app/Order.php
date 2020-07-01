<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customerId', 'relatedInfo', 'branchId', 'orderTypeId', 'cgst', 'sgst', 'igst', 'orderAmount', 'packingCharge', 'extraCharge', 'excludeFromReport', 'deliverCharge', 'orderStatus'
    ];
}
