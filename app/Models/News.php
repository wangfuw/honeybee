<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Base
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'id',
        'title',
        'face',
        'text',
        'path',
        'author',
        'publish_time',
        'type',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    /**
     * 查询轮播图
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getList($params)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $type = $params['type']??1;
        return self::query()->select( 'id',
            'title',
            'face',
            'text',
            'author',
            'publish_time',
            'type')
            ->where('type',$type)
            ->orderBy('publish_time','desc')
            ->get()
            ->forPage($page,$page_size);
    }

    public function getInfo($id)
    {
        return self::query()->select('id',
            'title',
            'face',
            'text',
            'path',
            'author',
            'publish_time',
            'type')->where('id',$id)->first()->toArray();
    }
}
