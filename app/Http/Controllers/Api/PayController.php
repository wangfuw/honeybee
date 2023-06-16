<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\AsacNode;
use App\Models\Config;
use App\Models\Score;
use App\Models\Store;
use App\Models\User;
use App\Validate\PayValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PayController extends BaseController
{
    const WX_APPID="wx1f331c16c0da4ede";
    const WX_SECRET="3b5c0e3bd111f8ab14d338b3e67140d0";

    const VERSION = '2.2';
    const MERCHANTNO = '888120600004799';

    const TRADEMERCHANTNO = "777190600517604";

    const TRADEMERCHANTNO_BAT = "777163800517605";

    const M_SECRET = '82315039593446e3a81d61e71dfdac99';

    const QE_RATE = 0.7;
    const PAY_APPID="";

    const PAY_SECRET="";

    const URL = "https://www.joinpay.com/trade/uniPayApi.action";

    private $down_url;
    public function __construct(PayValidate $validate)
    {
        $this->validate = $validate;
        $this->middleware('auth:api', ['except' => ['to_pey','notify_url']]);
        $this->down_url = DB::table('down')->where('id',1)->value('android');
    }

    //输入电话 金额 选着支付方式 调取api/to_pay （注册用户，异步生成预支付订单）返回订单信息 拉去微信或支付宝jsapi支付，支付成功给用户加消费积分，商家加积分
    public function to_pey(Request $request){
        //测试
        $data = $request->only(['code','id','phone','money','pay_type']);
        if(!$this->validate->scene('pre_pay')->check($data)){
            return $this->success($this->validate->getError());
        }
        if(!check_phone($data['phone'])){
            return $this->fail('电话号码错误');
        }
        if($data['pay_type'] == 'wx_pay'){
            //获取用户openid
            $info = curl_get(self::WX_APPID,self::WX_SECRET,$data['code'],'authorization_code','GET');
            //根据用户id 获取用户商户编号
            $store_info = Store::query()->where('user_id',$data['id'])->first();
            //注册用户，默认密码是123456
            $this->auto_register($data);
            //调汇聚接口生成预支付订单
            $paramData = $this->pre_data($data,$info['openid'],$store_info->mch_no);
            $paramString = $this->pre_data($data,$info['openid'],$store_info->mch_no);
            $hashSecret = hmacRequest($paramString,self::M_SECRET,1);
            $paramData['hmac'] = $hashSecret;
            $ret = post_url(self::URL,$paramData);
            dd(json_decode($ret,true));
            //预支付信息返回前端
            return $this->success('请求成功',$ret['rc_Result']);
        }else{
            //支付宝支付

        }

        return $this->success('请求成功',[]);
    }

    //自动注册
    protected function auto_register($data)
    {
        $s_users = User::query()->where('id',$data['id'])->first();
        $user = User::query()->where('phone',$data['phone'])->first();
        if($user){
            return false;
        }
        $myself_invite_code = inviteCode($data['phone']);
        $num = Config::register_give_lucky();
        $user = User::query()->create([
            'phone' => $data['phone'],
            'invite_code'=> $myself_invite_code,
            //初始密码123456
            'password'=>Hash::make('123456'),
            'master_id' => $s_users->id,
            'master_pos'=>','.$user->id.$s_users->master_pos??'',
            'luck_score'=>$num??180,
            'max_luck_num'=>$num??180,
            'sale_password'=>'123456'
        ]);
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
        $this->send_register($data['phone'],$this->down_url);
        return true;
    }

    //发送注册信息--todo
    protected function send_register($phone,$down_url)
    {
        $content = "【源宇通商城】您已注册源宇通商城。您的账户是:".$phone."您的登录初始密码是:123456。"."APP下安装地址是:".$down_url;
        send_sms($phone,$content);
    }
    //生成预支付订单
    protected function make_pre_order($data,$openid)
    {

    }

    //支付成功回调 给用户增加消费积分，给商户增加消费积分
    public function pay_order_back()
    {

    }

    protected function pre_data($data,$openid,$mch_no)
    {
        return [
            'p0_Version'=>self::VERSION,
            'p1_MerchantNo'=>self::MERCHANTNO,
            'p2_OrderNo'=>generateOrderNumber(),
            'p3_Amount'=>$data['money'],
            'p4_Cur'=>'1',
            'p5_ProductName'=>'源宇通商品',
            'p9_NotifyUrl'=>'/api/notify_url',
            'q1_FrpCode'=>'WEIXIN_GZH',
            'q5_OpenId'=>$openid,
            'q7_AppId'=>self::WX_APPID,
            'qa_TradeMerchantNo'=>self::TRADEMERCHANTNO,
            'qc_IsAlt'=>1,
            'qd_AltType'=>11,
            'qe_AltInfo'=>json_encode([['altMchNo'=>$mch_no,"altAmount"=>bcmul($data['money'],self::QE_RATE)]]),
            //分账通知地址'qf_AltUrl'=>
        ];
    }

    //系统通知地址
    public function notify_url(Request $request){

    }

}
