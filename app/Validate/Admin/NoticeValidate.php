<?php

namespace App\Validate\Admin;

use App\Validate\BaseValidate;

class NoticeValidate extends BaseValidate
{
    //验证规则
    protected $rule = [
        'id' => 'required',
        'title' => 'required|string|max:255',
        'text' => 'required|string',
        'face' => 'required|string',
        'type' => 'required|numeric|lte:4'
    ];
    //自定义验证信息
    protected $message = [
        'title.required' => '标题不能为空',
        'title.max' => '标题不能大于 255',
        'text.required' => '内容不能为空',
        'face.required' => '展示图不能为空',
        'type.required'=>'类型必须设置',
        'type.lte'=>'类型必须为数字',

    ];

    //自定义场景
    protected $scene = [
        'modify' => "title,text,id",
        'add' => "title,text",
        'addNews'=>'title,text,face,type',
        'modifyNews'=>'title,text,face,id,type'
    ];
}
