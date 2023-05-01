<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exp', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('快递公司名称');
            $table->string('logo')->nullable()->comment('Logo');
            $table->tinyInteger('status')->default(1)->comment('1 正常 0 异常');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
            $table->integer('deleted_at')->nullable()->comment('取消时间');
        });
        \DB::statement("ALTER TABLE `exp` comment '快递公司管理'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exp');
    }
}
