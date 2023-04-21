<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBaseOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('base_order', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->json('products')->comment('商品信息');
            $table->string('order_no')->comment('订单号');
            $table->decimal('money',16,2)->default(0)->comment('订单价格');
            $table->tinyInteger('type')->comment('订单状态1-待支付 2-已支付');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('取消时间');
        });
        \DB::statement("ALTER TABLE `base_order` comment '主订单表'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('base_order');
    }
}
