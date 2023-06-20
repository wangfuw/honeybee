<?php

namespace App\Http\Controllers\Admin;

use App\Models\Store;
use App\Models\StoreSupply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Predis\Command\Redis\EXEC;

class SupplyController extends AdminBaseController
{
    //平台伤号
    const MERCHANTNO = '888120600004799';
    //版本号
    const VERSION = '1.1';
    const CENTEN_V = '1.0';
    //入网方法
    const FUNCTION = "altmch.create";

    //页面签约
    const CONTENT_F = "altMchSign.getSignUrl";

    //上传文件
    const UPLOAD_F = "altMchPics.uploadPic";

    //平台商户密钥
    const M_SECRET = '82315039593446e3a81d61e71dfdac99';

    //MD5 加密
    const MD5 = 1;

    //rsa 加密
    const RSA = 2;
    //入网地址
    const url = "https://www.joinpay.com/allocFunds";

    public function __construct()
    {
        $this->middleware('admin.sign', ['except' => ['agree_back', 'upload_back','notify_url']]);
    }

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
        if($request->sign_status){
            $condition[] = ['sign_status','=',$request->sign_status];
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
        try {
            [$data,$sign] = $this->make_apply($apply_data);
            $data['sign'] = $sign;
            unset($data['key']);
            $ret = post_url(self::url,$data);
            $result = json_decode($ret,true);
            if($result['resp_code'] == "A1000")
            {
                $store->msg = $result['resp_msg'];
                $store->alt_mch_no = $result['data']['alt_mch_no'];
                $store->status = 2;
                $store->save();
                return  $this->executeSuccess($result['resp_msg']);
            }else{
                $store->msg = $result['resp_msg'];
                $store->status = 3;
                $store->save();
                return  $this->executeSuccess($result['resp_msg']);
            }

        }catch (\Exception $e){
            return  $this->executeSuccess($e);
        }
    }

    //签约协议一面签约签约 -- 发送签约合约
    public function content_agreement(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = StoreSupply::query()->where('id',$request->id)->first();
        [$data,$sign] = $this->agreement_data($store);
        $data['sign'] = $sign;
        dd($data);
        unset($data['key']);
        try {
            $ret = post_url(self::url,$data);
            $result = json_decode($ret,true);
            if($result['resp_code'] == "A1000"){
                $store->sign_url = $result['data']['sign_url'];
                $store->save();
            }
            return  $this->executeSuccess($result['resp_msg']);
        }catch (\Exception $e){
            return  $this->fail($e->getMessage());
        }

    }

    //上传汇聚材料
    public function upload_to_pay(Request $request)
    {
        $id = $request->id;
        $store_s = StoreSupply::query()->where('id',$id)->first();
        $store_info = Store::query()->select('id','front_image','back_image','images')->where('user_id',$store_s->user_id)->first();
        [$data,$sign] = $this->upload_data($store_s->alt_mch_no,$store_info);
        $data['sign'] = $sign;
        unset($data['key']);
        try {
            $ret = post_url(self::url,$data);
            $result = json_decode($ret,true);
            if($result['resp_code'] == "A1000"){
                $store_s->is_upload = 1;
                $store_s->save();
            }
            return  $this->executeSuccess($result['resp_msg']);
        }catch (\Exception $e){
            return  $this->fail($e->getMessage());
        }
    }

    //签约协议回调
    public function agree_back(Request $request)
    {
        $data = $request->all();
        if($data['resp_code'] == "A1000" && $data['resp_msg']=="success"){
            $alt_mch_no = $data['data']['alt_mch_no'];
            $store = StoreSupply::query()->where('alt_mch_no',$alt_mch_no)->first();
            $store->sign_status = $data['data']['sign_status'] == "P1000"?1:2;
            $store->sign_time = $data['data']['sign_time'];
            $store->sign_trx_no = $data['data']['sign_trx_no'];
            $store->save();
        }
    }

    //文件抽查回调 -- 发送消息给商家联系人
    public function upload_back(Request $request)
    {
        $data = $request->all();
        if($data['resp_code'] == "A1000"){
            $msg = $data['data']['approve_note'];
            $alt_mch_no = $data['data']['alt_mch_no'];
            $user_id = StoreSupply::query()->where('alt_mch_no',$alt_mch_no)->value('user_id');
            $phone = User::query()->where('id',$user_id)->value('phone');
            $content = "【源宇通商城】您在汇聚支付分账账户资料异常:".$msg."请联系重新上传支付材料";
            send_sms($phone,$content);
        }
    }

    protected function make_apply($apply_data=[])
    {
        $data = [
            'data'=>$apply_data,
            'mch_no'=>self::MERCHANTNO,
            'method'=>self::FUNCTION,
            'rand_str'=>rand_str_pay(32),
            'sign_type'=>"1",
            'version'=>self::VERSION,
            'key'=>self::M_SECRET,
        ];
        $str ='';
        foreach ($data as $key=>$value){
            if($key=='data'){
                $value = json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
            $str.="$key=$value&";
        }
        $str = rtrim($str,'&');
        return [$data,md5($str)];
    }

    //入住成功异步通知
    public function notify_url(Request $request){
        $data  = $request->all();
        return $this->success('异步返回成功',$data);
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

    //
    public function agreement_data($store)
    {
        $data = [
            'data'=>[
                'alt_mch_no'=>$store->alt_mch_no,
                'callback_url'=>env("CALL_BACK","http://beeapi.hitoo.xyz").'/hack/agree_back',
            ],
            'mch_no'=>self::MERCHANTNO,
            'method'=>self::CONTENT_F,
            'rand_str'=>rand_str_pay(32),
            'sign_type'=>self::MD5,
            'version'=>self::CENTEN_V,
            'key'=>self::M_SECRET,
        ];
        return $this->sign_str($data);
    }

    protected function upload_data($alt_mch_no,$image = [])
    {
        $data = [
            'data'=>[
                'alt_mch_no'=>$alt_mch_no,
                'card_positive'=>get_file_base64($image->front_image),
                'card_negative'=>get_file_base64($image->back_image),
                'trade_licence'=>isset($image->images['business_license'])?$this->get_file_base64($image->images['business_license']):"",
                'open_account_licence'=>isset($image->images['business_license'])?$this->get_file_base64($image->images['business_license']):"",
                'callback_url'=>env("CALL_BACK","http://beeapi.hitoo.xyz").'/hack/upload_back',
            ],
            'mch_no'=>self::MERCHANTNO,
            'method'=>self::UPLOAD_F,
            'rand_str'=>rand_str_pay(32),
            'sign_type'=>self::MD5,
            'version'=>self::CENTEN_V,
            'key'=>self::M_SECRET,
        ];
        return $this->sign_str($data);
    }

    protected function get_file_base64($file='')
    {
        $image_info             = getimagesize($file);
        $base64_image_content   = "data:{$image_info['mime']};base64," . base64_encode(file_get_contents($file));
        return $base64_image_content;
    }
    private function sign_str($data)
    {
        $str ='';
        foreach ($data as $key=>$value){
            if($key=='data'){
                $value = json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
            $str.="$key=$value&";
        }
        $str = rtrim($str,'&');
        return [$data,md5($str)];
    }
}
