<?php

namespace App\Validate;

class PayValidate extends BaseValidate
{
    protected $rule =[
        'id'=>'required|numeric',
        'code' => 'required',
        'phone'=>'required',
        'money'=>'required|numeric',
        'pay_type'=>'required|numeric'
    ];

    //自定义验证信息
    protected $message = [
        'id.required'  => 'ID不能为空',
        'id.numeric'   => 'ID类型为数字',
        'code.required'   => 'CODE必须',
        'phone.required'   => '电话号码必须',
        'money.required'   => '金额必须',
        'money.numeric'   => '金额必须是数字',
        'pay_type.required'=>'请选择支付方式',
    ];

    //自定义场景
    protected $scene = [
        'pre_pay'  => "id,code,phone,money"
    ];
}
