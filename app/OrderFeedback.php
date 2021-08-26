<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderFeedback extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'orderId', 'customerId', 'rating', 'comments', 'isSync'
    ];

    
    protected $casts = [
        'isSync' => 'boolean',
        // 'customerId' => 'int',
        // 'orderId' => 'int'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($feedback) {
            $loggedUser = \Auth::user();
            $feedback->id = Helper::GenerateId($loggedUser, 'order_feedbacks');
        });
    }
}
