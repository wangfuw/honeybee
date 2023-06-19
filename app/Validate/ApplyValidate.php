<?php

namespace App\Validate;

class ApplyValidate extends BaseValidate
{
    protected $rule =[
        'id'=>'numeric',
        'bank_account_name'=>'required',
        'bank_account_no'=>'required',
        'bank_account_type'=>'required|numeric',
        'contact_mobile_no'=>'required',
        'contact_name'=>'required',
        'id_card_no'=>'required',
        'legal_person'=>'required',
        'license_no'=>'required',
        'mch_name'=>'required',
        'merchant_type'=>'required|numeric',
        'phone_no'=>'required',
        'risk_day'=>'required',
        'scope'=>'required',
        'sett_date_type'=>'required|numeric',
        'sett_mode'=>'required|numeric'
    ];

    //自定义验证信息
    protected $message = [
        'id.numeric'   => 'ID类型为数字',
        'bank_account_name.required'   => '银行账户名称必须',
        'bank_account_no.required'   => '银行卡号必须',
        'bank_account_type.required'   => '银行卡类型',
        'bank_account_type.numeric'   => '银行卡数字',
        'contact_mobile_no.required'=>'业务联系人电话必须',
        'legal_person.required'=>'法人人必须',
        'contact_name.required'=>'业务联系人必须',
        'id_card_no.required'=>'法人身份证必须',
        'mch_name.required'=>'分账方全称',
        'merchant_type.required'=>'分账方全称',
        'merchant_type.numeric'=>'分账方类型必须',
        'phone_no.required'=>'法人电话必须',
        'license_no.required'=>'营业执照编号必须',
        'risk_day.required'=>'结算周期必须',
        'scope.required'=>'营业范围必须',
        'sett_date_type.required'=>'结算周期类型必须',
        'sett_date_type.numeric'=>'结算周期类型错误',
        'sett_mode.required'=>'结算方式必须',
        'sett_mode.numeric'=>'结算方式型错误',
    ];

    //自定义场景
    protected $scene = [
        'apply'  => "id,bank_account_name,
            bank_account_no,bank_account_type,contact_mobile_no,contact_name,id_card_no,legal_person,license_no,
            mch_name,merchant_type,phone_no,risk_day,scope,sett_date_type,sett_mode"
    ];
}
