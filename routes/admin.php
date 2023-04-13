<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Middleware\AdminSign;

Route::middleware(['middleware' => 'admin.sign'])->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post("login", 'login');
    });
});

