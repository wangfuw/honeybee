<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsacBlock;
use App\Models\AsacDestory;
use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use App\Models\User;
use Illuminate\Http\Request;

class BlockController extends AdminBaseController
{
    public function blockList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("id")) {
            $condition[] = ["id", "=", $request->id];
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $data = AsacBlock::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function tradeList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("id")) {
            $condition[] = ["id", "=", $request->id];
        }
        if ($request->filled("block_id")) {
            $condition[] = ["block_id", "=", $request->block_id];
        }
        if ($request->filled("from_address")) {
            $condition[] = ["from_address", "=", $request->from_address];
        }
        if ($request->filled("to_address")) {
            $condition[] = ["to_address", "=", $request->to_address];
        }
        if ($request->filled("trade_hash")) {
            $condition[] = ["trade_hash", "=", $request->trade_hash];
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $data = AsacTrade::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function destroyList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->id) {
            $condition[] = ["asac_destory.user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["asac_destory.id", "=", "-1"];
            }
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["asac_destory.created_at", ">=", strtotime($start)];
            $condition[] = ["asac_destory.created_at", "<", strtotime($end)];
        }
        $data = AsacDestory::join("users", "users.id", "=", "asac_destory.user_id")
            ->where($condition)
            ->orderByDesc("id")
            ->select("asac_destory.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function asacInfo(Request $request)
    {
        $data = Asaconfig::first();
        return $this->executeSuccess("请求", $data);
    }

    public function editAsac(Request $request)
    {
        $price = $request->input("last_price", 10);
        $config = Asaconfig::first();
        $config->last_price = $price;
        $config->save();
        return $this->executeSuccess("修改");
    }


    public function addressList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("user_id")) {
            $condition[] = ["user_id", "=", $request->user_id];
        }

        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["asac_node.id", "=", "-1"];
            }
        }

        if($request->address){
            $condition[] = ["wallet_address","=",$request->address];
        }

        $data = AsacNode::join("users", "users.id", "=", "asac_node.user_id")
            ->where($condition)
            ->select("asac_node.*","users.phone")
            ->paginate($size);
        return $this->executeSuccess("request", $data);
    }
}
