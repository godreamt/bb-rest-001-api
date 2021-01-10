<?php

namespace App;

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
            $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
            $feedback->id = IdGenerator::generate(['table' => 'order_feedbacks', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
