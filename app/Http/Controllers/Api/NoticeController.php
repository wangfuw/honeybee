<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Notice;
use App\Validate\NewsValidate;
use Illuminate\Http\Request;

class NoticeController extends BaseController
{
    protected $model;

    protected $validate;
    public function __construct(Notice $model,NewsValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
    }

    /**
     * 公告列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNotices(Request $request)
    {
        if(!$this->validate->scene('getNews')->check($request->toArray())){
            return  $this->fail($this->validate->getError());
        }
        $data = $this->model->getNotices($request->toArray());
        return $this->successPaginate($data);
    }

    public function getInfo(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $info = $this->model->getInfo((int)$id,(int) $type);
        if(empty($info)) return $this->fail('暂无数据');
        return $this->success('success',$info);
    }

}
