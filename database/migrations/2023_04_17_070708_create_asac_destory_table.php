<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsacDestoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asac_destory', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('销毁人id');
            $table->string('dest_address')->comment('销毁地址');
            $table->decimal('number',16)->default(0)->comment('销毁数量');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
        });
        \DB::statement("ALTER TABLE `asac_destory` comment 'ASAC销毁记录'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asac_destory');
    }
}
