<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;

class BranchRoom extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id', 
        'roomName', 
        'withAc',
        'serveLiquor',
        'branch_id', 
        'isActive', 
        'isSync'
    ];


    protected $casts = [
        'isSync' => 'boolean',
        'isActive' => 'boolean',
        'withAc' => 'boolean',
        'serveLiquor' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($room) {
            if(empty($room->id)) {
                $loggedUser = \Auth::user();
                $room->id = Helper::GenerateId($loggedUser, 'branch_rooms');
            }
        });
    }
}
