<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminNav;
use App\Models\AdminGroup;
use App\Models\AdminRule;
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

        if (!$this->validate->scene('login')->check($credentials)) {
            return $this->fail($this->validate->getError());
        }
        $token = auth('admin')->setTTl(10)->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败');
        }

        $user = auth('admin')->user();
        return $this->success('登录成功', [
            'user' => $user,
        ]);
    }

    public function menuList(Request $request)
    {
        $admin = auth("admin")->user();
        $ag = AdminGroup::where("id", $admin->group_id)->first();
        $menuOne = AdminNav::where("pid", 0)->orderBy("order_number", "desc")->select("id","title","icon","path")->get()->toArray();

        $rules = explode(",", $ag->rules);
        foreach ($menuOne as $k => &$v) {
            $exist = AdminRule::where("nav_id", $v["id"])->whereIn('id', $rules)->first();
            dd($exist);
            $menuTwo = AdminNav::where("pid", $v["id"])->select("id","title","icon","path")->get()->toArray();
            foreach ($menuTwo as $a => &$m) {
                $existTwo = AdminRule::where("nav_id", $m["id"])->where("id", "in", $rules)->first();
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
}
