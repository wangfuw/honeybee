<?php

namespace App\Http\Controllers\Admin;

use App\Validate\Admin\AdminUserValidate;
use Illuminate\Http\Request;


class LoginController extends AdminBaseController
{
    private $validate;
    public function __construct(AdminUserValidate $validate)
    {
        $this->validate = $validate;
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if(!$this->validate->scene('login')->check($credentials)){
            return $this->fail($this->validate->getError());
        }
        $token = auth('admin')->setTTl(10)->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败');
        }

        $user = auth('admin')->user();
        return $this->success('登录成功',[
            'user'=>$user,
        ]);
    }

    public function menuList(Request $request)
    {

    }
}
