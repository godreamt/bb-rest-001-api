<?php

namespace App;

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
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $method->id = IdGenerator::generate(['table' => 'branch_payment_methods', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
            }
        });
    }
}
