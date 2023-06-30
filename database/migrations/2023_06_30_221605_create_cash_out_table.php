<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashOutTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_out', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable(false)->comment('用户id');
            $table->string('bank_name')->nullable(false)->comment('银行');
            $table->string('bank_card')->nullable(false)->comment('银行卡号');
            $table->string('fax_name')->nullable(false)->comment('银行卡账户');
            $table->decimal('amount',10,2)->nullable(false)->comment('提现额度');
            $table->string('payment_image')->nullable()->comment('银行回执单');
            $table->string('note')->nullable()->comment('驳回原因');
            $table->tinyInteger('status')->default(0)->comment('0待审核 1 审核打开 2 驳回');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
        });
        \DB::statement("ALTER TABLE `cash_out` comment '线下提现申请'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_out');
    }
}
