<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\UserMoney;
use Illuminate\Http\Request;

class MoneyController extends AdminBaseController
{
    public function userMoneyList(Request $request)
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
                $condition[] = ["user_money.id", "=", "-1"];
            }
        }
        if($request->status){
            $condition[] = ["status","=",$request->status];
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["user_money.created_at", ">=", strtotime($start)];
            $condition[] = ["user_money.created_at", "<", strtotime($end)];
        }
        $data = UserMoney::join("users", "users.id", "=", "recharge.user_id")
            ->where($condition)
            ->orderByDesc("id")
            ->select("user_money.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editUserMoney(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("id");
        }
        $um = UserMoney::find($request->id);
        if (!$um) {
            return $this->error("id");
        }
        $status = $request->filled("status", 2);
        if ($status == 2) {
            if (!$request->filled("note")) {
                return $this->fail("驳回原因必填");
            }
            $um->note = $request->note;
        }
        $um->status = $status;
        $um->admin_id = auth("admin")->user()->id;
        $um->save();
        return $this->executeSuccess("操作");
    }

    public function moneyTradeList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->from_id) {
            $condition[] = ["from_id", "=", $request->from_id];
        }
        if ($request->to_id) {
            $condition[] = ["to_id", "=", $request->to_id];
        }
        if ($request->from_phone) {
            $user = User::where("phone", $request->from_phone)->first();
            if ($user) {
                $condition[] = ["from_id", "=", $user->id];
            } else {
                $condition[] = ["id", "=", "-1"];
            }
        }
        if ($request->to_phone) {
            $user = User::where("phone", $request->from_phone)->first();
            if ($user) {
                $condition[] = ["to_id", "=", $user->id];
            } else {
                $condition[] = ["id", "=", "-1"];
            }
        }

        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $data = UserMoney::with(['fromUser' => function ($query) {
            return $query->select("phone as from_phone");
        }, 'toUser' => function ($query) {
            return $query->select("phone as to_phone");
        }])->where($condition)
            ->orderByDesc("id")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }
}
