<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderType extends Model
{
    protected $fillable = [
        'typeName', 'description', 'enableTables', 'enableExtraInfo', 'enableDeliverCharge', 'enableExtraCharge', 'isActive', 'branch_id'
    ];

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function tables()
    {
        return $this->hasMany('App\TableManager', 'orderTypeId');
    }
}
