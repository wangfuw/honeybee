<?php

namespace App\Http\Controllers\Admin;

use App\Validate\Admin\SpuValidate;
use Illuminate\Http\Request;


class SpuController extends AdminBaseController
{
    private $validate;

    public function __construct(SpuValidate $validate)
    {
        $this->validate = $validate;
    }


    public function addSpu(Request $request)
    {
        $params = $request->only('area', 'category','name','logo','banner_imgs','detail_imgs','special_spec','skus');

        if (!$this->validate->scene('add')->check($params)) {
            return $this->fail($this->validate->getError());
        }

        var_dump($params);
    }
}
