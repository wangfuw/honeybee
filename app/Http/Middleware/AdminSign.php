<?php

namespace App\Http\Middleware;

use App\Common\Rsa;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;
use Illuminate\Http\Response;

class AdminSign
{
    public function handle($request, Closure $next)
    {
        $sign = $request->header('sign');
        if ($sign == null || $sign == "") {
            return $this->baseReturn();
        }
        $sign = Rsa::decodeByPrivateKey($sign);
        if ($sign == "") {
            return $this->baseReturn();
        }
        $infos = explode("_", $sign);
        if(count($infos) != 2 || $infos[0] != "beeadmin"){
            return $this->baseReturn();
        }
        printf("%s\n",time() - (int)$infos[1]);
        if(time() - (int)$infos[1] > 10){

            return $this->baseReturn();
        }
        return $next($request);
    }

    private function baseReturn(){
        return response()->json([
            "status" => 0,
            "info" => "签名错误",
        ]);
    }
}
