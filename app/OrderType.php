<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrderType extends Model
{
    protected $fillable = [
        'typeName', 'description', 'enableTables', 'enableExtraInfo', 'enableDeliverCharge', 'enableExtraCharge', 'isActive', 'branch_id'
    ];

    

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->branch_id = $user->branch_id;
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function tables()
    {
        return $this->hasMany('App\TableManager', 'orderTypeId');
    }
}
