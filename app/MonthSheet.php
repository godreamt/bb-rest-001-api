<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
