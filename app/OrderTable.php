<?php

namespace App;

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
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $item->id = IdGenerator::generate(['table' => 'order_tables', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
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
