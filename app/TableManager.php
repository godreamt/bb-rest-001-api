<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TableManager extends Model
{
    protected $fillable = [
        'tableId', 'description', 'noOfChair', 'bookedChairs', 'isReserved', 'isActive', 'chairs', 'branch_id'
    ];

    
    protected $casts = [
        'branch_id' => 'int',
        'isActive' => 'boolean',
        'isReserved' => 'boolean',
    ];

    protected $appends = ['chairs'];

    public function getChairsAttribute()
    {
        return range(1, $this->noOfChair, 1);
    }
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
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
