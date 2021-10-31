<?php

namespace App;

use App\User;
use App\Branch;
use App\helper\Helper;
use Panoscape\History\HasHistories;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Order extends Model
{
    protected $primaryKey = 'id'; // or null

    public $incrementing = false;

    // In Laravel 6.0+ make sure to also set $keyType
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'customerId',
        'relatedInfo',
        'customerAddress',
        'company_id',
        'branch_id',
        'cgst',
        'sgst',
        'igst',
        'orderAmount',
        'packingCharge',
        'discountReason',
        'discountValue',
        'finalisedBy',
        'finalisedDate',
        'excludeFromReport',
        'deliverCharge',
        'orderStatus',
        'takenBy',
        'taxDisabled',
        'taxPercent',
        'orderType',
        'paymentMethod',
        'isPaid',
        'roundOfAmount',
        'orderComboTotal',
        'isSync'
    ];


    protected $casts = [
        'taxDisabled' => 'boolean',
        'isSync' => 'boolean',
        'taxPercent' => 'float',
        'roundOfAmount' => 'float',
        'orderAmount' => 'float'
    ];
    // protected $appends = ['order_ready_count'];

    // public function getOrderReadyCountAttribute() {
    //     return 5;
    // }


    protected static function boot()
    {
        parent::boot();


        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user instanceof User) {
                if($user->roles != 'Super Admin') {
                    $builder->where('orders.company_id',  $user->company_id);
                }
                if($user->roles != 'Super Admin' && $user->roles != 'Company Admin' && $user->roles != 'Company Accountant') {
                    $builder->where('orders.branch_id',  $user->branch_id);
                }
            }
        });


        static::updating(function ($order) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $order->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $order->branch_id = $loggedUser->branch_id;
                }
            }
            $branch = Branch::find($order->branch_id);
            $order->company_id = $branch->company_id;
        });

        static::creating(function ($order) {

            $loggedUser = \Auth::user();
            if($loggedUser instanceof User) {
                // throw new ValidationException('test the code first');
                if($loggedUser->roles != 'Super Admin') {
                    $order->company_id = $loggedUser->company_id;
                }
                if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                    $order->branch_id = $loggedUser->branch_id;
                }
                $branch = Branch::find($order->branch_id);
                $order->company_id = $branch->company_id;
                $order->takenBy = $loggedUser->id;
                if(empty($order->id)) {
                    $order->id = Helper::GenerateId($loggedUser, 'orders');
                }
            }
        });
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer', 'customerId');
    }

    public function branch()
    {
        return $this->belongsTo('App\Branch', 'branch_id');
    }

    public function orderitems()
    {
        return $this->hasMany('App\OrderItem', 'orderId');
    }

    public function orderItemCombos()
    {
        return $this->hasMany('App\OrderItemCombo', 'orderId');
    }

    public function orderTables()
    {
        return $this->hasMany('App\OrderTable', 'orderId');
    }

    public function orderType()
    {
        return $this->belongsTo('App\BranchOrderType', 'orderType');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('App\BranchPaymentMethods', 'paymentMethod');
    }

    public function bearer()
    {
        return $this->belongsTo('App\User', 'takenBy');
    }

    public function getModelLabel()
    {
        return $this->id;
    }
}
