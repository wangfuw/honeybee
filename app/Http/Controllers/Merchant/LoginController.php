<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\BaseController;
use App\Validate\UserValidate;
use Illuminate\Http\Request;

class LoginController extends BaseController
{
    public function __construct(UserValidate $validate)
    {
        $this->validate = $validate;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone', 'password');

        if (!$this->validate->scene('login')->check($credentials)) {
            return $this->fail($this->validate->getError());
        }
        $token = auth('merchant')->setTTl(30)->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败，请确认账号密码是否正确');
        }

        $user = auth('merchant')->user();
        return $this->success('登录成功', [
            'user' => $user,
            'token' => $token,
        ]);
    }
}
