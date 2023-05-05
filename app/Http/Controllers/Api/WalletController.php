<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AsacNode;
use App\Models\AsacTrade;
use App\Models\Coin;
use App\Models\UserMoney;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends BaseController
{
    //钱包明细
    public function list(Request $request)
    {
        $user = auth()->user();
        $type = $request->type;
        $page = $request->page??1;
        $page_size = $request->page_size??5;
        switch ($type){
            case 1:
                //--todo 充值
                $list = $this->get_excharge($user,$page,$page_size);
                return $this->success('请求成功',$list);
            case 2:
                //--todo 提现
                $list = $this->get_excharge($user,$page,$page_size);
                return $this->success('请求成功',$list);
            case 3:
                //--todo 转账
                $list = $this->get_exchange($user,$page,$page_size);
                return $this->success('请求成功',$list);
            case 4:
                //交易
                $list = $this->get_trades($user,$page,$page_size);
                return $this->success('请求成功',$list);
        }
    }

    protected function get_excharge($user,$page,$page_size){
        $user_id = $user->id;
        //获取我的地址
        $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
        $list = AsacTrade::query()->where('to_address',$wallet_address)->where('type',AsacTrade::RECHARGE)->get()
            ->map(function ($item,$items){
                $item->type_name = "充值";
                $item->num = '+'.$item->num;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }
    protected function get_withdraw($user,$page,$page_size){
        $user_id = $user->id;
        //获取我的地址
        $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
        $list = AsacTrade::query()->where('from_address',$wallet_address)->where('type',AsacTrade::WITHDRAW)->get()
            ->map(function ($item,$items){
                $item->type_name = "提现";
                $item->num = '-'.$item->num;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }

    protected function get_exchange($user,$page,$page_size)
    {
        $user_id = $user->id;
        //获取我的地址
        $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');

        $list = AsacTrade::query()->where(function ($query) use($wallet_address){
            return $query->where('from_address',$wallet_address)->orWhere('to_address',$wallet_address);
        })->whereIn('type',[AsacTrade::CHANG_IN,AsacTrade::CHANG_OUT])->select('num','from_address','to_address','created_at')
            ->get()->map(function ($item,$items) use($wallet_address){
                if($item->from_address == $wallet_address){
                    $item->type_name = '转出';
                    $item->num = '-'.$item->num;
                }else{
                    $item->type_name = '转入';
                    $item->num = '+'.$item->num;
                }
                return $item;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }
    protected function get_trades($user,$page,$page_size)
    {
        $user_id = $user->id;
        //获取我的地址
        $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');

        $list = AsacTrade::query()->where(function ($query) use($wallet_address){
            return $query->where('from_address',$wallet_address)->orWhere('to_address',$wallet_address);
        })->whereIn('type',[AsacTrade::BUY,AsacTrade::SELL])->select('num','from_address','to_address','created_at')
            ->get()->map(function ($item,$items) use($wallet_address){
                if($item->from_address == $wallet_address){
                    $item->type_name = '购买商品花费';
                    $item->num = '-'.$item->num;
                }else{
                    $item->type_name = '售出商品获得';
                    $item->num = '+'.$item->num;
                }
                return $item;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }

    public function coin_log(Request $request){
        $id = $request->id;
        $user_id = Auth::user()->id;
        $page = $request->page??1;
        $page_size = $request->page_size??6;
        switch ($id){
            case -1:
                //获取我的地址
                $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
                $list = AsacTrade::query()->where('to_address',$wallet_address)->where('type',AsacTrade::RECHARGE)->get()
                    ->map(function ($item,$items){
                        $item->type_name = "充值";
                        $item->num = '+'.$item->num;
                        $item->coin = 'ASAC';
                    })->forPage($page,$page_size);
                return collect([])->merge($list)->toArray();
                break;
            default:
                $list = UserMoney::query()->where('user_id',$user_id)->where('status',1)->select('id','money as num','created_at','coin_id')
                    ->orderBy('created_at','desc')->get()->map(function ($item,$items){
                        $item->type_name = "充值成功";
                        $item->num = "+".$item->num;
                        $item->coin = Coin::query()->where('id',$item->coin_id)->value('name');
                        return $item;
                    })->forPage($page,$page_size);
                return collect([])->merge($list)->toArray();
        }
    }
}
