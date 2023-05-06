<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsacNode;
use App\Models\AsacTrade;
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
            $an = AsacNode::where("user_id", $request->id)->first();
            if ($an) {
                $condition[] = ["from_address", "=", $an->wallet_address];
            } else {
                $condition[] = ["id", "=", -1];
            }
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $an = AsacNode::where("user_id", $request->id)->first();
                if ($an) {
                    $condition[] = ["from_address", "=", $an->wallet_address];
                } else {
                    $condition[] = ["id", "=", -1];
                }
            } else {
                $condition[] = ["id", "=", -1];
            }
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $condition[] = ["type", "=", 3];
        $data = AsacTrade::where($condition)
            ->orderByDesc("id")
            ->paginate($size)->toArray();
        foreach ($data["data"] as $k => &$v) {
            $an = AsacNode::where("wallet_address", $v["from_address"])->first();
            $user = User::find($an->user_id);
            $v["user"] = $user->toArray();
        }
        return $this->executeSuccess("请求", $data);
    }

    public function withdrawList(Request $request)
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
                $condition[] = ["withdraw.id", "=", "-1"];
            }
        }

        if($request->filled("status")){
            $condition[] = ["withdraw.status","=",$request->status];
        }

        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["withdraw.created_at", ">=", strtotime($start)];
            $condition[] = ["withdraw.created_at", "<", strtotime($end)];
        }
        $data = Withdraw::join("users", "users.id", "=", "withdraw.user_id")
            ->where($condition)
            ->orderBy("status")
            ->select("withdraw.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editWithdraw(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return $this->error("ID");
        }
        $flag = $request->flag;
        if ($flag != 1 && $flag != 2) {
            return $this->fail("操作");
        }

        if ($flag == 2) {
            if (!$request->err) {
                return $this->fail("驳回原因必填");
            }
        }
        if ($flag == 1) {
            // 接三方 ,写 币的交易记录
            return $this->fail("第三方接口未接通");
        }
        $withdraw = Withdraw::find($request->id);
        if (!$withdraw) {
            return $this->error("ID");
        }
        $user = User::find($withdraw->user_id);
        $user->coin_num += $withdraw->amount;
        $user->save();
        return $this->executeSuccess("驳回");
    }
}
