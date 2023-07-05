<?php

namespace App\Http\Controllers\Admin;

use App\Models\CashOut;
use App\Models\PayOrder;
use App\Models\Store;
use App\Models\StoreSupply;
use App\Models\TicketPay;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

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
            $condition[] = ["store.on_line","=",$request->on_line];
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
        if (!is_numeric($num)) {
            return $this->error("数量");
        }
        $rate = $request->input("rate",8);
        if(!in_array($rate,[8,16,32])){
            return  $this->fail('让利比列只支持8,16,32');
        }
        $store->amount += $num;
        $store->rate = $rate;
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
            $user = Store::where("mobile", $request->phone)->first();
            if ($user) {
                $condition[] = ["store_id", "=", $user->user_id];
            } else {
                $condition[] = ["pay_order.id", "=", "-1"];
            }
        }

        if($request->pay_status){
            $condition[] = ["pay_status", "=", $request->pay_status];
        }
        $data = PayOrder::join("store", "store.user_id", "=", "pay_order.store_id")->join("users","users.id","=","pay_order.store_id")
            ->where($condition)
            ->orderBy("pay_order.pay_time","desc")
            ->select("pay_order.*", "store.store_name","store.mobile")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function applyCash(Request $request){
        $size = $request->size ?? $this->size;
        $condition = [];
        if($request->id){
            $condition[] = ["user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = Store::where("mobile", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->user_id];
            } else {
                $condition[] = ["id", "=", "-1"];
            }
        }
        if($request->status){
            $condition[] = ["status", "=", $request->status];
        }
        $data = CashOut::join("store", "store.user_id", "=", "cash_out.user_id")
            ->where($condition)
            ->orderby('cash_out.status','asc')
            ->orderBy('created_at','desc')
            ->select("cash_out.*", "store.store_name","store.mobile","store.zfb_payment","store.wx_payment")
            ->paginate($size)->toArray();

        foreach($data['data'] as $k=>&$v){
            [$alt_mch_no,$all_money,$out_money] = $this->getCashInfo($v["user_id"]);
            $v["alt_mch_no"] = $alt_mch_no;
            $v["all_money"] = round($all_money,2);
            $v["leave_money"] = bcsub($all_money,$out_money,2);
        }
        return $this->executeSuccess("请求", $data);
    }

    public function checkCashApply(Request $request)
    {
        $data = $request->only(['id','status','note']);
        $cashApply =  CashOut::query()->where('id',$data['id'])->where('status',1)->first();
        if(!$cashApply){
            return $this->fail("无该记录");
        }
        try{
            $cashApply->status = $data['status'];
            $cashApply->note = $data['note'];
            $cashApply->save();
            return $this->executeSuccess("审核",[]);
        }catch (\Exception $e){
            return $this->fail($e->getMessage());
        }
    }

    public function ticketPay(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if($request->id){
            $condition[] = ["ticket_pay.user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = Store::where("mobile", $request->phone)->first();
            if ($user) {
                $condition[] = ["ticket_pay.user_id", "=", $user->user_id];
            } else {
                $condition[] = ["ticket_pay.id", "=", "-1"];
            }
        }
        $data = TicketPay::join("store", "store.user_id", "=", "ticket_pay.user_id")
            ->where($condition)
            ->orderBy('created_at','desc')
            ->select("ticket_pay.*", "store.store_name","store.mobile")
            ->paginate($size)->toArray();
        return $this->executeSuccess("请求", $data);
    }

    protected function getCashInfo($id)
    {
        $alt_mch_no = StoreSupply::query()->where('user_id',$id)->value('alt_mch_no');
        $out_money = CashOut::query()->where('user_id',$id)->where('status',2)->sum('amount');
        $all = PayOrder::query()->where('store_id',$id)
            ->where('pay_status',100)
            ->orderByDesc("id")
            ->select("*")->get();
        $all_money = 0;
        foreach ($all as $a) {
            $a["alt_mch_no"] = $alt_mch_no;
            if($a['alt_info']){
                $temp = explode('|',$a['alt_info']);
                $f = explode('-',$temp[1]);
                $all_money += $f["2"];
            }
        }
        return [$alt_mch_no,$all_money,$out_money];
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
            $condition[] = ["store.on_line","=",$request->on_line];
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
