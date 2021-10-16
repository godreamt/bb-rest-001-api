<?php

namespace App\Http\Controllers;

use App\Branch;
use App\Company;
use App\BranchRoom;
use App\BranchKitchen;
use App\BranchOrderType;
use Illuminate\Http\Request;
use App\BranchPaymentMethods;
use Illuminate\Pagination\Paginator;

class BranchController extends Controller
{
    public function getBranches(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $branches = Branch::select($fields)->with('company');

        if(!empty($request->searchString)) {
            $branches = $branches->where('branchTitle', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->companyId)) {
            $branches = $branches->where('company_id', $request->companyId);
        }
        if(!empty($request->status)) {
            $branches = $branches->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $branches = $branches->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $branches->paginate(10);
        }else {
            return $branches->get();
        }
    }

    public function getBranchDetails(Request $request, $id) {
        return Branch::with('kitchens')->with('rooms')->with('orderTypes')->with('paymentMethods')->where('id', $id)->first();
    }

    public function updateBranch(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                if(empty($request->id)) {
                    $branch = new Branch();
                    $branch->branchCode = $request->branchCode;

                    $company = Company::find($request->company_id);
                    $previousBranches = Branch::where('company_id', $request->company_id)->get();
                    if(sizeof($previousBranches) >= $company->numberOfBranchesAllowed) {
                        return response()->json(['msg' => 'Branch limit exceeded.'], 400);
                    }
                }else {
                    $branch = Branch::find($request->id);
                }
                $branch->branchTitle = $request->branchTitle;
                $branch->description = $request->description;
                $branch->branchAddress = $request->branchAddress;
                $branch->gstNumber = $request->gstNumber ?? null;
                $branch->isActive = $request->isActive ?? false;
                $branch->taxPercent = $request->taxPercent;
                $branch->billPrinter = $request->billPrinter;
                $branch->kotPrinter = $request->kotPrinter;
                $branch->company_id = $request->company_id;
                $branch->appDefaultOrderType = $request->appDefaultOrderType ?? null;
                $branch->adminDefaultOrderType = $request->adminDefaultOrderType ?? null;
                
                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "user-".time().".png";
                    $path = '/img/branch/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $branch->branchLogo = '/uploads'.$path;
                }

                $branch->isSync = false;
                $branch->save();

                $kitchens = $request->kitchens ?? [];
                foreach($kitchens as $kitchen) {
                    if(!empty($kitchen['deletedFlag'])) {
                        $kitchen = BranchKitchen::find($kitchen['id']);
                        $kitchen->delete();
                    }else {
                        if(empty($kitchen['id'])) {
                            $kitchenObj = new BranchKitchen();
                        }else {
                            $kitchenObj = BranchKitchen::find($kitchen['id']);
                        }
                        $kitchenObj->kitchenTitle = $kitchen['kitchenTitle'];
                        $kitchenObj->branch_id = $branch->id;
                        $kitchenObj->isSync = false;
                        $kitchenObj->save();
                    }
                }

                $rooms = $request->rooms ?? [];
                foreach($rooms as $room) {
                    if(!empty($room['deletedFlag'])) {
                        $room = BranchRoom::find($room['id']);
                        $room->delete();
                    }else {
                        if(empty($room['id'])) {
                            $roomObj = new BranchRoom();
                        }else {
                            $roomObj = BranchRoom::find($room['id']);
                        }
                        $roomObj->roomName = $room['roomName'];
                        $roomObj->withAc = $room['withAc'] ?? false;
                        $roomObj->serveLiquor = $room['serveLiquor'] ?? false;
                        $roomObj->isActive = $room['isActive'] ?? false;
                        $roomObj->branch_id = $branch->id;
                        $roomObj->isSync = false;
                        $roomObj->save();
                    }
                }


                $orderTypes = $request->orderTypes ?? [];

                foreach($orderTypes as $type) {
                    if(!empty($type['deletedFlag'])) {
                        $type = BranchOrderType::find($type['id']);
                        $type->delete();
                    }else {
                        if(empty($type['id'])) {
                            $typeObj = new BranchOrderType();
                        }else {
                            $typeObj = BranchOrderType::find($type['id']);
                        }
                        $typeObj->orderType = $type['orderType'];
                        $typeObj->tableRequired = $type['tableRequired'] ?? false;
                        $typeObj->isActive = $type['isActive'] ?? false;
                        $typeObj->branch_id = $branch->id;
                        $typeObj->isSync = false;
                        $typeObj->save();
                    }
                }

                $paymentMethods = $request->paymentMethods ?? [];
                foreach($paymentMethods as $method) {
                    if(!empty($method['deletedFlag'])) {
                        $method = BranchPaymentMethods::find($method['id']);
                        $method->delete();
                    }else {
                        if(empty($method['id'])) {
                            $methodObj = new BranchPaymentMethods();
                        }else {
                            $methodObj = BranchPaymentMethods::find($method['id']);
                        }
                        $methodObj->methodTitle = $method['methodTitle'];
                        $methodObj->branch_id = $branch->id;
                        $methodObj->isSync = false;
                        $methodObj->save();
                    }
                }

                return $branch;
            }catch(\Exception $e) {
                return response()->json(['msg' => ' Can not able to create branch', 'error'=> $e->getMessage()], 404);
            }
        });
    }

    public function deleteBranch(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $branch = Branch::find($id);
                if($branch instanceof Branch) {
                    $branch->delete();
                    return $branch;
                }else {
                    return response()->json(['msg' => 'Branch Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete branch', 'error'=>$e], 404);
            }
        }); 
    }

    public function changeBranchStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $branch = Branch::find($id);
                if($branch instanceof Branch) {
                    $branch->isActive = $request->isActive;
                    $branch->isSync = false;
                    $branch->save();
                    return ['data' => $branch, 'msg'=> "Branch status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Branch Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Branch status can not changed'], 404);
            }
        });
    }
}
