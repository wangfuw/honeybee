<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->id();
            $table->integer('green_one_allowance')->default(16)->comment('绿色专区1倍让利 %');
            $table->integer('green_twice_allowance')->default(60)->comment('绿色专区2被倍让利%');
            $table->integer('green_threefold_allowance')->default(90)->comment('绿色专区3倍让利%');
            $table->integer('green_free_before_rate')->default(3)->comment('绿色积分释放静态速度回本前1/1000,同比扣幸运值');
            $table->integer('green_free_next_rate')->default(10)->comment('绿色积分释放静态速度回本后1/1000,同比扣幸运值');
            $table->integer('consume_one_allowance')->default(16)->comment('消费优选专区1倍让利 %');
            $table->integer('consume_twice_allowance')->default(60)->comment('消费优选2被倍让利%');
            $table->integer('consume_threefold_allowance')->default(90)->comment('消费优选3倍让利%');
            $table->integer('consume_free_rate')->default(1)->comment('消费优选释放速度让利1/1000');
            $table->decimal('lucky_base',4,2)->default(4)->comment('幸运专区初级奖励');
            $table->decimal('lucky_middle',4,2)->default(4.5)->comment('幸运专区中级奖励');
            $table->decimal('lucky_last',4,2)->default(6 )->comment('幸运专区初级奖励');
            $table->integer('lucky_base_reward_coin')->default(15 )->comment('幸运专区推荐人初级奖励，带烧伤');
            $table->integer('lucky_middle_reward_coin')->default(20 )->comment('幸运专区推荐人中级奖励，带烧伤');
            $table->integer('lucky_last_reward_coin')->default(25 )->comment('幸运专区推荐人高级奖励，带烧伤');
            $table->decimal('ticket_ratio_rmb',3,2)->default(1)->comment('消费卷比人名币');
            $table->integer('register_give_lucky')->default(100)->comment('注册赠送幸运值');
            $table->decimal('burn_give_green',3,2)->default(3)->comment('商家燃烧一个asac获得绿色节分');
            $table->decimal('free_green_ratio_lucky',3,2)->default(1)->comment('释放绿色积分消耗幸运值比');
            $table->integer('leader_average_rate')->default(10)->comment('领导释放比例本?均分给直推人');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `config` comment '平台配置'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config');
    }
}
