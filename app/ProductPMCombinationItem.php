<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPMCombinationItem extends Model
{
    protected $fillable = [
        'combinationId', 'priceModelUnitId'
    ];
}
