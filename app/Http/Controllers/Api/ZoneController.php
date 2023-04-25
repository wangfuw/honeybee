<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\MallSpu;
use App\Validate\ZoneValidate;
use Illuminate\Http\Request;

class ZoneController extends BaseController
{
    protected $model;

    protected $validate;
    public function __construct(MallSpu $model ,ZoneValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
    }
    //福利专区
    public function welfareZone(Request $request)
    {
        if(!$this->validate->scene('welfare')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $list = $this->model->get_welfare($request->toArray());
        return $this->success('请求成功',$list);
    }

    //优选
    public function preferredZone(Request $request)
    {
        if(!$this->validate->scene('welfare')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $list = $this->model->get_preferred($request->toArray());
        return $this->success('请求成功',$list);
    }

    //幸福
    public function happinessZone(Request $request)
    {
        $list = $this->model->get_happiness($request->toArray());
        return $this->success('请求成功',$list);
    }

    //消费
    public function consumeZone(Request $request)
    {
        $list = $this->model->get_consume($request->toArray());
        return $this->success('请求成功',$list);
    }
}
