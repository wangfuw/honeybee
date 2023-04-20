<?php

namespace App\Http\Controllers\Admin;

use App\Models\Recharge;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;

class RechargeController extends AdminBaseController
{

    public function rechargeList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->id) {
            $condition[] = ["user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["recharge.id", "=", "-1"];
            }
        }
        $data = Recharge::join("users", "users.id", "=", "recharge.user_id")
            ->where($condition)
            ->orderByDesc("id")
            ->select("recharge.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function withdrawList(Request  $request){
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->id) {
            $condition[] = ["user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["withdraw.id", "=", "-1"];
            }
        }
        $data = Withdraw::join("users", "users.id", "=", "withdraw.user_id")
            ->where($condition)
            ->orderByDesc("id")
            ->select("withdraw.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }
}
