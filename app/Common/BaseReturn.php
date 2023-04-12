<?php

use Illuminate\Http\Response;

function baseReturn($status, $info, $data = null)
{
    return response()->json([
        'status' => $status,
        'info' => $info,
        'data' => $data
    ]);
}
