<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketPayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_pay', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('商家uid');
            $table->string('pay_phone')->comment('支付人电话');
            $table->integer('amount')->comment('消费数量');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
        });
        \DB::statement("ALTER TABLE `ticket_pay` comment '线下消费卷支付记录'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_pay');
    }
}
