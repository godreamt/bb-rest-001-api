<?php

namespace App;

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
            $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
            $item->id = IdGenerator::generate(['table' => 'product_addons', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }

    public function table() {
        return $this->belongsTo('App\TableManager', 'tableId');
    }
}
