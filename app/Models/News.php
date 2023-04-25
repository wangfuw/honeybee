<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\isEmpty;

class News extends Base
{
    use HasFactory;


    protected $table = 'news';

    protected $fillable = [
        'id',
        'title',
        'face',
        'text',
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
        $data = self::query()->select( 'id',
            'title',
            'face',
            'text',
            'type',
            'created_at'
        )
            ->where('type',$type)
            ->orderBy('id','desc')
            ->get()
            ->forPage($page,$page_size);
        return collect([])->merge($data);
    }

    public function getInfo($id)
    {
        $info =  self::query()->select('id',
            'title',
            'face',
            'text',
            'created_at',
            'type')->where('id',$id)->first();
        if(empty($info)) return [];
        return  $info->toArray();
    }
}
