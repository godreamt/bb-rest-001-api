<?php

namespace App;

use App\helper\Helper;
use Panoscape\History\HasHistories;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderItem extends Model
{
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
        'kotPrintedQuantity',
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

    protected $appends = ['kot_pending'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if(empty($item->id)) {
                $item->id = Helper::GenerateId($loggedUser, 'order_items');
            }
            // $item->kotPendingQuantity = $item->quantity;
        });

        // static::updating(function ($item) {
        //     \Debugger::dump($item->getDirty());
        //     // if($item->isDirty('quantity')) {

        //     // }
        //     throw \Exception("0000");
        // });
    }

    public function product() {
        return $this->belongsTo('App\Product', 'productId');
    }

    public function comments() {
        return $this->hasMany('App\OrderItemComment', 'itemId');
    }


    public function getModelLabel()
    {
        return $this->product->productName;
    }


    public function getKotPendingAttribute() {
        return (int)$this->quantity - $this->kotPrintedQuantity;
    }
}
