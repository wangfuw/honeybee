<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Validate\UserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use function PHPUnit\Framework\isEmpty;

class UserController extends BaseController
{
    private $validate;
    public function __construct(UserValidate $validate)
    {
        $this->validate = $validate;
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    /**
     * 登录
     */
    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');
        if(!$this->validate->scene('login')->check($credentials)){
            return $this->fail($this->validate->getError());
        }
        $token = Auth::attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败');
        }
        $user = Auth::user();
        return $this->success('登录成功',[
            'user'=>$user,
            'access_token'=>[
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
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
        if(check_phone($request->phone) == false){
            return $this->fail('请正确输入手机号');
        }
        $f_users = User::query()->where('invite_code',$request->invite_code)->first();
        if(!$f_users->id){
            return $this->fail('邀请码错误,请确认邀请码');
        }
        if(User::query()->where('phone',$request->phone)->exists()){
            return $this->fail('改电话号码已被注册');
        }

        //--todo 短信验证

        $myself_invite_code = inviteCode($request->phone);
        $user = User::create([
            'phone' => $request->phone,
            'invite_code' =>$myself_invite_code,
            'password' => Hash::make($request->password),
            'master_pos'=>$f_users->id,
            'master_pos'=>','.$f_users->id.$f_users->master_pos??'',
        ]);
        //--todo 注册成功赠送幸运值

        $token = Auth::login($user);

        return $this->success('注册成功',[
            'user' => $user,
            'access_token' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }


    public function logout()
    {
        Auth::logout();
        return $this->fail('登出成功');
    }

    public function me()
    {
        return $this->success('success',['user' => Auth::user()]);

    }

    public function refresh()
    {
        return $this->success('刷新成功',['user' => Auth::user(),'access_token'=>['token' => Auth::refresh(),
            'type' => 'bearer']]);
    }
}
