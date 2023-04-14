<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\News;
use App\Validate\NewsValidate;
use Illuminate\Http\Request;

class NewsController extends BaseController
{
    protected $validate;
    protected $model;

    public function __construct(News $model,NewsValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
    }

    public function getNews(Request $request)
    {
        if(!$this->validate->scene('getNews')->check($request->toArray())){
            return  $this->fail($this->validate->getError());
        }
        $data = $this->model->getList($request->toArray());
        return $this->successPaginate($data);
    }

    public function getInfo(Request $request)
    {
        $id = $request->id;
        $info = $this->model->getInfo((int)$id);
        if(empty($info)) return $this->fail('暂无数据');
        return $this->success('success',$info);
    }
}
