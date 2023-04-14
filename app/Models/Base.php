<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Base extends Model
{
    use HasFactory;

    //关闭自动时间管理 默认值true
    public $timestamps = true;

    protected $dateFormat = 'U';

    protected function serializeDate(\DateTimeInterface $date)
    {
        $date->tz = new \DateTimeZone('Asia/Shanghai');
        return $date->format('Y-m-d H:i:s');
    }

}
