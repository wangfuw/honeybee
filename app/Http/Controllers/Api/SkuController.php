<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\MallSku;
use App\Validate\SkuValidate;
use Illuminate\Http\Request;

class SkuController extends BaseController
{
    protected $model;

    protected $validate;
    public function __construct(MallSku $model,SkuValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
    }

    //切换商品规格
    public function get_product(Request $request)
    {

        $data = $request->only(['spu_id','indexes']);
        if(!$this->validate->scene('info')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $list = $this->model->get_sku($data);
        return $this->success('请求成功',$list);
    }



}
