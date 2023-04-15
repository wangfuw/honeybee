<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->string('store_name')->comment('商店名称');
            $table->tinyInteger('business_type')->default(0)->comment('营业范围');
            $table->string('desc')->nullable()->comment('商家简介');
            $table->string('mobile')->nullable()->comment('商家电话');
            $table->string('store_image')->nullable()->comment('店铺头像');
            $table->integer('sale_volume')->default(0)->comment('销量');
            $table->integer('stock')->default(0)->comment('存量');
            $table->integer('class_num')->default(0)->comment('商品种类数量');
            $table->tinyInteger('star_level')->default(0)->comment("商家星级");
            $table->integer('area')->nullable()->comment('商店地理位置');
            $table->string('address')->nullable()->comment('商店相信地址');
            $table->tinyInteger('status')->default(1)->comment('1-正常 0-异常');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `store` comment '商家信息'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store');
    }
}
