<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsacConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asac_config', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('名称');
            $table->string('contract_address')->comment('合约地址');
            $table->string('destruction_address')->comment('销毁地址');
            $table->tinyInteger('accuracy')->comment('精度');
            $table->integer('number')->comment('发行总量');
            $table->integer('price')->default(10)->comment('发行价格');
            $table->decimal('flow_num',16)->comment('流动池总量');
            $table->decimal('precat_num',16)->comment('预发池总量');
            $table->decimal('flux',16)->comment('流通量');
            $table->decimal('dest_num',16)->default(0)->comment('销毁量');
            $table->integer('owner_num')->default(0)->comment('持有人地址数');
            $table->integer('trans_num')->default(0)->comment('交易笔数');
            $table->decimal('old_price',4)->comment('上一次价格');
            $table->decimal('last_price',4)->comment('现价');
            $table->string('username')->nullable()->comment('编辑账户');
            $table->string('ip')->nullable()->comment('修改ip');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
        });
        \DB::statement("ALTER TABLE `asac_config` comment 'asac配置'");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asac_config');
    }
}
