<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsacTradeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asac_trade', function (Blueprint $table) {
            $table->id();
            $table->string('from_address')->comment('流出地址');
            $table->string('to_address')->comment('转入地址');
            $table->decimal('num',16)->comment('流转数量');
            $table->string('trade_hash')->comment('交易hash');
            $table->tinyInteger('is_block')->default(0)->comment('0-为记录区块 1-已记录区块');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
        });

        \DB::statement("ALTER TABLE `asac_trade` comment 'asac交易'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asac_trade');
    }
}
