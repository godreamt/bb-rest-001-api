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
}
