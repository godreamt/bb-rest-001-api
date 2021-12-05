<?php

namespace App\Http\Controllers;

use App\Order;
use App\Branch;
use App\OrderComboItemComment;
use App\OrderItemCombo;
use App\OrderItemComment;
use App\Product;
use App\Category;
use App\Customer;
use App\OrderItem;
use App\OrderTable;
use App\ProductCombo;
use App\TableManager;
use Illuminate\Http\Request;
use App\ProductAdvancedPricing;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class OrderController extends Controller
{
    public function getTableManger(Request $request)
    {

        $fields = $request->get('fields', '*');
        if ($fields != '*') {
            $fields = explode(',', $fields);
        }
        $tables = TableManager::select($fields)->with('branch')->with('room');

        if (!empty($request->searchString)) {
            $tables = $tables->where('tableId', 'LIKE', '%' . $request->searchString . '%');
        }

        if (!empty($request->companyId)) {
            $tables = $tables->where('company_id', $request->companyId);
        }

        if (!empty($request->branchId)) {
            $tables = $tables->where('branch_id', $request->branchId);
        }

        if (!empty($request->room_id)) {
            $tables = $tables->where('room_id', $request->room_id);
        }

        if (!empty($request->status)) {
            $tables = $tables->where('isActive', ($request->status == 'active') ? true : false);
        }
        if (!empty($request->orderCol) && !empty($request->orderType)) {
            $tables = $tables->orderBy($request->orderCol, $request->orderType);
        } else {
            $tables = $tables->orderBy('tableId', 'ASC');
        }
        $currentPage = $request->pageNumber;
        if (!empty($currentPage)) {
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $tables->paginate(10);
        } else {
            return $tables->get();
        }
    }

    public function updateTable(Request $request)
    {
        return \DB::transaction(function () use ($request) {
            try {
                if (empty($request->id)) {
                    $t1 = new TableManager();
                } else {
                    $t1 = TableManager::find($request->id);
                }
                $t1->branch_id = $request->branch_id;
                $t1->room_id = $request->room_id;
                $t1->tableId = $request->tableId;
                $t1->description = $request->description;
                $t1->noOfChair = $request->noOfChair;
                $t1->isActive = $request->isActive ?? false;
                $t1->isSync = false;
                $t1->save();
                return $t1;
            } catch (\Exception $e) {
                return response()->json(['msg' => $e], 400);
            }
        });
    }

    public function deleteTable(Request $request, $id)
    {
        return \DB::transaction(function () use ($request, $id) {
            try {
                $table = TableManager::find($id);
                if ($table instanceof TableManager) {
                    $table->delete();
                    return ['data' => $table, 'msg' => "Table deleted successfully"];
                } else {
                    return response()->json(['msg' => 'Table Does not exist'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['msg' => 'Can not delete table', 'error' => $e], 400);
            }
        });
    }

    // public function getOrderTypeDetails(Request $request, $id) {
    //     return OrderType::with('branch')->with('tables')->find($id);
    // }

    // public function createOrderType(Request $request) {
    //     return \DB::transaction(function() use($request) {
    //         try {
    //             $orderType = new OrderType();
    //             $orderType->typeName = $request->typeName;
    //             $orderType->description = $request->description;
    //             $orderType->enableTables = $request->enableTables?true:false;
    //             $orderType->enableExtraInfo = $request->enableExtraInfo?true:false;
    //             $orderType->enableDeliverCharge = $request->enableDeliverCharge?true:false;
    //             $orderType->enableExtraCharge = $request->enableExtraCharge?true:false;
    //             $orderType->branch_id = $request->branch_id;
    //             $orderType->isActive =$request->isActive?true:false;
    //             $orderType->save();
    //             foreach ($request->tables as $table) {
    //                 $table['isActive'] = true;
    //                 $table['branch_id']=$request->branch_id;
    //                 $orderType->tables()->create($table);
    //             }
    //             return $orderType;
    //         }catch(\Exception $e) {
    //             return response()->json(['msg' => $e], 400);
    //         }
    //     });
    // }

    public function updateTableManager(Request $request)
    {
        return \DB::transaction(function () use ($request) {
            try {
                // $orderType = OrderType::find($request->id);
                // $orderType->typeName = $request->typeName;
                // $orderType->description = $request->description;
                // $orderType->enableTables = $request->enableTables?true:false;
                // $orderType->enableExtraInfo = $request->enableExtraInfo?true:false;
                // $orderType->enableDeliverCharge = $request->enableDeliverCharge?true:false;
                // $orderType->enableExtraCharge = $request->enableExtraCharge?true:false;
                // $orderType->branch_id = $request->branch_id;
                // $orderType->isActive = $request->isActive?true:false;
                $tables = [];
                foreach ($request->tables as $table) {
                    if ($table['deletedFlag'] == true) {
                        $t = TableManager::find($table['id']);
                        $t->delete();
                    } else {
                        if (empty($table['id'])) {
                            $t1 = new TableManager();
                        } else {
                            $t1 = TableManager::find($table['id']);
                        }
                        $t1->branch_id = $table['branch_id'];
                        $t1->tableId = $table['tableId'];
                        $t1->description = $table['description'];
                        $t1->noOfChair = $table['noOfChair'];
                        $table['isActive'] = $table['isActive'] ?? false;
                        $t1->isSync = false;
                        $t1->save();
                        $tables[] = $t1;
                    }
                    // else if(empty($table['id'])){
                    //     unset($table['deletedFlag']);
                    //     $table['isActive'] = true;
                    //     TableManager::create($table);
                    //     $tables[] = $table;
                    // }else {
                    //     unset($table['deletedFlag']);
                    //     $table['isActive'] = true;
                    //     $t1 = TableManager::find($table['id']);
                    //     $t1->tableId = $table['tableId'];
                    //     $t1->description = $table['description'];
                    //     $t1->noOfChair = $table['noOfChair'];
                    //     $t1->save();
                    //     $tables[] = $t1;
                    // }
                }
                return $tables;
            } catch (\Exception $e) {
                return response()->json(['msg' => $e], 400);
            }
        });
    }

    // public function deleteOrderType(Request $request, $id) {
    //     return \DB::transaction(function() use($request, $id) {
    //         try {
    //             $orderType = OrderType::find($id);
    //             if($orderType instanceof OrderType) {
    //                 $orderType->delete();
    //                 return ['data' => $orderType, 'msg'=> "Order Type deleted successfully"];
    //             }else {
    //                 return response()->json(['msg' => 'Order Type Does not exist'], 400);
    //             }
    //         }catch(\Exception $e) {
    //             return response()->json(['msg' => 'Can not delete order type', 'error'=> $e], 400);
    //         }
    //     });
    // }

    // public function changeOrderTypeStatus(Request $request, $id) {
    //     return \DB::transaction(function() use($request, $id) {
    //         try {
    //             $orderType = OrderType::find($id);
    //             if($orderType instanceof OrderType) {
    //                 $orderType->isActive = $request->isActive;
    //                 $orderType->save();
    //                 return ['data' => $orderType, 'msg'=> "Order type status updated successfully"];
    //             }else {
    //                 return response()->json(['msg' => 'Order type Does not exist'], 400);
    //             }
    //         }catch(\Exception $e) {
    //             return response()->json(['msg' => 'Order type status can not changed'], 400);
    //         }
    //     });
    // }

    public function changeTableReserved(Request $request, $id)
    {
        return \DB::transaction(function () use ($request, $id) {
            try {
                $table = TableManager::find($id);
                if ($table instanceof TableManager) {
                    $table->isReserved = $request->isReserved;
                    $table->isSync = false;
                    $table->save();
                    return ['data' => $table, 'msg' => "Table manager reserved status updated successfully"];
                } else {
                    return response()->json(['msg' => 'Table manager Does not exist'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['msg' => 'Table manager reserved status can not changed'], 400);
            }
        });
    }

    public function getOrderTypeWithTableOccupy(Request $request)
    {
        $companyId = $request->get('company_id');
        $branchId = $request->get('branch_id');
        $user = \Auth::user();
        if ($user->roles != 'Super Admin') {
            $companyId = $user->company_id;

            if ($user->roles != 'Company Admin') {
                $branchId = $user->branch_id;
            }
        }

        $branches = Branch::select('branches.id', 'branches.branchTitle', 'branches.isActive', 'branches.company_id')->with('company')
            ->leftJoin('branch_order_types', 'branches.id', '=', 'branch_order_types.branch_id')
            ->where('branch_order_types.tableRequired', true)
            ->where('branches.company_id', $companyId)
            ->where('branches.isActive', true);

        if (!empty($branchId)) {
            $branches = $branches->where('branches.id', $branchId);
        }
        $branches = $branches->distinct()->get();

        $result = [];
        foreach ($branches as $branch) {

            $orderTables = OrderTable::leftJoin('orders', 'orders.id', 'order_tables.orderId')
                ->where(function ($q) use ($request) {
                    $q->where('orders.orderStatus', 'new')
                        ->orWhere('orders.orderStatus', 'prepairing')
                        ->orWhere('orders.id', $request->orderId);
                })
                ->where('orders.branch_id', $branch->id)
                ->select('order_tables.selectedChairs', 'order_tables.orderId', 'order_tables.tableId')
                ->distinct()->get();

            $tables = TableManager::with('room')->select('table_managers.id', 'table_managers.isActive', 'table_managers.description', 'isReserved', 'noOfChair', 'tableId', 'room_id', 'table_managers.branch_id')
                ->leftJoin('branches', 'table_managers.branch_id', 'branches.id')
                ->where('branches.id', $branch->id);

            if (!empty($request->showActive)) {
                $tables = $tables->where('table_managers.isActive', true);
            }
            $tables = $tables->orderBy('tableId', 'ASC')->get();
            foreach ($tables as $table) {
                $runningOrderIdList = [];
                $selectedChairs = "";
                $orderSelectedChairs = "";
                foreach ($orderTables as $ot) {
                    if ($ot->tableId == $table->id) {
                        if ($ot->orderId == $request->orderId) {
                            $orderSelectedChairs = $orderSelectedChairs . $ot->selectedChairs . ",";
                        }

                        $selectedChairs = $selectedChairs . $ot->selectedChairs . ",";
                        $runningOrderIdList[] = $ot->orderId;
                    }
                }

                $table['orderSelectedChairs'] = $orderSelectedChairs;
                $table['selectedChairs'] = $selectedChairs;
                $table['runningOrderIds'] = array_unique($runningOrderIdList);
            }
            $result[] = [
                'branch' => $branch,
                'tables' => $tables
            ];

            // if(!empty($request->orderId)) {
            //     $result = $result[0]['tables'];
            // }
        }

        return $result;
    }

//    public function handleTableOccupy($orderId=null) {
//
//        $orderTables = OrderTable::leftJoin('orders', 'orders.id', 'order_tables.orderId')
//            ->where(function($q) use ($orderId) {
//                $q->where('orders.orderStatus', 'new')
//                    ->orWhere('orders.orderStatus', 'prepairing')
//                    ->orWhere('orders.id', $orderId);
//            })
//            ->select('order_tables.selectedChairs', 'order_tables.orderId', 'order_tables.tableId')
//            ->distinct()->get();
//
//        $tables = TableManager::where('isActive', true)->get();
//        $runningOrderIdList = [];
//        foreach($tables as $table) {
//            $selectedChairs="";
//            $orderSelectedChairs="";
//            foreach($orderTables as $ot) {
//                if($ot->tableId == $table->id) {
//                    if($ot->orderId == $orderId) {
//                        $orderSelectedChairs = $orderSelectedChairs.$ot->selectedChairs.",";
//                    }
//
//                    $selectedChairs = $selectedChairs.$ot->selectedChairs.",";
//                    $runningOrderIdList[] = $ot->orderId;
//                }
//            }
//
//            $table['orderSelectedChairs']=$orderSelectedChairs;
//            $table['selectedChairs']=$selectedChairs;
//            $table['runningOrderIds']=$runningOrderIdList;
//        }
//
//        return $tables;
//    }

    public function getOrderList(Request $request)
    {
        $currentUser = \Auth::user();
        $fields = $request->get('fields', 'orders.*');
        if ($fields != 'orders.*') {
            $fields = explode(',', $fields);
        }
        $orders = Order::with('customer')->select($fields)->with('branch')->with('orderitems')->with('bearer')->with('orderTables')->with('orderTables.table')->with('orderType')->with('paymentMethod')
            ->leftJoin('users as bearer', 'orders.takenBy', 'bearer.id');

        if (!empty($request->searchString)) {
            $orders = $orders->where(function ($q) use ($request) {
                $q->where('orders.id', 'LIKE', '%' . $request->searchString . '%');
            });
        }


        if (!empty($request->orderStatus)) {
            $orderStatus = \explode(",", $request->orderStatus);
            $orders = $orders->whereIn('orders.orderStatus', $orderStatus);
        }

        if (!empty($request->startDate) && !empty($request->endDate) && $request->ongoing != 'true') {
            $endDate = (new \Datetime($request->endDate))->modify('+1 day');
            $orders = $orders->whereBetween('orders.created_at', [new \Datetime($request->startDate), $endDate]);
        }

        if (!empty($request->status)) {
            $orders = $orders->where('isActive', $request->status);
        }

        if (!empty($request->company_id)) {
            $orders = $orders->where('orders.company_id', $request->company_id);
        }

        if (!empty($request->branch_id)) {
            $orders = $orders->where('orders.branch_id', $request->branch_id);
        }

        if (!empty($request->typeOfOrder)) {
            $typeOfOrder = \explode(",", $request->typeOfOrder);
            $orders = $orders->whereIn('orders.orderType', $typeOfOrder);
        }

        if (!empty($request->paymentMethod)) {
            $paymentMethod = \explode(",", $request->paymentMethod);
            $orders = $orders->whereIn('orders.paymentMethod', $paymentMethod);
        }


        if ($request->showAll != 'active' && $currentUser->roles == "Bearer") {
            $orders = $orders->where('takenBy', $currentUser->id);
        }

        if (!empty($request->orderCol) && !empty($request->orderType)) {
            $orderCol = $request->orderCol;
            if ($request->orderCol == 'id') $orderCol = 'orders.id';
            if ($request->orderCol == 'created_at') $orderCol = 'orders.created_at';
            if ($request->orderCol == 'updated_at') $orderCol = 'orders.updated_at';
            if ($request->orderCol == 'bearer') $orderCol = 'bearer.firstName';
            $orders = $orders->orderBy($orderCol, $request->orderType);
        } else {
            $orders = $orders->orderBy('orders.updated_at', 'desc');
        }

        $currentPage = $request->pageNumber;
        if (!empty($currentPage)) {
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            $orders = $orders->paginate(10);
        } else {
            $orders = $orders->get();
        }
        foreach ($orders as $order) {
            $this->handleOrderDerivedData($order);
        }
        return response()->json($orders, 200);
    }

    public function handleOrderDerivedData($order)
    {

        $rejectedCount = 0;
        $readyCount = 0;
        foreach ($order['orderitems'] as $item) {
            $rejectedCount = $rejectedCount + ($item['productionRejectedQuantity']);
            $readyCount = $readyCount + ($item['productionReadyQuantity'] - $item['servedQuantity']);
        }
        unset($order['orderitems']);
        $order['rejectedCount'] = $rejectedCount;
        $order['readyCount'] = $readyCount;
        $order['timeDif'] = (new \Datetime())->diff(new \Datetime($order->created_at));
        return $order;
    }

    public function getOrderDetails(Request $request, $id)
    {
        $order = Order::with('branch')->with('customer')->with('orderTables')->with('orderTables.table')->with('orderItems')->with('orderItems.comments')->with('orderItems.product')->with('orderItemCombos')->with('orderItemCombos.comments')->with('orderItemCombos.productCombo')->with('branch')->with('bearer')->with('orderType')->with('paymentMethod')
            ->where('id', $id)->first();
        $this->handleOrderDerivedData($order);

        return $order;
    }


    public function updateOrder(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $loggedUser = \Auth::user();
                if (!empty($request->id)) {
                    $order = Order::find($request->id);
                } else {
                    $order = new Order();
                }
                $order->branch_id = $request->branch_id;
                if ($loggedUser->roles != 'Super Admin') {
                    $order->branch_id = $loggedUser->branch_id;
                }
                $order->orderType = $request->orderType;
                $order->paymentMethod = $request->paymentMethod;
                if (!empty($request->mobileNumber)) {
                    $customer = $this->handleCustomerCreation($request->all(), $order->branch_id);
                    $order->customerId = $customer->id;
                } else {
                    $order->customerId = null;
                }
                $order->customerAddress = $request->customerAddress;
                $order->relatedInfo = $request->relatedInfo;
                $order->discountReason = $request->discountReason;
                $order->discountValue = $request->discountValue ?? 0;
                $order->taxPercent = (float)$request->taxPercent;
                $order->isPaid = $request->isPaid == true ? 1 : 0;
                $order->taxDisabled = $request->taxDisabled == true ? 1 : 0;

                if ($request->orderStatus != $order->orderStatus && ($request->orderStatus == 'completed' || $request->orderStatus == 'cancelled')) {
                    $order->finalisedBy = $loggedUser->id;
                    $order->finalisedDate = new \Datetime();
                    $order->isPaid = 1;
                }

                $order->orderStatus = $request->orderStatus;

                if (empty($request->id)) {
                    $order->isSync = 0;
                    $order->save();
                }


                $totalOrderAmount = 0;
                $nonTaxableAmount = 0;
                $isOnlyNonTaxable = true;
                foreach ($request->items as $item) {
                    if ($item['deletedFlag']) {
                        $orderItem = OrderItem::find($item['id']);
                        $orderItem->delete();
                    } else if (!empty($item['quantity']) && !empty($item['productId'])) {
                        if (empty($item['id'])) {
                            $orderItem = new OrderItem();
                        } else {
                            $orderItem = OrderItem::find($item['id']);
                        }
                        $product = Product::find($item['productId']);
                        $orderItem->quantity = (int)$item['quantity'];
                        $orderItem->servedQuantity = $item['servedItems'];
                        $orderItem->productId = $product->id;
                        $orderItem->orderId = $order->id;
                        $orderItem->isParcel = $item['isParcel'] == true ? 1 : 0;
                        $orderItem->price = (float)$item['price'];
                        if ($product->isAdvancedPricing) {
                            $pricing = ProductAdvancedPricing::find($item['advancedPriceId']);
                            if ($pricing instanceof ProductAdvancedPricing) {
                                $orderItem->advancedPriceId = $pricing->id;
                                $orderItem->price = $pricing->price;
                                $orderItem->advancedPriceTitle = $pricing->title;
                            }
                        } else {
                            $orderItem->advancedPriceId = null;
                            $orderItem->advancedPriceTitle = null;
                        }
                        $orderItem->packagingCharges = $product->packagingCharges;
                        $totalPrice = $orderItem->quantity * $orderItem->price;
                        if ($orderItem->isParcel) {
                            $totalPrice = $totalPrice + ($orderItem->quantity * $orderItem->packagingCharges);
                        } else {
                            $orderItem->packagingCharges = 0;
                        }
                        $orderItem->totalPrice = $totalPrice;
                        $totalOrderAmount = $totalOrderAmount + $totalPrice;
                        if ($product->inclTax) {
                            $nonTaxableAmount = $nonTaxableAmount + $totalPrice;
                        }else {
                            $isOnlyNonTaxable = false;
                        }
                        $orderItem->isSync = false;
                        $orderItem->save();

                        if (is_array($item['comments'])) {

                            foreach ($item['comments'] as $comment) {

                                if ($comment['deletedFlag']) {
                                    $orderItemComment = OrderItemComment::find($comment['id']);
                                    $orderItemComment->delete();
                                } else if (!empty($comment['description'])) {
                                    if (empty($comment['id'])) {
                                        $orderItemComment = new OrderItemComment();
                                    } else {
                                        $orderItemComment = OrderItemComment::find($comment['id']);
                                    }
                                    $orderItemComment->description = $comment['description'];
                                    $orderItemComment->itemId = $orderItem->id;
                                    $orderItemComment->save();
                                }
                            }
                        }
                    }
                }
                $order->orderItemTotal = $totalOrderAmount;


                $totalComboAmount = 0;
                foreach ($request->comboItems as $item) {
                    if ($item['deletedFlag']) {
                        $orderItemCombo = OrderItemCombo::find($item['id']);
                        $orderItemCombo->delete();
                    } else if (!empty($item['quantity']) && !empty($item['comboProductId'])) {
                        if (empty($item['id'])) {
                            $orderItemCombo = new OrderItemCombo();
                        } else {
                            $orderItemCombo = OrderItemCombo::find($item['id']);
                        }
                        $comboProduct = ProductCombo::find($item['comboProductId']);
                        $orderItemCombo->quantity = (int)$item['quantity'];
                        $orderItemCombo->servedQuantity = $item['servedItems'];
                        $orderItemCombo->comboProductId = $comboProduct->id;
                        $orderItemCombo->orderId = $order->id;
                        $orderItemCombo->isParcel = $item['isParcel'] == true ? 1 : 0;
                        $orderItemCombo->price = (float)$item['price'];
                        $orderItemCombo->packagingCharges = $comboProduct->packagingCharges;
                        $totalPrice = $orderItemCombo->quantity * $orderItemCombo->price;
                        if ($orderItemCombo->isParcel) {
                            $totalPrice = $totalPrice + ($orderItemCombo->quantity * $orderItemCombo->packagingCharges);
                        } else {
                            $orderItemCombo->packagingCharges = 0;
                        }
                        $orderItemCombo->totalPrice = $totalPrice;
                        $totalComboAmount = $totalComboAmount + $totalPrice;

                        if ($comboProduct->inclTax) {
                            $nonTaxableAmount = $nonTaxableAmount + $totalPrice;
                        } else {
                            $isOnlyNonTaxable = false;
                        }
                        $orderItemCombo->isSync = false;
                        $orderItemCombo->save();

                        if (is_array($item['comments'])) {

                            foreach ($item['comments'] as $comment) {

                                if ($comment['deletedFlag']) {
                                    $orderItemComment = OrderComboItemComment::find($comment['id']);
                                    $orderItemComment->delete();
                                } else if (!empty($comment['description'])) {
                                    if (empty($comment['id'])) {
                                        $orderItemComment = new OrderComboItemComment();
                                    } else {
                                        $orderItemComment = OrderComboItemComment::find($comment['id']);
                                    }
                                    $orderItemComment->description = $comment['description'];
                                    $orderItemComment->itemId = $orderItemCombo->id;
                                    $orderItemComment->save();
                                }
                            }
                        }
                    }
                }
                $order->orderComboTotal = $totalComboAmount;
                if(!$isOnlyNonTaxable) {
                    $nonTaxableAmount = 0;
                }
                if (!empty($request->deliverCharge)) {
                    $order->deliverCharge = (float)$request->deliverCharge;
                } else {
                    $order->deliverCharge = 0;
                }
                $order = $this->handleFinalCalculationOrder($order, $nonTaxableAmount);
                $order->isSync = 0;
                $order->save();


                $order->orderTables()->delete();
                // return $orderType;
                foreach ($request->tables as $table) {
                    $orderTable = new OrderTable();
                    $orderTable->tableId = $table['id'];
                    $orderTable->selectedChairs = $table['chairs'];
                    $orderTable->orderId = $order->id;
                    $orderTable->isSync = 0;
                    $orderTable->save();
                }

                return $this->getOrderDetails($request, $order->id);
            });
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Can not able to update', 'error' => $e->getMessage()], 400);
        }
    }

    public function handleFinalCalculationOrder($order, $nonTaxableAmount = null)
    {
        $totalOrderAmount = $order->orderItemTotal;
        $totalOrderAmount = $totalOrderAmount + $order->orderComboTotal;

        $order->igst = 0;
        if (!empty($order->deliverCharge)) {
            $totalOrderAmount = $totalOrderAmount + $order->deliverCharge;
        }
        $taxAmount = 0;
        $taxableAmount = $totalOrderAmount - $nonTaxableAmount;
        if ($taxableAmount > 0) {
            $taxAmount = ($totalOrderAmount * $order->taxPercent / 100);
        }
        $order->taxableAmount = $taxableAmount;
        if (!$order->taxDisabled && $taxAmount > 0) {
            $order->cgst = $taxAmount / 2;
            $order->sgst = $taxAmount / 2;
            $totalOrderAmount = $totalOrderAmount + $taxAmount;
        } else {
            $order->cgst = 0;
            $order->sgst = 0;
        }

        if (!empty($order->discountValue)) {
            $totalOrderAmount = $totalOrderAmount - (float)$order->discountValue;
        }
        $floatSplit = explode('.', $totalOrderAmount);
        if (sizeof($floatSplit) == 2) {
            $order->roundOfAmount = (float)("0." . $floatSplit[1]);
        } else {
            $order->roundOfAmount = 0;
        }
        $order->orderAmount = $totalOrderAmount - $order->roundOfAmount;
        return $order;
    }

    public function handleCustomerCreation($request, $branch_id)
    {
        $mobileNumber = $request['mobileNumber'];
        $customer = Customer::where('mobileNumber', $mobileNumber)->first();
        if (!$customer instanceof Customer) {
            $customer = new Customer();
            $customer->branch_id = $branch_id;
            $customer->mobileNumber = $mobileNumber;
        }
        $customer->customerName = $request['customerName'];
        $customer->emailId = $request['emailId'] ?? "";
        $customer->isSync = false;
        $customer->save();
        return $customer;
    }

    public function removeRejectedItems(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                if ($request->itemType == 'item') {
                    $orderItem = OrderItem::find($request->id);
                } else {
                    $orderItem = OrderItemCombo::find($request->id);
                }
                $orderItem->quantity = $orderItem->quantity - $orderItem->productionRejectedQuantity;

                $totalDeductablePrice = ($orderItem->price * $orderItem->productionRejectedQuantity) + ($orderItem->price * $orderItem->packagingCharges);

                $orderItem->productionRejectedQuantity = 0;
                $orderItem->totalPrice = $orderItem->totalPrice - $totalDeductablePrice;
                $order = Order::find($orderItem->orderId);
                $order->orderItemTotal = $order->orderItemTotal - $totalDeductablePrice;
                $nonTaxableAmount = 0;
                $isOnlyNonTaxable = true;
                foreach ($order->orderitems as $item) {
                    if ($item->product->inclTax) {
                        if($request->itemType == 'item' && $item->id === $orderItem->id) {
                            $nonTaxableAmount = $nonTaxableAmount + $orderItem->totalPrice;
                        }else {
                            $nonTaxableAmount = $nonTaxableAmount + $item->totalPrice;
                        }
                    } else {
                        $isOnlyNonTaxable = false;
                    }
                }
                foreach ($order->orderItemCombos as $item) {
                    if ($item->productCombo->inclTax) {
                        if($request->itemType != 'item' && $item->id === $orderItem->id) {
                            $nonTaxableAmount = $nonTaxableAmount + $orderItem->totalPrice;
                        }else {
                            $nonTaxableAmount = $nonTaxableAmount + $item->totalPrice;
                        }
                    } else {
                        $isOnlyNonTaxable = false;
                    }
                }
                if(!$isOnlyNonTaxable) {
                    $nonTaxableAmount = 0;
                }
                $order = $this->handleFinalCalculationOrder($order, $nonTaxableAmount);
                $order->isSync = false;
                $order->save();
                if ($orderItem->quantity == 0) {
                    $orderItem->delete();
                } else {
                    $orderItem->isSync = false;
                    $orderItem->save();
                }

                return $orderItem;
            });
        } catch (\Exception $e) {
            return response()->json(['msg' => 'Can not able to update', 'error' => $e], 400);
        }
    }

    public function kotPrintedItems(Request $request)
    {
        foreach ($request->items as $item) {
            $orderItem = OrderItem::find($item['id']);
            if ($orderItem instanceof OrderItem) {
                $orderItem->kotPrintedQuantity = $orderItem->kotPrintedQuantity + $item['kot_pending'];
                $orderItem->save();
            }
        }
        return response()->json(['msg' => 'Saved successfully']);
    }

    public function changeStatusBack(Request $request)
    {
        try {
            $order = Order::find($request->id);
            if ($order instanceof Order) {
                if ($order->orderStatus == 'cancelled' || $order->orderStatus == 'completed') {
                    $order->orderStatus = $request->status;
                    $order->save();
                    return $this->getOrderDetails($request, $order->id);
                } else {
                    return response()->json(['msg' => 'Order is not closed till now'], 400);
                }
            } else {
                return response()->json(['msg' => 'Order not found'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }


    public function orderItemReportBasedOnProduct(Request $request)
    {
        $orderItems = OrderItem::leftJoin('orders', 'orders.id', 'order_items.orderId')
            ->leftJoin('products', 'products.id', 'order_items.productId')
            ->leftJoin('product_advanced_pricings', 'product_advanced_pricings.id', 'order_items.advancedPriceId')
            ->select('order_items.productId', 'order_items.advancedPriceId', \DB::raw('sum(order_items.quantity) as total'), 'products.productName', 'products.isAdvancedPricing', 'product_advanced_pricings.title')
            ->where('orders.orderStatus', 'completed');


        if (!empty($request->searchString)) {
            $orderItems = $orderItems->where(function ($q) use ($request) {
                $q->where('products.productName', 'LIKE', '%' . $request->searchString . '%');
            });
        }


        if (!empty($request->company_id)) {
            $orderItems = $orderItems->where('orders.company_id', $request->company_id);
        }

        if (!empty($request->branch_id)) {
            $orderItems = $orderItems->where('orders.branch_id', $request->branch_id);
        }


        if (!empty($request->startDate) && !empty($request->endDate)) {
            $endDate = (new \Datetime($request->endDate))->modify('+1 day');
            $orderItems = $orderItems->whereBetween('orders.created_at', [new \Datetime($request->startDate), $endDate]);
        }

        $orderItems = $orderItems->groupBy('order_items.productId', 'order_items.advancedPriceId')
            ->get();

        return $orderItems->groupBy('productId');
    }


    public function deleteBulkOrders(Request $request)
    {
        try {
            $orderIds = explode(',', $request->orderIds);
            Order::whereIn('id', $orderIds)->delete();
            return response()->json(['msg' => 'Deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }
}
