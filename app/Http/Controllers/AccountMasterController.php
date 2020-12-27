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
        $units = MeasureUnit::with('company')->select($fields);

        if(!empty($request->searchString)) {
            $units = $units->where('unitLabel', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->companyId)) {
            $units = $units->where('company_id', $request->companyId);
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
            
            // Existing unit validation
            $existingUnits = MeasureUnit::where('company_id', $request->company_id)
                                    ->where('unitLabel', $request->unitLabel);
            if(!empty($request->id)) {
                $existingUnits = $existingUnits->where('id', '<>', $request->id);
            }
            // $existingUnits = $existingUnits->where(function($query) use ($request) {
            //     $query->where('branch_id', NULL)->orWhere('branch_id', '');
            //     if(!empty($request->branch_id)) {
            //         $query->orWhere('branch_id', $request->branch_id);
            //     }
            // });
            $existingUnits = $existingUnits->get();
            if(sizeof($existingUnits) > 0) {
                return response()->json(['msg' => 'Unit already exists in company.'], 400);
            }

            // if(empty($request->branch_id)) {
            //     $existingUnitsOnBranches = MeasureUnit::where('company_id', $request->company_id)
            //                             ->where('branch_id', '<>', NULL)
            //                             ->where('unitLabel', $request->unitLabel);
            //     if(!empty($request->id)) {
            //         $existingUnitsOnBranches = $existingUnitsOnBranches->where('id', '<>', $request->id);
            //     }
            //     $existingUnitsOnBranches = $existingUnitsOnBranches->get();
            //     if(sizeof($existingUnitsOnBranches) > 0) {
            //         return response()->json(['msg' => 'Unit already exists in branches. Please move that to company level.'], 400);
            //     }
            // }
            // End of Existing unit validation
            
            return \DB::transaction(function() use($request) {


                if(empty($request->id)) {
                    $unit = new MeasureUnit();
                }else {
                    $unit = MeasureUnit::find($request->id);
                }
                $unit->unitLabel =  $request->unitLabel;
                $unit->company_id =  $request->company_id;
                // $unit->branch_id =  $request->branch_id ?? NULL;
                $unit->isActive =  $request->isActive ?? false;
                $unit->description =  $request->description;
                $unit->save();
                return MeasureUnit::with('company')->find($unit->id);
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
        $ledgers = LedgerAccount::with('company')->select($fields);

        if(!empty($request->searchString)) {
            $ledgers = $ledgers->where('ledgerName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->companyId)) {
            $ledgers = $ledgers->where('company_id', $request->companyId);
        }

        if(!empty($request->status)) {
            $ledgers = $ledgers->where('isActive', ($request->status == 'in-active')?false:true);
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
            $existingLedgers = LedgerAccount::where('company_id', $request->company_id)
                                    ->where('ledgerName', $request->ledgerName);
            if(!empty($request->id)) {
                $existingLedgers = $existingLedgers->where('id', '<>', $request->id);
            }
            $existingLedgers = $existingLedgers->get();
            if(sizeof($existingLedgers) > 0) {
                return response()->json(['msg' => 'Ledger already exists in company.'], 400);
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
                $ledger->company_id =  $request->company_id;
                $ledger->isActive =  $request->isActive ?? false;
                $ledger->description =  $request->description;
                $ledger->save();
                return LedgerAccount::with('company')->find($ledger->id);
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update ledger', 'error'=>$e], 400);
        }
    }

    // public function updateLedger(Request $request, $id) {
    //     try {
    //         return \DB::transaction(function() use($request, $id) {

    //             $ledger = LedgerAccount::find($id);
    //             $ledger->ledgerName =  $request->ledgerName;
    //             $ledger->accountType =  $request->accountType;
    //             $ledger->taxPercentage =  $request->taxPercentage;
    //             $ledger->openingBalance =  $request->openingBalance;
    //             $ledger->description = $request->description;
    //             $ledger->isActive =  $request->isActive?true:false;
    //             $ledger->save();
    //         });
    //     }catch(\Exception $e) {
    //         return response()->json(['msg' => ' Can not able to update ledger', 'error'=>$e], 400);
    //     }
    // }

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
