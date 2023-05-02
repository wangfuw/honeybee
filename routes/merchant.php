<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Merchant\LoginController;
use App\Http\Controllers\Merchant\SpuController;
use App\Http\Controllers\Merchant\OrderController;
use App\Http\Controllers\Merchant\HomeController;
use App\Http\Controllers\Merchant\AsacController;

Route::middleware(['admin.sign'])->prefix("merchant")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::post("uploadOne", [LoginController::class, "uploadOne"]);
    Route::middleware(['merchant.token'])->group(function () {
        Route::get("dealLine", [HomeController::class, "dealLine"]);
        Route::get("storeInfo", [HomeController::class, "storeInfo"]);
        Route::post("bindPay", [HomeController::class, "bindPay"]);
        Route::get("menuList", [LoginController::class, "menuList"]);
        Route::get("categoryList", [SpuController::class, "categoryList"]);
        Route::get("spuList", [SpuController::class, "spuList"]);
        Route::get("spuDetail", [SpuController::class, "spuDetail"]);
        Route::post("editSpu", [SpuController::class, "editSpu"]);
        Route::post("editSaleable", [SpuController::class, "editSaleable"]);

        Route::get("orderList", [OrderController::class, "orderList"]);
        Route::post("sendSku", [OrderController::class, "sendSku"]);

        Route::get("info", [AsacController::class, "info"]);
        Route::get("config", [AsacController::class, "config"]);
        Route::post("burn", [AsacController::class, "burn"]);
        Route::get("burnLog", [AsacController::class, "burnLog"]);
    });
});
