<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_order', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_no')->nullable()->comment('商户编号');
            $table->string('order_no')->nullable()->comment('商户订单号');
            $table->decimal('amount',8,2)->nullable()->comment('支付金额');
            $table->tinyInteger('cur')->nullable()->default('1')->comment('1人民币');
            $table->string('fre_code')->nullable()->comment('交易类型');
            $table->string('trx_no')->nullable()->comment('交易流水号');
            $table->string('code')->nullable()->comment('响应码');
            $table->string('code_msg')->nullable()->comment('响应码描述');
            $table->string('result')->nullable()->comment('结果');
            $table->string('hmac')->nullable()->comment('签名');
            $table->string('pay_status')->nullable()->comment('支付状态');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_order');
    }
}
