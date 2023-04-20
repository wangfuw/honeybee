<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;

class BaseController extends Controller
{
    use ApiResponse;

    public $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }
}
