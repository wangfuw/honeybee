<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Base
{
    use HasFactory;

    protected $table = 'score';

    protected $guarded = [];

    protected $hidden = [
        'deleted_at'
    ];


    const BACK_ADD = 1;
    const BACK_SUB = 2;

    const F_TYPES = [
        self::BACK_ADD => "后台增加",
        self::BACK_SUB => "后台扣除"
    ];
}
