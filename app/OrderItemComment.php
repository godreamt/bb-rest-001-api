<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;

class OrderItemComment extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'itemId',
        'description',
        'isPrinted',
        'isSync'
    ];

    protected $casts = [
        'isSync' => 'boolean',
        'isPrinted' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if(empty($item->id)) {
                $item->id = Helper::GenerateId($loggedUser, 'order_item_comments');
            }
        });
    }

    public function orderItem() {
        return $this->belongsTo('App\OrderItem', 'itemId');
    }
}
