<?php

namespace app\Http\Controllers\Admin;

use App\Common\Rsa;
use App\Http\Controllers\Controller;
use App\Models\AdminLogin;
use App\Models\AdminUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");
        $googleCode = $request->input("google");
        var_dump($googleCode);
        $adminUser = AdminUser::where('username', $username)->first();
        if ($adminUser == null) {
            return $this->baseResponse(0, "账号不存在");
        }
        if (Rsa::encryptPass($password, $adminUser->salt) != $adminUser->password) {
            return $this->error("密码");
        }
        $adminLogin = AdminLogin::first();

        if($adminLogin->google == 2){

        }
        $token = auth()->tokenById(123);
        return $this->success("登录");
    }

    public function menuList(Request $request)
    {

    }
}
