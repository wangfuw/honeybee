<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Exceptions\ApiException;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AsacNode;
use App\Models\Coin;
use App\Models\Config;
use App\Models\MoneyTrade;
use App\Models\Score;
use App\Models\User;
use App\Models\UserMoney;
use App\Validate\MoneyValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class UserMoneyController extends BaseController
{
    protected $model;

    protected $validate;

    public function __construct(UserMoney $money,MoneyValidate $validate){
        $this->model = $money;
        $this->validate = $validate;
    }

    public function apply(Request $request){
        $data = $request->only(['num','charge_image','id']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        if($data['id'] == -1){
            return $this->fail('该币种充值方式错误');
        }
        $rate = Coin::query()->where('id',$data['id'])->value('money');
        $res = UserMoney::query()->create([
           'user_id' => Auth::user()->id,
            'num'    => $data['num'],
            'charge_image'=>$data['charge_image'],
            'money' => bcmul($data['num'],$rate,2),
            'coin_id' => $data['id']
        ]);
        return $this->success('上传成功',$res);
    }

    public function trade(Request $request)
    {
        $data = $request->only(['num','wallet_address','sale_password']);
        if(!$this->validate->scene('trade')->check($data)){
            return $this->fail($this->validate->getError());
        }
        $address = Rsa::decodeByPrivateKey($data['wallet_address']);
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
            $to_user->save();
            //写入交易记录
            MoneyTrade::query()->create([
                'from_id'=>$user->id,
                'to_id'  =>$to_user->id,
                'num'    =>$data['num'],
                'type'   => MoneyTrade::CHANGE
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
                        $item->type_name = "充值";
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
                        $item->address = AsacNode::query()->where('user_id',$item->to_id)->value('wallet_address')??'';
                    }else{
                        $item->num = '+'.$item->num;
                        $item->type_name = '转入';
                        $item->address = AsacNode::query()->where('user_id',$item->from_id)->value('wallet_address')??'';
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
                        $item->address = AsacNode::query()->where('user_id',$item->to_id)->value('wallet_address')??'';
                    }else{
                        $item->num = '+'.$item->num;
                        $item->type_name = '交易转入';
                        $item->address = AsacNode::query()->where('user_id',$item->from_id)->value('wallet_address')??'';
                    }
                    return $item;
                })->forPage($page,$page_size);
                break;
            case 4:
                $list = Score::query()->where('type',5)->select('id','num','created_at','f_type')->orderBy('created_at','desc')->get()->map(function ($item,$items){
                    $item->type_name = Score::F_TYPES[$item->f_type];
                    $item->num = '+'.$item->num;
                    return $item;
                })->forPage($page,$page_size);
        }
        $data = collect([])->merge($list)->toArray();
        return $this->success('请求成功',$data);
    }

    public function get_coins()
    {
        $user = Auth::user();
        $wallet_address= AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
        $other_coin = Coin::query()->select('id','name','address')->where('status',1)->get();
        $asac_arr = ['id'=>'-1','name'=>'ASAC','address'=>$wallet_address];
        $data = [];
        if($other_coin){
            $data= $other_coin->toArray();
        }
        array_push($data,$asac_arr);
        foreach ($data as &$datum){
            $img = QrCode::format('png')->size(200)->generate($datum['address']);
            $datum['address_code'] = 'data:image/png;base64,' . base64_encode($img );
        }
        return $this->success('请求成功',$data);
    }
}
