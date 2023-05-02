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

        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["orders.created_at", ">=", strtotime($start)];
            $condition[] = ["orders.created_at", "<", strtotime($end)];
        }

        $condition[] = ["orders.store_id", "=", 0];
        $data = Order::join("users", "users.id", "=", "orders.user_id")
            ->where($condition)
            ->orderByDesc("orders.id")
            ->select("orders.*", "users.phone")
            ->paginate($size)->toArray();
        foreach ($data["data"] as $k => &$v) {
            $sku = MallSku::find($v["sku_id"]);
            $spu = MallSpu::find($sku["spu_id"]);
            $v["spu"] = $spu;
            $v["sku"] = $sku;
            $v["address"]["address"] = city_name($v["address"]["area"]);
        }
        return $this->executeSuccess("请求", $data);
    }

    public function sendSku(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("ID");
        }
        if (!$request->filled("express_no")) {
            return $this->fail("快递单号必填");
        }
        if (!$request->filled("express_name")) {
            return $this->fail("快递单号必填");
        }
        $order = Order::find($request->id);
        if (!$order) {
            return $this->error("ID");
        }
        if($order->status != 2){
            return $this->fail("订单未支付");
        }
        $order->express_no = $request->express_no;
        $order->express_name = $request->express_name;
        $order->express_status = 1;
        $order->save();
        return $this->executeSuccess("发货");
    }

    public function shopOrderList(Request $request)
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
        if ($request->store_id) {
            $condition[] = ["store_id", "=", $request->store_id];
        }
        if ($request->store_phone) {
            $user = User::where("phone", $request->store_phone)->first();
            if ($user) {
                $condition[] = ["store_id", "=", $user->id];
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
        $condition[] = ["orders.store_id", ">=", 1];
        $data = Order::join("users", "users.id", "=", "orders.user_id")
            ->where($condition)
            ->orderByDesc("orders.id")
            ->select("orders.*", "users.phone")
            ->paginate($size)->toArray();
        foreach ($data["data"] as $k => &$v) {
            $sku = MallSku::find($v["sku_id"]);
            $spu = MallSpu::find($sku["spu_id"]);
            $u = User::find($v["store_id"]);
            $v["spu"] = $spu;
            $v["sku"] = $sku;
            $v["address"]["address"] = city_name($v["address"]["area"]);
            $v["store_phone"] = $u["phone"];
        }
        return $this->executeSuccess("请求", $data);
    }
}
