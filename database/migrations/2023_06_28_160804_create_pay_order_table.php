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
            $table->decimal('amount',16,2)->nullable()->comment('支付金额');
            $table->tinyInteger('cur')->nullable()->default('1')->comment('1人民币');
            $table->string('fre_code')->nullable()->comment('交易类型');
            $table->string('phone')->nullable()->comment('交易人手机号');
            $table->integer('store_id')->comment('商家id');
            $table->string('trx_no')->nullable()->comment('交易流水号');
            $table->string('f_trx_no')->nullable()->comment('分账到账流水号');
            $table->decimal('free',16,2)->default(0)->comment('订单手续费');
            $table->string('bank_order_no')->nullable()->comment('银行订单号');
            $table->string('bank_trx_no')->nullable()->comment('银行流水号');
            $table->string('pay_time')->nullable()->comment('支付时间');
            $table->string('bank_code')->nullable()->comment('银行编码');
            $table->string('openid')->nullable()->comment('用户openid');
            $table->string('card_type')->nullable()->comment('卡类型');
            $table->string('bank_type')->nullable()->comment('银行类型');
            $table->string('alt_info')->nullable()->comment('分账信息');
            $table->string('pay_status')->default(0)->comment('支付状态 0 待支付 100 支付成功 101 支付失败');
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
