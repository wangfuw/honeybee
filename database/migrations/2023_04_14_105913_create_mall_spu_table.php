<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMallSpuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mall_spu', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('商品名称');
            $table->string('sub_title')->nullable()->comment('副标题 (一般是促销信息)');
            $table->string('description')->nullable()->comment('商品描述');
            $table->integer('category_one')->comment('1级分类Id');
            $table->integer('category_two')->comment('1级分类Id');
            $table->tinyInteger('saleable')->default(1)->comment('是否上架 (0-下架，1-上架)');
            $table->string('logo')->comment('商品logo');
            $table->json('banners')->default(null)->comment('商品轮播图');
            $table->json('details')->comment('商品详情图');
            $table->json('special_spec')->default(null)->comment('规格键值对json格式');
            //商品绑定商家
            $table->integer('user_id')->default(0)->comment('0 - 为自营商品');
            //商品绑定消费分区
            $table->tinyInteger('game_zone')->default(1)->comment('1-福利专区 2-优选专区 3-幸运专区 4-消费专区');
            //商品绑定倍数分区
            $table->tinyInteger('score_zone')->default(0)->comment('1-一倍积分 2-二倍积分 3-三倍积分');

            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });

        \DB::statement("ALTER TABLE `mall_spu` comment '商品'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mall_spu');
    }
}
