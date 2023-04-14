<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminActionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_action', function (Blueprint $table) {
            $table->id();
            $table->integer("admin_id")->comment("管理员id");
            $table->integer("rule_id")->comment("权限id");
            $table->string("ip")->comment("操作时的ip地址");
            $table->dateTime("created_at")->comment("操作的时间");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_action');
    }
}
