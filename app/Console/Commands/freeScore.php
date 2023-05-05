<?php

namespace App\Console\Commands;

use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use App\Models\Config;
use App\Models\Order;
use App\Models\Score;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;

class freeScore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'free_score';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'free score timer';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('积分释放，开始时间：'.date('Y-m-d H:i:s'));

        $users = User::query()->where('is_ban',1)->where('id',36)->select('id','green_score','sale_score','luck_score','phone',
            'coin_num','master_id','ticket_num','green_score_total','sale_score_total','contribution','master_pos')->orderBy('id','asc')->get();
        //消费积分释放比例
        $sale_rate = Config::consume_free_rate();
        //回本前
        $green_before = Config::green_free_before_rate();
        //回本后
        $green_next = Config::green_free_next_rate();
        $flue_address = AsacNode::query()->where('id',1)->value('wallet_address');
        $pre_address = AsacNode::query()->where('id',2)->value('wallet_address');
        $last_price = Asaconfig::get_price();
        //静态释放
        echo $sale_rate.'*'.$green_before.'*'.$green_next.'*'.$pre_address.'*'.$last_price.'*'.$pre_address;
        try {
            DB::beginTransaction();
            foreach ($users as $user){
                //释放消费积分
                $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
                if($user->sale_score > 0){
                    $this->sale_free($user,$sale_rate,$last_price,$flue_address,$pre_address,$user_address);
                }
                //释放绿色积分
                if($user->green_score > 0){
                    $this->green_free($user,$green_before,$green_next,$last_price,$flue_address,$pre_address,$user_address);
                }
            }
            DB::commit();
            return true;
        }catch (\Exception $e){
            DB::rollBack();
            Log::info('错误：'.$e->getMessage().date('Y-m-d H:i:s'));
        }

    }

    protected function sale_free($user,$sale_rate,$last_price,$flue_address,$pre_address,$user_address)
    {
        $sale_num = bcmul($user->sale_score/1000,$sale_rate,2);
        $asac_num = bcdiv($sale_num,$last_price,2);
        $user->coin_num += $asac_num;
        $user->sale_score -= $sale_num;
        $user->save();
        AsacTrade::query()->create([
            'from_address' => $pre_address,
            'to_address'   => $user_address,
            'num'          => $asac_num,
            'trade_hash'   => rand_str_pay(64),
            'type'         => AsacTrade::FREE_USED
        ]);
    }

   protected function green_free($user,$green_before,$green_next,$last_price,$flue_address,$pre_address,$user_address)
   {
       //消费总额
       $all_moeny = Order::query()->where('user_id',$user->id)->where('give_green_score','>',0)->sum('price');
       //检查幸运值
       if($user->luck_score < 0 || $user->green_score < 0){
           Log::info($user->phone.'积分不足：'.date('Y-m-d H:i:s'));
           return true;
       }
       //检查回本
       if($user->green_score_total - $user->green_score > $all_moeny){
           //回本了
           $num = bcmul($user->green_score/1000, $green_next, 2);
           $num = min($user->luck_score, $num);
           $asac_num = bcdiv($num * 0.8, $last_price, 2);
           $user->coin_num += $asac_num;
           $user->green_score -= $num;
           $ticket_num = bcmul($num , 0.2, 2);
           $user->luck_score -= $num;
           $user->ticket_num += $ticket_num;
           $user->save();
       }else {
           //未回本

           $num = bcmul($user->green_score/1000, $green_before, 2);

           $num = min($user->luck_score, $num);
           echo $num.'&&';
           $asac_num = bcdiv($num * 0.8, $last_price, 2);
           echo $asac_num.'&&';
           echo  bcmul($num , 0.2, 2).'^^';
           $user->coin_num = bcadd($asac_num,$user->coin_num,2);
           $user->green_score = bcsub($user->green_score,$num,2);
           $ticket_num = bcmul($num , 0.2, 2);
           $user->luck_score = bcsub($user->luck_score,$num,2);
           $user->ticket_num = bcadd($ticket_num,$user->ticket_num,2);
           $user->save();
           echo $user->coin_num;
       }
        //写释放日志 绿色积分 幸运值 消费卷
        Score::query()->create([
            'user_id'=>$user->id,
            'flag'   => 2,
            'num'    => $num,
            'type'   => 1,
            'f_type' => Score::LUCKY_FREE_USED,
            'amount' => $asac_num,
        ]);
        Score::query()->create([
           'user_id'=>$user->id,
           'flag'   => 2,
           'num'    => $num,
           'type'   => 3,
            'f_type' => Score::LUCKY_FREE_USED,
           'amount' => 0,
        ]);
        Score::query()->create([
           'user_id'=>$user->id,
           'flag'   => 1,
           'num'    => $ticket_num,
           'type'   => 4,
            'f_type' => Score::FREE_HAVE,
           'amount' => 0,
        ]);
        //於挖池释放给用户
        AsacTrade::query()->create([
           'from_address' => $pre_address,
           'to_address'   => $user_address,
           'num'          => $asac_num,
           'trade_hash'   => rand_str_pay(64),
           'type'         => AsacTrade::FREE_USED
        ]);
        //直推人加速
       $this->get_dict_free($user,$num,$pre_address,$last_price);
       //前面两人加速
       $this->get_up_two($user,$num,$pre_address,$last_price);
       //团队释放
       $this->free_team($user,$num,$pre_address,$last_price);
   }

   //给直推人加速释放
   protected function get_dict_free($user,$num,$pre_address,$last_price){
        $dict_users = User::query()->where('master_id',$user->id)->select('id','green_score','luck_score','ticket_num')->get();
        $free_num = bcdiv($num * 0.1,count($dict_users),2);
        foreach ($dict_users as $user)
        {
            if($user->luck_score <= 0 || $user->green_score <=0){
                break;
            }else{
                $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
                //按小释放
                $num = min($user->green_score,$user->luck_score,$free_num);
                $asac_num = bcdiv($num * 0.8, $last_price, 2);
                $user->coin_num += $asac_num;
                $user->green_score -= $num;
                $ticket_num = bcmul($num , 0.2, 2);
                $user->luck_score -= $num;
                $user->ticket_num += $ticket_num;
                $user->save();

                //写释放日志 绿色积分 幸运值 消费卷
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag'   => 2,
                    'num'    => $num,
                    'type'   => 1,
                    'f_type' => Score::LUCKY_FREE_USED,
                    'amount' => $asac_num,
                ]);
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag'   => 2,
                    'num'    => $num,
                    'type'   => 3,
                    'f_type' => Score::LUCKY_FREE_USED,
                    'amount' => 0,
                ]);
                Score::query()->create([
                    'user_id'=>$user->id,
                    'flag'   => 1,
                    'num'    => $ticket_num,
                    'type'   => 4,
                    'f_type' => Score::FREE_HAVE,
                    'amount' => 0,
                ]);
                //於挖池释放给用户
                AsacTrade::query()->create([
                    'from_address' => $pre_address,
                    'to_address'   => $user_address,
                    'num'          => $asac_num,
                    'trade_hash'   => rand_str_pay(64),
                    'type'         => AsacTrade::FREE_USED
                ]);
            }
        }
   }

   //全网注册排序 给上两给人5% 的静态释放
   protected function get_up_two($user,$num,$pre_address,$last_price)
   {
       //获取上两人
       $up_users = User::query()->where('id','<',$user->id)->select('id','green_score','luck_score','ticket_num')->orderBy('id','desc')->limit(2)->get();
       $free_num = bcdiv($num , 0.05,2);
       if(isEmpty($up_users)){
           Log::info('前面没人:'.date("y-m-d H:i:s"));
           return true;
       }else{
            foreach ($up_users as $user){
                if($user->luck_score <= 0 || $user->green_score <=0){
                    break;
                }else{
                    $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
                    $num = min($user->green_score,$user->luck_score,$free_num);
                    $asac_num = bcdiv($num * 0.8, $last_price, 2);
                    $user->coin_num += $asac_num;
                    $user->green_score -= $num;
                    $ticket_num = bcmul($num , 0.2, 2);
                    $user->luck_score -= $num;
                    $user->ticket_num += $ticket_num;
                    $user->save();

                    //写释放日志 绿色积分 幸运值 消费卷
                    Score::query()->create([
                        'user_id'=>$user->id,
                        'flag'   => 2,
                        'num'    => $num,
                        'type'   => 1,
                        'f_type' => Score::LUCKY_FREE_USED,
                        'amount' => $asac_num,
                    ]);
                    Score::query()->create([
                        'user_id'=>$user->id,
                        'flag'   => 2,
                        'num'    => $num,
                        'type'   => 3,
                        'f_type' => Score::LUCKY_FREE_USED,
                        'amount' => 0,
                    ]);
                    Score::query()->create([
                        'user_id'=>$user->id,
                        'flag'   => 1,
                        'num'    => $ticket_num,
                        'type'   => 4,
                        'f_type' => Score::FREE_HAVE,
                        'amount' => 0,
                    ]);
                    //於挖池释放给用户
                    AsacTrade::query()->create([
                        'from_address' => $pre_address,
                        'to_address'   => $user_address,
                        'num'          => $asac_num,
                        'trade_hash'   => rand_str_pay(64),
                        'type'         => AsacTrade::FREE_USED
                    ]);
                }
            }
       }
   }

   protected function free_team($user,$num,$pre_address,$last_price)
   {
       $masters =  explode(',',substr($user->master_pos,1,strlen($user->master_pos) - 2));
       $up_level_users = User::query()->whereIn('id',$masters)
           ->select('id','green_score','luck_score','ticket_num','contribution','green_score_total','sale_score_total')
           ->get();
       if(isEmpty($up_level_users)){
           Log::info('团队加速放完毕:'.date('Y-m-d H:i:s'));
           return true;
       }
       foreach ($up_level_users as $user){
           if($user->luck_score <= 0 || $user->green_score <= 0){
               continue;
           }else{
               if($user->contribution > 60000000){
                   $users   = User::query()->where('master_id',$user->id)->select('green_score','sale_score','contribution');
                   $temp = 0;
                   foreach ($users as $down){
                       $self_contribution = bcadd(bcdiv($down->green_score/3,2),bcdiv($down->sale_score,6,2));
                       $dict_contribution = bcadd($self_contribution,$down->contribution);
                       if($dict_contribution > 5000000){
                           $temp += 1;
                       }else{
                           continue;
                       }
                   }
                   if($temp >= 2){
                       $grade = grade($user->contribution,$temp);
                   }
               }else{
                   $grade = grade($user->contribution);
               }
               $num = $this->get_grade_num($grade,$num);
               if($num == 0){
                   continue;
               }else{
                   $num = min($user->green_score,$user->luck_score,$num);
                   $user_address = AsacNode::query()->where('user_id',$user->id)->value('wallet_address');
                   $asac_num = bcdiv($num * 0.8, $last_price, 2);
                   $user->coin_num += $asac_num;
                   $user->green_score -= $num;
                   $ticket_num = bcmul($num , 0.2, 2);
                   $user->luck_score -= $num;
                   $user->ticket_num += $ticket_num;
                   $user->save();

                   //写释放日志 绿色积分 幸运值 消费卷
                   Score::query()->create([
                       'user_id'=>$user->id,
                       'flag'   => 2,
                       'num'    => $num,
                       'type'   => 1,
                       'f_type' => Score::LUCKY_FREE_USED,
                       'amount' => $asac_num,
                   ]);
                   Score::query()->create([
                       'user_id'=>$user->id,
                       'flag'   => 2,
                       'num'    => $num,
                       'type'   => 3,
                       'f_type' => Score::LUCKY_FREE_USED,
                       'amount' => 0,
                   ]);
                   Score::query()->create([
                       'user_id'=>$user->id,
                       'flag'   => 1,
                       'num'    => $ticket_num,
                       'type'   => 4,
                       'f_type' => Score::FREE_HAVE,
                       'amount' => 0,
                   ]);
                   //於挖池释放给用户
                   AsacTrade::query()->create([
                       'from_address' => $pre_address,
                       'to_address'   => $user_address,
                       'num'          => $asac_num,
                       'trade_hash'   => rand_str_pay(64),
                       'type'         => AsacTrade::FREE_USED
                   ]);
               }
           }
       }
   }

   protected function get_grade_num($grade = 0,$num)
   {
       switch ($grade){
           case 1:
               return $num * 0.10;
           case 2:
               return $num * 0.15;
           case 3:
               return $num * 0.20;
           case 4:
               return $num * 0.25;
           case 5:
               return $num * 0.35;
           default:
               return 0;
       }
   }
}
