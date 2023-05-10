<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Exceptions\ApiException;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Asac;
use App\Models\AsacBlock;
use App\Models\AsacDestory;
use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use App\Models\Config;
use App\Models\Notice;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;

class AsacController extends BaseController
{

    public function coin_info()
    {
        $config = Asaconfig::query()->select('last_price','old_price','name')->find(1);
        $temp = bcsub($config['last_price'] , $config['old_price'],2);
        $rate = bcdiv($temp*100,$config['old_price'],2).'%';
        return $this->success('请求成功',['last_price'=>$config['last_price'],'name'=>$config['name'],'rate'=>$rate]);
    }
    public function asac_login(Request $request)
    {
        $private_key = $request->private_key;
        if(!AsacNode::query()->where('private_key',$request->private_key)->exists()){
            return $this->fail('不存在该用户');
        }
        $wallet_address = AsacNode::query()->select('wallet_address','number','user_id')
            ->where('private_key',$request->private_key)
            ->first();
        if($wallet_address->user_id != 0){
            $wallet_address->number = User::query()->where('id',$wallet_address->user_id)->value('coin_num')??0;
        }
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
        ,'owner_num','trans_num'
        )->first();
        $list->owner_num = AsacNode::query()->count();
        $list->flux += AsacTrade::where("type",AsacTrade::FREE_USED)->sum("num");
        $list->trans_num = AsacTrade::query()->count();
        $list->dest_num = AsacDestory::query()->sum('number');
        return $this->success('success',$list);
    }

    //区块详情
    public function block_info(Request $request)
    {
        $id = $request->id;
        $block = AsacBlock::query()->where('id',$id)->first();
        if(!$block){
            return $this->success('success',['trade_num'=>0,'number'=>0]);
        }
        if(!empty($block)){
            $trades = AsacTrade::query()->where('block_id',$id)->get()->toArray();
            $data = [];
            $data['id'] = $block['id'];
            $data['trade_num'] = $block['trade_num'];
            $data['number'] = AsacTrade::query()->where('block_id',$id)->sum('num');
            $data['list'] = $trades;
            $data['time'] = $block['created_at'];
            return $this->success('success',$data);
        }else{
            return $this->success('success',['trade_num'=>0,'number'=>0]);
        }
    }

    /**首页
     * @return void
     */
    public function index(){
        $list = AsacBlock::query()->with(['trade'=>function($query){
            return $query->select('block_id','num');
        }])->select('id','created_at','trade_num as number')
            ->orderBy('id','desc')->limit(4)->get()->map(function ($item,$items){
               $temp = 0;
               foreach ($item->trade as $value){
                   $temp += $value['num'];
               }
               $item->trade_num = $temp;
               unset($item->trade);
               return $item;
            })->toArray();
        $trade_total = AsacTrade::query()->sum('num');
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
        return $this->success('请求成功',$list);
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
        return $this->success('请求成功',["list"=>$list]);
    }

    public function get_notices(Request $request)
    {
        $page = $request->page??1;
        $page_size = $request->page_size??3;
        $data = Notice::query()->where('type',5)->get();
        if(!$data){
            return $this->success('请求成功，暂无数据',[]);
        }
        $data->forPage($page,$page_size);
        $list = collect([])->merge($data)->toArray();
        return $this->success('请求成功',$list);
    }

    public function get_destory(Request $request)
    {
        $page = $request->page??1;
        $page_size = $request->page_size??3;
        $list = AsacDestory::query()->select('id','dest_address','number','created_at')->get()->forPage($page,$page_size);
        $sum = AsacDestory::query()->sum('number');
        $count = AsacDestory::query()->count();
        $data = collect([])->merge($list)->toArray();
        return $this->success('请求成功',compact('sum','count','data'));
    }

    //流动池 预挖池 流转记录
    public function get_flue(Request $request)
    {
        $type = $request->post('type'); //1 流动池子 2 预挖池
        if($type == 1){
            //流动池地址
            $address = AsacNode::query()->where('id',1)->value('wallet_address');
        }else{
            //於挖池地址
            $address = AsacNode::query()->where('id',2)->value('wallet_address');
        }
        $page = $request->page??1;
        $page_size = $request->page_size??6;
        $handler = AsacTrade::query();
        dd($handler);
        $handler->where(function ($query) use ($address){
            return $query->where('to_address',$address)->orWhere('from_address',$address);
        });

        $list = $handler->select('id','from_address','to_address','num','trade_hash','block_id','created_at','type')->orderBy('id','desc')->get()
            ->map(function ($item,$items) use($address){
                if($address == $item->from_address){
                    $item->type_name = AsacTrade::typeData[$item->type];
                    $item->num = '-'.$item->num;
                    $item->address = $item->to_address;
                }else{
                    $item->type_name = AsacTrade::typeData[$item->type];
                    $item->num = '+'.$item->num;
                    $item->address = $item->from_address;
                }
                return $item;
            })->forPage($page,$page_size);

        $data = collect([])->merge($list)->toArray();
        return $this->success('请求成功',$data);
    }

    //充值 成功 返回交易hash
    public function excharge(Request $request){
        //充值地址
        $from_address = $request->from_address;
        //接受地址
        $to_address = $request->to_address;
        //数量
        $num = $request->num;
        $hash = rand_str_pay(64);
        try{
            DB::beginTransaction();
            AsacTrade::query()->create([
                'from_address'=>$from_address,
                'to_address' => $to_address,
                'num'        => $num,
                'type'       => AsacTrade::RECHARGE,
                'trade_hash'      => $hash,
            ]);
            $user_id = AsacNode::query()->where('wallet_address',$to_address)->value('user_id');
            $user = User::query()->where('id',$user_id)->first();
            $user->coin_num += $num;
            $user->save();
            DB::commit();
            return $this->success('充值成功',compact('hash'));
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail($e->getMessage());
        }


    }

    //提现
    public function withdraw(Request $request){
        $user = Auth::user();
        $to_address = $request->to_address;
        $address = Rsa::decodeByPrivateKey($to_address);
        $num = $request->num;
        $fee_rate = Config::get_fee();
        $fee = bcmul($num/100,$fee_rate);
        try{
            DB::beginTransaction();
            $res = Withdraw::query()->create([
                'user_id' => $user->id,
                'withdraw_address' => $address,
                'amount' => $num,
                'fee'    => $fee,
                'actual'    => bcsub($num,$fee,2),
                'status'=>0,
            ]);
            $user->coin_num = bcsub($user->coin_num,$num,4);
            $user->save();
            DB::commit();
            return $this->success('提现申请成功',$res);
        }catch (ApiException $e){
            DB::rollBack();
            return $this->fail('提现申请失败');
        }
    }

    //转账
    public function change(Request $request)
    {
        $to_address = $request->to_address;
        $to_address = Rsa::decodeByPrivateKey($to_address);
        $num = $request->num;
        $sale_password = $request->sale_password;
        $user = Auth::user();
        if($user->coin_num < $num){
            return $this->fail('余额不足');
        }
        $user = User::query()->where('id',$user->id)->first();
        if(Rsa::decodeByPrivateKey($sale_password) != $user->sale_password){
            return $this->fail('交易密码错误');
        }
        $to_user_id = AsacNode::query()->where('wallet_address',$to_address)->value('user_id');
        if(!$to_user_id){
            return $this->fail('收款地址错误');
        }
        $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
        $to_user = User::query()->where('id',$to_user_id)->where('is_ban',1)->first();
        if(!$to_user){
            return $this->fail('收款地址不可用');
        }
        try {
            DB::beginTransaction();
            $user->coin_num -= $num;
            $user->save();
            $to_user->coin_num += $num;
            $to_user->save();
            AsacTrade::query()->create([
                'from_address'=> $user_address,
                'to_address'  => $to_address,
                'num'         => $num,
                'trade_hash'  => rand_str_pay(64),
                'type'        => 9
            ]);
            DB::commit();
            return $this->success('转账成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail($e->getMessage());
        }
    }
}
