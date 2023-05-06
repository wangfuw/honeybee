<?php

namespace App\Console;

use App\Console\Commands\Blockd;
use App\Console\Commands\freeScore;
use App\Models\MoneyTrade;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $comment = [
        freeScore::class,
        Blockd::class,
        MoneyTrade::class,
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('free_score_new')
            ->daily()
            ->withoutOverlapping() //防止重复执行
            ->onOneServer() //在单台服务器上跑
            ->runInBackground() //任务后台运行
            //->appendOutputTo('log_path')//日志输出，默认追加
            ->sendOutputTo('log_path'); //日志输出，默认覆盖先前日志
        //区块打包
        $schedule->command('blockd')->everyFiveMinutes()
            ->withoutOverlapping() //防止重复执行
            ->onOneServer() //在单台服务器上跑
            ->runInBackground() //任务后台运行
            //->appendOutputTo('log_path')//日志输出，默认追加
            ->sendOutputTo('log_path'); //日志输出，默认覆盖先前日志;
        //释放冻结余额
        $schedule->command('free_money')->everyTenMinutes()
            ->daily() //防止重复执行
            ->onOneServer() //在单台服务器上跑
            ->runInBackground() //任务后台运行
            //->appendOutputTo('log_path')//日志输出，默认追加
            ->sendOutputTo('log_path'); //日志输出，默认覆盖先前日志;

    }


    /**
     * Register the commands for the application.
     *
     * @return void
     */

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
