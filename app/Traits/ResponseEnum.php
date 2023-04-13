<?php

namespace App\Traits;

class ResponseEnum
{
    const HTTP_OK = [1, '操作成功'];
    const HTTP_ERROR = [0, '操作失败'];
    const TOKEN_EXPIRED = 1005;
}
