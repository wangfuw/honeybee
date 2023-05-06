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
                if($user->new_freeze < 0){
                    continue;
                }
                $temp = bcmul($user->freeze_money,$free_rate/1000,4);
                $temp = min($temp,$user->new_freeze);
                $user->money = bcadd($user->money,$temp,4);
                $user->new_freeze = bcsub($user->new_freeze,$temp,4);
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
