<?php
namespace app\Http\Controllers\Admin;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends BaseController
{
    public function login(Request $request){
        return response()->json([
            'name' => 'Abigail',
            'state' => 'CA',
        ]);
    }
}
