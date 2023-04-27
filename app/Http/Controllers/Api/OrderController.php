<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\OrderService;
use App\Validate\OrderValidate;
use Illuminate\Http\Request;

class OrderController extends BaseController
{
    //创建订单
    protected $service;

    protected $user;

    protected $validate;
    public function __construct(OrderService $service,OrderValidate $validate){
        $this->service = $service;
        $this->validate = $validate;
        $this->user = auth()->user();
    }
    //订单列表
    public function order_list(Request $request)
    {
        $order_list = $this->service->orders($request->toArray(),$this->user);
        return $this->success('请求成功',$order_list);
    }

    public function info(Request $request){
        $order_no = $request->only(['order_no']);
        if(!$this->validate->scene('order_no')->check($order_no))
        {
            return $this->fail($this->validate->getError());
        }
        $info = $this->service->info($order_no);
        return $this->success('请求成功',$info);
    }

    //新增订单
    public function create_order(Request $request)
    {
        $data = $request->only(['sku_id','number','spu_id','address']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $orders = $this->service->add_order($data,$this->user);
        return $this->success('下单成功',$orders);
    }

    //删除订单
    public function del_order(Request $request)
    {
        $order_no = $request->only(['order_no']);
        if(!$this->validate->scene('order_no')->check($order_no))
        {
            return $this->fail($this->validate->getError());
        }

        $result = $this->service->del($order_no,$this->user);
        return $this->success('撤单成功');
    }

    //订单支付
    public function pay_order(Request $request)
    {
        $data = $request->only(['order_no','sale_password']);
        if(!$this->validate->scene('pay')->check($data))
        {
            return $this->fail($this->validate->getError());
        }
        $res = $this->service->pay_order($data,$this->user);
        return $this->success('支付成功',$res);
    }

    //订单签收
    public function sign_order(Request $request)
    {
        $order_no = $request->only(['order_no']);
        if(!$this->validate->scene('order_no')->check($order_no))
        {
            return $this->fail($this->validate->getError());
        }
        $res = $this->service->sign_order($order_no,$this->user);
        if($res == true){
            return $this->success('签收成功,获得消费积分',[]);
        }else{
            return $this->fail('签收失败,稍后再试');
        }
    }

    //订单换货
    public function apply_revoke(Request $request)
    {
        $data= $request->only(['order_no','reason','photo']);
        $info = $this->service->apply_revoke($data,$this->user);
        return $this->success('申请成功',$info);
    }
    //换货列表
    public function revokes(Request $request)
    {
        $list = $this->service->revokes($request->toArray(),$this->user);
        return $this->success('请求成功',$list);
    }

    //取消换货
    public function del_revoke(Request $request)
    {
        $order_no = $request->only(['order_no']);
        if(!$this->validate->scene('order_no')->check($order_no))
        {
            return $this->fail($this->validate->getError());
        }
        $info = $this->service->del_revoke($order_no,$this->user);
        return $this->success('撤销成功',[]);
    }
}
