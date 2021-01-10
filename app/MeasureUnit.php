<?php

namespace App;

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
        'company_id',
        'isActive',
        'isSync'
    ];
    


    protected $casts = [
        // 'company_id' => 'int',
        'isSync' => 'boolean',
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
            $loggedUser = \Auth::user();
            if($loggedUser->roles != 'Super Admin') {
                $item->company_id = $loggedUser->company_id;
            }
            $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
            $item->id = IdGenerator::generate(['table' => 'measure_units', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
        });

        
        static::updating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->company_id = $user->company_id;
            }
        });
    }
}
