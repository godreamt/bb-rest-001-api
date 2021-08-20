<?php 
namespace App\Helper;

use App\User;
use App\InventoryItemManager;

class Helper {

    public function getInventoryManager($inventoryId, $branchId) {
        $loggedUser = \Auth::user();
        if($loggedUser instanceof User) {
            if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
                $branchId = $loggedUser->branch_id;
            }
        }
        $inventoryManager = InventoryItemManager::where('inventoryId', $inventoryId)
                                                                    ->where('branch_id', $branchId)
                                                                    ->first();

        if(!($inventoryManager instanceof InventoryItemManager)) {
            $inventoryManager = new InventoryItemManager();
            $inventoryManager->inventoryId =  $inventoryId;
            $inventoryManager->branch_id =  $branchId;
            $inventoryManager->availableStock = 0;
        }

        return $inventoryManager;
    }

}

?>