<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MeasureUnit extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'unitLabel', 'description', 'branch_id'
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
}
