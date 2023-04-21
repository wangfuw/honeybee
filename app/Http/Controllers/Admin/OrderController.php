<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends AdminBaseController
{
    public function orderList(Request $request)
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
                $condition[] = ["orders.id", "=", "-1"];
            }
        }
        if ($request->filled("status")) {
            $condition[] = ["orders.status", "=", $request->status];
        }
        if ($request->filled("express_status")) {
            $condition[] = ["orders.express_status", "=", $request->express_status];
        }
        if ($request->filled("order_no")) {
            $condition[] = ["orders.order_no", "=", $request->order_no];
        }
        $condition[] = ["orders.store_id", "=", 0];
        $data = Order::join("users", "users.id", "=", "orders.user_id")
            ->where($condition)
            ->orderByDesc("orders.id")
            ->select("orders.*", "users.phone")
            ->paginate($size);
        foreach ($data->data as $k => &$v) {
            $sku = MallSku::find($v["sku_id"]);
            $spu = MallSpu::find($sku["spu_id"]);
            $v["spu"] = $spu;
            $v["sku"] = $sku;
        }
        return $this->executeSuccess("请求", $data);
    }
}
