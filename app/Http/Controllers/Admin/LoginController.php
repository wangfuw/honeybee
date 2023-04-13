<?php

namespace App\Http\Controllers\Admin;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Models\AdminUser;
use App\Validate\Admin\AdminUserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends BaseController
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
        $token = auth('admin')->attempt($credentials);
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
