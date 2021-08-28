<?php

namespace App;

use App\User;
use App\Branch;
use App\helper\Helper;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class MonthSheet extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'amountBrought',
        'totalMonthlyIncome',
        'totalMonthlyExpense',
        'amountCarried',
        'month',
        'year',
        'branch_id',
        'yearly_sheet_id',
        'isSync'
    ];
    
    protected $casts = [
        'month' => 'int',
        'year' => 'int',
        'amountBrought' => 'double',
        'totalMonthlyIncome' => 'double',
        'totalMonthlyExpense' => 'double',
        'amountCarried' => 'double',
        'isSync' => 'boolean',
        // 'branch_id' => 'int',
        // 'yearly_sheet_id' => 'int',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('month_sheets.branch_id',  $user->branch_id);
            }
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin') {
                $builder->where('month_sheets.branch_id',  $user->branch_id);
            }
        });

        
        static::updating(function ($monthlySheet) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $monthlySheet->branch_id = $loggedUser->branch_id;
                }
            }
        });

        static::creating(function ($monthlySheet) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                    $monthlySheet->branch_id = $loggedUser->branch_id;
                }
            }
            $monthlySheet->id = Helper::GenerateId($loggedUser, 'month_sheets');
        });
    }
}
