<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\BaseController;
use App\Models\Store;
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
        if ($user->is_shop != 1) {
            return $this->fail("您不是商家");
        }
        $store = Store::where("user_id", $user->id)->first();
        if ($store->status != 1) {
            return $this->fail("您的店铺状态异常，请联系平台管理员");
        }
        return $this->success('登录成功', [
            'user' => $user,
            'token' => $token,
            "menu"=> $this->menuList($store->on_line)
        ]);
    }

    private function menuList($type){
        $menus = [
            [
                "id"=>1,
                "title"=>"首页",
                "icon"=>"HomeFilled",
                "path"=>"/dashboard"
            ],
            [
                "id"=>2,
                "title"=>"店铺管理",
                "icon"=>"Operation",
                "path"=>"/shop",
                "children"=>[
                    [
                        "id"=>3,
                        "title"=>"商品列表",
                        "icon"=>"Tickets",
                        "path"=>"/spuList"
                    ],
                    [
                        "id"=>4,
                        "title"=>"商品详情",
                        "icon"=>"Film",
                        "path"=>"/spuDetail"
                    ],
                    [
                        "id"=>5,
                        "title"=>"订单管理",
                        "icon"=>"Burger",
                        "path"=>"/orderList"
                    ]
                ],
            ],
        ];
        if($type == 1){
            return $menus;
        }else{
            return array_splice($menus,0,1);
        }
    }
}
