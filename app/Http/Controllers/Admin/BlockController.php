<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsacBlock;
use App\Models\AsacDestory;
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
            $condition[] = ["asac_destroy.user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["asac_destroy.id", "=", "-1"];
            }
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["asac_destroy.created_at", ">=", strtotime($start)];
            $condition[] = ["asac_destroy.created_at", "<", strtotime($end)];
        }
        $data = AsacDestory::join("users", "users.id", "=", "asac_destroy.user_id")
            ->where($condition)
            ->orderByDesc("id")
            ->select("asac_destroy.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function asacInfo(Request $request)
    {
        $data = Asaconfig::first();
        return $this->executeSuccess("请求", $data);
    }
}
