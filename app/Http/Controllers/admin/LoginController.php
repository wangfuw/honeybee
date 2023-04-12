<?php

namespace app\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        var_dump(env("DB_PASSWORD"));
        $username = $request->input("username");
        $password = $request->input("password");
        $adminUser = AdminUser::where('username', $username)->first();
        var_dump($adminUser);
        $token = auth()->tokenById(123);
        return $this->success("登录");
    }

    public function menuList(Request $request)
    {

    }
}
