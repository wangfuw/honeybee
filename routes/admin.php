<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use  \App\Http\Controllers\Admin\AdminController;

Route::middleware(['admin.sign'])->prefix("hack")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::middleware(['admin.token'])->group(function () {
        Route::controller(LoginController::class)->group(function () {
            Route::get("menuList", 'menuList');
        });
        Route::controller(AdminController::class)->group(function () {
            Route::get("admins", 'admins');
            Route::get("groups", 'groups');
        });
    });
});

