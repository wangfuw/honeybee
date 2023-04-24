<?php

namespace App\Services;

use App\Models\Store;

class StoreService
{
    public function get_info($params)
    {
        $model = new Store();
        $list = $model->get_ss_info($id);

    }
}
