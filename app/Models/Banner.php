<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public function getCreatedATAttribute($value)
    {
        if(!is_numeric($value)){
            return  $value;
        }
        return date("Y-m-d h:i:s",$value);
    }
}
