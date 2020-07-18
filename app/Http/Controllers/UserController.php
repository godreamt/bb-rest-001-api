<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getUsers(Request $request) {
        $loggedUser = Auth::user();
        
        $fields = $request->get('fields', '*');
        if($fields != '*'){
            $fields = explode(',',$fields);
        }
        $users = User::select($fields)->with('branch');

        if(!empty($request->searchString)) {
            $users = $users->where('firstName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->status)) {
            $users = $users->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            $users = $users->orderBy($request->orderCol, $request->orderType);
        }

        if($loggedUser->roles != 'Super Admin') {
            $users = $users->where('branch_id', $loggedUser->branch_id);
        }
        $currentPage = $request->pageNumber;
        if(!empty($currentPage)){
            Paginator::currentPageResolver(function () use ($currentPage) {
                return $currentPage;
            });

            return $users->paginate(10);
        }else {
            return $users->get();
        }
    }

    public function getUser(Request $request, $id) {
        $loggedUser = Auth::user();

        $user = User::with('branch')->where('users.id', $id);

        if($loggedUser->roles != 'Super Admin') {
            $user = $user->where('branch_id', $loggedUser->branch_id);
        }
        return $user->first();
    } 

    public function createUser(Request $request) {
        try {
            return \DB::transaction(function() use($request) {

                $user = new User();
                $user->firstName =  $request->firstName;
                $user->lastName =  $request->lastName;
                $user->profilePic =  '';
                $user->email =  $request->email;
                $user->mobileNumber =  $request->mobileNumber;
                $user->password =  Hash::make($request->password);
                $user->roles =  $request->roles;
                $user->isActive =  $request->isActive;
                $user->branch_id =  $request->branch_id;
                $user->save();
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to create user', 'error'=>$e], 400);
        }
    }

    public function updateUser(Request $request, $id) {
        try {
            return \DB::transaction(function() use($request, $id) {

                $user = User::find($id);
                $user->firstName =  $request->firstName;
                $user->lastName =  $request->lastName;
                $user->profilePic =  '';
                $user->email =  $request->email;
                $user->mobileNumber =  $request->mobileNumber;
                if(!empty($request->password)) {
                    $user->password =  Hash::make($request->password);
                }
                $user->roles =  $request->roles;
                $user->isActive =  $request->isActive;
                $user->branch_id =  $request->branch_id;
                $user->save();
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update user', 'error'=>$e], 400);
        }
    }

    public function deleteUser(Request $request, $id) {
        return \DB::transaction(function() use($request, $id) {
            try {
                $user = User::find($id);
                if($user instanceof User) {
                    $user->delete();
                    return $user;
                }else {
                    return response()->json(['msg' => 'User Does not exist'], 400);
                }
            }catch(\Exception $e) {
                return response()->json(['msg' => 'Can not delete user'], 400);
            }
        });
    }
}
