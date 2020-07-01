<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TableManager extends Model
{
    protected $fillable = [
        'tableId', 'description', 'noOfChair', 'isReserved', 'isActive', 'orderTypeId'
    ];
    
    public function orderType()
    {
        return $this->belongsTo('App\OrderType', 'orderTypeId');
    }
}
