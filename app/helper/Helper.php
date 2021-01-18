<?php 
namespace App\Helper;

use App\User;
use App\InventoryItemManager;

class Helper {

    public function getInventoryManager($inventoryId, $companyId, $branchId) {
        $loggedUser = \Auth::user();
        if($loggedUser instanceof User) {
            if($loggedUser->roles != 'Super Admin') {
                $companyId = $loggedUser->company_id;
            }
            if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
                $branchId = $loggedUser->branch_id;
            }
        }
        $inventoryManager = InventoryItemManager::where('inventoryId', $inventoryId)
                                                                    ->where('company_id', $companyId)
                                                                    ->where('branch_id', $branchId)
                                                                    ->first();

        // \Debugger::dump($inventoryId,$companyId, $branchId, $inventoryManager);
        if(!($inventoryManager instanceof InventoryItemManager)) {
            $inventoryManager = new InventoryItemManager();
            $inventoryManager->inventoryId =  $inventoryId;
            $inventoryManager->company_id =  $companyId;
            $inventoryManager->branch_id =  $branchId;
            $inventoryManager->availableStock = 0;
        }

        return $inventoryManager;
    }

}

?>