<?php

namespace App\Http\Controllers;

use App\Order;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    public function getKitchenData(Request $request, $branch) {
        $yeterday = (new \Datetime())->modify('-1 day');
        $tommorow = (new \Datetime())->modify('+1 day');
        $orders = Order::with('branch')->with('orderTables')->with('orderItems')->with('orderItems.product')
                        ->whereBetween('orders.created_at', [$yeterday, $tommorow])
                        ->where(function($q) {
                            $q->where('orders.orderStatus', 'new')
                            ->orWhere('orders.orderStatus', 'accepted')
                            ->orWhere('orders.orderStatus', 'prepairing');
                        })
                        ->where('branch_id', $branch)->get();
        $pendingOrders=[];
        $items=[];
        foreach($orders as $order) {
            $pendingItems=[];
            foreach($order->orderItems as $item) {
                $servedItems = (int) $item->servedQuantity ?? 0;
                $quantity = (int)$item->quantity;
                if($servedItems < $quantity) {
                    $item['pendingQuantity'] = $quantity - $servedItems;
                    $pendingItems[] = $item;
                    $parcelQuantity = ($item['orderType'] != 'on-table')?$item['pendingQuantity']:0;
                    $quantity = ($item['orderType'] == 'on-table')?$item['pendingQuantity']:0;
                    if(empty($items[$item['product']['id']])) {
                        
                        $items[$item['product']['id']] = [
                            'quantity' =>  $quantity,
                            'parcelQuantity' => $parcelQuantity,
                            'product' => $item['product']
                        ];
                    }else {
                        $items[$item['product']['id']]['quantity'] = $items[$item['product']['id']]['quantity'] +  $quantity;
                        $items[$item['product']['id']]['parcelQuantity'] = $items[$item['product']['id']]['parcelQuantity'] + $parcelQuantity;
                    }
                }
            }
            if(sizeof($pendingItems) > 0) {
                $order->order_items = $pendingItems;
                $pendingOrders[]=$order;
            }
        }
        $resItems = [];
        foreach($items as $item) {
            $resItems[] = $item;
        }

        return [
            'orders'=> $pendingOrders,
            'items' => $resItems
        ];
    }
}
