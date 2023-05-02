<?php

namespace App\Http\Controllers\Merchant;

use App\Models\Store;
use App\Validate\UserValidate;
use Illuminate\Http\Request;

class LoginController extends MerchantBaseController
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
        $token = auth('merchant')->setTTl(120)->attempt($credentials);
        if (!$token) {
            return $this->fail('登录失败，请确认账号密码是否正确');
        }

        $user = auth('merchant')->user();
        if ($user->is_shop != 1) {
            return $this->fail("您不是商家");
        }
        $store = Store::where("user_id", $user->id)->where("type",1)->first();
        if ($store->status != 1) {
            return $this->fail("您的店铺状态异常，请联系平台管理员");
        }
        return $this->success('登录成功', [
            'user' => $user,
            'on_line' => $store->on_line,
            'token' => $token,
        ]);
    }

    public function menuList(Request $request)
    {
        $menus = [
            [
                "id" => 1,
                "title" => "首页",
                "icon" => "HomeFilled",
                "path" => "/dashboard"
            ],
            [
                "id" => 2,
                "title" => "店铺管理",
                "icon" => "Operation",
                "path" => "/shop",
                "children" => [
                    [
                        "id" => 3,
                        "title" => "商品列表",
                        "icon" => "Tickets",
                        "path" => "/spuList"
                    ],
                    [
                        "id" => 8,
                        "title" => "上传商品",
                        "icon" => "Setting",
                        "path" => "/addSpu"
                    ],
                    [
                        "id" => 4,
                        "title" => "商品详情",
                        "icon" => "Film",
                        "path" => "/spuDetail"
                    ],
                    [
                        "id" => 5,
                        "title" => "订单管理",
                        "icon" => "Burger",
                        "path" => "/orderList"
                    ],
                    [
                        "id" => 6,
                        "title" => "积分和ASAC",
                        "icon" => "Goods",
                        "path" => "/asac"
                    ],
                    [
                        "id" => 7,
                        "title" => "燃烧记录",
                        "icon" => "Notebook",
                        "path" => "/burnLog"
                    ]
                ],
            ],
        ];
        $user = auth('merchant')->user();
        $store = Store::where("user_id", $user->id)->first();
        if ($store->on_line == 1) {
            return $this->executeSuccess("请求", $menus);
        } else {
            return $this->executeSuccess("请求", array_splice($menus, 0, 1));
        }
    }

    public function uploadOne(Request $request)
    {
        $file = $request->file('image');
        $path = $this->uploadFile($file, "banners");
        return $this->executeSuccess("上传", ["path" => $path]);
    }
}
