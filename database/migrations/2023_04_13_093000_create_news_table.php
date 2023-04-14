<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable()->comment('标题');
            $table->longText('text')->nullable()->comment('正文');
            $table->string('author')->nullable()->comment('作者');
            $table->string('face')->nullable()->comment('封面');
            $table->tinyInteger('type')->default(1)->comment('1-资讯 2-上陶文化3-行业新闻 4-学习园地');
            $table->integer('created_at')->comment('创建时间');
            $table->integer('updated_at')->comment('编辑时间');
            $table->integer('deleted_at')->comment('删除时间');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news');
    }
}
