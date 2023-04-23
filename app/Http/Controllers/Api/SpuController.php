<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Address;
use App\Models\Asaconfig;
use App\Models\MallSpu;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SpuController extends BaseController
{
    protected $model;

    protected $user;

    public function __construct(MallSpu $model)
    {
        $this->model = $model;
        $this->user  = auth()->user();
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function get_last_price()
    {
        $last_price = Asaconfig::get_price();
        return $this->success('请求成功',compact('last_price'));
    }
    //商品收索
    public function search(Request $request)
    {
        $user_id = $this->user->id;
        $list = $this->model->get_search_spu($request->toArray(),$user_id);
        return $this->success('请求成功',$list);
    }

    public function get_search_keys()
    {
        $user_id = $this->user->id;
        $hot_keys = ["衣服","包包","手机","化妆品","电脑","零食","书","米","油"];
        $history_keys = Redis::lrange("SHANGTAO_".$user_id,0,4);
        return $this->success('请求成功',compact('hot_keys','history_keys'));
    }

    //获取商品详情
    public function get_spu_first(Request $request)
    {
        $info = $this->model->getInfo($request->toArray());
//        dd($info);
        if(empty($info)) return $this->fail('数据错误');
        //获取用户默认地址
        $info['store_info'] = [];
        if($info['store_id'] != 0){
            $info['store_info'] = Store::query()->where('id',$info['store_id'])->select('id','store_image')->first()->toArray();
        }
        $user_id = $this->user->id;
        $address = Address::query()->where('user_id',$user_id)
            ->where('is_def',1)
            ->select('area','address_detail')->first();
        $info['area'] = '';
        if(!empty($address)){
            $info['area'] = city_name($address->area).$address->address;
        }
        return  $this->success('请求成功',$info);
    }

    //商店商品分类
    public function get_store_category(Request $request){
        $store_id = $request->store_id;
        $list = $this->model->get_category($store_id);
        return $this->success('请求成功',$list);
    }


}
