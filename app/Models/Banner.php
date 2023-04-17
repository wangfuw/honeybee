<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class Banner extends Base
{
    use HasFactory;

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
