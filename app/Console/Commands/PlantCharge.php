<?php

namespace App\Console\Commands;

use App\Models\AsacNode;
use App\Models\AsacTrade;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlantCharge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plan_charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'plan_charge';
    private $url = "http://4619p19v09.qicp.vip/app/token/transferAccounts";
    private $publicKeyString = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5GnfqIyYaaat8MLYofU49fjghLR2X5j4Dy/FQMu+IAWVKtum/yUOFwTWI1lFD6qGLTFAPdFwaVhpLeZqnpIyuwcgnTGfEONKYUg+NSUMwMe2QhE/KqoyexL1xmB3O5V99xGxUPrGqFKU092AjLzYiWhvWNuUOFvSyVvmXduGgHQIDAQAB";
    private $privateKeyString=
        <<<EOF
-----BEGIN RSA PRIVATE KEY-----
MIICdQIBADANBgkqhkiG9w0BAQEFAASCAl8wggJbAgEAAoGBAJ
Gx7S3kBHExy8fDjXVJTBYlhl+udHqcNjnHekJa/9IPnug7FzBB7WTYt1prVucet
CqkaF6XmiM547/0qSrKwcB0iWP/mX08kcUToSNlF/IrRQwQSJC5SWWkg4CEHAe1Ez6PlhF
ic6Kauy+Y3ZlLvgJ5adJywbQiTQxBZPR+edKfAgMBAAECgYAlLvnPWoBfu0u84gW6R0ip5k3TIZ0Mp
c2ZFXX4dW0Q2Xe+4UXbxRenQ0AYTYjSV523vFn+ukZWczHTDcgW6cRkzO6LFFJcqRwSaYxs1Y+XBnzD0C4OouP
orOqrX8Q+QWkvtNeRbcCq48otMBV8ldtw2DE5Lox4groZno0bL7nhEQJBANHJ3iCaidQTNwLEsJrshFxIxGQIMoD2/EO26X
vyxyB03FdyQUS4DRZ3mD+Cuu65Mba9WtokW2UHsvtBU0Hb/B0CQQCxyccHWYtekK8Z2RNIljDz3/2kFI0aAjenNu2f
sQrdDYu6N504bJExiWZl5SiybFTKmdWq1WET694N/MnYSbTrAkAR4MMmQiU2xoBsovht63QILUdXQv+Sz/mFh8P0BSeFE5Bn
f1wikBDFdmGqH+jwSSX8bbydVHXZcUOGd/Wxah7dAkADlsowyMr6drtCL1KnVXjZg30QUeXjT4YmFwLHBYDto3ddo//FgtO
Ph3GMvwqKR4uSp3Zbw4D5MPrDl4XPmvVPAkBi8axxOS2pABPOgh3o4fqQGnEEY32A2sjnS7Zqkk7mMXwEuBiT
6OiM3wOnuof2WmzVMfPeyQMj8HTSF/klkAOC
-----END RSA PRIVATE KEY-----
EOF;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * RSA签名
     * @param $data 待签名数据
     * @param $private_key 私钥字符串
     * return 签名结果
     */
    protected function rsaSign($data, $private_key) {

        $search = [
            "-----BEGIN RSA PRIVATE KEY-----",
            "-----END RSA PRIVATE KEY-----",
            "\n",
            "\r",
            "\r\n"
        ];
        $private_key = str_replace($search,"",$private_key);
        $private_key = $search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
        $private_key_resource_id=openssl_get_privatekey($private_key);
        if($private_key_resource_id)
        {
            openssl_sign($data, $sign,$private_key_resource_id,OPENSSL_ALGO_MD5);
            openssl_free_key($private_key_resource_id);
        }else {
            exit("私钥格式有误");
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    protected function post_url($url, $data = NULL)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!$data){
            return 'data is null';
        }
        if(is_array($data))
        {
            $data = json_encode($data);
        }
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            return $errorno;
        }
        curl_close($curl);
        return $res;
    }

    protected function rand_str_pay($length=40) {
        $rand='';
        $randstr= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $max = strlen($randstr)-1;
        mt_srand((double)microtime()*1000000);
        for($i=0;$i<$length;$i++) {
            $rand.=$randstr[mt_rand(0,$max)];
        }
        return $rand;
    }
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Log::info('开始执行，读取充值数据：'.date('Y-m-d H:i:s'));
        $data = [
            "address"=> "0xf897ec4fc2b6775a84bca810d3df9308fd2112ee",
            "appId"=>"1120122425175191552",
            "timestamp"=>time(),
            "publicKey"=> $this->publicKeyString,
        ];
        $res = $this->formatBizQueryParaMap($data,false);
        $sign = $this->rsaSign($res,$this->privateKeyString);
        $data["sign"] = $sign;
        $data['endTime'] = date("Y-m-d H:i:s");
        $data['startTime'] = date('Y-m-d H:i:s',strtotime("-2 minute"));
        $result = $this->post_url($this->url,$data);
        $ret = json_decode($result,true);
        if(!isset($ret['code']) || $ret['code'] != 200){
            Log::info("暂无充值".date('Y-m-d H:i:s'));
        }
        if(empty($ret['data'])){
            Log::info("暂无充值".date('Y-m-d H:i:s'));
            return false;
        }
        try {
            DB::beginTransaction();
            foreach ($ret['data'] as $r){
                if(AsacTrade::query()->where('order_nu',$r['orderSn'])->exists()){
                    continue;
                }
                if(!$r['remark']){
                    continue;
                }
                $user_id = AsacNode::query()->where('wallet_address',$r['remark'])->value('user_id');
                if(!$user_id){
                    continue;
                }
                AsacTrade::query()->create([
                    'from_address'=>$r['address'],
                    'to_address'  => $r['remark'],
                    'num'=>$r['num'],
                    'type'=>AsacTrade::RECHARGE,
                    'trade_hash'=>$this->rand_str_pay(64)
                ]);
                $user = User::query()->where('id',$user_id)->first();
                $user->coin_num += $r['num'];
                $user->save();
            }
            DB::commit();
            Log::info('同步充值结束'.date('Y-m-d H:i:s'));
            return false;
        }catch (\Exception $e){
            DB::rollBack();
            Log::info($e->getMessage());
        }
    }
}
