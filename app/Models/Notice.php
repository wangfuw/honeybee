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


    public function getNotices($params)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $type = $params['type']??1;
        $data = self::query()->select( 'id',
            'title',
            'text',
            'created_at'
        )->where('type',$type)
            ->orderBy('id','desc')
        ->get()
        ->forPage($page,$page_size);
        return collect([])->merge($data);
    }

    public function getInfo($id)
    {
        $info = self::query()->select('id',
            'title',
            'text',
            'created_at',
        )->where('id',$id)->first();
        if(isEmpty($info)) return  [];
        return  $info->toArray();
    }
}
