<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;
use Panoscape\History\HasHistories;

class OrderItemCombo extends Model
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
        'comboProductId',
        'totalPrice',
        'isParcel',
        'productionAcceptedQuantity',
        'productionReadyQuantity',
        'productionRejectedQuantity',
        'kotPrintedQuantity',
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
                $item->id = Helper::GenerateId($loggedUser, 'order_item_combos');
            }
        });
    }

    public function productCombo() {
        return $this->belongsTo('App\ProductCombo', 'comboProductId');
    }

    public function comments() {
        return $this->hasMany('App\OrderComboItemComment', 'itemId');
    }


    public function getModelLabel()
    {
        return $this->productCombo->comboTitle;
    }


    public function getKotPendingAttribute() {
        return (int)$this->quantity - $this->kotPrintedQuantity;
    }
}
