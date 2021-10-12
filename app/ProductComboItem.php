<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;
use Panoscape\History\HasHistories;

class ProductComboItem extends Model
{
    use HasHistories;

    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'combo_id',
        'product_id',
        'advancedPriceTitle',
        'advancedPriceId',
        'price',
        'quantity',
        'subTotal',
        'isSync'
    ];

    protected $casts = [
        'isSync' => 'boolean'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if(empty($item->id)) {
                $item->id = Helper::GenerateId($loggedUser, 'product_combo_items');
            }
        });

    }

    public function productCombo() {
        return $this->belongsTo('App\ProductCombo', 'combo_id');
    }

    public function product() {
        return $this->belongsTo('App\Product', 'product_id');
    }



    public function getModelLabel()
    {
        return $this->product->productName;
    }

}
