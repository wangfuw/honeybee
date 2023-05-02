<?php

namespace App\Http\Controllers\Merchant;

use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends MerchantBaseController
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

        $user = auth("merchant")->user();
        $condition[] = ["orders.store_id", "=", $user->id];
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
        $user = auth("merchant")->user();
        if($order->store_id != $user->id){
            return $this->error("ID");
        }
        $order->express_no = $request->express_no;
        $order->express_name = $request->express_name;
        $order->express_status = 1;
        $order->save();
        return $this->executeSuccess("发货");
    }
}
