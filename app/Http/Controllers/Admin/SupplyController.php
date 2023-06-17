<?php

namespace App\Http\Controllers\Admin;

use App\Models\StoreSupply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplyController extends AdminBaseController
{

    //平台伤号
    const MERCHANTNO = '888120600004799';
    //版本号
    const VERSION = '1.1';
    //入网方法
    const FUNCTION = "altmch.create";

    //平台商户密钥
    const M_SECRET = '82315039593446e3a81d61e71dfdac99';

    //MD5 加密
    const MD5 = 1;

    //rsa 加密
    const RSA = 2;
    //申请列表
    public function supplyList(Request $request)
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
                $condition[] = ["store_supply.id", "=", "-1"];
            }
        }
        if($request->store_name){
            $condition[] = ['mch_name','like','%'.$request->store_name.'%'];
        }
        if($request->status){
            $condition[] = ['status','=',$request->status];
        }

        $data = StoreSupply::join("users", "users.id", "=", "store_supply.user_id")
            ->where($condition)
            ->orderBy("store_supply.status")
            ->select("store_supply.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    //提交入住申请
    public function apply(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = StoreSupply::query()->where('id',$request->id)->first();
        $apply_data = $this->apply_data($store);
        $apply = $this->make_data($apply_data);
        $to_sign = formatBizQueryParaMap($apply,false);
        $apply['sign'] = sign_ru_zhu($to_sign,self::M_SECRET);
        dd(json_encode($apply));
        $url = "https://www.joinpay.com/allocFunds";
        $ret = post_url($url,$apply);
        //发送请求入住
        dd($ret);
        return $this->executeSuccess("操作");
    }

    protected function make_data($apply_data = [])
    {
        $data = [
            'method'=>self::FUNCTION,
            'version'=>self::VERSION,
            'data'=>json_encode($apply_data,true),
            'rand_str'=>rand_str_pay(32),
            'sign_type'=>self::MD5,
            'mch_no'=>self::MERCHANTNO,
        ];
        return $data;
    }

    protected function apply_data($store)
    {
        //密钥串拼接在待签名字符串最后并进行加密得到的结果为 sign 值
        $data = [
            "login_name" => User::query()->where('id',$store->user_id)->value('phone'),
            "alt_mch_name"=>$store->mch_name,
            "alt_merchant_type"=>$store->merchant_type,
            "busi_contact_name"=>$store->contact_name,
            "busi_contact_mobile_no"=>$store->contact_mobile_no,
            "phone_no"=>$store->phone_no,
            "manage_scope"=>$store->scope,
            "manage_addr"=>$store->addr,
            "legal_person"=>$store->legal_person,
            "id_card_no"=>$store->id_card_no,
            "license_no"=>$store->license_no,
            "sett_mode"=>$store->sett_mode,
            "sett_date_type"=>$store->sett_date_type,
            "risk_day"=>$store->risk_day,
            "bank_account_type"=>$store->bank_account_type,
            "bank_account_name"=>$store->bank_account_name,
            "bank_account_no"=>$store->bank_account_no,
            "bank_channel_no"=>$store->bank_channel,
            "notify_url"=>"",
        ];
        return $data;
    }
}
