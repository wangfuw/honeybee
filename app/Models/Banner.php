<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use DateTimeZone;
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


    public function getBanners()
    {
        return self::query()->select('id','path','title','created_at')->orderBy('created_at','desc')->get()->toArray();
    }

}
