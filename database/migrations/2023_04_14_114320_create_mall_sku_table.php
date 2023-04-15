<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallSkuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    /**
     *id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'SKU Id',
    `spu_id` bigint(20) NOT NULL COMMENT 'SPU Id',
    `stock` int(8) unsigned DEFAULT '9999' COMMENT '库存',
    `price` bigint(16) NOT NULL DEFAULT '0' COMMENT '销售价格 (单位为分)',
    `indexes` varchar(32) DEFAULT '' COMMENT '规格参数在SPU规格模板中对应的下标组合(如1_0_0)',
    `enable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效 (0-无效，1-有效)',
     */
    public function up()
    {
        Schema::create('mall_sku', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('spu_id')->nullable(false)->comment('商品id spu_id');
            $table->integer('stock')->default(0)->comment('库存');
            $table->integer('price')->default(0)->comment('商品价格');
            $table->string('indexes')->default(null)->comment('规格参数在SPU规格模板中对应的下标组合(如1_0_0)');
            $table->tinyInteger('enable')->default(1)->comment('是否有效(0-无效 1-有效)');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });

        \DB::statement("ALTER TABLE `mall_sku` comment '商品详情'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_sku');
    }
}
