<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AsacNode;
use App\Models\AsacTrade;
use App\Models\Coin;
use App\Models\UserMoney;
use App\Models\Withdraw;
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
                $list = $this->get_withdraw($user,$page,$page_size);
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
        $list = AsacTrade::query()->where('to_address',$wallet_address)->where('type',AsacTrade::RECHARGE)->orderBy('created_at','desc')->get()
            ->map(function ($item,$items){
                $item->type_name = "充值";
                $item->coin = "ASAC";
                $item->num = '+'.$item->num;
                $item->address = $item->to_address;
                return $item;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }
    protected function get_status($status){
        switch ($status){
            case 0:
                return '待审核';
            case 1:
                return '审核通过';
            case 2:
                return '审核撤回';
        }
    }
    protected function get_withdraw($user,$page,$page_size){
        $user_id = $user->id;
        //获取我的地址
        $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
        $list = Withdraw::query()->where('user_id',$user_id)->orderBy('created_at','desc')->get()
            ->map(function ($item,$items){
                $item->type_name = "提现";
                $item->coin = "ASAC";
                $item->num = $item->amount;
                $item->address = $item->withdraw_address;
                $item->status = $item->status;
                $item->status_name =$this->get_status($item->status);
                $item->err = $item->err;
                return $item;
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
        })->whereIn('type',[AsacTrade::CHANG_IN,AsacTrade::CHANG_OUT,AsacTrade::FREE_USED,AsacTrade::FREE_HAVED])->select('num','from_address','to_address','created_at')->orderBy('created_at','desc')
            ->get()->map(function ($item,$items) use($wallet_address){
                if($item->from_address == $wallet_address){
                    $item->type_name = '转出';
                    $item->num = '-'.$item->num;
                    $item->address = $item->from_address;
                }else{
                    $item->type_name = '转入';
                    $item->num = '+'.$item->num;
                    $item->address = $item->to_address;
                }
                $item->coin = "ASAC";
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
        })->whereIn('type',[AsacTrade::BUY,AsacTrade::SELL])->select('num','from_address','to_address','created_at','game_zone','type')->orderBy('created_at','desc')
            ->get()->map(function ($item,$items) use($wallet_address){
                if($item->from_address == $wallet_address){
                    $item->type_name = $this->get_game($item->game_zone).AsacTrade::typeData[$item->type];
                    $item->num = '-'.$item->num;
                    $item->address = $item->from_address;
                }else{
                    $item->type_name = $this->get_game($item->game_zone).AsacTrade::typeData[$item->type];
                    $item->num = '+'.$item->num;
                    $item->address = $item->to_address;
                }
                $item->coin = "ASAC";
                return $item;
            })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }

    protected function get_game($game_zone)
    {
        switch ($game_zone){
            case 1:
                return '福利专区';
            case 2:
                return '优选专区';
            case 3:
                return '幸运专区';
            case 4:
                return '消费专区';
        }
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
                $list = AsacTrade::query()->where('to_address',$wallet_address)->where('type',AsacTrade::RECHARGE)->orderBy('created_at','desc')->get()
                    ->map(function ($item,$items){
                        $item->type_name = "充值";
                        $item->num = '+'.$item->num;
                        $item->coin = 'ASAC';
                    })->forPage($page,$page_size);
                $list = collect([])->merge($list)->toArray();
                break;
            default:
                $wallet_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
                $list = UserMoney::query()->where('user_id',$user_id)->where('status',1)->select('id','money','created_at','coin_id','num')
                    ->orderBy('created_at','desc')->get()->map(function ($item,$items) use($wallet_address){
                        $item->type_name = "充值成功";
                        $item->money = "+".$item->money;
                        $item->coin = Coin::query()->where('id',$item->coin_id)->value('name');
                        $item->to_address = $wallet_address;
                        return $item;
                    })->forPage($page,$page_size);
                $list = collect([])->merge($list)->toArray();
        }
        return $this->success('请求成功',$list);
    }
}
