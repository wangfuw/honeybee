<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Models\Address;
use App\Models\AsacNode;
use App\Models\User;
use App\Models\UserIdentity;
use App\Validate\UserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends BaseController
{
    private $validate;
    public function __construct(UserValidate $validate)
    {
        $this->validate = $validate;
        $this->middleware('auth:api', ['except' => ['register', 'login','forget_password']]);
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
        $token = Auth::attempt($data);
        if (!$token) {
            return $this->fail('登录失败');
        }
        $user = Auth::user();
        return $this->success('登录成功',[
            'user'=>$user,
            'access_token' =>$token]);
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
        if(check_phone($phone) == false){
            return $this->fail('请正确输入手机号');
        }
        $f_users = User::query()->where('invite_code',$request->invite_code)->first();
        if(!$f_users){
            return $this->fail('邀请码不存在,请确认邀请码');
        }
        if(User::query()->where('phone',$phone)->exists()){
            return $this->fail('改电话号码已被注册');
        }

        if(Rsa::decodeByPrivateKey($request->password) != Rsa::decodeByPrivateKey($request->re_password))
        {
            return $this->fail('两次密码不一致');
        }


        //--todo 短信验证
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
                'luck_score'=>env('BASE_LUCK',100)
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
        $ids   = User::query()->where('master_pos','like','%'.','.$user->id.','.'%')->pluck('id');
        $amount = $this->statistics($ids);
        if($amount >= 60000000){
            $temp = 0;
            $directs = User::query()->select('id','phone','master_pos','created_at')->where('master_id',$user->id)->get()->toArray();
            foreach ($directs as $direct){
                $ids = User::query()->where('master_pos','like','%'.','.$direct['id'].','.'%')->pluck('id');
                if(!empty($ids)){
                    $contribute = $this->statistics($ids);
                    if($contribute > 5000000){
                        $temp += 1;
                    }
                }else{
                   continue;
                }
            }
            $user->grade = grade($amount,$temp);
        }else{
            $user->grade = grade($amount);
        }
        $wallet_address  = AsacNode::query()->where('id',$user->id)->value('wallet_address');
        $user->wallet_address = $wallet_address;


        $status = UserIdentity::query()->where('user_id',$user->id)->select('status','id')->first();
        if(empty($status)){
            $user->status = -1;
        }else{
            $user->status = $status->status;
        }

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
        $data = $request->only(['phone','sale_password','re_sale_password','code']);
       ///dd($data);
        if(!$this->validate->scene('change_sale')->check($data)){

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
        if(Rsa::decodeByPrivateKey($request->sale_password) != Rsa::decodeByPrivateKey($request->re_sale_password))
        {
            return $this->fail('两次密码不一致');
        }
        //--todo 短信验证
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

    //获取手机验证码
    public function getCode($phone)
    {
        return $this->success('success',['code'=>123]);
    }

    //todo 未完成
    public function teams(Request $request)
    {
        if(!$this->validate->scene('team')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $user = auth()->user();
        $temp = 0;
        $directs = User::query()->select('id','phone','master_pos','created_at')
            ->where('master_id',$user->id)
            ->get()->map(function ($item,$items) use($temp){
            $ids = User::query()->where('master_pos','like','%'.','.$item->id.','.'%')->pluck('id');

            if(!empty($ids)){
                $item->contribute = $this->statistics($ids);
                if($item->contribute > 5000000){
                    $temp += 1;
                }
                $item->grade = grade($item->contribute);
            }else{
                $item->contribute = 0;
                $item->grade = 0;
            }
            if($temp > 2){
                $item->grade = grade($item->contribute,$temp);
            }
            $item->phone = make_phone($item->phone);
            unset($ids);
            return $item;
        })->forPage($request->page,$request->page_size);
        $list = collect([])->merge($directs);
        $direct_num = $directs->count();
        //我的团队
        $ids   = User::query()->where('master_pos','like','%'.','.$user->id.','.'%')->pluck('id');
        $team_num = count($ids);
        if($team_num == 0){
            $amount = 0;
        }else{
            $amount = $this->statistics($ids);
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
        $url =  'http//:'.'www.baidu.com/#/?'.'invite_cdoe='.$user->invite_code;
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
}
