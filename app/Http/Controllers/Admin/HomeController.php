<?php

namespace App\Http\Controllers\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends AdminBaseController
{
    public function registerLine(Request $request)
    {
        $flag = $request->input("flag", 1);
        $condition = [];
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        if ($flag == 1) {
            $data = User::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as date"), DB::raw('COUNT(id) as num')])
                ->toArray();
        } else {
            $data = User::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m') as date"), DB::raw('COUNT(id) as num')])
                ->toArray();
        }

        $dates = array_column($data, "date");
        $nums = array_column($data, "num");
        return $this->executeSuccess("请求", compact('dates', 'nums'));
    }

    public function dealLine(Request $request)
    {
        $flag = $request->input("flag", 1);
        $condition = [];
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $condition[] = ["status", "=", "2"];
        if ($flag == 1) {
            $data = Order::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m-%d') as date"), DB::raw('SUM(coin_num) as num')])
                ->toArray();
        } else {
            $data = Order::where($condition)->groupBy('date')
                ->get([DB::raw("FROM_UNIXTIME(created_at,'%Y-%m') as date"), DB::raw('SUM(coin_num) as num')])
                ->toArray();
        }

        $dates = array_column($data, "date");
        $nums = array_column($data, "num");
        return $this->executeSuccess("请求", compact('dates', 'nums'));
    }
}
