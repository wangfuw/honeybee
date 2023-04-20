<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Validate\ZoneValidate;
use Illuminate\Http\Request;

class ScoreController extends BaseController
{
    protected $model;

    private $validate;

    const GREEN = 1;
    const SALE = 2;

    const TICKET = 4;

    const LUCKY = 3;
    public $user;
    public function __construct(Score $model,ZoneValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
        $this->user = auth()->user();
    }

    public function get_green_sore(Request $request){
        $user_id = $this->user->id;
        $own_num = $this->user->green_score;
        $data = $this->model->get_list($request->toArray(),self::GREEN,$user_id);
        $used_num = $data['used_num']??0;
        $list = $data['data'];
        return  $this->success('请求成功',compact('own_num','used_num','list'));
    }
    public function get_sale_sore(Request $request)
    {
        $user_id = $this->user->id;
        $own_num = $this->user->sale_score;
        $data = $this->model->get_list($request->toArray(),self::SALE,$user_id);
        $used_num = $data['used_num']??0;
        $list = $data['data'];
        return  $this->success('请求成功',compact('own_num','used_num','list'));
    }
    public function get_ticket_sore(Request $request){
        $user_id = $this->user->id;
        $own_num = $this->user->ticket_num;
        $data = $this->model->get_list($request->toArray(),self::TICKET,$user_id);
        $used_num = $data['used_num']??0;
        $list = $data['data'];
        return  $this->success('请求成功',compact('own_num','used_num','list'));
    }
    public function get_lucky_sore(Request $request)
    {
        $user_id = $this->user->id;
        $own_num = $this->user->luck_score;
        $data = $this->model->get_list($request->toArray(),self::LUCKY,$user_id);
        $used_num = $data['used_num']??0;
        $list = $data['data'];
        return  $this->success('请求成功',compact('own_num','used_num','list'));
    }
}
