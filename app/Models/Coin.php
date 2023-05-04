<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coin extends Base
{
    protected $table = 'coin';

    protected $fillable = [
        'id','name','address','money','created_at','updated_at'
    ];

}
