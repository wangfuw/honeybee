<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\LoginController;

Route::post("/hack/login", [LoginController::class, 'login']);
