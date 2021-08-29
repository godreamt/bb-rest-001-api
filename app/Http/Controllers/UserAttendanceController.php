<?php

namespace App\Http\Controllers;

use App\User;
use App\UserAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAttendanceController extends Controller
{
    public function updateAttendace(Request $request) {
        return \DB::transaction(function() use ($request) {
            if(empty($request->users)) {
                $users = [$request->all()];
            }else {
                $users =  $request->users;
            }
            foreach($users as $user) {
                $affDate = new \Datetime($user['effectedDate']);
                if($affDate > new \Datetime()) {
                    return response()->json(['msg' => 'Invalid date selected'], 400);
                }
                $attendance = UserAttendance::where('effectedDate', $affDate)->where('user_id', $user['user_id'])->first();
                if(!($attendance instanceof UserAttendance)) {
                    $attendance = new UserAttendance();
                }
                $attendance->effectedDate = $affDate;
                $attendance->user_id = $user['user_id'];
                $attendance->isPresent = $request->isPresent ?? false;
                $attendance->description = $request->description;
                $attendance->isSync = false;
                $attendance->save();
            }
            return response()->json(['msg'=>'saved successfully'], 200);
        });
    }

    public function getAttendance(Request $request) {
        $loggedUser = Auth::user();
        $startDate = new \Datetime($request->startDate);
        $endDate = (new \Datetime($request->endDate))->modify('+1 day');
        $users= User::select('*')->where('attendaceRequired', true);
        if($loggedUser->roles != 'Super Admin') {
            $users = $users->where('company_id',  $loggedUser->company_id);
        }
        if($loggedUser->roles != 'Super Admin' && $loggedUser->roles != 'Company Admin' && $loggedUser->roles != 'Company Accountant') {
            $users = $users->where('branch_id',  $loggedUser->branch_id);
        }
        $users = $users->get();
        $result = [];
        foreach($users as $user) {
            $user['attendance'] = UserAttendance::select('user_attendances.*')->whereBetween('effectedDate', [$startDate, $endDate])->where('user_id', $user->id)->get();
            if(!$user->isActive && sizeof($user['attendance']) > 0) {
                $result[] = $user;
            }
        }
        return $result;
    }
}
