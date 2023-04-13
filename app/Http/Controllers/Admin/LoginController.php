<?php

namespace App\Http\Controllers\Admin;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class LoginController extends BaseController
{
    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");

        $adminUser = AdminUser::where('username', $username)->first();
        if ($adminUser == null) {
            return $this->fail(0, "账号不存在");
        }
        if (Rsa::encryptPass($password, $adminUser->salt) != $adminUser->password) {
            return $this->error("密码");
        }

        return $this->success("登录");
    }

    public function menuList(Request $request)
    {

    }
}
