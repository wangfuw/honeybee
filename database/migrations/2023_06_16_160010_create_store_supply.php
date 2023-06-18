<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreSupply extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_supply', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable()->comment('用户id');
            $table->string('mch_name')->nullable()->comment('分账方全称');
            $table->tinyInteger('merchant_type')->nullable()->comment('10:个人，11：个体工商户，12：企业');
            $table->string('contact_name')->nullable()->comment('业务联系人姓名');
            $table->string('contact_mobile_no')->nullable()->comment('业务联系人电话');
            $table->string('phone_no')->nullable()->comment('法人电话');
            $table->string('scope')->nullable()->comment('营业范围');
            $table->string('addr')->nullable()->comment('营业地址');
            $table->string('legal_person')->nullable()->comment('法人姓名');
            $table->string('id_card_no')->nullable()->comment('法人身份证号');
            $table->string('license_no')->nullable()->comment('营业执照编号');
            $table->tinyInteger('sett_mode')->default(1)->comment('结算方式 1 自动结算 2 手动结算');
            $table->tinyInteger('sett_date_type')->default(2)->comment('结算周期类型');
            $table->tinyInteger('risk_day')->default(1)->comment('结算周期');
            $table->tinyInteger('bank_account_type')->default(1)->comment('1 借记卡 4对公账户');
            $table->string('bank_account_name')->nullable()->comment('银行账户名称 对公是企业名称 个人姓名');
            $table->string('bank_account_no')->nullable()->comment('银行卡号');
            $table->string('bank_channel')->nullable()->comment('联行号 对公账户必填');
            $table->tinyInteger('status')->default(0)->comment('0-待入住 1-入住成功 2入住失败');
            $table->string('msg')->nullable()->comment('入住返回信息');
            $table->string('alt_mch_no')->nullable()->comment('分账编号');
            $table->integer('created_at')->comment('插入时间');
            $table->integer('updated_at')->comment('修改时间');
        });
        \DB::statement("ALTER TABLE `store_supply` comment '分账方入网申请'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_supply');
    }
}
