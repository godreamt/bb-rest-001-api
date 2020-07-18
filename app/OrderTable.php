<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderTable extends Model
{
    protected $fillable = [
        'orderId', 'tableId', 'selectedChairs' 
    ];
}
