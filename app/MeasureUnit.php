<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MeasureUnit extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'unitLabel', 
        'description', 
        'company_id',
        'isActive'
    ];
    


    protected $casts = [
        'company_id' => 'int',
        'isActive' => 'boolean'
    ];

    
    
    public function company()
    {
        return $this->belongsTo('App\Company', 'company_id');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('company_id',  $user->company_id);
            }
        });

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });

        
        static::updating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });
    }
}
