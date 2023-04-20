<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Asac;
use App\Models\AsacBlock;
use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use Illuminate\Http\Request;

class AsacController extends BaseController
{
    public function asac_login(Request $request)
    {
        $private_key = $request->private_key;
        if(!AsacNode::query()->where('private_key',$request->private_key)->exists()){
            return $this->fail('不存在该用户');
        }
        $wallet_address = AsacNode::query()->select('wallet_address','number')
            ->where('private_key',$request->private_key)
            ->first()
            ->toArray();
        return $this->success('登陆成功',$wallet_address);
    }

    /**
     * 搜索
     * @param Request $request
     * @return void
     */
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $length = strlen($request->keyword);
        if(is_numeric($request->keyword)){
            //数字查区块
            $list = AsacTrade::query()->where('id',$keyword)->get()->toArray();
            if(empty($list)) return $this->fail('暂无数据');
            return $this->success('success',$list);
        }
        if($length>50){
            //查hash
            $info = AsacTrade::query()->where('trade_hash',$keyword)->first();
            if(empty($info)) return $this->fail('未检索到数据');
            return $this->success('success',$info);
        }else{
            //查交易地址
            $list = AsacTrade::query()->where(function ($query) use($keyword){
                $query->orWhere('from_address',$keyword)->orWhere('to_address',$keyword);
            })->get();
            if(empty($list)) return $this->fail('暂无数据');
            return $this->success('success',$list->toArray());
        }
    }

    /**
     * 通证详情
     * @return void
     */
    public function info()
    {
        $list = Asaconfig::query()->select('name','contract_address',
        'destruction_address','accuracy','number','flux'
        ,'dest_num','owner_num','trans_num'
        )->find(1);
        return $this->success('success',$list);
    }

    //区块详情
    public function block_info(Request $request)
    {
        $id = $request->id;
        $block = AsacBlock::query()->where('id',$id)->first();
        if(!empty($block)){
            $trades = AsacTrade::query()->where('id',$id)->get()->toArray();
            $data = [];
            $data['id'] = $block->id;
            $data['trade_num'] = $block->trade_num;
            $data['number'] = $block->number;
            $data['list'] = $trades;
            return $this->success('success',$data);
        }else{
            return $this->fail('暂无数据');
        }

    }

    /**首页
     * @return void
     */
    public function index(){
        $list = AsacBlock::query()->select('id','number','trade_num','created_at')
            ->orderBy('id','desc')->limit(4)->get()->toArray();
        $trade_total = AsacTrade::query()->count('num');
        $last_height = AsacBlock::query()->max('id');
        $config = Asaconfig::query()->select('last_price','old_price','name')->find(1);
        $last_price = $config->last_price;
        $name = $config->name;
        $dividend = $config->last_price - $config->old_price;
        $divisor  = $config->old_price;
        $rate = bcdiv($dividend*100,$divisor,2).'%';
        return $this->success('success',compact('list','trade_total',
            'last_height','name','rate','last_price'));
    }

    //查看更多区块
    public function blocks(Request $request)
    {
        $model = new AsacBlock();
        $list = $model->get_list($request);
        return $this->successPaginate($list);
    }

    /**
     * 获取地址金额
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function owners(Request $request)
    {
        $model = new AsacNode();
        $config = Asaconfig::query()->select('number','last_price')->find(1);
        $list = $model->get_list($request,$config);
        return $this->successPaginate($list);
    }
}
