<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Exceptions\ApiException;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AsacNode;
use App\Models\Config;
use App\Models\MoneyTrade;
use App\Models\Score;
use App\Models\User;
use App\Models\UserMoney;
use App\Validate\MoneyValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserMoneyController extends BaseController
{
    protected $model;

    protected $validate;

    public function __construct(UserMoney $money,MoneyValidate $validate){
        $this->model = $money;
        $this->validate = $validate;
    }

    public function apply(Request $request){
        $data = $request->only(['num','charge_image']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $rate = Config::money_rate()??1;
        $res = UserMoney::query()->create([
           'user_id' => Auth::user()->id,
            'num'    => $data['num'],
            'charge_image'=>$data['charge_image'],
            'money' => bcmul($data['num'],$rate,2)
        ]);
        return $this->success('上传成功',$res);
    }

    public function trade(Request $request)
    {
        $data = $request->only(['num','wallet_address','sale_password']);
        if(!$this->validate->scene('trade')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $address = $data['wallet_address'];
        $user = Auth::user();
        if($user->money < $data['num']){
            return $this->fail('余额不足');
        }
        $user = User::query()->where('id',$user->id)->first();
        $to_user_id = AsacNode::query()->where('wallet_address',$address)->value('user_id');
        $to_user = User::query()->where('id',$to_user_id)->where('is_ban',1)->first();
        if(!$to_user){
            return $this->fail('收款人不存在');
        }
        if(Rsa::decodeByPrivateKey($data['sale_password']) != $user->sale_password){
            return $this->fail('交易密码错误');
        }
        try {
            DB::beginTransaction();
            //减余额
            $user->money = bcsub($user->money,$data['num'],2);
            $user->save();
            //加余额
            $to_user->money = bcadd($to_user->money,$data['num'],2);
            //写入交易记录
            MoneyTrade::query()->create([
                'from_id'=>$user->id,
                'to_id'  =>$to_user->id,
                'num'    =>$data['num'],
            ]);
            DB::commit();
            return $this->success('转账成功');
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException($e->getMessage());
        }
    }

    //获取余额交易记录
    public function money_trades(Request $request){
        $user_id = Auth::user()->id;
        $page = $request->page??1;
        $page_size = $request->page_size??1;
        $type = $request->type??0;  //1充值 2 转账 3 交易 4释放
        switch ($type){
            case 1:
                $list = UserMoney::query()->where('user_id',$user_id)->where('status',1)->select('id','money as num','created_at')
                    ->orderBy('created_at','desc')->get()->map(function ($item,$items){
                        $item->type_name = "充值成功";
                        $item->num = "+".$item->num;
                        return $item;
                    })->forPage($page,$page_size);
                break;
            case 2:
                $list = MoneyTrade::query()->where(function ($query) use($user_id){
                    return $query->orWhere('from_id',$user_id)->orWhere('to_id',$user_id);
                })->where('type',1)->orderBy('created_at','desc')->get()->map(function ($item,$items) use($user_id){
                    if($item->from_id = $user_id){
                        $item->num = '-'.$item->num;
                        $item->type_name = '转出';
                    }else{
                        $item->num = '+'.$item->num;
                        $item->type_name = '转入';
                    }
                    return $item;
                })->forPage($page,$page_size);
                break;
            case 3:
                $list = MoneyTrade::query()->where(function ($query) use($user_id){
                    return $query->orWhere('from_id',$user_id)->orWhere('to_id',$user_id);
                })->where('type',2)->orderBy('created_at','desc')->get()->map(function ($item,$items) use($user_id){
                    if($item->from_id = $user_id){
                        $item->num = '-'.$item->num;
                        $item->type_name = '交易转出';
                    }else{
                        $item->num = '+'.$item->num;
                        $item->type_name = '交易转入';
                    }
                    return $item;
                })->forPage($page,$page_size);
                break;
            case 4:
                $list = Score::query()->where('type',5)->select('id','num','created_at')->orderBy('created_at','desc')->get()->map(function ($item,$items){
                    $item->type_name = '释放获得';
                    $item->num = '+'.$item->num;
                    return $item;
                })->forPage($page,$page_size);


        }
        $data = collect([])->merge($list)->toArray();
        return $this->success('请求成功',$data);
    }
}
