<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevokeOrder extends Base
{
    use HasFactory;

    protected $table = 'revoke_order';

    protected $casts = [
        'photo' => 'array'
    ];

    protected $fillable = [
        'id','user_id','reason','photo','created_at','updated_at','order_no'
    ];
}
