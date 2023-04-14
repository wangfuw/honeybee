<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AdminResponse;

class AdminBaseController extends Controller {
    use AdminResponse;

    protected $page = 1;
    protected $size = 10;
}
