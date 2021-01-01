<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPMCombinationItem extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
    
    protected $fillable = [
        'combinationId', 'priceModelUnitId'
    ];

    
    protected $casts = [
        'combinationId' => 'int',
        'priceModelUnitId' => 'int',
    ];
}
