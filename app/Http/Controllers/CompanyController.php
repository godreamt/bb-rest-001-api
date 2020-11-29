<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

class CompanyController extends Controller
{
    public function getAllCompanies(Request $request) {
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $companies = Company::select($fields);

        if(!empty($request->searchString)) {
            $companies = $companies->where('companyName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->status)) {
            $companies = $companies->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $companies = $companies->orderBy($request->orderCol, $request->orderType);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $companies->paginate(10);
        }else {
            return $companies->get();
        }
    }

    

    public function getCompanyDetails(Request $request, $id) {
        return Company::find($id);
    }

    public function updateCompany(Request $request) {
        return \DB::transaction(function() use($request) {
            try {
                if(empty($request->id)) {
                    $company = new Company();
                }else {
                    $company = Company::find($request->id);
                }
                $company->companyName = $request->companyName;
                $company->companyDetails = $request->companyDetails;
                $company->apiKey = $request->apiKey;
                $company->numberOfBranchesAllowed = $request->numberOfBranchesAllowed;
                $company->enableAccounting = $request->enableAccounting;
                $company->enableRestaurantFunctions = $request->enableRestaurantFunctions;
                $company->isActive = $request->isActive ?? false;

                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "com-".time().".png";
                    $path = '/img/company/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $company->companyLogo = '/uploads'.$path;
                }

                $company->save();

                return $company;
            }catch(\Exception $e) {
                return response()->json(['msg' => ' Can not able to create company', 'error'=>$e->getMessage()], 404);
            }
        });
    }

    public function deleteCompany(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $company = Company::find($id);
                if($company instanceof Company) {
                    $company->delete();
                    return $company;
                }else {
                    return response()->json(['msg' => 'Company Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete company', 'error'=>$e->getMessage()], 404);
            }
        }); 
    }

    public function changeCompanyStatus(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $company = Company::find($id);
                if($company instanceof Company) {
                    $company->isActive = $request->isActive;
                    $company->save();
                    return ['data' => $company, 'msg'=> "Company status updated successfully"];
                }else {
                    return response()->json(['msg' => 'Company Does not exist'], 404);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Company status can not changed'], 404);
            }
        });
    }
}
