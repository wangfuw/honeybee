<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserIdentityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_identity', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->string('username')->comment('姓名');
            $table->string('id_card')->comment('身份证');
            $table->string('address_code')->comment('地址编码');
            $table->string('front_image')->comment('正面');
            $table->string('back_image')->comment('背面');
            $table->tinyInteger('status')->default(0)->comment('0-待审核 1-审核过 2-审核不通过');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `user_identity` comment '用户认证信息'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_identity');
    }
}
