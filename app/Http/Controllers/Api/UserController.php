<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Models\Address;
use App\Models\AsacNode;
use App\Models\Config;
use App\Models\Score;
use App\Models\User;
use App\Models\UserIdentity;
use App\Validate\UserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends BaseController
{
    private $validate;
    public function __construct(UserValidate $validate)
    {
        $this->validate = $validate;
        $this->middleware('auth:api', ['except' => ['register', 'login','forget_password','send_message']]);
    }

    /**
     * 登录
     */
    public function login(Request $request)
    {
        $phone = Rsa::decodeByPrivateKey($request->phone);
        $password = Rsa::decodeByPrivateKey($request->password);
        $data = ['phone'=>$phone,'password'=>$password];
        if(!$this->validate->scene('login')->check(['phone'=>$phone,'password'=>$password])){
            return $this->fail($this->validate->getError());
        }
        if(User::query()->where('phone',$phone)->value('is_ban') == 2){
            return  $this->fail('该用户被禁用');
        }
        $token = Auth::setTTl(60*24*365)->attempt($data);
        if (!$token) {
            return $this->fail('登录失败,账号或密码错误');
        }
        $user = Auth::user();
        return $this->success('登录成功',[
            'user'=>$user,
            'access_token' =>$token]);
    }

    //发送登录验证码
    public function send_message(Request $request)
    {
        $phone = Rsa::decodeByPrivateKey($request->phone);
        $phone = $request->phone;
        if(check_phone($phone) == false){
            return $this->fail('请正确输入手机号');
        }
        if(User::query()->where('phone',$phone)->exists()){
            return $this->fail('该电话号码已被注册');
        }
        $code = make_code();
        //        将验证码储在缓冲，设置过期时间为六分钟
        $content = "【源宇通商城】您的验证码是".$code."。如非本人操作，请忽略本短信";
        Redis::setex($phone,600,$code);
        send_sms($phone,$content);
        return $this->success('发送成功',[]);
    }

    //修改交易密码
    public function send_sale_code()
    {
        $phone = Auth::user()->phone;
        $code = make_code();
        //        将验证码储在缓冲，设置过期时间为六分钟
        $content = "【源宇通商城】您的验证码是".$code."。如非本人操作，请忽略本短信";
        Redis::setex($phone,600,$code);
        send_sms($phone,$content);
        return $this->success('发送成功',[]);
    }
    /**
     * 注册
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){

        if(!$this->validate->scene('register')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $phone = Rsa::decodeByPrivateKey($request->phone);
        $f_users = User::query()->where('invite_code',$request->invite_code)->first();
        if(User::query()->where('phone',$phone)->exists()){
            return $this->fail('该电话已被占用');
        }
        if(!$f_users){
            return $this->fail('邀请码不存在,请确认邀请码');
        }
        if(Rsa::decodeByPrivateKey($request->password) != Rsa::decodeByPrivateKey($request->re_password))
        {
            return $this->fail('两次密码不一致');
        }
        // 短信验证  todo 打开
        $code = $request->code;
        if($code !== Redis::get($phone)){
            return $this->fail('验证码错误');
        }
//        获取配置注册赠送
        $num = Config::register_give_lucky();
        try{
            DB::beginTransaction();
            $myself_invite_code = inviteCode($phone);
            $user = User::create([
                'phone' => $phone,
                'invite_code' =>$myself_invite_code,
                'password' => Hash::make(Rsa::decodeByPrivateKey($request->password)),
                'master_id'=>$f_users->id,
                'master_pos'=>','.$f_users->id.$f_users->master_pos??'',
                //--todo 注册成功赠送幸运值
                'luck_score'=>$num??180,
                //注册写入最大幸运值
                'max_luck_num'=>$num??180,
                'sale_password' => Rsa::decodeByPrivateKey($request->password)
            ]);
            //注册赠送幸运值
            Score::query()->create([
                "user_id"=>$user->id,
                "flag"   => 1,
                "num"    =>$num??180,
                "type"=>3,
                "f_type"=>Score::REGISTER_REWARD
            ]);
            $user_id = $user->id;
            $asac_address = AsacNode::create([
                'user_id' => $user_id,
                'wallet_address' => rand_str_pay(40),
                'private_key' => rand_str_pay(64)
                ]);
            //分配一个地址和密钥

            $token = Auth::login($user);
            DB::commit();
            return $this->success('注册成功',[
                'user' => $user,
                'access_token' =>$token
            ]);
        }catch (\Exception $e ){
            DB::rollBack();
            return $this->fail($e->getMessage());
        }

    }


    public function logout()
    {

        Auth::logout();
        return $this->fail('登出成功');
    }

    /**
     * 当前登录者信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        //计算等级
        $amount  = $user->contribution;
        if($amount >= 60000000){
            $users   = User::query()->where('master_id',$user->id)->select('green_score','sale_score','contribution');
            $temp = 0;
            foreach ($users as $down){
                $self_contribution = bcadd(bcdiv($down->green_score,3,2),bcdiv($down->sale_score,6,2));
                $dict_contribution = bcadd($self_contribution,$down->contribution);
                if($dict_contribution > 5000000){
                    $temp += 1;
                }else{
                    continue;
                }
            }
            if($temp >= 2){
                $user->grade = grade($amount,$temp);
            }
        }else{
            $user->grade = grade($amount);
        }
        $wallet_address  = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
        $user->wallet_address = $wallet_address;

        $status = UserIdentity::query()->where('user_id',$user->id)->select('status','id','note')->first();
        if(empty($status)){
            $user->status = -1;
        }else{
            $user->status = $status->status;
            $user->identity_note = $status->note;
        }
        $user->freeze_money = $user->new_freeze;
        return $this->success('success',['user' => $user]);

    }

    public function refresh()
    {
        return $this->success('刷新成功',['user' => Auth::user(),'access_token'=>['token' => Auth::refresh(),
            'type' => 'bearer']]);
    }

    /**
     * 修改密码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function change(Request $request)
    {
        if(!$this->validate->scene('change')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $user_id =auth()->id();
        if(Rsa::decodeByPrivateKey($request->password) != Rsa::decodeByPrivateKey($request->re_password))
        {
            return $this->fail('两次密码不一致');
        }
        $users = User::query()->where('id',$user_id)->first();
        try {
            $users->password = Hash::make(Rsa::decodeByPrivateKey($request->password));
            $users->save();
            return  $this->success('修改成功');
        }catch (\Exception $e){
            if($e->getMessage()){
                return  $this->fail("修改失败");
            }
        }
    }

    public function forget_password(Request $request){
        if(!$this->validate->scene('forget')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $phone = Rsa::decodeByPrivateKey($request->phone);

        if(check_phone($phone) == false){
            return $this->fail('请正确输入手机号');
        }
        $users = User::query()->where('phone',$phone)->first();

        if(!$users->id){
            return $this->fail('该用户不存在');
        }
        if(Rsa::decodeByPrivateKey($request->password) != Rsa::decodeByPrivateKey($request->re_password))
        {
            return $this->fail('两次密码不一致');
        }
        try {
            $users->password = Hash::make(Rsa::decodeByPrivateKey($request->password));
            $users->save();
            return  $this->success('修改成功');
        }catch (\Exception $e){
            if($e->getMessage()){
                return  $this->fail("修改失败");
            }
        }
    }
    public function change_sale_password(Request $request)
    {
        $data = $request->only(['sale_password','re_sale_password','code']);
        if(!$this->validate->scene('change_sale')->check($data)){
            return $this->fail($this->validate->getError());
        }
//        $phone = Rsa::decodeByPrivateKey($request->phone);
//        if(check_phone($phone) == false){
//            return $this->fail('请正确输入手机号');
//        }
        $phone = Auth::user()->phone;
        $users = User::query()->where('phone',$phone)->first();
        if(!$users->id){
            return $this->fail('该用户不存在');
        }
        if(Rsa::decodeByPrivateKey($request->sale_password) != Rsa::decodeByPrivateKey($request->re_sale_password))
        {
            return $this->fail('两次密码不一致');
        }
        $code = $request->code;
        //短信验证-- todo 打开
        if($code != Redis::get($phone)){
            return $this->fail('验证码错误');
        }
        try {
            $users->sale_password = Rsa::decodeByPrivateKey($request->sale_password);
            $users->save();
            return  $this->success('修改成功');
        }catch (\Exception $e){
            if($e->getMessage()){
                return  $this->fail($e->getMessage());
            }
        }
    }

    /**
     * 完善个人信息
     * @param Request $request
     * @return void
     */
    public function complete_self(Request $request)
    {
        $user = auth()->user();
        if($request->nickname){
            $user->nickname = $request->nickname;
        }
        if($request->image){
            $user->image = $request->image;
        }
        $phone = Rsa::decodeByPrivateKey($request->phone);
        if($phone){
            if(check_phone($phone) == true){
                $user->phone = $phone;
            }
        }
        $user->save();
        return $this->success('success');
    }

    /**
     * 注销账号
     * @param Request $request
     * @return void
     */
    public function del_self(Request $request)
    {
        $user = auth()->user();
        $password = Rsa::decodeByPrivateKey($request->password);
        $credentials = [
            "phone"=>$user->phone,
            "password"=>$password,
        ];
        $token = Auth::attempt($credentials);
        if(!$token){
            return $this->fail('密码错误');
        }else{
            User::query()->where('id',$user->id)->delete();
            Auth::logout();
            return $this->success('登出成功');
        }
    }


    //todo 未完成
    public function teams(Request $request)
    {
        if(!$this->validate->scene('team')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $user = auth()->user();
        //直推人数
        $directs = User::query()->select('id','phone','master_pos','created_at','contribution')
            ->where('master_id',$user->id)
            ->forPage($request->page,$request->page_size)
            ->get()->map(function ($item,$items){
                $item->phone = make_phone($item->phone);
                if($item->contribution > 60000000){
                    $six_team_ids = User::query()->where('master_id',$item->id)->select('green_score','sale_score','contribution')->get();
                    $temp = 0;
                    foreach ($six_team_ids as $down){
                        $self_contribution = bcadd(bcdiv($down->green_score,3,2),bcdiv($down->sale_score,6,2));
                        $dict_contribution = bcadd($self_contribution,$down->contribution);
                        if($dict_contribution > 5000000){
                            $temp += 1;
                        }else{
                            continue;
                        }
                    }
                    if($temp >= 2){
                        $item->grade = grade($item->contribution,$temp);
                    }else{
                        $item->grade = grade($item->contribution,1);
                    }
                }else{
                    $item->grade = grade($item->contribution);
                }
                $item->contribute = $item->contribution;
                return $item;
        });
        $list = collect([])->merge($directs);
        $direct_num = $directs->count();
        //我的团队人数
        $ids   = User::query()->where('master_pos','like','%'.','.$user->id.','.'%')->pluck('id');
        $team_num = count($ids);
        if($team_num == 0){
            $amount = 0;
        }else{
            $amount = $user->contribution;
        }
        return $this->success('success',compact('direct_num','team_num','amount','list'));
    }

    protected function statistics($data = [])
    {
        $list = User::query()->select(DB::raw('sum(green_score_total) as green_count,sum(sale_score_total) as score_count'))->whereIn('id',$data)->first();
        $contribute = bcdiv($list->green_count,3,2) + bcdiv($list->score_count,6,2);
        return $contribute;
    }

    public function invite()
    {
        //邀请好友 返回 邀请码 和 注册接口
        $user = auth()->user();
        $url =  env('INVITE_RUL','http://register.yuanyutong.shop').'/#/?'.'invite_code='.$user->invite_code;
        $img =  QrCode::format('png')->size(200)->generate($url);
        return  $data = 'data:image/png;base64,' . base64_encode($img );
    }

    //交易密码获取通证私钥
    public function get_private_key(Request $request){
        $user = Auth::user();
        $sale_password = Rsa::decodeByPrivateKey($request->sale_password);
        $user_sale_password = User::query()->where('id',$user->id)->value('sale_password');
        if($sale_password != $user_sale_password){
            return $this->fail('密码错误');
        }
        $private_key = AsacNode::query()->where('user_id',$user->id)->value('private_key');
        return  $this->success('请求成功',['private_key'=>$private_key]);
    }

    //通知路由
    public function asac_url()
    {
        return $this->success('请求成功',['url'=>env('ASACURL',"http://asac.yuanyutong.shop")]);
    }

    public function get_bold_coin()
    {
        $data = AsacNode::query()->whereIn('id',[1,2])->pluck('number');
        $data = ['flow_address'=>$data[0],'pre_address'=>$data[1]];
        return $this->success('请求成功',$data);
    }

    //扫码自动注册
    public function auto_register(Request $request){
        $data = $request->only(['id','phone','pay_type','money']);
        if(!$this->validate->scene('auto')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $phone = Rsa::decodeByPrivateKey($data['phone']);
        $pay_type = $data['pay_type'];
        $info = User::query()->where('phone',$phone)->first();
        if($info){
            switch ($pay_type){
                case 1:
                    //消费卷支付
                    break;
                case 2:

            }
        }else{

        }
    }
}
