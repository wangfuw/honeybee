<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class CartValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'store_id' => 'required|numeric',
        'id' => 'required|numeric',
        'sku_id' => 'required|numeric',
        'number' => 'required|numeric',

    ];
    //自定义验证信息
    protected $message = [
        'store_id.required'=>'店铺ID必须',
        'store_id.numeric'=>'店铺ID是数组',
        'sku_id.required'=>'商品ID必须',
        'sku_id.numeric'=>'商品ID必须',
        'number.required'=>'商品数量必须',
        'number.numeric'=>'商品数量是数子',
        'id.required'=>'商品ID必须',
        'id.numeric'=>'商品ID是数子',
    ];

    //自定义场景
    protected $scene = [
        'add' => ['store_id','sku_id','number'],
        'id'  => ['id'],
    ];
}
