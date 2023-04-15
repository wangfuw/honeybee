<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('sku_id')->comment("商品id");
            $table->string('desc')->comment('描述');
            $table->string('image')->nullable()->comment('图片描述');
            $table->integer('nickname')->comment('昵称');
            $table->tinyInteger('star_level')->default(0)->comment('0-1 差评 2-3 中评 4-5好评');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `reviews` comment '商品评论'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
