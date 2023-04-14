<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Banner extends Base
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = [
        'id',
        'title',
        'path'
    ];
    public function getCreatedAtAttribute($value)
    {

        $date = Carbon::parse($value);
        return $date->format('Y-m-d H:i:s',$value);
        return date("Y-m-d h:i:s",$value);
    }


    public function getBanners()
    {
        return self::query()->select('id','path','title','created_at')->orderBy('created_at','desc')->get()->toArray();
    }

}
