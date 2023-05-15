<?php

namespace App\Services;

use App\Common\Rsa;
use App\Exceptions\ApiException;
use App\Exceptions\OrderException;
use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use App\Models\Config;
use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\MoneyTrade;
use App\Models\Order;
use App\Models\RevokeOrder;
use App\Models\Score;
use App\Models\ShopCart;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use function PHPUnit\Framework\isEmpty;

class OrderService
{

    protected $model;

    const GREEN = 4;
    const SALE  = 8;

    protected $base;
    protected $middle;
    protected $last;
    public function __construct(MallSku $model, Config $config){
        $this->model = $model;
        $this->base = $config::lucky_base();
        $this->middle = $config::lucky_middle();
        $this->base = $config::lucky_last();

    }
    protected  function getOrderId()
    {
        //今天时间
        $date = date('Ymd',time());
        //当天自增数
        $dateNum = Redis::hincrby($date, 1, 1);
        $dateNum = sprintf("%08d", $dateNum);
        //当天订单号
        $order_id = $date.$dateNum;
        //清除前天的数据
        $yesterdayDate = date('Ymd',time()-86400*2);
        if(Redis::exists($yesterdayDate)){
            Redis::del($yesterdayDate);
        }
        return $order_id;
    }

    public function orders($params = [],$user)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??5;
        $type = $params['type']??0;
        switch ($type){
            case 1:
                //待支付
                $list = Order::query()->with(['sku'=>function($query){
                    return $query->select('id','indexes','price');
                },'spu'=>function($query){
                    return $query->select('id','logo','special_spec','name','user_id','game_zone');
                }])->select('id','spu_id','sku_id','sku_num','order_no','coin_num','ticket_num','status','express_status','price','money','created_at','updated_at',)
                    ->where('status',1)
                    ->where('user_id',$user->id)
                    ->where('is_return',0)
                    ->orderBy('created_at','desc')
                    ->forPage($page,$page_size)
                    ->get();
                if(empty($list)) return [];
                break;
            case 2:
                //代发货
                $list = Order::query()->with(['sku'=>function($query){
                    return $query->select('id','indexes','price');
                },'spu'=>function($query){
                    return $query->select('id','logo','special_spec','name','user_id','game_zone');
                }])->select('id','spu_id','sku_id','sku_num','order_no','coin_num','ticket_num','status','express_status','price','money','created_at','updated_at')
                    ->where('status',2)
                    ->where('express_status',0)
                    ->where('user_id',$user->id)
                    ->where('is_return',0)
                    ->orderBy('created_at','desc')
                    ->forPage($page,$page_size)
                    ->get();
                if(empty($list)) return [];
                break;
            case 3:
                //待收获
                $list = Order::query()->with(['sku'=>function($query){
                    return $query->select('id','indexes','price');
                },'spu'=>function($query){
                    return $query->select('id','logo','special_spec','name','user_id','game_zone');
                }])->select('id','spu_id','sku_id','sku_num','order_no','coin_num','ticket_num','status','express_status','price','money','created_at','updated_at','express_no','express_name')
                    ->where('status',2)
                    ->where('express_status',1)
                    ->where('user_id',$user->id)
                    ->where('is_return',0)
                    ->orderBy('created_at','desc')
                    ->forPage($page,$page_size)
                    ->get();
                if(empty($list)) return [];
                break;
            case 4:
                //已完成 签收的商品
                $list = Order::query()->with(['sku'=>function($query){
                    return $query->select('id','indexes','price');
                },'spu'=>function($query){
                    return $query->select('id','logo','special_spec','name','user_id','game_zone');
                }])->select('id','spu_id','sku_id','sku_num','order_no','coin_num','ticket_num','status','express_status','price','money','created_at','updated_at','express_no','express_name')
                    ->where('status',2)
                    ->where('express_status',2)
                    ->where('user_id',$user->id)
                    ->where('is_return',0)
                    ->orderBy('created_at','desc')
                    ->forPage($page,$page_size)
                    ->get();
                if(empty($list)) return [];
                break;
            default:
                $list = Order::query()->with(['sku'=>function($query){
                    return $query->select('id','indexes','price');
                },'spu'=>function($query){
                    return $query->select('id','logo','special_spec','name','user_id','game_zone');
                }])->select('id','spu_id','sku_id','sku_num','order_no','coin_num','ticket_num','status','express_status','price','money','created_at','updated_at','express_no','express_name')
                    ->where('user_id',$user->id)
                    ->where('is_return',0)
                    ->orderBy('created_at','desc')
                    ->forPage($page,$page_size)
                    ->get();
                if(empty($list)) return [];
        }
        $list = $list->map(function ($item,$items){
            $item->one_price = $item->sku->price;
            $item->indexes = $item->sku->indexes;
            $indexes = explode('_',$item->sku->indexes);
            $item->logo = $item->spu->logo;
            $item->game_zone = $item->spu->game_zone;
            $item->game_name = $this->get_name($item->spu->game_zone);
            $item->special_spec = $item->spu->special_spec;
            $item->allTotal = $item->sku_num * $item->price;
            $special = array_values((array)$item->spu->special_spec);
            $index_special = [];
            if($item->spu->user_id == 0){
                $item->store_name = '源宇通自营';
            }else{
                $item->store_name = Store::query()->where('user_id',$item->spu->user_id)->value('store_name')??'';
            }
            for($i=0;$i<count($indexes);$i++){
                array_push($index_special,$special[$i][$indexes[$i]]);
            }
            $item->index_special = $index_special;
            $item->name = $item->spu->name;
            unset($item->sku,$item->spu,$index_special,$special,$indexes);
            return $item;
        });
        return collect([])->merge($list)->toArray();
    }

    protected function get_name($game_zone){
        switch ($game_zone){
            case 1:
                return '幸福专区';
            case 2:
                return '优选专区';
            case 3:
                return '幸运专区';
            case 4:
                return '消费专区';
        }
    }
    //详情
    public function info($order_no)
    {
        $info = Order::query()->with(['sku'=>function($query){
            return $query->select('id','indexes','price');
        },'spu'=>function($query){
            return $query->select('id','logo','special_spec','name','user_id','game_zone','score_zone');
        }])->select('*')->where('order_no',$order_no)
            ->first();
        if(!$info){
            return [];
        }
        $info->one_price = $info->sku->price;
        $info->indexes = $info->sku->indexes;
        $indexes = explode('_',$info->indexes);
        $special = array_values((array)$info->spu->special_spec);
        $index_special = [];
        if($info->spu->user_id == 0){
            $info->store_name = '源宇通自营';
        }else{
            $info->store_name = Store::query()->where('user_id',$info->spu->user_id)->value('store_name')??'';
        }
        for($i=0;$i<count($indexes);$i++){
            array_push($index_special,$special[$i][$indexes[$i]]);
        }
        $info->index_special = $index_special;
        $info->logo = $info->spu->logo;
        $info->special_spec = $info->spu->special_spec;
        $info->area_china = city_name($info->address['area']);
        $info->area = $info->address['area'];
        $info->exp_phone = make_phone($info->address['exp_phone']);
        $info->exp_person = $info->address['exp_person'];
        $info->address_detail = $info->address['address_detail'];
        $info->name = $info->spu->name;
        $info->game_zone = $info->spu->game_zone;
        $info->game_name = $this->get_name($info->spu->game_zone);
        $info->score_zone = $info->spu->score_zone;
        unset($info->sku,$info->spu,$info->address,$index_special);
        return $info->toArray();
    }
    //创建订单
    public function add_order($data,$user){
        $add_data = [];
        $order_no = $this->getOrderId();
        //订单号
        $add_data['order_no'] = $order_no;
        $last_price = Asaconfig::get_price();
        $sku_info = $this->model->select('id','stock','spu_id','price')->where('id',$data['sku_id'])->first();
        $spu_info = MallSpu::query()->where('id',$sku_info->spu_id)->select('game_zone','score_zone','user_id','fee')->first();

        //dd($sku_info);
        unset($sku_info->spu);
        $add_data['user_id'] = $user->id;
        $add_data['sku_id'] = $data['sku_id'];
        $add_data['spu_id'] = $data['spu_id'];
        $add_data['sku_num'] = $data['number'];
        $add_data['store_id'] = $spu_info->user_id;
        $add_data['express_fee'] = $spu_info->fee;
        $add_data['address']  = $data['address'];

        try{
            DB::beginTransaction();
            //检查库存
            if($sku_info->stock < $data['number']){
                throw new ApiException([0,'商品库存不足']);
            }else{
                //下单扣除库存
               $sku_info->stock = $sku_info->stock - $data['number'];

            }
            $price_total = bcmul($sku_info->price,$data['number'],2);

            //检查商家绿色积分
            if($spu_info->user_id >= 2 && $spu_info->game_zone == 1){
                //商家绿色积分
                $boss = User::query()->where('id',$spu_info->user_id)->select('id','green_score','phone')->first();
                $need_green = $price_total * $spu_info->score_zone;
                if($need_green > $boss->green_score){
                    //短信通知商家
                    send_sms($boss->phone,'您的绿色积分不足,请购买获取绿色积分');
                    throw new ApiException([0,'商家绿色积分不足']);
                }else{
                    $boss->green_score = bcsub($boss->green_score,$need_green);
                    $boss->save();
                }
            }

            switch ($spu_info->game_zone)
            {
                case 1:
                    //绿色积分分区
                    $add_data['coin_num'] = bcdiv($price_total,$last_price + $spu_info->fee,2);
                    $need_green = $price_total * $spu_info->score_zone;
                    $add_data['give_green_score'] = $need_green;
                    break;
                case 2:
                    //消费卷专区
                    $add_data['coin_num'] = bcdiv($price_total,$last_price + $spu_info->fee,2);
                    $need = $price_total * $spu_info->score_zone;
                    $add_data['give_sale_score'] = $need;
                    break;
                case 3:
                    //幸运专区获取幸运值
                    $add_data['money'] = $price_total;
                    switch ($price_total){
                       case $price_total > 1 && $price_total<2000:
                            $add_data['give_lucky_score'] = $price_total * $this->base;
                            break;
                       case $price_total>=2000 && $price_total<10000:
                            $add_data['give_lucky_score'] = $price_total * $this->middle;
                            break;
                       case $price_total>=10000;
                            $add_data['give_lucky_score'] = $price_total * $this->last;
                            break;
                    }
                   break;
                case 4:
                    $add_data['ticket_num'] = bcdiv($price_total,Config::ticket_ratio_rmb(),2);
                    break;
                default:
                    $add_data['coin_num'] = bcdiv($price_total,$last_price + $spu_info->fee,2);
            }
            $add_data['price'] = $price_total;
            $order = Order::query()->create($add_data);
            DB::commit();
            $info = Order::query()->where('id',$order->id)->first();
            $info->area = city_name($info->address['area']);
            $info->address_detail = $info->address['address_detail'];
            $info->phone = make_phone($info->address['exp_phone']);
            $info->exp_person = $info->address['exp_person'];
            return $info;
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException([0,$e->getMessage()]);
        }
    }

    public function bay_order(){}
    //取消订单，回滚库存
    public function del($order_no,$user)
    {
        try{
            DB::beginTransaction();
            $info = Order::query()->where('order_no',$order_no)->where('status',1)->first();
            if(!$info){
                throw new ApiException([0,'该订单不可撤销']);
            }
            if($info->user_id != $user->id){
                throw new ApiException([0,'该订单不是您的订单']);
            }
            if($info->give_green_score > 0 && $info->store_id > 0){
                //返还商家
                $boss = User::query()->where('id',$info->store_id)->first();
                $boss->green_score += $info->give_green_score;
                $boss->save();
            }
            $info->status = 3;
            $info->save();
            $info->delete();
            DB::commit();
            return true;
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException([0,$e->getMessage()]);
        }
    }

    public function pay_order($params,$user){
        $sale_password = $params['sale_password'];
        $c_sale_password = Rsa::decodeByPrivateKey($sale_password);
        $order_no = $params['order_no'];
        try {
            DB::beginTransaction();
            if($c_sale_password != $user->sale_password){
                throw new ApiException([0,'支付密码错误']);
            }
            $info = Order::query()->where('order_no',$order_no)->where('status',1)->first();
            if(empty($info)){
                throw new ApiException([0,'该订单不可支付']);
            }
            if($info->user_id != $user->id){
                throw new ApiException([0,'该订单不是您的订单']);
            }
            //检查余额
            if($info->coin_num > 0 && $info->coin_num > $user->coin_num){
                throw new ApiException([0,'您的币余额不足']);
            }
            if($info->ticket_num > 0 && $info->ticket_num > $user->ticket_num){
                throw new ApiException([0,'您的消费卷不足']);
            }
            if($user->money - $info->money < 0){
                throw new ApiException([0,'余额不足']);
            }
            //检测商品分区
            $spu_id    = MallSku::query()->where('id',$info->sku_id)->value('spu_id');
            $spuS      = MallSpu::query()->where('id',$spu_id)->select('game_zone','user_id','score_zone')->first();
            //自营
            if($spuS->user_id == 0){
                $spuS->user_id = 1;
            }
            $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
            //写日志,币流转
            $this->read_zone_log($spuS,$info,$user,$user_address);
            $info->status = 2;
            $info->save();
            ShopCart::query()->where('spu_id',$spu_id)->where('sku_id',$info->sku_id)->delete();
            DB::commit();
            return $info;
        }catch (ApiException $e){
            DB::beginTransaction();
            throw new ApiException([0,$e->getMessage()]);
        }
    }

    //
    public function read_zone_log($spuS,$info,$user,$from_address)
    {
        $last_price = Asaconfig::get_price();
        $game_zone = $spuS->game_zone;
        $score_zone = $spuS->score_zone;
        $user_id   = $spuS->user_id;
        $user_area = $user->area;
        $up_area = get_up_area($user_area);
        //活期地址
        $current = AsacNode::query()->where('id',4)->first();
        switch ($game_zone){
            case 1:
                //币流转
                if($user_id == 0){
                    $to_address = AsacNode::query()->where('user_id',1)->value('wallet_address');
                }else{
                    $to_address = AsacNode::query()->where('user_id',$user_id)->value('wallet_address');
                }

                //写入地址流转
                AsacTrade::query()->create([
                    'from_address' => $from_address,
                    'to_address'   => $to_address,
                    'num'          => $info->coin_num,
                    'trade_hash'   => rand_str_pay(64),
                    'type'         => AsacTrade::BUY,
                    'game_zone'    => 1,
                ]);
                //根据商品倍数分区
                switch ($score_zone){
                    case 1:
                        //商家让利
                        $rate = Config::green_one_allowance() / 100;
                        break;
                    case 2:
                        $rate = Config::green_twice_allowance()/100;
                        break;
                    case 3:
                        $rate = Config::green_threefold_allowance()/100;
                        break;
                    default:
                        $rate = Config::green_threefold_allowance()/100;
                }
                $temp_num = bcmul($info->coin_num,$rate,2);
                //流动扣除
                $flue_address = AsacNode::query()->where('id',1)->first();
                if($flue_address->number < $temp_num){
                    throw new ApiException([0,'流动池数量不足,支付失败']);
                }
                $flue_address->number = bcsub($flue_address->number,$temp_num,2);
                $flue_address->save();
                //预挖增加
                $pre_address = AsacNode::query()->where('id',2)->first();
                $pre_address->number = bcadd($pre_address->number,$temp_num,2);
                $pre_address->save();
                AsacTrade::query()->create([
                    'from_address' => $flue_address->wallet_address,
                    'to_address'   => $pre_address->wallet_address,
                    'num'          => $temp_num,
                    'trade_hash'   => rand_str_pay(64),
                    'type'         => AsacTrade::STORE,
                    'game_zone'    => 1,
                ]);
                //用户获得绿色积分，减少余额,累计绿色积分
                $user->green_score = bcadd($info->give_green_score,$user->green_score,2);
                $user->green_score_total = bcadd($info->give_green_score,$user->green_score_total,2);
                $user->coin_num = bcsub($user->coin_num,$info->coin_num,2);
                $user->save();

                //销售获得 asac
                if($user_id == 1 || $user_id == 0){
                    $admin_user = User::query()->where('id',1)->first();
                    $admin_user->coin_num = bcadd($admin_user->coin_num,$info->coin_num,4);
                    $admin_user->save();
                }


                //增加团队贡献值
                $masters = $user->master_pos;
                //绿色积分日志
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag' => 1,
                    'num' =>$info->give_green_score,
                    'type'=>1,
                    'f_type'=>Score::TRADE_HAVE,
                    'amount' => '-'.$info->coin_num,
                    'game_zone'    => 1,
                ]);

                //非自营扣绿色积分
                if($user_id >= 2){
                    Score::query()->create([
                        'user_id'=>$user_id,
                        'flag' => 2,
                        'num' => $info->give_green_score,
                        'type'=>1,
                        'f_type'=>Score::TRADE_USED,
                        'amount' => '+'.$info->coin_num,
                        'game_zone'    => 1,
                    ]);
                }


                if($masters){
                    $masters =  explode(',',substr($masters,1,strlen($masters) - 2));
                    $temp = bcdiv($info->give_green_score,self::GREEN,2);
                    foreach ($masters as $master){
                        $user1 = User::query()->where('id',$master)->select('id','contribution')->first();
                        if($user1){
                            $user1->contribution += $temp;
                            $user1->save();
                        }else{
                            continue;
                        }

                    }
                }
                break;
            case 2:
                //消费积分区 -- 不会立马获得
                $to_address = AsacNode::query()->where('user_id',1)->value('wallet_address');
                //写入地址流转
                AsacTrade::query()->create([
                    'from_address' => $from_address,
                    'to_address'   => $to_address,
                    'num'          => $info->coin_num,
                    'trade_hash'   => rand_str_pay(64),
                    'type'         => AsacTrade::BUY,
                    'game_zone'    => 2,
                ]);

                switch ($score_zone){
                    case 1:
                        //商家让利
                        $rate = Config::consume_one_allowance()/100;
                        break;
                    case 2:
                        $rate = Config::consume_twice_allowance()/100;
                        break;
                    case 3:
                        $rate = Config::consume_threefold_allowance()/100;
                        break;
                    default:
                        $rate = Config::consume_threefold_allowance()/100;
                }
                $temp_num = bcmul($info->coin_num,$rate,2);

                //流动扣除
                $flue_address = AsacNode::query()->where('id',1)->first();

                if($flue_address->number < $temp_num){
                    throw new ApiException([0,'流动池数量不足,下单失败']);
                }
                $flue_address->number = bcsub($flue_address->number,$temp_num,2);
                $flue_address->save();
                //预挖增加
                $pre_address = AsacNode::query()->where('id',2)->first();
                $pre_address->number = bcadd($pre_address->number,$temp_num,2);
                $pre_address->save();
                $res = AsacTrade::query()->create([
                    'from_address' => $flue_address->wallet_address,
                    'to_address'   => $pre_address->wallet_address,
                    'num'          => $temp_num,
                    'trade_hash'   => rand_str_pay(64),
                    'type'         => AsacTrade::STORE,
                    'game_zone'    => 2,
                ]);
                //用户减少余额
                $user->coin_num = bcsub($user->coin_num,$info->coin_num,2);
                $user->save();
                //平台减少
                //销售获得 asac
                if($user_id == 1 || $user_id == 0){
                    $admin_user = User::query()->where('id',1)->first();
                    $admin_user->coin_num = bcadd($admin_user->coin_num,$info->coin_num,4);
                    $admin_user->save();
                }

                break;
            case 3:
                //幸运值专区
                //写入地址流转
                $to_address = AsacNode::query()->where('user_id',1)->value('wallet_address');
                MoneyTrade::query()->create([
                    'from_id' => $user->id,
                    'to_id'   => 1,
                    'num'     => $info->money,
                    'type'    => MoneyTrade::BUY,
                ]);
                //单次消费最大额
                $max_luck_num = $user->max_luck_num;
                $price = $info->money;

                //上级user_id
                $master_id = $user->master_id;
                //给自己加幸运值,减余额,跟新幸运值最大消费
                $user->luck_score = bcadd($user->luck_score,$info->give_lucky_score,4);
                $user->money = bcsub($user->money,$info->money,4);
                $user->max_luck_num = max($max_luck_num,$price);
                $user->save();

                //管理员加余额
                $admin = User::query()->where('id',1)->first();
                $admin->money = bcadd($admin->money,$info->money,4);
                $admin->save();


                //获取幸运值日志
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag' => 1,
                    'num' =>$info->give_lucky_score,
                    'type'=>3,
                    'f_type'=>Score::TRADE_HAVE,
                    'game_zone'    => 3,
                    'amount' => '-'.$info->money,
                ]);

                //上级发asac奖励---凭空产生
                $masters = User::query()->where('id',$master_id)->first();
                //上级钱包地址
                $master_address = AsacNode::query()->where('user_id',$master_id)->value('wallet_address');
                $max = $masters->max_luck_num;
                switch ($max){
                    case $max >= 0 && $max < 2000:
                        $rate = Config::lucky_base_reward_coin();
                        break;
                    case $max >= 2000 && $max < 10000;
                        $rate = Config::lucky_middle_reward_coin();
                        break;
                    case $max >= 10000:
                        $rate = Config::lucky_last_reward_coin();
                        break;

                }
                $num = min($max,$info->money);
                //幸运值专区给商家发asac
                $temp =  bcmul($num/100,$rate,4);
                if($temp > 0.0001){
                    $masters->money += $temp;
                    $masters->save();
                }
                //奖励发放 -- 赞顶平台发放  实际不扣 1号的余额
                if($master_address && $temp > 0){
                    MoneyTrade::query()->create([
                        'from_id' => 1,
                        'to_id'   => $master_id,
                        'num'     =>  $temp,
                        'type'    => MoneyTrade::REWARD,
                    ]);
                }




                //给旗舰店/形象店发幸运值
