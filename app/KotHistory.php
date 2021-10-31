<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KotHistory extends Model
{

    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'kotNumber',
        'kotMeta',
        'status',
        'orderId',
        'isSync'
    ];


    protected $casts = [
        'isSync' => 'boolean'
    ];


    protected static function boot()
    {
        parent::boot();


        static::creating(function ($menu) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if(empty($menu->id)) {
                    $menu->id = Helper::GenerateId($loggedUser, 'kot_histories');
                }
            }
        });
    }

    public function order()
    {
        return $this->belongsTo('App\Order', 'orderId');
    }
}
