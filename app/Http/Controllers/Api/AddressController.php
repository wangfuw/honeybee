<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Address;
use App\Validate\AddressValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends BaseController
{
    protected $model;
    protected $validate;

    public function __construct(AddressValidate $validate,Address $model)
    {
        $this->validate = $validate;
        $this->model    = $model;
    }

    public function get_Address(Request $request)
    {
        if(!$this->validate->scene('page')->check($request->toArray())){
            return $this->fail($this->validate->getError());
        }
        $data = $this->model->getList($request,auth()->id());
        return $this->successPaginate($data);
    }
    /**
     * 新增地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_address(Request $request)
    {
        $data = $request->only(['address_detail','exp_person','exp_phone','area','is_def']);
        if(!$this->validate->scene('add')->check($data)){
            return $this->fail($this->validate->getError());
        }
        if(!check_phone($request->phone) == false){
            return $this->fail("电话号码格式错误");
        }
        $data['user_id'] = auth()->id();
        try {
            DB::beginTransaction();
            if($request->is_def == 1){
                $def_list = Address::query()->where('is_def',1)->where('user_id',auth()->id())->first();
                if($def_list){
                    $def_list->is_def = 0;
                    $def_list->save();
                }
            }
            $res = Address::query()->create($data);
            DB::commit();
            return $this->success('新增成功',$res);
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail($e->getMessage());
        }
    }

    /**
     * 设置默认地址
     * @param Request $request
     * @return void
     */
    public function set_def(Request $request)
    {
        $data = $request->only(['id']);
        if(!$this->validate->scene('id')->check($data)) {
            return $this->fail($this->validate->getError());
        }
        $info = Address::query()->where('id',$request->id)->where('user_id',auth()->id())->first();
        if(empty($info)){
            return $this->fail('该地址不存在');
        }
        if($info->is_def == 1){
            return $this->fail('改地址已是默认地址');
        }
        try{
            DB::beginTransaction();
            $def_address = Address::query()->where('is_def',1)->where('user_id',auth()->id())->first();
            if(!empty($def_address)){
                $def_address->is_def = 0;
                $def_address->save();
            }
            $info->is_def = 1;
            $info->save();
            DB::commit();
            return $this->success('设置成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail('设置失败');
        }
    }

    /**
     * 编辑地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_address(Request $request){
        $id  = $request->id;
        $info = Address::query()->where('id',$id)->where('user_id',auth()->id())->first();
        if(empty($info)){
            return $this->fail('该地址不存在');
        }
        if($request->area){
            $info->area = $request->area;
        }
        if($request->address_detail){
            $info->address_detail = $request->address_detail;
        }
        if($request->exp_person){
            $info->exp_person = $request->exp_person;
        }
        if($request->exp_phone && check_phone($request->exp_phone) == true){
            $info->exp_phone = $request->exp_phone;
        }
        if($request->is_def){
            $info->is_def = $request->is_def;
        }
        try{
            DB::beginTransaction();
            if($request->is_def == 1){
                $def_address = Address::query()->where('user_id',auth()->id())->where('is_def',1)->first();
                if(!empty($def_address)){
                    $def_address->is_def = 0;
                    $def_address->save();
                }
            }
            $info->save();
            DB::commit();
            return $this->success('编辑成功');
        }catch (\Exception $e){
            DB::rollBack();
            return $this->fail($e->getMessage());
        }
    }

    public function del_address(Request $request)
    {
        $data = $request->only(['id']);
        if(!$this->validate->scene('id')->check($data)){
            return $this->fail($this->validate->getError());
        }
        Address::query()->where('id',$request->id)->where('user_id',auth()->id())->delete();
        return  $this->success('删除成功');
    }
}
