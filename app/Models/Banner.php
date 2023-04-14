<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Base
{
    use HasFactory;


    public function getCreatedAtAttribute($value)
    {
        if(!is_numeric($value)){
            return  $value;
        }
        return date("Y-m-d h:i:s",$value);
    }

    public function getBanners()
    {
        return self::query()->select('id','path','title')->orderBy('created_at','desc')->get()->toArray();
    }
}
