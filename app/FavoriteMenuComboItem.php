<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Model;

class FavoriteMenuComboItem extends Model
{

    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'menu_id',
        'comboId',
        'isSync'
    ];

    protected $casts = [
        'isSync' => 'boolean',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if(empty($item->id)) {
                $item->id = Helper::GenerateId($loggedUser, 'favorite_menu_combo_items');
            }
        });

    }

    public function product() {
        return $this->belongsTo('App\ProductCombo', 'comboId');
    }
}

