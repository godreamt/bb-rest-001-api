<?php

namespace App;

use App\Branch;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Validation\ValidationException;

class TableManager extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'tableId', 'description', 'noOfChair', 'bookedChairs', 'isReserved', 'isActive', 'branch_id', 'isSync'
    ];

    
    protected $casts = [
        // 'branch_id' => 'int',
        'isActive' => 'boolean',
        'isSync' => 'boolean',
        'isReserved' => 'boolean',
    ];

    protected $appends = ['chairs'];

    public function getChairsAttribute()
    {
        return range(1, $this->noOfChair, 1);
    }
    
    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    protected static function boot()
    {
        parent::boot();

        
        static::addGlobalScope('role_handler', function (Builder $builder) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $builder->where('table_managers.company_id',  $loggedUser->company_id);
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $builder->where('table_managers.branch_id',  $loggedUser->branch_id);
                }
            }
        });

        static::creating(function ($tableManager) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $tableManager->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $tableManager->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($tableManager->branch_id);
            $tableManager->company_id = $branch->company_id;

            if(empty($tableManager->id)) {
                $prefix = Config::get('app.hosted') . substr(($loggedUser->company_id ?? ""), -3) . substr(($loggedUser->branch_id ?? ""), -3);
                $tableManager->id = IdGenerator::generate(['table' => 'table_managers', 'length' => 20, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
            }
        });
        
        
        static::updating(function ($tableManager) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $tableManager->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $tableManager->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($tableManager->branch_id);
            $tableManager->company_id = $tableManager->company_id;
        });

    }
}
