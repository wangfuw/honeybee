<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Middleware\AdminSign;

Route::middleware([AdminSign::class])->group(function (){
    Route::post("/hack/login", [LoginController::class, 'login']);
});

