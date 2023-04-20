<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacTrade extends Model
{
    use HasFactory;

    protected $table = 'asac_trade';

    protected $fillable = [
        'id','from_address','to_address','num','trade_hash','block_id','created_at','updated_at'
    ];

    protected $hidden = [
        'updated_at'
    ];

}
