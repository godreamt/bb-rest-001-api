<?php

namespace App;

use App\User;
use App\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MonthSheet extends Model
{
    protected $fillable = [
        'amountBrought',
        'totalMonthlyIncome',
        'totalMonthlyExpense',
        'amountCarried',
        'month',
        'year',
        'company_id',
        'branch_id',
        'yearly_sheet_id',
    ];
    
    protected $casts = [
        'month' => 'int',
        'year' => 'int',
        'amountBrought' => 'double',
        'totalMonthlyIncome' => 'double',
        'totalMonthlyExpense' => 'double',
        'amountCarried' => 'double',
        'company_id' => 'int',
        'branch_id' => 'int',
        'yearly_sheet_id' => 'int',
    ];

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('company_id',  $user->company_id);
            }
            if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        
        static::updating(function ($monthlySheet) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $monthlySheet->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $monthlySheet->branch_id = $loggedUser->branch_id;
                }
            }
            if(empty($monthlySheet->company_id)) {
                $branch = Branch::find($monthlySheet->branch_id);
                if($branch instanceof Branch) {
                    $monthlySheet->company_id = $branch->company_id;
                }
            }
        });

        static::creating(function ($monthlySheet) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $monthlySheet->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $monthlySheet->branch_id = $loggedUser->branch_id;
                }
            }
            if(empty($monthlySheet->company_id)) {
                $branch = Branch::find($monthlySheet->branch_id);
                if($branch instanceof Branch) {
                    $monthlySheet->company_id = $branch->company_id;
                }
            }
        });
    }
}
