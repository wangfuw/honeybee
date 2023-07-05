<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSupply extends Base
{
    protected $table = 'store_supply';
    protected $fillable=[
        'id','user_id','mch_name','merchant_type','contact_name','contact_mobile_no','phone_no','scope','addr','legal_person','id_card_no',
        'license_no','sett_mode','sett_date_type','risk_day','bank_account_type','bank_account_name','bank_account_no','bank_channel',
        'status','msg','alt_mch_no','created_at','updated_at','sign_status','sign_url','sign_time','sign_trx_no','aes_key','is_upload','bank_name'
    ];
    protected $hidden = [];
}
