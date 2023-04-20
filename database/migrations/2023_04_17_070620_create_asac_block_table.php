<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsacBlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asac_block', function (Blueprint $table) {
            $table->id();
            $table->decimal('number',16)->default(0)->comment('交易总额');
            $table->integer('trade_num')->default(0)->comment('交易笔数');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
        });
        \DB::statement("ALTER TABLE `asac_block` comment 'ASAC区块'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asac_block');
    }
}
