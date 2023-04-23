<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Store;
use App\Services\StoreService;
use App\Validate\StoreValidate;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    const STEP_type_1 = 0;
    const STEP_type_2 = 1;
    const STEP_type_3 = 2;

    protected  $model;

    protected $validate;

    protected $service;
    public function __construct(Store $model, StoreValidate $validate,StoreService $service){
        $this->model = $model;
        $this->validate = $validate;
        $this->service = $service;
    }

    public function add_store(Request $request)
    {
        $data = $request->only(['store_name','business_type','mobile','store_image','master','images','area','address','on_line']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        if(check_phone($data['mobile']) == false){
            return $this->fail('电话号码格式错误');
        }
        $data['user_id'] = auth()->id();
        if(Store::query()->where('user_id',auth()->id())->whereIn('status',[self::STEP_type_1,self::STEP_type_2])->exists()){
            return $this->fail('你的申请正在审核中');
        }
        $store = Store::create($data);
        if($store) return $this->success('申请成功',$store);
        return $this->fail('申请失败稍后重试');
    }

    public function get_store(Request $request)
    {
       $data = $request->only(['id']);
        if(!$this->validate->scene('info')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $info = $this->model->get_info($data);
        return $this->success('请求成功',$info);
    }

    public function update(Request $request)
    {

        $data = $request->only(['id','store_name','business_type','mobile','store_image','master','images','area','address','on_line']);
        $info = Store::query()->where('id',$data['id'])->first();
        $info->store_name = $data['store_name'];
        $info->business_type = $data['business_type'];
        $info->mobile = $data['mobile'];
        $info->store_image = $data['store_image'];
        $info->master = $data['master'];
        $info->images = $data['images'];
        $info->area = $data['area'];
        $info->address = $data['address'];
        $info->on_line = $data['on_line'];
        $info->type = 0;
        $info->save();
        return $this->success('重新提交成功,待审核');
    }

    //进店看店铺详情
    public function store(Request $request)
    {
        if(!$this->validate->scene('info')->check($request->only(['id'])))
        {
            return $this->fail($this->validate->getError());
        }
        $store_info = $this->model->get_store_info($request->id);
        return $this->success('请求成功',$store_info);
    }


}
