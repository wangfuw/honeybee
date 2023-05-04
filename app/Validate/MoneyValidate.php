<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class MoneyValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'id'=>'required|numeric',
        'num' => 'required|numeric',
        'charge_image' => 'required',
        'wallet_address'        => 'required',
        'sale_password'=>'required'
    ];
    //自定义验证信息
    protected $message = [
        'num.required'=>'充值数量必须',
        'num.numeric'=>'充值数量必须是数子',
        'id.required'=>'ID必须',
        'id.numeric'=>'ID必须是数子',
        'charge_image.required'=>'充值记录必须',
        'phone.required'=>'被充值人电话不能为空',
        'sale_password.required'=>'交易密码必须',
    ];

    //自定义场景
    protected $scene = [
        'add' => ['num','charge_image','id'],
        'trade' => ['num','wallet_address','sale_password'],
    ];
}
