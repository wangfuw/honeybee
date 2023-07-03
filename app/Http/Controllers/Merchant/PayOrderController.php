<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Admin\SupplyController;
use App\Http\Controllers\Controller;
use App\Models\CashOut;
use App\Models\PayOrder;
use App\Models\StoreSupply;
use App\Validate\ApplyValidate;
use Illuminate\Http\Request;

class PayOrderController extends MerchantBaseController
{
    private $validate;

    public function __construct(ApplyValidate $validate){
        $this->validate = $validate;
    }
    public function payOrderList(Request $request){
        $size = $request->size ?? 5;
        $pay_status = $request->pay_status;
        $user = auth("merchant")->user();
        $data = PayOrder::query()->where('store_id',$user->id)
            ->when($pay_status,function ($query) use($pay_status){
                return $query->where('pay_status',$pay_status);
            })
            ->orderByDesc("id")
            ->select("*")
            ->paginate($size)
            ->toArray();
        $alt_mch_no = StoreSupply::query()->where('user_id',$user->id)->value('alt_mch_no');
        $out_money = CashOut::query()->where('user_id',$user->id)->where('status',2)->sum('amount');
        foreach ($data["data"] as $k => &$v) {
            $v["alt_mch_no"] = $alt_mch_no;
            if($v['alt_info']){
                $temp = explode('|',$v['alt_info']);
                $f = explode('-',$temp[1]);
                $v["money"] = $f["2"];
            }
        }
        $all = PayOrder::query()->where('store_id',$user->id)
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
        return $this->executeSuccess("请求", ["data"=>$data,"all_money"=>round($all_money,2),"out_money"=>round($out_money,2),"leave_money"=>bcsub($all_money,$out_money,2)]);
    }

    public function outCashList(Request $request)
    {
        $size = $request->size ?? 5;
        $status = $request->status;
        $user = auth("merchant")->user();
        $data = CashOut::query()->where('user_id',$user->id)
            ->when($status,function ($query) use($status){
                return $query->where('status',$status);
            })
            ->orderByDesc("id")
            ->select("*")
            ->paginate($size)
            ->toArray();
        return $this->success('请求',$data);
    }

    public function applyCash(Request $request)
    {
        $user = auth("merchant")->user();
        $data = $request->only(['bank_name','bank_card','fax_name','amount']);
        if(!$this->validate->scene('out')->check($data)){
            return $this->fail($this->validate->getError());
        }

        $ret = CashOut::query()->create(
            [
                "user_id" => $user->id,
                "bank_card" => $data["bank_card"],
                "bank_name" => $data["bank_name"],
                "fax_name" => $data["fax_name"],
                "amount" => $data["amount"]
            ]
        );
        if($ret) return $this->success("申请成功,待审核打款");
        return $this->fail('申请失败');
    }
}
