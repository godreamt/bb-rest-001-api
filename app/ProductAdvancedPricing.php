<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ProductAdvancedPricing extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'productId', 
        'title', 
        'price', 
        'isSync'
    ];

    
    protected $casts = [
        'isSync' => 'boolean',
        'price' => 'double'
        // 'combinationId' => 'int',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if(empty($item->id)) {
                $loggedUser = \Auth::user();
                $item->id = Helper::GenerateId($loggedUser, 'product_advanced_pricings');
            }
        });
    }
}


// Gallery photos related to individual comination can be added here.