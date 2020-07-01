<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customerName', 'mobileNumber', 'emailId', 'branchId'
    ];
}
