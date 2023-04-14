<?php

namespace App\Validate\Admin;

use App\Validate\BaseValidate;

class AdminUserValidate extends BaseValidate
{
    //验证规则
    protected $rule = [
        'id' => 'required',
        'group_id' => 'required',
        'username' => 'required|string|max:255',
        'password' => 'required|string|min:6',
        'password_confirm' => 'required|string|min:6',
    ];
    //自定义验证信息
    protected $message = [
        'username.required' => '用户名不能为空',
        'username.max' => '用户名长度不能大于 255',
        'password.required' => '请输入密码',
        'password.min' => '密码不能少于6位有效数字',
    ];

    //自定义场景
    protected $scene = [
        'modify' => "id,username,password",
        'login' => "username,password",
        'add'=>"group_id,username,password,password_confirm"
    ];
}
