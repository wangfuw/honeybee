<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminNav;
use App\Models\AdminGroup;
use App\Models\AdminRule;
use App\Models\AdminUser;
use App\Validate\Admin\AdminUserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


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

        if (!$this->validate->scene('login')->check($credentials)) {
            return $this->fail($this->validate->getError());
        }
        $token = auth('admin')->setTTl(1)->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败，请确认账号密码是否正确');
        }

        $user = auth('admin')->user();
        return $this->success('登录成功', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function changePassword(Request $request)
    {
        $credentials = $request->only('username', 'password', 'password_confirm');
        if (!$this->validate->scene('modify')->check($credentials)) {
            return $this->fail($this->validate->getError());
        }
        if ($credentials["password"] != $credentials["password_confirm"]) {
            return $this->fail("两次输入的密码不一致");
        }
        $admin = auth("admin")->user();

        $password = Hash::make($credentials["password"]);
        try {
            AdminUser::where("id", $admin->id)->update(["password" => $password]);
            return $this->executeSuccess("修改");
        } catch (\Exception $exception) {
            var_dump($exception);
            return $this->executeFail("修改");
        }
    }

    public function menuList(Request $request)
    {
        $admin = auth("admin")->user();
        $ag = AdminGroup::where("id", $admin->group_id)->first();
        $menuOne = AdminNav::where("pid", 0)->orderBy("order_number", "desc")->select("id", "title", "icon", "path")->get()->toArray();

        $rules = explode(",", $ag->rules);
        foreach ($menuOne as $k => &$v) {
            $exist = AdminRule::where("nav_id", $v["id"])->whereIn('id', $rules)->first();

            $menuTwo = AdminNav::where("pid", $v["id"])->select("id", "title", "icon", "path")->get()->toArray();
            foreach ($menuTwo as $a => &$m) {
                $existTwo = AdminRule::where("nav_id", $m["id"])->whereIn("id", $rules)->first();
                if (!$existTwo) {
                    unset($menuTwo[$a]);
                }
            }
            if (!$exist && empty($menuTwo)) {
                unset($menuOne[$k]);
            }
            if (!empty($menuTwo)) {
                $menuTwo = array_values($menuTwo);
                $v["children"] = $menuTwo;
            }
        }
        $menuOne = array_values($menuOne);
        return $this->success("请求成功", $menuOne);
    }

    public function uploadOne(Request $request)
    {
        $file = $request->file('image');
        $path = $this->uploadFile($file, "banners");
        return $this->executeSuccess("上传", ["path" => $path]);
    }

    public function uploadMany(Request $request)
    {
        foreach ($_FILES as $k => $v) {
            $file = $request->file($k);
            try {
                $path = $this->uploadFile($file, "banners");
                $scavenge[] = ["url" => config('app.url') . $path];
            } catch (\Exception $exception) {
                return response()->json([
                    'error' => 1
                ]);
            }
        }
        return response()->json([
            'errno' => 0, 'data' => $scavenge
        ]);
    }
}
