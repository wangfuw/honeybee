<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class OrderValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'sku_id'=>'required|numeric',
        'number'=>'required|numeric',
        'address'=>'required',
        'order_no'=>'required',
        'sale_password'=>'required',
    ];
    //自定义验证信息
    protected $message = [
        'sku_id.required'=>'商品不能为空',
        'sku_id.numeric'=>'商品数字',
        'number.required'=>'商品数量必须',
        'address.required'=>'地区需要',
        'order_no.required'=>'订单号需要',
        'sale_password.required'=>'通证密码需要',
    ];

    //自定义场景
    protected $scene = [
        'add'=> ['sku_id','number','address'],
        'order_no'=>['order_no'],
        'pay'=>['order_no','sale_password'],
    ];
}
