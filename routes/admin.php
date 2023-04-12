<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use app\Http\Controllers\admin\LoginController;

Route::post("/hack/login", [LoginController::class, 'login']);
