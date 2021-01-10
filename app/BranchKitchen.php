<?php

namespace App;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class BranchKitchen extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'kitachenTitle', 'branch_id', 'isSync'
    ];


    protected $casts = [
        'isSync' => 'boolean',
        // 'branch_id' => 'int',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kitchen) {
            $loggedUser = \Auth::user();
            $prefix = Config::get('app.hosted') . ($loggedUser->company_id ?? "") . ($loggedUser->branch_id ?? "" );
            $kitchen->id = IdGenerator::generate(['table' => 'branch_kitchens', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });
    }
}
