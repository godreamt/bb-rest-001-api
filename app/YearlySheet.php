<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YearlySheet extends Model
{
    protected $fillable = [
        'amountBrought',
        'fromDate',
        'toDate',
        'amountCarried',
        'company_id',
        'branch_id'
    ];
    
    protected $casts = [
        'amountBrought' => 'double',
        'fromDate' => 'date',
        'toDate' => 'date',
        'amountCarried' => 'double',
        'company_id' => 'int',
        'branch_id' => 'int',
    ];
}
