<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\AsacNode;
use App\Models\Config;
use App\Models\PayOrder;
use App\Models\Score;
use App\Models\Store;
use App\Models\StoreSupply;
use App\Models\User;
use App\Validate\PayValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{
    //微信appid
    const WX_APPID="wx1f331c16c0da4ede";
//    const WX_APPID="wx590a33db7af62cba";
    //微信密钥
    const WX_SECRET="3b5c0e3bd111f8ab14d338b3e67140d0";
//    const WX_SECRET="7b8746071ca479736fa29a0fc06eca4e";

    const VERSION = '2.2';

    //平台编号
    const MERCHANTNO = '888120600004799';

    //报备商户号
    const TRADEMERCHANTNO = "777190600517604";
   //报备商户号 备用
    const TRADEMERCHANTNO_BAT = "777163800517605";

    const CUR = 1; //人名币
    const CAI = 1; //1分账
    const CAI_TYPE = 11; //时时分账
    //md5 密钥
    const M_SECRET = '82315039593446e3a81d61e71dfdac99';

    //分配率
    const QE_RATE = 0.7;
    const PAY_APPID="";

    const PAY_SECRET="";

    //支付url
    const URL = "https://www.joinpay.com/trade/uniPayApi.action";

    private $down_url;
    public function __construct(PayValidate $validate)
    {
        $this->validate = $validate;
        $this->middleware('auth:api', ['except' => ['to_pey','notify_url','qf_alt_url']]);
    }

    //输入电话 金额 选着支付方式 调取api/to_pay （注册用户，异步生成预支付订单）返回订单信息 拉去微信或支付宝jsapi支付，支付成功给用户加消费积分，商家加积分
    public function to_pey(Request $request){
        //测试
        $p_data = $request->only(['code','id','phone','money','pay_type']);
        if(!$this->validate->scene('pre_pay')->check($p_data)){
            return $this->success($this->validate->getError());
        }
        if(!check_phone($p_data['phone'])){
            return $this->fail('电话号码错误');
        }
        try {
            if($p_data['pay_type'] == 'wx_pay'){
                //获取用户openid
                $info = curl_get("https://api.weixin.qq.com/sns/oauth2/access_token",["appid"=>self::WX_APPID,"secret"=>self::WX_SECRET,'code'=>$p_data['code'],'grant_type'=>'authorization_code']);
                //根据用户id 获取用户商户编号
                $store_info = StoreSupply::query()->where('user_id',$p_data['id'])->first();
                //调汇聚接口生成预支付订单
                $this->auto_register($p_data);
                [$data,$sign] = $this->pre_data($p_data,$info['openid'],$store_info->alt_mch_no);
                unset($data['key']);
                $data['qe_AltInfo'] = json_encode($data['qe_AltInfo'],JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                $data['hmac'] = $sign;

                PayOrder::create([
                    'order_no'=>$data['p2_OrderNo'],
                    'merchant_no'=>$data['p1_MerchantNo'],
                    'amount'=> $data['money'],
                    'cur' => self::CUR,
                    'fre_code'=>'WEIXIN_GZH',
                    'openid' => $info['openid'],
                    'phone'  => $p_data['phone'],
                    'store_id' => $p_data['id']
                ]);

                $result = post_url_pay(self::URL,$data);
                $ret = json_decode($result,true);
                if($ret['ra_Code'] == 100){
                    return $this->success('请求成功',json_decode($ret["rc_Result"],true));
                }else{
                    return $this->fail($ret['rb_CodeMsg']);
                }
            }else{
                //支付宝支付

            }

        }catch (\Exception $e){
            return  $this->fail($e->getMessage());
        }

    }

    //wx - 支付参数
    protected function pre_data($data,$openid,$mch_no)
    {
        $data =  [
            'p0_Version'=>self::VERSION,
            'p1_MerchantNo'=>self::MERCHANTNO,
            'p2_OrderNo'=>generateOrderNumber(),
            'p3_Amount'=>$data['money'],
            'p4_Cur'=>self::CUR,
            'p5_ProductName'=>'源宇通下线商品',
            //服务器异步通知地址
            'p9_NotifyUrl'=>env("CALL_BACK","http://api.yuanyutong.shop").'/api/notify_url',
            'q1_FrpCode'=>'WEIXIN_GZH',
            'q5_OpenId'=>$openid,
            'q7_AppId'=>self::WX_APPID,
            'qa_TradeMerchantNo'=>self::TRADEMERCHANTNO,
            'qc_IsAlt'=>self::CAI,
            'qd_AltType'=>self::CAI_TYPE,
            'qe_AltInfo'=>[
                [
                    'altMchNo'=>$mch_no,"altAmount"=>bcmul($data['money'],
                    self::QE_RATE,2)
                ]
            ],
            //实时分账在支付完成后，分账信息会异步通知商户结果，回调 qf_AltUrl 中的地址
            'qf_AltUrl'=>env("CALL_BACK","http://api.yuanyutong.shop").'/api/qf_alt_url',
            'qi_FqSellerPercen' => 0,
            'key'=> self::M_SECRET
        ];
        return $this->sign_str($data);
    }
    //加密
    private function sign_str($data)
    {
        $str ='';
        foreach ($data as $key=>$value){
            if($key=='qe_AltInfo'){
                $value = json_encode($value,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            }
            $str.="$value";
        }
        $str = rtrim($str,'');
        Log::info($str);
        return [$data,md5($str)];
    }

    //服务器异步通知地址
    public function notify_url(Request $request)
    {
        $data = $request->all();
        if($data){
            $info = PayOrder::query()->where('order_no',$data['r2_OrderNo'])->first();
            $info->pay_status = $data['r6_Status'];
            $info->trx_no = $data['r7_TrxNo'];
            $info->bank_order_no = $data['r8_BankOrderNo'];
            $info->bank_trx_no = isset($data['r9_BankTrxNo'])?$data['r9_BankTrxNo']:'';
            $info->free = $data["r10_Fee"];
            $info->pay_time = urldecode(urldecode($data['ra_PayTime']));
            $info->bank_code = $data['rc_BankCode'];
            $info->card_type = $data['rh_cardType'];
            $info->bank_type = $data['ri_BankType'];
            $info->save();
            if($data['r6_Status'] == 100){
                //支付成功给用户加消费积分
                $this->auto_register($info);
                $user = User::query()->where('phone',$info->phone)->first();
                if($user){
                    $user->sale_score += $info->money;
                    $user->sale_score_total += $info->money;
                    $user->save();
                }
            }
            return 'success';
        }else{
            return 'error';
        }
    }
    //分账通知地
    public function qf_alt_url(Request $request)
    {
        $data = $request->all();
        if($data){
            $info = PayOrder::query()->where('order_no',$data['r2_OrderNo'])->first();
            $info->alt_info = isset($data["r6_altInfo"])?$data["r6_altInfo"]:'';
            $info->f_trx_no = isset($data["r3_TrxNo"])?$data["r3_TrxNo"]:'';
            $info->save();
            if(isset($data["r6_altInfo"])){
                //给商家增加积分  -- todo

            }
        }
    }
    //自动注册
    protected function auto_register($info):void
    {
        $user = User::query()->where('phone',$info->phone)->first();
        if(!$user){
            $s_users = User::query()->where('id',$info->store_id)->first();
            $num = Config::register_give_lucky();
            $d = [
                'phone' => $info->phone,
                'invite_code'=> inviteCode($info->phone),
                //初始密码123456
                'password'=>Hash::make('123456'),
                'master_id' => $s_users->id,
                'master_pos'=>','.$user->id.$s_users->master_pos.','??'',
                'luck_score'=>$num??180,
                'max_luck_num'=>$num??180,
                'sale_password'=>'123456'
            ];
            $user = User::create($d);
            //注册赠送幸运值
            Score::query()->create([
                "user_id"=>$user->id,
                "flag"   => 1,
                "num"    =>$num??180,
                "type"=>3,
                "f_type"=>Score::REGISTER_REWARD
            ]);
            AsacNode::create([
                'user_id' => $user->id,
                'wallet_address' => rand_str_pay(40),
                'private_key' => rand_str_pay(64)
            ]);
            $content = "【源宇通商城】您已注册源宇通商城。您的账户是:".$info->phone."您的登录初始密码是:123456。";
            send_sms($info->phone,$content);
        }

    }
}
