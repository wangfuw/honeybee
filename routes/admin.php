<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;
use  App\Http\Controllers\Admin\AdminController;
use  App\Http\Controllers\Admin\AuthController;
use  App\Http\Controllers\Admin\BannerController;
use  App\Http\Controllers\Admin\NoticeController;
use  App\Http\Controllers\Admin\NewsController;
use  App\Http\Controllers\Admin\UserController;
use  App\Http\Controllers\Admin\CategoryController;

Route::middleware(['admin.sign'])->prefix("hack")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::middleware(['admin.token'])->group(function () {
        Route::get("menuList", [LoginController::class, "menuList"]);
        Route::post("changePwd", [LoginController::class, "changePassword"]);
        Route::post("uploadOne", [LoginController::class, "uploadOne"]);

        Route::middleware(['admin.auth'])->group(function () {
            Route::get("admins", [AdminController::class, "admins"]);
            Route::get("groups", [AdminController::class, "groups"]);
            Route::get("actionLog", [AdminController::class, "actionLog"]);
            Route::post("addGroup", [AdminController::class, "addGroup"])->middleware(["admin.response"]);
            Route::post("delGroup", [AdminController::class, "delGroup"])->middleware(["admin.response"]);
            Route::post("addAdmin", [AdminController::class, "addUser"])->middleware(["admin.response"]);
            Route::post("delAdmin", [AdminController::class, "delUser"])->middleware(["admin.response"]);
            Route::post("banAdmin", [AdminController::class, "banUser"])->middleware(["admin.response"]);

            Route::get("authList", [AuthController::class, "authList"]);
            Route::post("addAuth", [AuthController::class, "addAuth"])->middleware(["admin.response"]);
            Route::post("delAuth", [AuthController::class, "delAuth"])->middleware(["admin.response"]);

            Route::get("bannerList", [BannerController::class, "bannerList"]);
            Route::post("addBanner", [BannerController::class, "addBanner"])->middleware(["admin.response"]);
            Route::post("delBanner", [BannerController::class, "delBanner"])->middleware(["admin.response"]);

            Route::get("noticeList", [NoticeController::class, "noticeList"]);
            Route::post("delNotice", [NoticeController::class, "delNotice"])->middleware(["admin.response"]);
            Route::post("addNotice", [NoticeController::class, "addNotice"])->middleware(["admin.response"]);
            Route::post("editNotice", [NoticeController::class, "editNotice"])->middleware(["admin.response"]);

            Route::get("newsList", [NewsController::class, "newsList"]);
            Route::post("delNews", [NewsController::class, "delNews"])->middleware(["admin.response"]);
            Route::post("addNews", [NewsController::class, "addNews"])->middleware(["admin.response"]);
            Route::post("editNews", [NewsController::class, "editNews"])->middleware(["admin.response"]);

            Route::get("userList", [UserController::class, "userList"]);
            Route::post("banUser", [UserController::class, "banUser"]);
            Route::get("teamTree", [UserController::class, "teamTree"]);
            Route::get("userAuthList", [UserController::class, "userAuthList"]);
            Route::post("editUserAuth", [UserController::class, "editUserAuth"])->middleware(["admin.response"]);

            Route::get("categoryList", [CategoryController::class, "categoryList"]);
            Route::post("addCategory", [CategoryController::class, "addCategory"])->middleware(["admin.response"]);
            Route::post("editCategory", [CategoryController::class, "editCategory"])->middleware(["admin.response"]);
            Route::post("delCategory", [CategoryController::class, "delCategory"])->middleware(["admin.response"]);
        });
    });
});

