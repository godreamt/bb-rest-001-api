<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class UserAttendance extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';
    

    protected $fillable = [
        'id',
        'effectedDate', 
        'isPresent', 
        'description', 
        'user_id', 
        'isSync'
    ];

    
    protected $casts = [
        'isPresent' => 'boolean',
        'isSync' => 'boolean'
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if(empty($item->id)) {
                $loggedUser = \Auth::user();
                $item->id = Helper::GenerateId($loggedUser, 'user_attendances');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
