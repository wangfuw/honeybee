<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\AsacNode;
use App\Models\Asaconfig;
use App\Models\AsacTrade;
use App\Models\Config;
use App\Models\Order;
use App\Models\Score;
use App\Models\User;
use App\Models\UserIdentity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function PHPUnit\Framework\isEmpty;

class freeScoreNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'free_score_new';

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

    const DE = 4;
    const MIN = 0.0001;
    const GREEN_FREE_RATE = 0.9;
    const SALE_FREE_RATE = 0.1;

    const DICT_RATE = 0.1;

    const J_RATE = 0.05;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('积分释放，开始时间：' . date('Y-m-d H:i:s'));

        $users = User::query()->select('id', 'green_score', 'sale_score', 'luck_score', 'phone',
            'coin_num', 'master_id', 'ticket_num', 'green_score_total', 'sale_score_total', 'contribution', 'master_pos')->orderBy('id', 'asc')->get();
        $last_price = Asaconfig::get_price();

        printf("这是新的命令:%s\n", count($users));

        // 1. 释放所有人的消费积分和绿色积分，并记录绿色积分释放数量
        $green_free_nums = $this->sale_and_green($users, $last_price);
        $green_free_num = $green_free_nums['green_free_num'];
        $sale_free_num = $green_free_nums['sale_free_num'];

        if (count($green_free_num) > 0) {
            foreach ($green_free_num as $k => $v) {
                Log::info($k . ':的绿色分享直推加速态释放开始：' . $k);
                DB::beginTransaction();
                try {
                    $this->share_free($k, $v, $last_price, 1);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }

            foreach ($sale_free_num as $k => $v) {
                Log::info($k . ':的消费积分分享直推加速态释放开始：' . $k);
                DB::beginTransaction();
                try {
                    $this->share_free($k, $v, $last_price, 0);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }

            foreach ($green_free_num as $k => $v) {
                // 2. 直推加速
                DB::beginTransaction();
                try {
                    $this->get_dict_free($k, $v, $last_price);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }

            }
            foreach ($green_free_num as $k => $v) {
                DB::beginTransaction();
                try {
                    $this->get_up_two($k, $v, $last_price);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }

            foreach ($green_free_num as $k => $v) {
                DB::beginTransaction();
                try {
                    $this->free_team($k, $v, $last_price);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
            }

            foreach ($green_free_num as $k=>$v){
                DB::beginTransaction();
                try{
                    $this->xingxiang($k,$v,$last_price);
                    DB::commit();
                }catch (\Exception $e){
                    Log::error("形象店加速失败：".$e->getMessage());
                    DB::rollBack();
                }
            }

            foreach ($green_free_num as $k=>$v){
                DB::beginTransaction();
                try{
                    $this->qijian($k,$v,$last_price);
                    DB::commit();
                }catch (\Exception $e){
                    Log::error("形象店加速失败：".$e->getMessage());
                    DB::rollBack();
                }
            }
        }
    }

    protected function sale_and_green($users, $last_price)
    {
        //消费积分释放比例
        $sale_rate = Config::consume_free_rate();

        //回本前
        $green_before = Config::green_free_before_rate();
        //回本后
        $green_next = Config::green_free_next_rate();

        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();
        $sale_free_num = [];
        $green_free_num = [];
        foreach ($users as $user) {
            // 1.释放消费积分
            try {
                $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');

                $sale_num = bcmul($user->sale_score / 1000, $sale_rate, self::DE);
                $asac_num = bcdiv($sale_num, $last_price, self::DE);
                if ($asac_num >= self::MIN) {
                    $user->coin_num += $asac_num;
                    $user->sale_score -= $sale_num;
                    AsacTrade::query()->create([
                        'from_address' => $pre_address->wallet_address,
                        'to_address' => $user_address,
                        'num' => $asac_num,
                        'trade_hash' => rand_str_pay(64),
                        'type' => AsacTrade::FREE_HAVED
                    ]);
                    Score::query()->create([
                        'user_id' => $user->id,
                        'flag' => 2,
                        'num' => $sale_num,
                        'type' => 2,
                        'f_type' => Score::FREE_USED,
                        'amount' => $asac_num,
                    ]);
                    $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                    $sale_free_num[$user->id] = $sale_num;
                }


                // 2. 释放绿色积分
                //消费总额
                $all_moeny = Order::query()->where('user_id', $user->id)->where('give_green_score', '>', 0)->sum('price');
                //检查幸运值
                if ($user->luck_score >= 0 && $user->green_score >= 0) {
                    //检查回本
                    $rate = $user->green_score_total - $user->green_score > $all_moeny ? $green_next : $green_before;
                    $num = bcmul($user->green_score / 1000, $rate, self::DE);


                    $num = min($user->luck_score, $num);

                    $asac_num = bcdiv($num * self::GREEN_FREE_RATE, $last_price, self::DE);
                    if ($asac_num >= self::MIN) {
                        $user->coin_num = bcadd($asac_num, $user->coin_num, self::DE);
                        $user->green_score = bcsub($user->green_score, $num, self::DE);
                        $ticket_num = bcmul($num, self::SALE_FREE_RATE, self::DE);
                        $user->luck_score = bcsub($user->luck_score, $num, self::DE);
                        $user->ticket_num = bcadd($ticket_num, $user->ticket_num, self::DE);
                        //写释放日志 绿色积分 幸运值 消费卷
                        Score::query()->create([
                            'user_id' => $user->id,
                            'flag' => 2,
                            'num' => $num,
                            'type' => 1,
                            'f_type' => Score::FREE_USED,
                            'amount' => $asac_num,
                        ]);
                        Score::query()->create([
                            'user_id' => $user->id,
                            'flag' => 2,
                            'num' => $num,
                            'type' => 3,
                            'f_type' => Score::FREE_USED,
                            'amount' => 0,
                        ]);
                        Score::query()->create([
                            'user_id' => $user->id,
                            'flag' => 1,
                            'num' => $ticket_num,
                            'type' => 4,
                            'f_type' => Score::FREE_HAVE,
                            'amount' => 0,
                        ]);
                        //於挖池释放给用户
                        AsacTrade::query()->create([
                            'from_address' => $pre_address->wallet_address,
                            'to_address' => $user_address,
                            'num' => $asac_num,
                            'trade_hash' => rand_str_pay(64),
                            'type' => AsacTrade::FREE_USED
                        ]);
                    }
                    $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                    $green_free_num[$user->id] = $num;
                }
                $user->save();
                $pre_address->save();
                DB::commit();
                Log::info('静态释放成功:' . $user->id);
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::info('静态释放失败' . $user->id);
            }
        }
        return compact('green_free_num', 'sale_free_num');
    }

    protected function share_free($current_user_id, $num, $last_price = 10, $teb = 0)
    {
        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();

        $user = User::query()->where('id', $current_user_id)->first();

        $re_dict_user = User::query()->where('id', $user->master_id)->where('is_ban', 1)->first(); //我的直推
        if (!$re_dict_user) {
            return true;
        }

        if ($re_dict_user->luck_score > 0 && $re_dict_user->green_score > 0) {
            $re_dict_user_address = AsacNode::query()->where('user_id', $re_dict_user->id)->value('wallet_address') ?? '';
            $free_num = bcmul($num, self::DICT_RATE, 4);
            $num1 = min($re_dict_user->green_score, $re_dict_user->luck_score, $free_num);
            if ($num1 >= self::MIN) {
                $re_dict_user->green_score -= $num1;
                $re_dict_user->luck_score -= $num1;
                $asac_num = bcdiv($num1 * self::GREEN_FREE_RATE, $last_price, self::DE);
                $ticket_num = bcmul($num1, self::SALE_FREE_RATE, self::DE);
                $re_dict_user->coin_num += $asac_num;
                $re_dict_user->ticket_num += $ticket_num;
                $re_dict_user->save();
                $f_type = $teb ? Score::DICT_FREE : Score::SALE_DICT_FREE;
                //写释放日志 绿色积分 幸运值 消费卷
                Score::query()->create([
                    'user_id' => $re_dict_user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 1,
                    'f_type' => $f_type,
                    'amount' => $asac_num,
                ]);
                Score::query()->create([
                    'user_id' => $re_dict_user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 3,
                    'f_type' => $f_type,
                    'amount' => 0,
                ]);
                Score::query()->create([
                    'user_id' => $re_dict_user->id,
                    'flag' => 1,
                    'num' => $ticket_num,
                    'type' => 4,
                    'f_type' => Score::FREE_HAVE,
                    'amount' => 0,
                ]);
                //於挖池释放给用户
                AsacTrade::query()->create([
                    'from_address' => $pre_address->wallet_address,
                    'to_address' => $re_dict_user_address,
                    'num' => $asac_num,
                    'trade_hash' => rand_str_pay(64),
                    'type' => AsacTrade::FREE_USED
                ]);
                $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                $pre_address->save();
            }
        }

        //我的减退
        $rej_dict_user = User::query()->where('id', $re_dict_user->master_id)->where('is_ban', 1)->first();

        if (!$rej_dict_user) {
            return true;
        }
        if ($rej_dict_user->luck_score <= 0 || $rej_dict_user->green_score <= 0) {
            return true;
        }

        $rej_dict_user_address = AsacNode::query()->where('user_id', $rej_dict_user->id)->value('wallet_address') ?? '';

        $free_num = bcmul($num, self::J_RATE, 4);

        $num2 = min($rej_dict_user->green_score, $rej_dict_user->luck_score, $free_num);

        if ($num2 < self::MIN) {
            return true;
        }
        $rej_dict_user->green_score -= $num2;
        $asac_num = bcdiv($num2 * self::GREEN_FREE_RATE, $last_price, self::DE);
        $ticket_num = bcmul($num2, self::SALE_FREE_RATE, self::DE);
        $rej_dict_user->luck_score -= $num2;
        $rej_dict_user->coin_num += $asac_num;
        $rej_dict_user->ticket_num += $ticket_num;
        $rej_dict_user->save();
        $f_j_type = $teb ? Score::J_DICT_FREE : Score::SALE_J_DICT_FREE;

        //写释放日志 绿色积分 幸运值 消费卷
        Score::query()->create([
            'user_id' => $rej_dict_user->id,
            'flag' => 2,
            'num' => $num2,
            'type' => 1,
            'f_type' => $f_j_type,
            'amount' => $asac_num,
        ]);
        Score::query()->create([
            'user_id' => $rej_dict_user->id,
            'flag' => 2,
            'num' => $num2,
            'type' => 3,
            'f_type' => $f_j_type,
            'amount' => 0,
        ]);
        Score::query()->create([
            'user_id' => $rej_dict_user->id,
            'flag' => 1,
            'num' => $ticket_num,
            'type' => 4,
            'f_type' => Score::FREE_HAVE,
            'amount' => 0,
        ]);
        //於挖池释放给用户
        AsacTrade::query()->create([
            'from_address' => $pre_address->wallet_address,
            'to_address' => $rej_dict_user_address,
            'num' => $asac_num,
            'trade_hash' => rand_str_pay(64),
            'type' => AsacTrade::FREE_USED
        ]);
        $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
        $pre_address->save();
    }


    //给直推人加速释放
    protected function get_dict_free($current_user_id, $num, $last_price)
    {
        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();
        $dict_users = User::query()->where('master_id', $current_user_id)
            ->select('id', 'green_score', 'luck_score', 'ticket_num', 'phone', 'coin_num')
            ->get();
        if (count($dict_users) == 0) return true;
        // dd($dict_users->toArray());
        $free_num = bcdiv($num * 0.1, count($dict_users), self::DE);
        foreach ($dict_users as $user) {
            if ($user->luck_score <= 0 || $user->green_score <= 0) {
                continue;
            } else {
                $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');
                //按小释放
                $num1 = min($user->green_score, $user->luck_score, $free_num);
                $asac_num = bcdiv($num1 * self::GREEN_FREE_RATE, $last_price, self::DE);
                if ($num1 < self::MIN) {
                    continue;
                }

                $user->coin_num += $asac_num;
                $user->green_score -= $num1;
                $ticket_num = bcmul($num1, self::SALE_FREE_RATE, self::DE);
                $user->luck_score -= $num1;
                $user->ticket_num += $ticket_num;
                $user->save();

                //写释放日志 绿色积分 幸运值 消费卷
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 1,
                    'f_type' => Score::DICT_FREE_USED,
                    'amount' => $asac_num,
                ]);
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 3,
                    'f_type' => Score::DICT_FREE_USED,
                    'amount' => 0,
                ]);
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 1,
                    'num' => $ticket_num,
                    'type' => 4,
                    'f_type' => Score::FREE_HAVE,
                    'amount' => 0,
                ]);
                //於挖池释放给用户
                AsacTrade::query()->create([
                    'from_address' => $pre_address->wallet_address,
                    'to_address' => $user_address,
                    'num' => $asac_num,
                    'trade_hash' => rand_str_pay(64),
                    'type' => AsacTrade::FREE_USED
                ]);
                $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                $pre_address->save();

            }
        }
    }

    //全网注册排序 给上两给人5% 的静态释放
    protected function get_up_two($current_user_id, $num, $last_price)
    {
        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();
        //获取上两人
        $up_users = User::query()
            ->where('id', '<', $current_user_id)
            ->select('id', 'green_score', 'luck_score', 'ticket_num', 'phone', 'coin_num')
            ->where('green_score', '>', 0)
            ->orderBy('id', 'desc')->limit(2)
            ->get();

        $free_num = bcmul($num, 0.05, self::DE);
        if (!$up_users) {
            Log::info('前面没人:' . date("y-m-d H:i:s"));
            return true;
        }
        foreach ($up_users as $user) {
            if ($user->luck_score <= 0 || $user->green_score <= 0) {
                continue;
            } else {
                $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');
                $num1 = min($user->green_score, $user->luck_score, $free_num);
                $asac_num = bcdiv($num1 * self::GREEN_FREE_RATE, $last_price, self::DE);
                if ($num1 < self::MIN) {
                    continue;
                }

                $user->coin_num = bcadd($user->coin_num, $asac_num, self::DE);
                $user->green_score = bcsub($user->green_score, $num1, self::DE);
                $ticket_num = bcmul($num1, self::SALE_FREE_RATE, self::DE);
                $user->luck_score = bcsub($user->luck_score, $num1, self::DE);
                $user->ticket_num = bcadd($ticket_num, $user->ticket_num, self::DE);

                //写释放日志 绿色积分 幸运值 消费卷
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 1,
                    'f_type' => Score::SORT_FREE_USED,
                    'amount' => $asac_num,
                ]);
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 2,
                    'num' => $num1,
                    'type' => 3,
                    'f_type' => Score::SORT_FREE_USED,
                    'amount' => 0,
                ]);
                Score::query()->create([
                    'user_id' => $user->id,
                    'flag' => 1,
                    'num' => $ticket_num,
                    'type' => 4,
                    'f_type' => Score::FREE_HAVE,
                    'amount' => 0,
                ]);
                //於挖池释放给用户
                AsacTrade::query()->create([
                    'from_address' => $pre_address->wallet_address,
                    'to_address' => $user_address,
                    'num' => $asac_num,
                    'trade_hash' => rand_str_pay(64),
                    'type' => AsacTrade::FREE_USED
                ]);
                $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                $user->save();
                $pre_address->save();
            }
        }
    }

    protected function free_team($current_user_id, $num, $last_price)
    {

        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();

        $current_user = User::find($current_user_id);
        $masters = explode(',', substr($current_user->master_pos, 1, strlen($current_user->master_pos) - 2));
        $up_level_users = User::query()
            ->whereIn('id', $masters)
            ->select('id', 'green_score', 'luck_score', 'ticket_num', 'contribution', 'green_score_total', 'sale_score_total', 'phone', 'coin_num')
            ->get();
        if (!$up_level_users) {
            Log::info($current_user . ':没有团队加速:');
            return true;
        }

//    case 1:
//                return $num * 0.10;
//            case 2:
//                return $num * 0.15;
//            case 3:
//                return $num * 0.20;
//            case 4:
//                return $num * 0.25;
//            case 5:
//                return $num * 0.35;
//            default:
//                return 0;
        $rates = [
            0 => 0,
            1 => 0.10,
            2 => 0.15,
            3 => 0.2,
            4 => 0.25,
            5 => 0.35
        ];

        $user_rate = 0;
        $last_grade = 0;
        $last_rate = 0;
        $par_ = [
            1 => false,
            2 => false,
            3 => false,
            4 => false,
            5 => false
        ];
        foreach ($up_level_users as $user) {
            if ($user->luck_score <= 0 || $user->green_score <= 0) {
                continue;
            } else {
                if ($user->contribution >= 60000000) {
                    $users = User::query()->where('master_id', $user->id)->select('green_score', 'sale_score', 'contribution')->get();
                    if (!$users) {
                        continue;
                    } else {
                        $temp = 0;
                        foreach ($users as $down) {
                            $self_contribution = bcadd(bcdiv($down->green_score, 3, self::DE), bcdiv($down->sale_score, 6, self::DE));
                            $dict_contribution = bcadd($self_contribution, $down->contribution);
                            if ($dict_contribution > 5000000) {
                                $temp += 1;
                            } else {
                                continue;
                            }
                        }
                        if ($temp >= 2) {
                            $grade = $this->grade($user->contribution, $temp);
                        } else {
                            $grade = $this->grade($user->contribution);
                        }
                    }

                } else {
                    $grade = $this->grade($user->contribution);
                }

                $f_type = Score::TEAM_FREE_USED;
                if ($grade > $last_grade) {
                    $rate = max($rates[$grade] - $user_rate,0);
                    if($rate <= 0){
                        break;
                    }
                } elseif ($grade < $last_grade) {
                    $par_[$last_grade] = true;
                    $rate = 0;
                } else {
                    if (!$par_[$grade]) {
                        $rate = $last_rate * 0.1;
                        $par_[$grade] = true;
                        $f_type = Score::LEVEL_FREE_USED;
                    }else{
                        $rate = 0;
                    }
                }

                $free_num = bcmul($num, $rate, self::DE);
                $last_rate = $rate;
                $user_rate += $rate;
                $last_grade = $grade;

                if ($free_num <= 0) {
                    continue;
                } else {
                    $free_num = min($user->green_score, $user->luck_score, $free_num);
                    $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');
                    $asac_num = bcdiv($free_num * self::GREEN_FREE_RATE, $last_price, self::DE);
                    if ($free_num < self::MIN) {
                        continue;
                    }
                    $user->coin_num = bcadd($asac_num, $user->coin_num, self::DE);
                    $user->green_score -= $free_num;
                    $ticket_num = bcmul($free_num, self::SALE_FREE_RATE, self::DE);
                    $user->luck_score -= $free_num;
                    $user->ticket_num += $ticket_num;


                    //写释放日志 绿色积分 幸运值 消费卷
                    Score::query()->create([
                        'user_id' => $user->id,
                        'flag' => 2,
                        'num' => $free_num,
                        'type' => 1,
                        'f_type' => $f_type,
                        'amount' => $asac_num,
                    ]);
                    Score::query()->create([
                        'user_id' => $user->id,
                        'flag' => 2,
                        'num' => $free_num,
                        'type' => 3,
                        'f_type' => $f_type,
                        'amount' => 0,
                    ]);
                    Score::query()->create([
                        'user_id' => $user->id,
                        'flag' => 1,
                        'num' => $ticket_num,
                        'type' => 4,
                        'f_type' => Score::FREE_HAVE,
                        'amount' => 0,
                    ]);
                    //於挖池释放给用户
                    AsacTrade::query()->create([
                        'from_address' => $pre_address->wallet_address,
                        'to_address' => $user_address,
                        'num' => $asac_num,
                        'trade_hash' => rand_str_pay(64),
                        'type' => AsacTrade::FREE_USED
                    ]);

                    $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
                    $user->save();
                    $pre_address->save();
                    DB::commit();
                    Log::info($current_user_id . ':的个团队加速态释放完成功：' . $user->id);
                }
            }
        }
    }


    // 形象店加速
    protected function xingxiang($current_user_id,$num,$last_price){
        $user_auth = UserIdentity::find($current_user_id);
        if(!$user_auth){
            return;
        }
        $xx = User::where("identity_area_code",$user_auth->address_code)
            ->where("identity_status",1)
            ->where("identity",1)
            ->where("green_score",">",0)
            ->where("luck_score",">",0)
            ->where("is_ban",1)
            ->all();

        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();
        $free_num = bcmul($num, 0.08, self::DE);
        foreach ($xx as $user){
            $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');
            $num1 = min($user->green_score, $user->luck_score, $free_num);
            $asac_num = bcdiv($num1 * self::GREEN_FREE_RATE, $last_price, self::DE);
            if ($num1 < self::MIN) {
                continue;
            }
            $user->coin_num = bcadd($user->coin_num, $asac_num, self::DE);
            $user->green_score = bcsub($user->green_score, $num1, self::DE);
            $ticket_num = bcmul($num1, self::SALE_FREE_RATE, self::DE);
            $user->luck_score = bcsub($user->luck_score, $num1, self::DE);
            $user->ticket_num = bcadd($ticket_num, $user->ticket_num, self::DE);

            //写释放日志 绿色积分 幸运值 消费卷
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 2,
                'num' => $num1,
                'type' => 1,
                'f_type' => Score::XX_USED,
                'amount' => $asac_num,
            ]);
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 2,
                'num' => $num1,
                'type' => 3,
                'f_type' => Score::XX_USED,
                'amount' => 0,
            ]);
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 1,
                'num' => $ticket_num,
                'type' => 4,
                'f_type' => Score::FREE_HAVE,
                'amount' => 0,
            ]);
            //於挖池释放给用户
            AsacTrade::query()->create([
                'from_address' => $pre_address->wallet_address,
                'to_address' => $user_address,
                'num' => $asac_num,
                'trade_hash' => rand_str_pay(64),
                'type' => AsacTrade::FREE_USED
            ]);
            $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
            $user->save();
            $pre_address->save();
        }
    }


    // 旗舰店加速
    protected function qijian($current_user_id,$num,$last_price){
        $user_auth = UserIdentity::find($current_user_id);
        if(!$user_auth){
            return;
        }
        $area = Area::where("code",$user_auth->address_code)->first();

        $qj = User::where("identity_area_code",$area->pcode)
            ->where("identity_status",1)
            ->where("identity",2)
            ->where("green_score",">",0)
            ->where("luck_score",">",0)
            ->where("is_ban",1)
            ->all();

        $pre_address = AsacNode::query()->where('id', 2)->select('id', 'wallet_address', 'number')->first();
        $free_num = bcmul($num, 0.05, self::DE);
        foreach ($qj as $user){
            $user_address = AsacNode::query()->where('user_id', $user->id)->value('wallet_address');
            $num1 = min($user->green_score, $user->luck_score, $free_num);
            $asac_num = bcdiv($num1 * self::GREEN_FREE_RATE, $last_price, self::DE);
            if ($num1 < self::MIN) {
                continue;
            }
            $user->coin_num = bcadd($user->coin_num, $asac_num, self::DE);
            $user->green_score = bcsub($user->green_score, $num1, self::DE);
            $ticket_num = bcmul($num1, self::SALE_FREE_RATE, self::DE);
            $user->luck_score = bcsub($user->luck_score, $num1, self::DE);
            $user->ticket_num = bcadd($ticket_num, $user->ticket_num, self::DE);

            //写释放日志 绿色积分 幸运值 消费卷
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 2,
                'num' => $num1,
                'type' => 1,
                'f_type' => Score::QJ_USED,
                'amount' => $asac_num,
            ]);
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 2,
                'num' => $num1,
                'type' => 3,
                'f_type' => Score::QJ_USED,
                'amount' => 0,
            ]);
            Score::query()->create([
                'user_id' => $user->id,
                'flag' => 1,
                'num' => $ticket_num,
                'type' => 4,
                'f_type' => Score::FREE_HAVE,
                'amount' => 0,
            ]);
            //於挖池释放给用户
            AsacTrade::query()->create([
                'from_address' => $pre_address->wallet_address,
                'to_address' => $user_address,
                'num' => $asac_num,
                'trade_hash' => rand_str_pay(64),
                'type' => AsacTrade::FREE_USED
            ]);
            $pre_address->number = bcsub($pre_address->number, $asac_num, self::DE);
            $user->save();
            $pre_address->save();
        }
    }

    protected function get_grade_num($grade = 0, $num)
    {
        switch ($grade) {
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

    protected function grade($number, $base_num = 0)
    {
        $grade = 0;
        switch ($number) {
            case $number >= 150000 && $number < 1000000:
                $grade = 1;
                break;
            case $number >= 1000000 && $number < 5000000:
                $grade = 2;
                break;
            case $number >= 5000000 && $number < 20000000:
                $grade = 3;
                break;
            case $number >= 20000000 && $number < 60000000:
                $grade = 4;
                break;
            case $number >= 60000000 && $base_num >= 2:
                $grade = 5;
                break;
            case $number >= 60000000 && $base_num < 2:
                $grade = 4;
                break;
            default:
                $grade = 0;
        }
        return $grade;
    }
}
