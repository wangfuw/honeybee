<?php

namespace App\Http\Controllers\Merchant;

use App\Models\StoreSupply;
use App\Validate\ApplyValidate;
use Illuminate\Http\Request;

class ApplyController extends MerchantBaseController
{
    private $validate;

    public function __construct(ApplyValidate $validate){
        $this->validate = $validate;
    }
    public function applyInfo()
    {
        $user = auth("merchant")->user();
        $info = StoreSupply::query()->where('user_id',$user->id)->first();
        return $this->executeSuccess("请求", $info);
    }


    public function apply(Request $request)
    {
        $data = $request->only(['id','alt_mch_no','bank_account_name',
            'bank_account_no','bank_account_type','bank_channel',
            'contact_mobile_no','contact_name','id_card_no','legal_person','license_no',
            'mch_name','merchant_type','phone_no','risk_day','scope','sett_date_type','sett_mode']);
        if(!$this->validate->scene('apply')->check($data)){
            return $this->executeFail($this->validate->getError());
        }
        if(check_phone($data['contact_mobile_no']) == false){
            return $this->executeFail("联系人电话错误");
        }
        if(check_phone($data['phone_no']) == false){
            return $this->executeFail("法人电话错误");
        }
        if($data['risk_day'] > 28){
            return $this->executeFail('结算周期1-28');
        }
        if(!$data['id']){
            StoreSupply::query()->create($data);
            return $this->executeSuccess("提交");
        }else{
            $user = auth("merchant")->user();
            $info = StoreSupply::query()->where('id',$data['id'])->where('user_id',$user->id)->first();
            if(!$info){
                return $this->executeFail("没有提交申请支付入住");
            }
            $info->bank_account_name = $data['bank_account_name'];
            $info->bank_account_no = $data['bank_account_no'];
            $info->bank_account_type = $data['bank_account_type'];
            $info->bank_channel = $data['bank_channel'];
            $info->contact_mobile_no = $data['contact_mobile_no'];
            $info->contact_name = $data['contact_name'];
            $info->id_card_no = $data['id_card_no'];
            $info->legal_person = $data['legal_person'];
            $info->license_no = $data['license_no'];
            $info->mch_name = $data['mch_name'];
            $info->merchant_type = $data['merchant_type'];
            $info->phone_no = $data['phone_no'];
            $info->risk_day = $data['risk_day'];
            $info->scope = $data['scope'];
            $info->sett_date_type = $data['sett_date_type'];
            $info->sett_mode = $data['sett_mode'];
            $info->save();
            return $this->executeSuccess("修改");
        }

    }
}
