<?php

namespace App;

use App\helper\Helper;
use Panoscape\History\HasHistories;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class OrderTable extends Model
{
    
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'orderId', 
        'tableId', 
        'selectedChairs', 
        'isSync'
    ];

    
    protected $casts = [
        'isSync' => 'boolean',
        // 'orderId' => 'int',
        // 'tableId' => 'int'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if(empty($item->id)) {
                $loggedUser = \Auth::user();
                $item->id = Helper::GenerateId($loggedUser, 'order_tables');
            }
        });
    }

    public function table() {
        return $this->belongsTo('App\TableManager', 'tableId');
    }

    public function getModelLabel()
    {
        return "Table";
    }
}
