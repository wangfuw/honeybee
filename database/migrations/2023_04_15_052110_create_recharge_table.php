<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRechargeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         * todo no
         */
        Schema::create('recharge', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->decimal('amount',12)->comment('金额');
            $table->string('charge_address')->comment('充币地址');
            $table->string('hash')->nullable()->comment('充币hash');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `recharge` comment '用户提现'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recharge');
    }
}
