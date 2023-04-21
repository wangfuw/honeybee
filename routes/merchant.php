<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Merchant\LoginController;

Route::middleware(['admin.sign'])->prefix("merchant")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::middleware(['merchant.token'])->group(function () {
        Route::get("menuList", [LoginController::class, "menuList"]);
    });
});
