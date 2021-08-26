<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class ProductAddon extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'addonTitle', 'price', 'productId', 'isSync'
    ];

    
    protected $casts = [
        'isSync' => 'boolean',
        // 'productId' => 'int',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            $item->id = Helper::GenerateId($loggedUser, 'product_addons');
        });
    }

    public function table() {
        return $this->belongsTo('App\TableManager', 'tableId');
    }
}
