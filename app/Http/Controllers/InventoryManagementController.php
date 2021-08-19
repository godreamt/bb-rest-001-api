<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderItem;
use App\InventoryItem;
use App\TransactionItem;
use Illuminate\Http\Request;
use App\InventoryItemJournal;
use App\InventoryItemManager;
use Illuminate\Pagination\Paginator;

class InventoryManagementController extends Controller
{
    public function getInventoryItems(Request $request) {
        $items = InventoryItem::with('unit')->with('branch')
                                ->leftJoin('inventory_item_managers', 'inventory_item_managers.inventoryId', 'inventory_items.id')
                                ->addSelect('inventory_items.*', 'inventory_item_managers.id as managerId', 'inventory_item_managers.availableStock', 'inventory_item_managers.lastPurchasedPrice');

        if(!empty($request->searchString)) {
            $items = $items->where('itemName', 'LIKE', '%'.$request->searchString.'%');
        }
        if(!empty($request->branch_id)) {
            $items = $items->where('inventory_items.branch_id', $request->branch_id);
        }

        if(!empty($request->status)) {
            $items = $items->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $items = $items->orderBy($request->orderCol, $request->orderType);
        }
        
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $items->paginate(10);
        }else {
            return $items->get();
        }
    }

    public function getInventoryItem(Request $request, $id) {
        return InventoryItem::with('unit')->with('branch')
                            ->leftJoin('inventory_item_managers', 'inventory_item_managers.inventoryId', 'inventory_items.id')
                            ->addSelect('inventory_items.*', 'inventory_item_managers.id as managerId', 'inventory_item_managers.availableStock', 'inventory_item_managers.lastPurchasedPrice')
                            ->where('inventory_items.id', $id)->first();
    }

    public function updateInventoryItem(Request $request) {
        try {
                           
            // Existing inventory validation
            $existingInventory = InventoryItem::where('company_id', $request->company_id)
                    ->where('itemName', $request->inventoryName);
            if(!empty($request->id)) {
                $existingInventory = $existingInventory->where('id', '<>', $request->id);
            }
            $existingInventory = $existingInventory->get();
            if(sizeof($existingInventory) > 0) {
                return response()->json(['msg' => 'Inventory already exists in company.'], 400);
            }
            // End of Existing unit validation

            return \DB::transaction(function() use($request) {


                if(empty($request->id)) {
                    $item = new InventoryItem();
                }else {
                    $item = InventoryItem::find($request->id);
                }
                $item->itemName =  $request->itemName;
                $item->unitId =  $request->unitId;
                $item->company_id =  $request->company_id;
                $item->description =  $request->description;
                $item->pricePerUnit =  $request->pricePerUnit;
                $item->isActive =  $request->isActive;
                $item->isSync = false;
                $item->save();
                return InventoryItem::with('company')->with('unit')->find($item->id);
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to create item', 'error'=>$e], 400);
        }
    }

    // public function updateInventoryItem(Request $request, $id) {
    //     try {
    //         return \DB::transaction(function() use($request, $id) {

    //             $item = InventoryItem::find($id);
    //             $item->itemName =  $request->itemName;
    //             $item->unitId =  $request->unitId;
    //             $item->description =  $request->description;
    //             $item->pricePerUnit =  $request->pricePerUnit;
    //             $item->isActive =  $request->isActive;
    //             $item->save();
    //         });
    //     }catch(\Exception $e) {
    //         return response()->json(['msg' => ' Can not able to update item', 'error'=>$e], 400);
    //     }
    // }

    public function deleteInventoryItem(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $item = InventoryItem::find($id);
                if($item instanceof InventoryItem) {
                    $item->delete();
                    return $item;
                }else {
                    return response()->json(['msg' => 'item Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete item'], 400);
            }
        });
    }


    public function getInventoryTrackings(Request $request, $invenotoryId) {
        $transactions = TransactionItem::leftJoin('transactions', 'transactions.id', 'transaction_items.transactionId') 
                                        ->leftJoin('users as updateUser', 'updateUser.id', 'transactions.updatedBy')
                                        ->selectRaw("transactions.transactionType, transactions.description")
                                        ->addSelect('transactions.id', 'transaction_items.quantity', 'transaction_items.amount as pricePerUnit', 'transaction_items.total as totalAmount', 'transactions.transactionDate', 'updateUser.id as userId', 'updateUser.firstName', 'updateUser.lastName')
                                        ->where('itemId', $invenotoryId);
        $journalEntries = InventoryItemJournal::where('inventoryId', $invenotoryId)
                                                ->leftJoin('users as updateUser', 'updateUser.id', 'inventory_item_journals.updatedBy')
                                                ->select('transactionType', 'description', 'inventory_item_journals.id', 'quantity', 'pricePerUnit', 'totalAmount', 'inventory_item_journals.created_at as transactionDate', 'updateUser.id as userId', 'updateUser.firstName', 'updateUser.lastName')
                                                ->union($transactions)
                                                ->orderBy('transactionDate', 'desc');
        
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $journalEntries->paginate(10);
        }else {
            return $journalEntries->get();
        }
    }

    public function updateInventoryStock(Request $request) {
        return \DB::transaction(function() use ($request) {
            try {
                $invenotory = InventoryItem::find($request->inventoryId);
                $invenotoryManager = InventoryItemManager::find($request->managerId);
                if($request->quantity > $invenotoryManager->availableStock) {
                    return response()->json(['msg' => $invenotory->itemName.' does not have enough storage'], 400);
                }
                $latestUpdate = new InventoryItemJournal();
                $latestUpdate->inventoryId = $request->inventoryId;
                $latestUpdate->description = $request->description;
                $latestUpdate->transactionType = $request->transactionType;
                $latestUpdate->quantity = $request->quantity;
                if($invenotoryManager->lastPurchasedPrice <= 0) {
                    $latestUpdate->pricePerUnit = $invenotory->pricePerUnit;
                }else {
                    $latestUpdate->pricePerUnit = $invenotoryManager->lastPurchasedPrice;
                }
                $latestUpdate->totalAmount = $request->quantity * $latestUpdate->pricePerUnit;
                $invenotoryManager->availableStock=$invenotoryManager->availableStock-$request->quantity;
                $latestUpdate->isSync = false;
                $latestUpdate->save();
                $invenotoryManager->isSync = false;
                $invenotoryManager->save();
                return $latestUpdate;
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not update inventory', 'error'=>$e->getMessage()], 400);
            }
        });
    }

}
