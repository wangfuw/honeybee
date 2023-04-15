<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('address', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('用户id');
            $table->integer('area')->nullable()->comment('地区id');
            $table->string('address_detail')->nullable()->comment('详情地址');
            $table->string('exp_person')->default(null)->comment('收货人');
            $table->string('exp_phone')->default(null)->comment('收货电话');
            $table->integer('created_at')->default(null)->comment('');
            $table->integer('updated_at')->default(null)->comment('修改时间');
            $table->integer('deleted_at')->default(null)->comment('');
        });
        \DB::statement("ALTER TABLE `address` comment '收获地址'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address');
    }
}
