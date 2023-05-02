<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('money_trade', function (Blueprint $table) {
            $table->id();
            $table->integer('from_id')->comment('转出人id');
            $table->integer('to_id')->comment('收入人id');
            $table->decimal('num',10,2)->comment('转出数量');
            $table->tinyInteger('status')->default(1)->comment('1转账成功，不需要审核');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('取消时间');
        });
        \DB::statement("ALTER TABLE `money_trade` comment '余额转账记录'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('money_trade');
    }
}
