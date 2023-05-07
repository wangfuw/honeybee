<?php

namespace App\Console\Commands;

use App\Models\AsacBlock;
use App\Models\AsacNode;
use App\Models\AsacTrade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class Blockd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Blockd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this is Blockd';

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
        Log::info('开始执行，几点打包,当前时间：'.date('Y-m-d H:i:s'));
        $list = AsacTrade::query()->where('block_id',0)->pluck('id');
        if(!$list){
            Log::info("执行打包结束".date('Y-m-d H:i:s'));
            return false;
        }else{
            try {
                DB::beginTransaction();
                $res = AsacBlock::query()->create([
                    'trade_num'=> count($list),
                    'number'   => 0,
                ]);
                AsacTrade::query()->whereIn('id',$list)->update(['block_id'=>$res->id]);
                DB::commit();
                Log::info('打包结束'.date('Y-m-d H:i:s'));
                return false;
            }catch (\Exception $e){
                DB::rollBack();
                Log::info($e->getMessage());
            }
        }


    }
}
