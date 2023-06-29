<?php

namespace App\Http\Controllers\Admin;

use App\Models\PayOrder;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreController extends AdminBaseController
{

    public function storeList(Request $request)
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
                $condition[] = ["store.id", "=", "-1"];
            }
        }
        if ($request->filled("store_name")) {
            $condition[] = ["store.store_name", "like", "%$request->store_name%"];
        }
        if ($request->filled("mobile")) {
            $condition[] = ["store.mobile", "=", $request->mobile];
        }
        if($request->filled("on_line")){
            $condition[] = ["store,on_line","=",$request->on_line];
        }
        $condition[] =["store.type", "=", 1];

        $data = Store::join("users", "users.id", "=", "store.user_id")
            ->where($condition)
            ->orderBy("store.type")
            ->select("store.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editStore(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = Store::find($request->id);
        if ($request->filled('status')) {
            $store->status = $request->status;
            $store->save();
            return $this->executeSuccess("操作");
        }
        return $this->executeSuccess("操作");
    }

    public function addAmount(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = Store::find($request->id);
        if ($store->type != 1) {
            return $this->fail("商家未通过审核");
        }
        if ($store->on_line != 2) {
            return $this->fail("不是线下商家");
        }
        $num = $request->input("num", 0);
        if (!is_numeric($num) || $num <= 0) {
            return $this->error("数量");
        }
        $rate = $request->input("rate",8);
        if(!in_array($rate,[8,16,32])){
            return  $this->fail('让利比列只支持8,16,32');
        }
        $store->amount += $num;
        $store->save();
        return $this->executeSuccess("操作");
    }

    //支付订单
    public function payOrder(Request $request){
        $size = $request->size ?? $this->size;
        $condition = [];
        if($request->id){
            $condition[] = ["store_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["store_id", "=", $user->id];
            } else {
                $condition[] = ["pay_order.id", "=", "-1"];
            }
        }
        if($request->status){
            $condition[] = ["status", "=", $request->status];
        }
        $data = PayOrder::join("store", "store.user_id", "=", "pay_order.store_id")->join("users","users.id","=","pay_order.store_id")
            ->where($condition)
            ->orderBy("pay_order.pay_time desc")
            ->select("pay_order.*", "store.store_name","store.mobile")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function storeReviewList(Request $request)
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
                $condition[] = ["store.id", "=", "-1"];
            }
        }
        if ($request->filled("store_name")) {
            $condition[] = ["store.store_name", "like", "%$request->store_name%"];
        }
        if ($request->filled("mobile")) {
            $condition[] = ["store.mobile", "=", $request->mobile];
        }
        if($request->filled("on_line")){
            $condition[] = ["store,on_line","=",$request->on_line];
        }
        $condition[] =["store.type", "=", 0];

        $data = Store::join("users", "users.id", "=", "store.user_id")
            ->where($condition)
            ->orderBy("store.type")
            ->select("store.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editReview(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = Store::find($request->id);

        if ($request->type) {
            $store->type = $request->type;
            if ($request->type == 1) {
                $user = User::find($store->user_id);
                DB::beginTransaction();
                try {
                    $store->save();
                    User::where("id", $store->user_id)->update(["is_shop" => 1]);
                    DB::commit();
                    // 发送短信提醒，店铺通过，附带登录链接
//                    $url = config("app.merchant","http://merchant.yuanyutong.shop");
                    $content = "【源宇通】您的开店申请已通过，请前往管理您的店铺，登录的账号为您的手机号，密码与APP端的密码一致";
                    $res = send_sms($user->phone, $content);
                    return $this->executeSuccess("操作");
                } catch (\Exception $exception) {
                    DB::rollBack();
                    Log::error($exception->getMessage());
                    return $this->executeFail("操作");
                }
            } else {
                if (!$request->filled("note")) {
                    return $this->fail("请填写不通过的原因");
                }
                $store->note = $request->note;
                $store->save();
                return $this->executeSuccess("操作");
            }
        }
        return $this->executeSuccess("操作");
    }
}
