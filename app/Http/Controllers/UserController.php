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
        
        $fields = $request->get('fields', 'users.*');
        if($fields != 'users.*'){
            $fields = explode(',',$fields);
        }
        $users = User::select($fields)->with('branch')->with('company')
                        ->leftJoin('companies', 'users.company_id', 'companies.id')
                        ->leftJoin('branches', 'users.branch_id', 'branches.id');

        if(!empty($request->searchString)) {
            $users = $users->where('firstName', 'LIKE', '%'.$request->searchString.'%');
        }

        if(!empty($request->attendaceRequired)) {
            $users = $users->where('attendaceRequired', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->status)) {
            $users = $users->where('isActive', ($request->status == 'in-active')?false:true);
        }

        if(!empty($request->orderCol) && !empty($request->orderType)) {
            
            if($request->orderCol === 'branch') {
                $users = $users->orderBy('branches.branchTitle', $request->orderType);
            }else if($request->orderCol === 'company') {
                $users = $users->orderBy('companies.companyName', $request->orderType);
            }else {
                $users = $users->orderBy($request->orderCol, $request->orderType);
            }
        }

        // if($loggedUser->roles != 'Super Admin') {
        //     $users = $users->where('branch_id', $loggedUser->branch_id);
        // }
        // if($loggedUser instanceof User) {
        if($loggedUser->roles != 'Super Admin') {
            $users = $users->where('users.company_id',  $loggedUser->company_id);
        }else if(!empty($request->company_id)) {
            $users = $users->where('users.company_id', $request->company_id);
        }
        if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin') {
            $users = $users->where('users.branch_id',  $loggedUser->branch_id);
        }else if(!empty($request->branch_id)) {
            $users = $users->where('users.branch_id', $request->branch_id);
        }
        
        
        // }
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

        $user = User::with('branch')->with('company')->where('users.id', $id);

        if($loggedUser->roles != 'Super Admin') {
            $user = $user->where('branch_id', $loggedUser->branch_id);
        }
        return $user->first();
    } 

    public function updateUser(Request $request) {
        try {
            return \DB::transaction(function() use($request) {
                if(empty($request->id)) {
                    $user = new User();
                    $user->password =  Hash::make($request->password);
                }else {
                    $user = User::find($request->id);
                    
                    if(!empty($request->password)) {
                        $user->password =  Hash::make($request->password);
                    }
                }
                $user->firstName =  $request->firstName;
                $user->lastName =  $request->lastName;
                if(!empty($request->image)) {
                    $data = $request->image;
                    $base64_str = substr($data, strpos($data, ",")+1);
                    $image = base64_decode($base64_str);
                    $png_url = "user-".time().".png";
                    $path = '/img/users/' . $png_url;
                    \Storage::disk('public')->put($path, $image);
                    $user->profilePic = '/uploads'.$path;
                }
                $user->email =  $request->email;
                $user->mobileNumber =  $request->mobileNumber;
                $user->roles =  $request->roles;
                $user->isActive =  $request->isActive ?? false;
                $user->attendaceRequired =  $request->attendaceRequired ?? false;
                $user->company_id =  $request->company_id;
                $user->branch_id =  $request->branch_id;
                $user->isSync = false;
                $user->save();
                return $user;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update user', 'error'=>$e->getMessage()], 400);
        }
    }

    public function uploadCurrentUserImage(Request $request) {
        try {
            return \DB::transaction(function() use ($request) {
                $user = \Auth::user();
                $data = $request->image;
                $base64_str = substr($data, strpos($data, ",")+1);
                $image = base64_decode($base64_str);
                $png_url = "user-".time().".png";
                $path = '/img/users/' . $png_url;
                \Storage::disk('public')->put($path, $image);
                $user->profilePic = '/uploads'.$path;
                $user->isSync = false;
                $user->save();
                return $user;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update user pic', 'error'=>$e], 400);
        }
    }
    

    public function changeCurrentUserPassword(Request $request) {
        try {
            return \DB::transaction(function() use ($request) {
                $user = \Auth::user();
                if(Hash::check($request->oldPassword,$user->password)) {
                    $user->password = Hash::make($request->password);
                    $user->isSync = false;
                    $user->save();
                    return $user;
                } else {
                    return response()->json(['msg' => 'Invalid old password.'], 400);
                }
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update password', 'error'=>$e], 400);
        }
    }
    

    public function changeOtherUserPassword(Request $request) {
        try {
            return \DB::transaction(function() use ($request) {
                $user = User::find($request->userId);
                $user->password = Hash::make($request->password);
                $user->isSync = false;
                $user->save();
                return $user;
            });
        }catch(\Exception $e) {
            return response()->json(['msg' => ' Can not able to update password', 'error'=>$e], 400);
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
