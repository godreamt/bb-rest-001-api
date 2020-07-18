<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TableManager extends Model
{
    protected $fillable = [
        'tableId', 'description', 'noOfChair', 'bookedChairs', 'isReserved', 'isActive', 'orderTypeId', 'chairs'
    ];

    protected $appends = ['chairs'];

    public function getChairsAttribute()
    {
        return range(1, $this->noOfChair, 1);
    }
    
    public function orderType()
    {
        return $this->belongsTo('App\OrderType', 'orderTypeId');
    }
}
