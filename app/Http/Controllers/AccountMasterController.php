<?php

namespace App\Http\Controllers;

use App\MeasureUnit;
use App\InventoryItem;
use App\LedgerAccount;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class AccountMasterController extends Controller
{
    public function getUnits(Request $request) {
        
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $units = MeasureUnit::with('branch')->select($fields);

        if(!empty($request->searchString)) {
            $units = $units->where('unitLabel', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->branch_id)) {
            $units = $units->where('branch_id', $request->branch_id);
        }

        if(!empty($request->status)) {
            $units = $units->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $units = $units->orderBy($request->orderCol, $request->orderType);
        }
        
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $units->paginate(10);
        }else {
            return $units->get();
        }
    }

    public function getUnit(Request $request, $id) {
        return MeasureUnit::find($id);
    }


    public function updateUnit(Request $request) {
        try {
            
            $existingUnits = MeasureUnit::where('branch_id', $request->branch_id)
                                    ->where('unitLabel', $request->unitLabel);
            if(!empty($request->id)) {
                $existingUnits = $existingUnits->where('id', '<>', $request->id);
            }
            $existingUnits = $existingUnits->get();
            if(sizeof($existingUnits) > 0) {
                return response()->json(['msg' => 'Unit already exists in company.'], 400);
            }

            
            return \DB::transaction(function() use($request) {


                if(empty($request->id)) {
                    $unit = new MeasureUnit();
                }else {
                    $unit = MeasureUnit::find($request->id);
                }
                $unit->unitLabel =  $request->unitLabel;
                $unit->branch_id =  $request->branch_id;
                $unit->isActive =  $request->isActive ?? false;
                $unit->description =  $request->description;
                $unit->isSync = false;
                $unit->save();
                return MeasureUnit::with('branch')->find($unit->id);
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update unit', 'error'=>$e->getMessage()], 400);
        }
    }

    public function deleteUnit(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $unit = MeasureUnit::find($id);
                if($unit instanceof MeasureUnit) {
                    $unit->delete();
                    return $unit;
                }else {
                    return response()->json(['msg' => 'unit Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete unit'], 400);
            }
        });
    }

    public function getLedgers(Request $request) {
        
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $ledgers = LedgerAccount::with('branch')->select($fields);

        if(!empty($request->searchString)) {
            $ledgers = $ledgers->where('ledgerName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->branch_id)) {
            $ledgers = $ledgers->where('branch_id', $request->branch_id);
        }

        if(!empty($request->status)) {
            $ledgers = $ledgers->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->accountTypes)) {
            $accountTypes = explode(',', $request->accountTypes);;
            $ledgers = $ledgers->whereIn('accountType', $accountTypes);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $ledgers = $ledgers->orderBy($request->orderCol, $request->orderType);
        }
        
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $ledgers->paginate(10);
        }else {
            return $ledgers->get();
        }
    }

    public function getLedger(Request $request, $id) {
        return LedgerAccount::find($id);
    }

    public function updateLedger(Request $request) {
        try {            
            // Existing ledger validation
            $existingLedgers = LedgerAccount::where('branch_id', $request->branch_id)
                                    ->where('ledgerName', $request->ledgerName);
            if(!empty($request->id)) {
                $existingLedgers = $existingLedgers->where('id', '<>', $request->id);
            }
            $existingLedgers = $existingLedgers->get();
            if(sizeof($existingLedgers) > 0) {
                return response()->json(['msg' => 'Ledger already exists in branch.'], 400);
            }
            // End of Existing unit validation
            
            return \DB::transaction(function() use($request) {


                if(empty($request->id)) {
                    $ledger = new LedgerAccount();
                }else {
                    $ledger = LedgerAccount::find($request->id);
                }
                $ledger->ledgerName =  $request->ledgerName;
                $ledger->accountType =  $request->accountType;
                $ledger->branch_id =  $request->branch_id;
                $ledger->isActive =  $request->isActive ?? false;
                $ledger->description =  $request->description;
                $ledger->isSync = false;
                $ledger->save();
                return LedgerAccount::with('branch')->find($ledger->id);
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update ledger', 'error'=>$e], 400);
        }
    }

    public function deleteLedger(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $ledger = LedgerAccount::find($id);
                if($ledger instanceof LedgerAccount && !$ledger->isAutoCreated) {
                    $ledger->delete();
                    return $ledger;
                }else if($ledger->isAutoCreated) {
                    return response()->json(['msg' => 'Can not delete auto created ledger'], 400);
                }else {
                    return response()->json(['msg' => 'Ledger Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete ledger'], 400);
            }
        });
    }
}
