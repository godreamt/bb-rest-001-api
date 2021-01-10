<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class BranchOrderType extends Model
{

    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
    protected $fillable = [
        'orderType', 'tableRequired', 'isActive', 'branch_id', 'isSync'
    ];


    protected $casts = [
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'tableRequired' => 'boolean'
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($orderType) {
            $loggedUser = \Auth::user();
            $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
            $orderType->id = IdGenerator::generate(['table' => 'branch_order_types', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
