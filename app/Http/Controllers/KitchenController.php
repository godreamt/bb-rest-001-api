<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderItem;
use App\BranchOrderType;
use Illuminate\Http\Request;

class KitchenController extends Controller
{

    public function getKitchenData(Request $request) {
        $kitchenId = $request->kitchen_id;
        $branchId = $request->branch_id;
        $yeterday = (new \Datetime())->modify('-1 day');
        $tommorow = (new \Datetime())->modify('+1 day');
        $orders = Order::with('branch')->with('bearer')->with('orderTables')->with('orderTables.table')->with('orderItems')->with('orderItems.product')
                        ->whereBetween('orders.created_at', [$yeterday, $tommorow])
                        ->where(function($q) {
                            $q->where('orders.orderStatus', 'new')
                            ->orWhere('orders.orderStatus', 'accepted')
                            ->orWhere('orders.orderStatus', 'prepairing');
                        })
                        ->where('branch_id', $branchId)->get();
        $readyToAcceptOrders=[];
        $acceptedOrders=[];
        $items=[];
        $pendingOrders=[];
        // return $kitchenId;
        foreach($orders as $order) {
            $orderType = BranchOrderType::find($order->orderType);
            $pendingItems=[];
            foreach($order->orderItems as $item) {
                if($item['product']['kitchen_id'] == $kitchenId) {
                    $item['isParcel'] = ($orderType->tableRequired)?$item['isParcel']:true;
                    $quantity = (int)$item->quantity;
                    $servedItems = (int) $item->servedQuantity ?? 0;
                    $productionAcceptedQuantity = (int) $item->productionAcceptedQuantity ?? 0;
                    $productionReadyQuantity = (int) $item->productionReadyQuantity ?? 0;
                    $productionRejectedQuantity = (int) $item->productionRejectedQuantity ?? 0;
                    if($servedItems < $quantity) {
                        $item['acceptPendingQuantity'] = $quantity -  $productionAcceptedQuantity - $productionRejectedQuantity;
                        $item['readyPendingQuantity'] = $productionAcceptedQuantity -  $productionReadyQuantity;
                        $pendingItems[] = $item;
                        $parcelQuantity = ($item['isParcel'])?$item['readyPendingQuantity']:0;
                        $quantity = (!$item['isParcel'])?$item['readyPendingQuantity']:0;
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
            }
            if(sizeof($pendingItems) > 0) {
                $order->order_items = $pendingItems;
                $order['timeDif']=(new \Datetime())->diff(new \Datetime($order->created_at));
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


    public function updateKitchenStatus(Request $request) {
        try {
            return \DB::transaction(function() use($request) {
                $type = $request->type;
                foreach($request->orderItems as $elem) {
                    $item = OrderItem::find($elem['id']);
                    $updateQuantity = $elem['quantity'];

                    
                    $quantity = (int)$item->quantity;
                    $servedItems = (int) $item->servedQuantity ?? 0;
                    $productionAcceptedQuantity = (int) $item->productionAcceptedQuantity ?? 0;
                    $productionReadyQuantity = (int) $item->productionReadyQuantity ?? 0;
                    $productionRejectedQuantity = (int) $item->productionRejectedQuantity ?? 0;
                    if($servedItems < $quantity) {
                        $acceptPendingQuantity = $quantity -  $productionAcceptedQuantity - $productionRejectedQuantity;
                        $readyPendingQuantity = $productionAcceptedQuantity -  $productionReadyQuantity;
                    }else {
                        return response()->json(['All the items already served.'], 400);
                    }

                    if($type == 'accept') {
                        if($updateQuantity <= $acceptPendingQuantity) {
                            $item->productionAcceptedQuantity = $productionAcceptedQuantity + $updateQuantity;
                        }else {
                            return response()->json(['Incorrect data. Please check after reloading.'], 400);
                        }
                    }
                    if($type == 'reject') {
                        if($updateQuantity <= $acceptPendingQuantity) {
                            $item->productionRejectedQuantity = $productionRejectedQuantity + $updateQuantity;
                        }else {
                            return response()->json(['Incorrect data. Please check after reloading.'], 400);
                        }
                    }
                    if($type == 'ready') {
                        if($updateQuantity <= $readyPendingQuantity) {
                            $item->productionReadyQuantity = $productionReadyQuantity + $updateQuantity;
                        }else {
                            return response()->json(['Incorrect data. Please check after reloading.'], 400);
                        }
                    }
                    $item->save();
                }
                return $item;
            });
        }catch(\Exception $e) {
            return response()->json(['Could not able to update status'], 400);
        }
    }


    // public function getKitchenData(Request $request, $branch) {
    //     $yeterday = (new \Datetime())->modify('-1 day');
    //     $tommorow = (new \Datetime())->modify('+1 day');
    //     $orders = Order::with('branch')->with('orderTables')->with('orderItems')->with('orderItems.product')
    //                     ->whereBetween('orders.created_at', [$yeterday, $tommorow])
    //                     ->where(function($q) {
    //                         $q->where('orders.orderStatus', 'new')
    //                         ->orWhere('orders.orderStatus', 'accepted')
    //                         ->orWhere('orders.orderStatus', 'prepairing');
    //                     })
    //                     ->where('branch_id', $branch)->get();
    //     $pendingOrders=[];
    //     $items=[];
    //     foreach($orders as $order) {
    //         $pendingItems=[];
    //         foreach($order->orderItems as $item) {
    //             $servedItems = (int) $item->servedQuantity ?? 0;
    //             $quantity = (int)$item->quantity;
    //             if($servedItems < $quantity) {
    //                 $item['pendingQuantity'] = $quantity - $servedItems;
    //                 $pendingItems[] = $item;
    //                 $parcelQuantity = ($item['orderType'] != 'on-table')?$item['pendingQuantity']:0;
    //                 $quantity = ($item['orderType'] == 'on-table')?$item['pendingQuantity']:0;
    //                 if(empty($items[$item['product']['id']])) {
                        
    //                     $items[$item['product']['id']] = [
    //                         'quantity' =>  $quantity,
    //                         'parcelQuantity' => $parcelQuantity,
    //                         'product' => $item['product']
    //                     ];
    //                 }else {
    //                     $items[$item['product']['id']]['quantity'] = $items[$item['product']['id']]['quantity'] +  $quantity;
    //                     $items[$item['product']['id']]['parcelQuantity'] = $items[$item['product']['id']]['parcelQuantity'] + $parcelQuantity;
    //                 }
    //             }
    //         }
    //         if(sizeof($pendingItems) > 0) {
    //             $order->order_items = $pendingItems;
    //             $pendingOrders[]=$order;
    //         }
    //     }
    //     $resItems = [];
    //     foreach($items as $item) {
    //         $resItems[] = $item;
    //     }

    //     return [
    //         'orders'=> $pendingOrders,
    //         'items' => $resItems
    //     ];
    // }
}
