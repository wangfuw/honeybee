<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMoney extends Base
{
    protected $table = 'user_money';
// `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
//  `user_id` int(11) DEFAULT NULL COMMENT '用户id',
//  `num` decimal(16,2) NOT NULL DEFAULT '0.00' COMMENT '充值额度',
//  `charge_image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '充值记录照片',
//  `money` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '价值多少余额',
//  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0-未审核 1-审核通过 2-驳回 审核通过用户表增加冻结余额',
//  `admin_id` int(11) DEFAULT NULL COMMENT '审核人id',
//  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '驳回原因',
    protected $fillable = [
        'id','user_id','num','charge_image','money','status','admin_id','note','created_at','updated_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];


}
