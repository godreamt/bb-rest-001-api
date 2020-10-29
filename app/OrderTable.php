<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderTable extends Model
{
    protected $fillable = [
        'orderId', 'tableId', 'selectedChairs' 
    ];

    public function table() {
        return $this->belongsTo('App\TableManager', 'tableId');
    }
}
