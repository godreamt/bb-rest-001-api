<?php

namespace App;

use App\User;
use App\Branch;
use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class YearlySheet extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'amountBrought',
        'fromDate',
        'toDate',
        'amountCarried',
        'branch_id',
        'isSync'
    ];
    
    protected $casts = [
        'amountBrought' => 'double',
        'fromDate' => 'date',
        'toDate' => 'date',
        'amountCarried' => 'double',
        'isSync' => 'boolean'
    ];
    
    protected static function boot() {
        parent::boot();

        // static::addGlobalScope('role_handler', function (Builder $builder) {
        //     $user = \Auth::user();
           
        //     if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' ) {
        //         $builder->where('yearly_sheets.branch_id',  $user->branch_id);
        //     }
        // });

        
        static::updating(function ($yearlySheet) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $yearlySheet->branch_id = $loggedUser->branch_id;
                }
            }          
           
        });

        static::creating(function ($yearlySheet) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' ) {
                    $yearlySheet->branch_id = $loggedUser->branch_id;
                }
            }
            if(empty($yearlySheet->id)) {
                $yearlySheet->id = Helper::GenerateId($loggedUser, 'yearly_sheets');
            }
        });
    }
}
