<?php

namespace App\Http\Controllers\Merchant;

use App\Models\Order;
use App\Models\Score;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class HomeController extends MerchantBaseController
{
    public function dealLine(Request $request)
    {
        $flag = $request->input("flag", 1);
        $condition = [];
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $condition[] = ["status", "=", "2"];
        $user = auth("merchant")->user();
        $condition[] = ["store_id", "=", $user->id];
        if ($flag == 1) {
            $data = Order::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as date"), DB::raw('SUM(coin_num) as num')])
                ->toArray();
        } else {
            $data = Order::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m') as date"), DB::raw('SUM(coin_num) as num')])
                ->toArray();
        }

        $dates = array_column($data, "date");
        $nums = array_column($data, "num");
        return $this->executeSuccess("请求", compact('dates', 'nums'));
    }

    public function storeInfo(Request $request)
    {
        $user = auth("merchant")->user();
        $store = Store::where("user_id", $user->id)->where("type",1)->first();
        if (!$store || $store->status != 1) {
            return $this->fail("您不是商家");
        }
        $data = $store->toArray();
        $url = "beepay?id=$store->user_id";
        $img =  QrCode::format('png')->size(200)->generate($url);
        $qr = 'data:image/png;base64,' . base64_encode($img );
        $data["qr"] = $qr;
        return $this->executeSuccess("请求", $data);
    }

    public function bindPay(Request $request)
    {
        $user = auth("merchant")->user();
        $store = Store::where("user_id", $user->id)->where("type",1)->first();
        if (!$store || $store->status != 1) {
            return $this->fail("您不是商家");
        }
        if ($store->on_line != 2) {
            return $this->fail("您不是线下商家");
        }
        if ($request->filled("wx_payment")) {
            if (!regex($request->wx_payment, "payAccount")) {
                return $this->fail("账户格式错误");
            }
            $store->wx_payment = $request->wx_payment;
        }
        if ($request->filled("zfb_payment")) {
            if (!regex($request->zfb_payment, "payAccount")) {
                return $this->fail("账户格式错误");
            }
            $store->zfb_payment = $request->zfb_payment;
        }
        if($request->filled("longitude")){
            if(!is_numeric($request->longitude)){
                return $this->fail("经度格式错误");
            }
            $store->longitude = $request->longitude;
        }
        if(!$request->filled("latitude")){
            if(!is_numeric($request->latitude)){
                return $this->fail("纬度格式错误");
            }
            $store->latitude = $request->latitude;
        }
        $store->save();
        return $this->executeSuccess("操作");
    }
}
