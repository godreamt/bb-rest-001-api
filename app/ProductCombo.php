<?php

namespace App;

use App\helper\Helper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductCombo extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'comboTitle',
        'description',
        'featuredImage',
        'company_id',
        'branch_id',
        'comboTotal',
        'packagingCharges',
        'canPriceAltered',
        'isActive',
        'isSync'
    ];
    protected $casts = [
        'isActive' => 'boolean',
        'canPriceAltered' => 'boolean',
        'isSync' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function items()
    {
        return $this->hasMany('App\ProductComboItem', 'combo_id');
    }

    protected static function boot() {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('product_combos.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('product_combos.branch_id',  $user->branch_id);
                }
            }
        });


        static::updating(function ($productCombo) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $productCombo->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $productCombo->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($productCombo->branch_id);
            $productCombo->company_id = $productCombo->company_id;
        });

        static::creating(function ($productCombo) {
            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                if($loggedUser->roles != 'Super Admin') {
                    $productCombo->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $productCombo->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($productCombo->branch_id);
            $productCombo->company_id = $branch->company_id;


            if(empty($productCombo->id)) {
                $productCombo->id = Helper::GenerateId($loggedUser, 'product_combos');
            }

        });
    }
}
