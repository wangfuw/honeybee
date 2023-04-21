<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Base
{
    protected $table = 'orders';

    /**
     *    $table->id();
    $table->integer('base_id')->comment('主订单id');
    $table->string('order_no')->comment('订单号');
    $table->integer('user_id')->comment('客户id');
    $table->integer('sku_id')->comment('商品id');
    $table->integer('sku_num')->comment('数量');
    $table->integer('store_id')->default(0)->comment('0-为自营 商店id');
    $table->tinyInteger('status')->default(1)->comment('1--待支付 2 -- 已支付 3--撤单');
    $table->tinyInteger('express_status')->default(0)->comment('0--待发货 1--已发货 2--签收');
    $table->tinyInteger('is_return')->default(0)->comment("0-no 1-申请换货");
    $table->string('express_no')->default(null)->comment('运单单号');
    $table->string('express_name')->default(null)->comment('快递公司');
    $table->decimal('express_fee',10)->default(0)->comment('运费');
    $table->decimal('give_green_score',16)->default(0)->comment('获得获得绿色积分');
    $table->decimal('give_sale_score',16)->default(0)->comment('获得消费积分');
    $table->decimal('give_lucky_score',16)->default(0)->comment('获得幸运值');
    $table->decimal('give_ticket_score',16)->default(0)->comment('获得消费卷');
    $table->integer('address_id')->default(0)->comment('收获地址id');
    $table->integer('created_at')->comment('下单时间');
    $table->integer('updated_at')->comment('修改时间');
    $table->integer('deleted_at')->nullable()->comment('删除时间');
     */
    protected $fillable = [
        'id','base_id','order_no','user_id','type','created_at','updated_at'
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'products' => 'array'
    ];
}
