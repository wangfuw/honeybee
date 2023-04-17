<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAsacNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asac_node', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('用户id');
            $table->string('wallet_address')->nullable()->comment('钱包地址');
            $table->string('private_key')->nullable()->comment('私钥');
            $table->decimal('number',16)->default(0)->comment('地址ASAC数量');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
        });
        \DB::statement("ALTER TABLE `asac_node` comment 'ASAC区块'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('asac_node');
    }
}
