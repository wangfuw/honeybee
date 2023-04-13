<?php

namespace App\Http\Controllers\Admin;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
{
    public function login(Request $request)
    {

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('username', 'password');
//        $username = $request->input("username");
//        $password = $request->input("password");
//
//        $token = auth()->tokenById(1);
//        var_dump($token);
//        var_dump(auth()->validate());
//        $adminUser = AdminUser::where('username', $username)->first();
//        if ($adminUser == null) {
//            return $this->fail(0, "账号不存在");
//        }
//        if (Rsa::encryptPass($password, $adminUser->salt) != $adminUser->password) {
//            return $this->error("密码");
//        }
        $token = auth()->guard("admin")->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败');
        }

        $user = auth()->guard("admin")->user();
        return $this->success('登录成功',[
            'user'=>$user,
            'access_token'=>[
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function menuList(Request $request)
    {

    }
}
