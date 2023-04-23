<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class SkuValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'spu_id' => 'required|numeric',
        'indexes'=> 'required'

    ];
    //自定义验证信息
    protected $message = [
        'spu_id.required' => '商品ID必须',
        'spu_id.numeric' => '商品ID必须是数字',
        'indexes.required' => '商品规格必须',
    ];

    //自定义场景
    protected $scene = [
        'info' => ['spu_id','indexes'],

    ];
}
