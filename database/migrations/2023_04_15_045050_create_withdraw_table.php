<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->string('withdraw_address')->comment('提币地址');
            $table->decimal('amount',12,)->comment('金额');
            $table->tinyInteger('status')->default(0)->comment('0-待审核 1-审核通过 2-审核撤回');
            $table->decimal('actual')->default(0)->comment('实际到账金额');
            $table->decimal('fee',16)->default(0)->comment('手续费');
            $table->string('out_order_no')->nullable()->comment('出币订单号');
            $table->string('err')->nullable()->comment('备注');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `withdraw` comment '用户提现'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('withdraw');
    }
}
