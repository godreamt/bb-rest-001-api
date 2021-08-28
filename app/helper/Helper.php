<?php
namespace App\helper;

use App\Branch;
use Illuminate\Support\Facades\Config;
use Haruncpi\LaravelIdGenerator\IdGenerator;

class Helper {
    public static function GenerateId($user, $table, $branchCode=null) {
        
        if($branchCode != null) {
            $prefix = $branchCode.Config::get('app.hosted');
        }else if($user->branch instanceof Branch) {
            $prefix = $user->branch->branchCode.Config::get('app.hosted');
        }else {
            $prefix = substr(($user->roles ?? ""), 0, 3).Config::get('app.hosted');
        }
        return IdGenerator::generate(['table' => $table, 'length' => 12, 'prefix' => $prefix, 'reset_on_prefix_change' => true]);
    }
}

?>