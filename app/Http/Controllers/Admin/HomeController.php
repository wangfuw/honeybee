<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends AdminBaseController
{
    public function registerLine(Request $request)
    {
        $condition = [];
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $data = User::where($condition)->groupBy('date')
            ->get([DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as num')])
            ->toArray();
        return $this->executeSuccess("请求", $data);
    }
}