//                $model_store = User::query()->where('identity',1)
//                    ->where('identity_status',1)
//                    ->where('identity_area_code',$user_area)->pluck('id');
//
//                if($model_store){
//                    $num = bcmul($info->give_lucky_score,0.15,2);
//                    foreach ($model_store as $value){
//                        $res = User::query()->where('id',$value)->select('id','lucky_score')->first();
//                        $res->lucky_score += $num;
//                        $res->save();
//                        //日志
//                        Score::query()->create([
//                            'user_id'=>$res->id,
//                            'flag' => 1,
//                            'num' =>$num,
//                            'type'=>3,
//                            'f_type'=>Score::TRADE_REWARD,
//                            'game_zone'    => 3,
//                        ]);
//                    }
//                }
//                $up_store = User::query()->where('identity',1)
//                    ->where('identity_status',1)
//                    ->where('identity_area_code',$up_area)->pluck('id');
//                if($up_store){
//                    $num = bcmul($info->give_lucky_score,0.05,2);
//                    foreach ($up_store as $value){
//                        $res = User::query()->where('id',$value)->select('id','lucky_score')->first();
//                        $res->luck_score += $num;
//                        $res->save();
//                        //日志
//                        Score::query()->create([
//                            'user_id'=>$res->id,
//                            'flag' => 1,
//                            'num' =>$num,
//                            'type'=>3,
//                            'f_type'=>Score::TRADE_REWARD,
//                            'game_zone'    => 3,
//                        ]);
//                    }
//                }
                break;
            case 4:
                //消费卷减少
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag' => 2,
                    'num' =>$info->ticket_num,
                    'type'=>4,
                    'f_type'=>Score::BUY_USED,
                    'amount'=>0,
                    'game_zone'    => 4,
                    'amount' => '-'.$info->ticket_num,
                ]);
                //减少消费卷
                $user->ticket_num = bcsub($user->ticket_num,$info->ticket_num);
                $user->save();

                //给形象店，旗舰店发币
                $model_store = User::query()->where('identity',1)
                    ->where('identity_status',1)
                    ->where('identity_area_code',$user_area)->pluck('id');
                if(!$model_store){
                    $num = bcmul($info->ticket_num,0.15,2);
                    $coin_num = bcdiv($num,$last_price,2);
                    foreach ($model_store as $value){
                        $res = User::query()->where('id',$value)->select('id','coin_num')->first();
                        $to_address = Asaconfig::query()->where('id',$value)->value('wallet_address');
                        $res->coin_num += $coin_num;
                        $res->save();
                        $current->number -= $coin_num;
                        $current->save();
                        //写日志
                        AsacTrade::query()->create([
                            'from_address' => $current->wallet_address,
                            'to_address'   => $to_address,
                            'num'          => $coin_num,
                            'trade_hash'   => rand_str_pay(64),
                            'type'         => AsacTrade::REWARD,
                            'game_zone'    => 4,
                        ]);
                    }
                }
                $up_store = User::query()->where('identity',1)
                    ->where('identity_status',1)
                    ->where('identity_area_code',$up_area)->pluck('id');
                if(!$up_store){
                    $num = bcmul($info->ticket_num,0.05,2);
                    $coin_num = bcdiv($num,$last_price,2);
                    foreach ($up_store as $value){
                        $res = User::query()->where('id',$value)->select('id','coin_num')->first();
                        $to_address = Asaconfig::query()->where('id',$value)->value('wallet_address');
                        $res->coin_num += $coin_num;
                        $res->save();
                        $current->number -= $coin_num;
                        $current->save();
                        //写日志
                        AsacTrade::query()->create([
                            'from_address' => $current->wallet_address,
                            'to_address'   => $to_address,
                            'num'          => $coin_num,
                            'trade_hash'   => rand_str_pay(64),
                            'type'         => AsacTrade::REWARD,
                            'game_zone'    => 4,
                        ]);
                    }
                }

                //给全网六级的用户发奖 2%
                $six_team_ids = User::query()->where('contribution',60000000)->pluck('id');
                if($six_team_ids){
                    $six_team = [];
                    foreach ($six_team_ids as $six_team_id){
                        $down_user = User::query()->where('master_id',$six_team_id)->select('green_score','sale_score','contribution')->get();
                        $temp = 0;
                        foreach ($down_user as $down){
                            $self_contribution = bcadd(bcdiv($down->green_score,self::GREEN,4),bcdiv($down->sale_score,self::SALE,4));
                            $dict_contribution = bcadd($self_contribution,$down->contribution);
                            if($dict_contribution > 5000000){
                                $temp += 1;
                            }else{
                                continue;
                            }
                        }
                        if($temp >= 2){
                            array_push($six_team,$six_team_id);
                        }
                    }
                    if($six_team){
                        $total = User::query()->whereIn('id',$six_team)->sum('contribution');
                        $total_coin = bcdiv($info->ticket_num,$last_price,2);
                        foreach ($six_team as $six){
                            $true_six_team = User::query()->where('id',$six)->select('id','coin_num','contribution')->first();
                            $to_address = AsacNode::query()->where('user_id',$six)->value('wallet_address');
                            $rete = bcdiv($true_six_team,$total,2);
                            $true_six_team->coin_num = bcdiv($total_coin * 0.02,$rete,2);
                            $true_six_team->save();
                            //发地址减去金额
                            $current->number -=  $true_six_team->coin_num;
                            $current->save();
                            //流转日志
                            AsacTrade::query()->create([
                                'from_address' => $current->wallet_address,
                                'to_address'   => $to_address,
                                'num'          =>  $true_six_team->coin_num,
                                'trade_hash'   => rand_str_pay(64),
                                'type'         => AsacTrade::REWARD,
                                'game_zone'    => 4,
                            ]);
                        }
                    }
                }
                break;
        }
    }

    public function sign_order($order_no,$user)
    {
        //已支付订单
        $info = Order::query()->where('order_no',$order_no)->where('status',2)->where('express_status',1)->first();
        if(empty($info)){
            throw new ApiException([0,'该订单不可签收']);
        }
        if($info->user_id != $user->id){
            throw new ApiException([0,'该订单不是您的订单']);
        }
        try{
            DB::beginTransaction();
            $spu_id    = MallSku::query()->where('id',$info->sku_id)->value('spu_id');
            $spuS      = MallSpu::query()->where('id',$spu_id)->select('game_zone','user_id','score_zone')->first();
           if($info->give_sale_score > 0){
               $user->sale_score = bcadd($user->sale_score,$info->give_sale_score,2);
               $user->sale_score_total = bcadd($user->sale_score_total,$info->give_sale_score,2);
               $user->save();
               //积分日志
               Score::query()->create([
                   'user_id'=>$user->id,
                   'flag' => 1,
                   'num' =>$info->give_sale_score,
                   'type'=>2,
                   'f_type'=>Score::TRADE_HAVE,
                   'amount'=>'-'.$info->coin_num,
                   'game_zone'    => 2,
               ]);
               //增加团队贡献值
               $masters = $user->master_pos;
               if($masters){
                   $masters =  explode(',',substr($masters,1,strlen($masters) - 2));
                   $temp = bcdiv($info->give_sale_score,self::SALE,2);
                   foreach ($masters as $master){
                       $user = User::query()->where('id',$master)->select('id','contribution')->first();
                       if($user){
                           $user->contribution += $temp;
                           $user->save();
                       }else{
                           continue;
                       }

                   }
               }
           }
            //给用户加消费积分

            //签收订单
            $info->express_status = 2;
            $info->save();
            DB::commit();
            return true;
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException([0,$e->getMessage()]);
        }
    }

    public function apply_revoke($params,$user)
    {
        //支付成功,未签收可以换货
        $info = Order::query()->where('order_no',$params['order_no'])->where('status',2)->where('express_status','<>',2)->where('is_return',0)
            ->where('user_id',$user->id)->first();
        try{
            DB::beginTransaction();
            if(empty($info)){
                throw new ApiException([0,'该订单不支持换货']);
            }
            $info->is_return = 1;
            $info->save();
            //写入换货
            $res = RevokeOrder::query()->create([
                'order_no' => $params['order_no'],
                'reason'  => $params['reason'],
                'photo'   => $params['photo'],
                'user_id' => $user->id,
            ]);
            DB::commit();
            return $res;
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException([0,'申请失败']);
        }
    }

    public function revokes($params,$user){
        $page = $params['page']??1;
        $page_size = $params['page_size']??5;
        $list = Order::query()->with(['sku'=>function($query){
            return $query->select('id','indexes','price');
        },'spu'=>function($query){
            return $query->select('id','logo','special_spec','name','user_id','game_zone');
        }])->select('*')
            ->where('user_id',$user->id)
            ->where('is_return',1)
            ->forPage($page,$page_size)
            ->get();
        if(empty($list)) return [];
        $list = $list->map(function ($item,$items){
            $item->one_price = $item->sku->price;
            $item->indexes = $item->sku->indexes;
            $item->logo = $item->spu->logo;
            $item->special_spec = $item->spu->special_spec;
            $item->game_zone = $item->spu->game_zone;
            $item->game_name = $this->get_name($item->spu->game_zone);
            $indexes = explode('_',$item->sku->indexes);
            $special = array_values((array)$item->spu->special_spec);
            $index_special = [];
            if($item->spu->user_id == 0){
                $item->store_name = '源宇通自营';
            }else{
                $item->store_name = Store::query()->where('user_id',$item->spu->user_id)->value('store_name')??'';
            }
            for($i=0;$i<count($indexes);$i++){
                array_push($index_special,$special[$i][$indexes[$i]]);
            }
            $item->index_special = $index_special;
            $item->name = $item->spu->name;
            unset($item->sku,$item->spu);
            return $item;
        });
        return collect([])->merge($list)->toArray();
    }

    public function del_revoke($order_no,$user)
    {
        $info = RevokeOrder::query()->where('order_no',$order_no)->first();
        $order = Order::query()->where('order_no',$order_no)->first();
        try {
            DB::beginTransaction();
            if(empty($info)){
                throw new ApiException([0,'该订单不存在']);
            }
            RevokeOrder::query()->where('order_no',$order_no)->delete();
            $order->is_return = 0;
            $order->save();
            DB::commit();
            return true;
        }catch (ApiException $e){
            DB::rollBack();
            throw new ApiException([0,'取消失败']);
        }
    }
}
