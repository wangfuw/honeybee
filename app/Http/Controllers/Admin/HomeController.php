<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends AdminBaseController
{
    public function registerLine(Request $request)
    {
        $dates = [];
        $nums = [];

        $first_user = User::first();
        $start = $first_user->created_at;

        var_dump($start);

        $last_user = User::orderByDesc("id")->first();
        $end = $last_user->created_at;
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
//            $condition[] = ["created_at", ">=", strtotime($start)];
//            $condition[] = ["created_at", "<", strtotime($end)];
        }

    }
}
