<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use  App\Http\Controllers\Admin\AdminController;
use  App\Http\Controllers\Admin\AuthController;
use  App\Http\Controllers\Admin\BannerController;
use  App\Http\Controllers\Admin\NoticeController;

Route::middleware(['admin.sign'])->prefix("hack")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::middleware(['admin.token'])->group(function () {
        Route::get("menuList", [LoginController::class, "menuList"]);
        Route::post("changePwd", [LoginController::class, "changePassword"]);
        Route::post("uploadOne", [LoginController::class, "uploadOne"]);

        Route::middleware(['admin.auth'])->group(function () {
            Route::get("admins", [AdminController::class, "admins"]);
            Route::get("groups", [AdminController::class, "groups"]);
            Route::post("addGroup", [AdminController::class, "addGroup"]);
            Route::post("delGroup", [AdminController::class, "delGroup"]);
            Route::post("addAdmin", [AdminController::class, "addUser"]);
            Route::post("delAdmin", [AdminController::class, "delUser"]);
            Route::post("banAdmin", [AdminController::class, "banUser"]);

            Route::get("authList", [AuthController::class, "authList"]);
            Route::post("addAuth", [AuthController::class, "addAuth"]);
            Route::post("delAuth", [AuthController::class, "delAuth"]);

            Route::get("bannerList", [BannerController::class, "bannerList"]);
            Route::post("addBanner", [BannerController::class, "addBanner"]);
            Route::post("delBanner", [BannerController::class, "delBanner"]);

            Route::get("noticeList", [NoticeController::class, "noticeList"]);
            Route::post("delNotice", [NoticeController::class, "delNotice"]);
            Route::post("addNotice", [NoticeController::class, "addNotice"]);
            Route::post("editNotice", [NoticeController::class, "editNotice"]);
        });
    });
});

Route::middleware(["admin.token"])->group(function (){
    Route::post("upload", [LoginController::class, "uploadMany"]);
});

