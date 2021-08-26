<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class BranchPaymentMethods extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'methodTitle', 'branch_id', 'isSync'
    ];


    protected $casts = [
        'isSync' => 'boolean',
        // 'branch_id' => 'int',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($method) {
            if(empty($method->id)) {
                $loggedUser = \Auth::user();
                $method->id = Helper::GenerateId($loggedUser, 'branch_payment_methods');
            }
        });
    }
}
