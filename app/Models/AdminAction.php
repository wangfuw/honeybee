<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAction extends Model
{
    protected $table = 'admin_action';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'admin_id',
        'rule_id',
        'ip',
        'param',
        'created_at',
    ];
}
