<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\ShopCart;
use App\Validate\CartValidate;
use Illuminate\Http\Request;

class ShopController extends BaseController
{
    protected $user;

    protected $model;

    protected $validate;
    public function __construct(ShopCart $model,CartValidate $validate)
    {
        $this->user = auth()->user();
        $this->model = $model;
        $this->validate = $validate;
    }
    //加入购物车
    public function add_shop_car(Request $request)
    {
        $data = $request->only(['store_id','sku_id','number']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $user_id = $this->user->id;
        $data['user_id'] = $user_id;
        $skues = MallSku::query()->where('id',$data['sku_id'])->select('spu_id','price')->first();
        $data['spu_id'] = $skues->spu_id;
        $data['order_money'] = bcmul($skues->price,$data['number'],2);
        $result = ShopCart::query()->create($data);
        if($result) return $this->success('添加成功',$result);
        return $this->fail('新增失败');
    }

    //查询展示
    public function show_shop_car(Request $request)
    {
        $user_id = $this->user->id;
        $list = $this->model->shops($request->toArray(),$user_id);
        return $this->success('请求成功',$list);
    }

    //从购物车移除
    public function del_from_car(Request $request)
    {
        $ids = [];
        $id = $request->id;
        if(!is_array($id)){
            array_push($ids,$id);
        }else{
            $ids = $id;
        }
        ShopCart::query()->whereIn('id',$ids)->delete();
        return $this->success('删除成功');
    }
}
