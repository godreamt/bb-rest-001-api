<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    protected $fillable = [
        'customerId', 'relatedInfo', 'branch_id', 'orderTypeId', 'cgst', 'sgst', 'igst', 'orderAmount', 'packingCharge', 'extraCharge', 'excludeFromReport', 'deliverCharge', 'orderStatus', 'takenBy'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('role_handler', function (Builder $builder) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $builder->where('branch_id',  $user->branch_id);
            }
        });

        
        static::creating(function ($item) {
            $user = \Auth::user();
            if($user->roles != 'Super Admin') {
                $item->branch_id = $user->branch_id;
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
    
    public function orderType()
    {
        return $this->belongsTo('App\OrderType', 'orderTypeId');
    }
    
    public function orderitems()
    {
        return $this->hasMany('App\OrderItem', 'orderId');
    }
    
    public function orderTables()
    {
        return $this->hasMany('App\OrderTable', 'orderId');
    }
}
