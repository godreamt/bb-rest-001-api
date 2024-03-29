<?php

namespace App;

use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class MeasureUnit extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    public $timestamps = false;
    protected $fillable = [
        'unitLabel', 
        'description', 
        'branch_id',
        'isActive',
        'isSync'
    ];
    


    protected $casts = [
        // 'branch_id' => 'int',
        'isSync' => 'boolean',
        'isActive' => 'boolean'
    ];

    
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }
    
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('measure_units.branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                $item->branch_id = $loggedUser->branch_id;
            }
            $item->id = Helper::GenerateId($loggedUser, 'measure_units');
        });

        
        static::updating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $item->branch_id = $user->branch_id;
            }
        });
    }
}
