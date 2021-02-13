<?php

namespace App;

use Panoscape\History\HasHistories;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderItem extends Model
{
    use HasHistories;
    
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'orderId', 
        'quantity', 
        'servedQuantity', 
        'price', 
        'packagingCharges', 
        'productId', 
        'totalPrice', 
        'isParcel', 
        'productionAcceptedQuantity', 
        'productionReadyQuantity', 
        'productionRejectedQuantity',
        'advancedPriceId',
        'advancedPriceTitle',
        'isSync'
    ];

    protected $casts = [
        // 'orderId' => 'int',
        // 'productId' => 'int',
        'isSync' => 'boolean',
        'isParcel' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if(empty($item->id)) {
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $item->id = IdGenerator::generate(['table' => 'order_items', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
            }
        });
    }

    public function product() {
        return $this->belongsTo('App\Product', 'productId');
    }

    
    public function getModelLabel()
    {
        return $this->product->productName;
    }
}
