<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class UserIdentity extends Base
{
    use HasFactory,SoftDeletes;

    protected $table = "user_identity";
    protected $fillable = [
        'id','user_id','username','id_card','address_code','front_image','back_image','status','created_at','updated_at'
    ];

    protected $hidden = [
        "deleted_at"
    ];


}
