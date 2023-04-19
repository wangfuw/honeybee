<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->tinyInteger('flag')->default(1)->comment('1正 2 负');
            $table->decimal('num')->default(0)->comment('数量');
            $table->tinyInteger('type')->default(1)->comment('1 绿色积分 2 消费积分 3 幸运值 4 消费卷');
            $table->integer('f_type')->nullable()->comment('消费类型');
            $table->decimal('amount',12)->comment('金额');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `score` comment '积分日志'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score');
    }
}
