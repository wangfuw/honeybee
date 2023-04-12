<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function baseResponse($status, $msg, $data = null)
    {
        return response()->json([
            'status' => $status,
            'info' => $msg,
            'result' => $data,
        ]);
    }

    public function success($msg, $data = [])
    {
        return $this->baseResponse(1, $msg, $data);
    }

    public function fail($msg)
    {
        return $this->baseResponse(0, $msg);
    }
}
