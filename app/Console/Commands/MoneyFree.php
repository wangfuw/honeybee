<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Score;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MoneyFree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'free_money';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'free_money';

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
        Log::info('开始执行，释放余额,当前时间：'.date('Y-m-d H:i:s'));
        $free_rate = Config::freeze_free();
        $list = User::query()->where('freeze_money','>',0)->where('is_ban',1)->pluck('id');
        try {
            DB::beginTransaction();
            foreach ($list as $l){
                $user = User::query()->where('id',$l)->first();
                $used_money = Score::query()->where('user_id',$l)->sum('num');
                if($used_money > $user->freeze_money){
                    continue;
                }
                $temp1 = bcmul($user->freeze_money,$free_rate/1000,2);
                $temp2 = bcsub($user->freeze_money ,$used_money,2);
                $temp = min($temp2,$temp1);
                $user->money = bcadd($user->money,$temp,2);
                $user->save();
                //写入释放记录
                Score::query()->create([
                    'user_id'=>$user->id,
                    'num'=>$temp,
                    'type'=>5,
                    'f_type'=>Score::FREE_HAVE,
                ]);
                unset($temp);
            }
            Log::info('释放余额结束，结束时间:'.date('Y-m-d H:i:s'));
            DB::commit();
            return true;
        }catch (\Exception $e){
            DB::rollBack();
            Log::info('释放失败'.$e->getMessage());
        }

    }
}
