<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\isEmpty;

class Notice extends Base
{
    use HasFactory;

    protected $table = 'notice';

    protected $fillable = [
        'id',
        'title',
        'text',
        'type',
        'created_at',
        'updated_at',
        'type'
    ];

//    public function getTextAttribute($value){
//        if($value == '') return $value;
//        return  strip_tags($value);
//    }
    public function getNotices($params)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $type = $params['type']??1;
        $data = self::query()->select( 'id',
            'title',
            'text as text_line',
            'created_at'
        )->where('type',$type)
            ->orderBy('id','desc')
            ->forPage($page,$page_size)
        ->get()->map(function ($item,$items){
                $item->text = strip_tags($item->text_line);
                return $item;
            })
       ;
        return collect([])->merge($data);
    }

    public function getInfo($id = 0,$type = 0)
    {
        if($id > 0){
            $info = self::query()->select('id',
                'title',
                'text',
                'created_at',
            )->where('id',$id)->first();
        }
        if($type > 0){
            $info = self::query()->select('id',
                'title',
                'text',
                'created_at',
            )->where('type',$type)->first();
        }

        if(empty($info)) return  [];
        return  $info->toArray();
    }
}
