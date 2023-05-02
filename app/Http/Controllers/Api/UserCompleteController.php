<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserIdentity;
use App\Validate\IdentityValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class UserCompleteController extends BaseController
{
    protected $model;

    protected $validate;
    //去认证
    public function __construct(UserIdentity $model,IdentityValidate $validate)
    {
        $this->model = $model;
        $this->validate = $validate;
    }

    /**
     * 提交实名认证
     * @return void
     */
    public function identity(Request $request)
    {
        $params = $request->only(['username','id_card','address_code','front_image','back_image']);
        if(!$this->validate->scene('identity')->check($params)){
            return $this->fail($this->validate->getError());
        }
        if(UserIdentity::query()->where('id_card',$request->id_card)->exists()){
            return $this->fail('该身份证已被使用');
        }
        if(checkIdentityCard($params['id_card']) == false){
            return $this->fail('身份证不合法');
        }
        $user = auth()->user();
        $params['user_id'] = $user->id;
        try {
            $info = UserIdentity::create($params);
            return $this->success('提交成功',$info);
        }catch (\Exception $e){
            return $this->fail($e->getMessage());
        }
    }

    //我的实名认证详情
    public function identityInfo()
    {
        $info = UserIdentity::query()->where('user_id',Auth::user()->id)->first();
        if($info){
            return $this->success('请求成功',$info);
        }else{
            return $this->success('暂无实名认证信息',[]);
        }
    }

    //修改我的实名认证
    public function identityUpdate(Request $request)
    {
        $info = UserIdentity::query()->where('user_id',Auth::user()->id)->where('status',2)->first();
        if(!$info){
            return $this->success('未申请实名认证');
        }
        $info->username = $request->username;
        $info->id_card  = $request->id_card;
        $info->address_code = $request->address_code;
        $info->front_image  = $request->front_image;
        $info->back_image   = $request->back_image;
        $info->status       = 0;
        $info->note         = '';
        $info->save();
        return $this->success('重新提交实名认证成功',[]);
    }

}
