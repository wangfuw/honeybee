<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->comment('订单号');
            $table->integer('user_id')->comment('客户id');
            $table->integer('sku_id')->comment('商品id');
            $table->integer('sku_num')->comment('数量');
            $table->integer('store_id')->comment('商店id');
            $table->tinyInteger('status')->default(1)->comment('1--待支付 2 -- 已支付 3--撤单');
            $table->tinyInteger('express_status')->default(0)->comment('0--待发货 1--已发货 2--签收');
            $table->string('express_no')->default(null)->comment('运单单号');
            $table->string('express_name')->default(null)->comment('快递公司');
            $table->integer('address_id')->default(0)->comment('收获地址id');
            $table->integer('created_at')->comment('下单时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->comment('撤单时间，15分钟未支付.env 设置');
        });
        \DB::statement("ALTER TABLE `orders` comment '订单'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
