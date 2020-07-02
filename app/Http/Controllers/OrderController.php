<?php

namespace App\Http\Controllers;

use App\OrderType;
use App\TableManager;
use Illuminate\Http\Request;
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
            $orderTypes = $orderTypes->where('isActive', $request->status);
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
                $orderType->isActive = true;
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
                $orderType->isActive = $orderType->isActive?true:false;
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
}
