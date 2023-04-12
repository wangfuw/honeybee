<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($msg,$data = [])
    {
        return response()->json([
            'status' => 1,
            'info' => $msg,
            'result' => $data,
        ]);
    }

    public function error($msg,$data){
        return response()->json([
            'status' => 0,
            'info' => $msg,
            'result' => $data,
        ]);
    }
}
