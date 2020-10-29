<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class InventoryItem extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'itemName', 'unitId', 'description', 'pricePerUnit', 'isActive', 'branch_id'
    ];


    protected $casts = [
        'branch_id' => 'int',
    ];

    public function unit() {
        return $this->belongsTo('App\MeasureUnit', 'unitId');
    }

    
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
} 
