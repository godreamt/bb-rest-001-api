<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BranchOrderType extends Model
{
    protected $fillable = [
        'orderType', 'tableRequired', 'isActive', 'branch_id'
    ];


    protected $casts = [
        'branch_id' => 'int',
        'isActive' => 'boolean',
        'tableRequired' => 'boolean'
    ];
}
