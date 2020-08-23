<?php

namespace App\Http\Controllers;

use App\Order;
use App\Customer;
use App\OrderItem;
use App\OrderType;
use App\OrderTable;
use App\TableManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class OrderController extends Controller
{
    public function getOrderTypes(Request $request) {
        
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $orderTypes = OrderType::select($fields)->with('branch');

        if(!empty($request->searchString)) {
            $orderTypes = $orderTypes->where('branchTitle', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->status)) {
            $orderTypes = $orderTypes->where('isActive', ($request->status == 'active')?true:false);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $orderTypes->paginate(10);
        }else {
            return $orderTypes->get();
        }
    }

    public function getOrderTypeDetails(Request $request, $id) {
        return OrderType::with('branch')->with('tables')->find($id);
    }

    public function createOrderType(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                $orderType = new OrderType();
                $orderType->typeName = $request->typeName;
                $orderType->description = $request->description;
                $orderType->enableTables = $request->enableTables?true:false;
                $orderType->enableExtraInfo = $request->enableExtraInfo?true:false;
                $orderType->enableDeliverCharge = $request->enableDeliverCharge?true:false;
                $orderType->enableExtraCharge = $request->enableExtraCharge?true:false;
                $orderType->branch_id = $request->branch_id;
                $orderType->isActive =$request->isActive?true:false;
                $orderType->save();
                foreach ($request->tables as $table) {
                    $table['isActive'] = true;
                    $orderType->tables()->create($table);
                }
                return $orderType;
            }catch(\Exception $e) {
                return response()->json(['msg' => $e], 400);
            }
        });
    }

    public function updateOrderType(Request $request, $id) {
        return \DB::transaction(function() use($request) {
            try {
                $orderType = OrderType::find($request->id);
                $orderType->typeName = $request->typeName;
                $orderType->description = $request->description;
                $orderType->enableTables = $request->enableTables?true:false;
                $orderType->enableExtraInfo = $request->enableExtraInfo?true:false;
                $orderType->enableDeliverCharge = $request->enableDeliverCharge?true:false;
                $orderType->enableExtraCharge = $request->enableExtraCharge?true:false;
                $orderType->branch_id = $request->branch_id;
                $orderType->isActive = $request->isActive?true:false;
                foreach ($request->tables as $table) {
                    if($table['deletedFlag'] == true) {
                        $t = TableManager::find($table['id']);
                        $t->delete();
                    }else if(empty($table['id'])){
                        unset($table['deletedFlag']);
                        $table['isActive'] = true;
                        $orderType->tables()->create($table);
                    }else {
                        unset($table['deletedFlag']);
                        $table['isActive'] = true;
                        $t1 = TableManager::find($table['id']);
                        $t1->tableId = $table['tableId'];
                        $t1->description = $table['description'];
                        $t1->noOfChair = $table['noOfChair'];
                        $t1->save();
                    }
                }
                $orderType->save();
                return $orderType;
            }catch(\Exception $e) {
                return response()->json(['msg' => $e], 400);
            }
        });
    }

    public function deleteOrderType(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $orderType = OrderType::find($id);
                if($orderType instanceof OrderType) {
                    $orderType->delete();
                    return ['data' => $orderType, 'msg'=> "Order Type deleted successfully"];
                }else {
                    return response()->json(['msg' => 'Order Type Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete order type', 'error'=> $e], 400);
            }
        });
    }

    public function changeOrderTypeStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $orderType = OrderType::find($id);
                if($orderType instanceof OrderType) {
                    $orderType->isActive = $request->isActive;
                    $orderType->save();
                    return ['data' => $orderType, 'msg'=> "Order type status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Order type Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Order type status can not changed'], 400);
            }
        });
    }

    public function getOrderTypeWithTableOccupy(Request $request) {
        $result = null;
        if(!empty($request->orderTypeId)) {
            $result = $this->handleTableOccupy(OrderType::find($request->orderTypeId), $request->orderId);
        }else {
            $orderTypes = OrderType::where('isActive', true)->where('enableTables', true)->get();
            foreach($orderTypes as $orderType) {
                $orderType['tables'] = $this->handleTableOccupy($orderType, $request->orderTypeId);
            }
            $result = $orderTypes;
        }

        return $result;
    }

    public function handleTableOccupy($orderType, $orderId=null) {
        
        $orderTables = OrderTable::leftJoin('orders', 'orders.id', 'order_tables.orderId')
            ->where(function($q) use ($orderId) {
                $q->where('orders.orderStatus', 'new')
                    ->orWhere('orders.orderStatus', 'prepairing')
                    ->orWhere('orders.id', $orderId);
            })
            ->where('orders.orderTypeId', $orderType->id)
            ->select('order_tables.selectedChairs', 'order_tables.orderId', 'order_tables.tableId')
            ->distinct()->get();

        $tables = TableManager::where('isActive', true)
                ->where('orderTypeId', $orderType->id)
                ->get();
                
        foreach($tables as $table) {
            $selectedChairs="";
            $orderSelectedChairs="";
            foreach($orderTables as $ot) {
                if($ot->tableId == $table->id) {
                    if($ot->orderId == $orderId) {
                        $orderSelectedChairs = $orderSelectedChairs.$ot->selectedChairs.",";
                    }

                    $selectedChairs = $selectedChairs.$ot->selectedChairs.",";
                    
                }
            }

            $table['orderSelectedChairs']=$orderSelectedChairs;
            $table['selectedChairs']=$selectedChairs;
        }

        return $tables;
    }

    public function getOrderList(Request $request) {
        $currentUser = \Auth::user();
        $fields = $request->get('fields', 'orders.*');
        if($fields != 'orders.*'){
            $fields = explode(',',$fields);
        }
        $orders = Order::with('customer')->select($fields)->with('branch')->with('orderType')
                        ->leftJoin('order_types', 'order_types.id', 'orders.orderTypeId');

        if(!empty($request->searchString)) {
            $orders = $orders->where(function($q) use ($request) {
                $q->where('orders.id', 'LIKE', '%'.$request->searchString.'%')
                  ->orWhere('order_types.typeName', 'LIKE', '%'.$request->searchString.'%');
            });
        }

        if(!empty($request->orderStatus)) {
            $orderStatus = \explode(",",$request->orderStatus);
            $orders = $orders->whereIn('orders.orderStatus', $orderStatus);
        }

        if(!empty($request->typeOfOrder)) {
            $typeOfOrder = \explode(",",$request->typeOfOrder);
            $orders = $orders->whereIn('order_types.id', $typeOfOrder);
        }

        if(!empty($request->startDate) && !empty($request->endDate)) {
            $endDate = (new \Datetime($request->endDate))->modify('+1 day');
            $orders = $orders->whereBetween('orders.created_at', [new \Datetime($request->startDate), $endDate]);
        }

        if(!empty($request->status)) {
            $orders = $orders->where('isActive', $request->status);
        }


        if($request->showAll != 'active' && $currentUser->roles == "Order Manager") {
            $orders = $orders->where('takenBy', $currentUser->id);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $orderCol = $request->orderCol;
            if($request->orderCol == 'id')$orderCol='orders.id';
            if($request->orderCol == 'orderType')$orderCol='order_types.typeName';
            if($request->orderCol == 'created_at')$orderCol='orders.created_at';
            if($request->orderCol == 'updated_at')$orderCol='orders.updated_at';
            $orders = $orders->orderBy($orderCol, $request->orderType);
        }else {
            $orders = $orders->orderBy('orders.updated_at', 'asc');
        }

        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $orders->paginate(10);
        }else {
            return $orders->get();
        }
    }

    public function getOrderDetails(Request $request, $id) {
        return Order::with('branch')->with('customer')->with('orderType')->with('orderTables')->with('orderItems')
                    ->where('id', $id)->first();
    }

    public function makeNewOrder(Request $request) {
        try {
                return DB::transaction(function() use ($request) {
                        $order = new Order();
                        $orderType = OrderType::find($request->orderTypeId);
                        $order->orderTypeId = $orderType->id;
                        $order->branch_id = $orderType->branch_id;
                        if(!empty($request->mobileNumber)) {
                            $customer = $this->handleCustomerCreation($request->all(), $orderType->branch_id);
                            $order->customerId = $customer->id;
                        }
                        $order->relatedInfo = $request->relatedInfo;
                        $order->cgst = $request->cgst;
                        $order->sgst = $request->sgst;
                        $order->igst = 0;
                        $order->packingCharge = $request->packingCharge;
                        $order->deliverCharge = $request->deliverCharge;
                        $order->orderStatus = $request->orderStatus;
                        $order->orderItemTotal = $request->orderItemTotal ?? '0'; 
                        $order->orderAmount = $request->orderAmount ?? '0';


                        $order->save();


                        foreach($request->items as $item) {
                            $orderItem = new OrderItem();
                            $orderItem->quantity = $item['quantity'];
                            $orderItem->price = $item['price'];
                            $orderItem->packagingCharges = $item['packagingCharges'];
                            $orderItem->totalPrice = $item['totalPrice'];
                            $orderItem->productId = $item['productId'];
                            $orderItem->orderId = $order->id;
                            $orderItem->itemStatus = 'new';
                            $orderItem->save();
                        }
                        foreach($request->tables as $table) {
                            $orderTable = new OrderTable();
                            $orderTable->tableId = $table['id'];
                            $orderTable->selectedChairs = $table['chairs'];
                            $orderTable->orderId = $order->id;
                            $orderTable->save();
                        }

                        return $order;
                });
        }catch(\Exception $e) {
            return response()->json(['msg' => 'Can not able to create', 'error'=>$e], 400);
        }
    }

    public function updateOrder(Request $request, $orderId) {
        try {
                return DB::transaction(function() use ($request, $orderId) {
                        $order = Order::find($orderId);
                        $orderType = OrderType::find($request->orderTypeId);
                        $order->orderTypeId = $orderType->id;
                        $order->branch_id = $orderType->branch_id;
                        if(!empty($request->mobileNumber)) {
                            $customer = $this->handleCustomerCreation($request->all(), $orderType->branch_id);
                            $order->customerId = $customer->id;
                        }
                        $order->relatedInfo = $request->relatedInfo;
                        $order->cgst = $request->cgst;
                        $order->sgst = $request->sgst;
                        $order->igst = 0;
                        $order->packingCharge = $request->packingCharge;
                        $order->deliverCharge = $request->deliverCharge;
                        $order->orderStatus = $request->orderStatus;
                        // $order->orderItemTotal = $request->orderItemTotal;
                        $order->orderAmount = $request->orderAmount;
                        
                        
                        $order->save();
                        
                        
                        foreach($request->items as $item) {
                            if(!empty($item['quantity']) && !empty($item['productId'])){
                                if(empty($item['id'])) {
                                    $orderItem = new OrderItem();
                                }else {
                                    $orderItem = OrderItem::find($item['id']);
                                }
                                $orderItem->quantity = $item['quantity'];
                                $orderItem->price = $item['price'];
                                $orderItem->packagingCharges = $item['packagingCharges'];
                                $orderItem->totalPrice = $item['totalPrice'];
                                $orderItem->productId = $item['productId'];
                                $orderItem->orderId = $order->id;
                                $orderItem->itemStatus = 'new';
                                $orderItem->save();
                            }
                        }
                        $order->orderTables()->delete();
                        // return $orderType;
                        foreach($request->tables as $table) {
                            $orderTable = new OrderTable();
                            $orderTable->tableId = $table['id'];
                            $orderTable->selectedChairs = $table['chairs'];
                            $orderTable->orderId = $order->id;
                            $orderTable->save();
                        }

                        return $order;
                });
        }catch(\Exception $e) {
            return response()->json(['msg' => 'Can not able to update', 'error'=>$e], 400);
        }
    }

    public function handleCustomerCreation($request, $branch_id) {
        $mobileNumber = $request['mobileNumber'];
        $customer = Customer::where('mobileNumber', $mobileNumber)->first();
        if(!$customer instanceof Customer) {
            $customer = new Customer();
            $customer->branch_id = $branch_id;
            $customer->mobileNumber = $mobileNumber;
        }
        $customer->customerName = $request['customerName'];
        $customer->emailId = $request['emailId'] ?? "";
        $customer->save();
        return $customer;
    }
}
