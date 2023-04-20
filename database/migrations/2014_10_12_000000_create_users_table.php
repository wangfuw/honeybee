<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nickname')->nullable()->comment('昵称');
            $table->string('phone');
            $table->tinyInteger('is_shop')->default(0)->comment('1-商家');
            $table->tinyInteger('identity')->default(0)->comment('0：无,1:形象店，2：旗舰店');
            $table->tinyInteger('identity_status')->default(0)->comment('0：考核中,1:考核完成');
            $table->string('identity_area_code')->nullable()->comment('身份绑定的区域代码');
            $table->tinyInteger('share_level')->default(0)->comment('分享奖等级：0：无,1:普通，2：中等，3：高级');
            $table->decimal('max_luck_num')->default(0)->comment('幸运专区单次最高消费');
            $table->string('image')->nullable()->comment('头像');
            $table->integer('green_score')->default(0)->comment("可用绿色积分");
            $table->integer('green_score_total')->default(0)->comment("累计绿色积分");
            $table->integer('sale_score')->default(0)->comment("消费积分");
            $table->integer('sale_score_total')->default(0)->comment("累计消费积分");
            $table->integer('luck_score')->default(0)->comment("幸运值");
            $table->integer('ticket_num')->default(0)->comment("消费卷");
            $table->integer('coin_num')->default(0)->comment("asac数量");
            $table->string('invite_code')->comment('邀请码');
            $table->integer('master_id')->default(0)->comment('直推人');
            $table->string('master_pos')->comment('所有上级:如,13,6,1');
            $table->string('password');
            $table->string('sale_password')->nullable()->comment('通证密码');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->nullable()->comment('删除时间');
        });
        \DB::statement("ALTER TABLE `users` comment '用户'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
