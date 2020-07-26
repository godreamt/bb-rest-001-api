<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TableManager extends Model
{
    protected $fillable = [
        'tableId', 'description', 'noOfChair', 'bookedChairs', 'isReserved', 'isActive', 'orderTypeId', 'chairs', 'branch_id'
    ];

    protected $appends = ['chairs'];

    public function getChairsAttribute()
    {
        return range(1, $this->noOfChair, 1);
    }
    
    public function orderType()
    {
        return $this->belongsTo('App\OrderType', 'orderTypeId');
    }

    

    protected static function boot()
    {
        parent::boot();

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->branch_id = $user->branch_id;
            }
        });

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });
    }
}
