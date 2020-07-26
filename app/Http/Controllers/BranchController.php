<?php

namespace App\Http\Controllers;

use App\Branch;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class BranchController extends Controller
{
    public function getBranches(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $branches = Branch::select($fields);

        if(!empty($request->searchString)) {
            $branches = $branches->where('branchTitle', 'LIKE', '%'.$request->searchString.'%');
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
        return Branch::find($id);
    }

    public function createBranch(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                $branch = new Branch();
                $branch->branchTitle = $request->branchTitle;
                $branch->description = $request->description;
                $branch->branchAddress = $request->branchAddress;
                $branch->branchCode = $request->branchCode;
                $branch->isActive = $request->isActive;
                $branch->save();
                return $branch;
            }catch(\Exception $e) {
                return response()->json(['msg' => ' Can not able to create branch', 'error'=>$e], 404);
            }
        });
    }
 
    public function updateBranch(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $branch = Branch::find($id);
                if($branch instanceof Branch) {
                    $branch->branchTitle = $request->branchTitle;
                    $branch->description = $request->description;
                    $branch->branchAddress = $request->branchAddress;
                    $branch->branchCode = $request->branchCode;
                    $branch->isActive = true;
                    $branch->save();
                    return $branch;
                }else {
                    return response()->json(['msg' => 'Branch Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not create branch data'], 404);
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
