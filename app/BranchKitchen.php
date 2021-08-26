<?php

namespace App;

use App\helper\Helper;
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
        'id', 'kitchenTitle', 'branch_id', 'isSync'
    ];


    protected $casts = [
        'isSync' => 'boolean',
        // 'branch_id' => 'int',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($kitchen) {
            if(empty($kitchen->id)) {
                $loggedUser = \Auth::user();
                $kitchen->id = Helper::GenerateId($loggedUser, 'branch_kitchens');
            }
        });
    }
}
