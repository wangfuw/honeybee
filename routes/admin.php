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
use  App\Http\Controllers\Admin\SpuController;
use  App\Http\Controllers\Admin\ScoreController;
use  App\Http\Controllers\Admin\RechargeController;
use  App\Http\Controllers\Admin\BlockController;
use  App\Http\Controllers\Admin\ConfigController;
use  App\Http\Controllers\Admin\StoreController;
use  App\Http\Controllers\Admin\OrderController;
use  App\Http\Controllers\Admin\HomeController;
use  App\Http\Controllers\Admin\ExpController;
use  App\Http\Controllers\Admin\MoneyController;
use  App\Http\Controllers\Admin\CoinController;
use  App\Http\Controllers\Admin\SupplyController;

Route::middleware(['admin.sign'])->prefix("hack")->group(function () {
    Route::post("login", [LoginController::class, "login"]);
    Route::middleware(['admin.token'])->group(function () {
        Route::get("menuList", [LoginController::class, "menuList"]);
        Route::post("changePwd", [LoginController::class, "changePassword"]);
        Route::post("uploadOne", [LoginController::class, "uploadOne"]);

        Route::middleware(['admin.auth'])->group(function () {
            Route::get("registerLine",[HomeController::class,"registerLine"]);
            Route::get("dealLine",[HomeController::class,"dealLine"]);

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
            Route::post("banUser", [UserController::class, "banUser"])->middleware(["admin.response"]);
            Route::get("teamTree", [UserController::class, "teamTree"]);
            Route::get("userAuthList", [UserController::class, "userAuthList"]);
            Route::post("editUserAuth", [UserController::class, "editUserAuth"])->middleware(["admin.response"]);
            Route::post("editUser", [UserController::class, "editUser"])->middleware(["admin.response"]);

            Route::get("categoryList", [CategoryController::class, "categoryList"]);
            Route::post("addCategory", [CategoryController::class, "addCategory"])->middleware(["admin.response"]);
            Route::post("editCategory", [CategoryController::class, "editCategory"])->middleware(["admin.response"]);
            Route::post("delCategory", [CategoryController::class, "delCategory"])->middleware(["admin.response"]);

            Route::post("addSpu", [SpuController::class, "addSpu"])->middleware(["admin.response"]);
            Route::get("spuList", [SpuController::class, "spuList"]);
            Route::get("spuDetail", [SpuController::class, "spuDetail"]);
            Route::post("editSpu", [SpuController::class, "editSpu"])->middleware(['admin.response']);
            Route::get("shopSpuList", [SpuController::class, "shopSpuList"]);
            Route::post("editSaleable", [SpuController::class, "editSaleable"])->middleware(['admin.response']);

            Route::get("scoreTypes", [ScoreController::class, "scoreTypes"]);
            Route::get("scoreList", [ScoreController::class, "scoreList"]);
            Route::get("asacLogType", [ScoreController::class, "asacLogType"]);
            Route::get("asacLog", [ScoreController::class, "asacLog"]);

            Route::get("rechargeList", [RechargeController::class, "rechargeList"]);
            Route::get("withdrawList", [RechargeController::class, "withdrawList"]);
            Route::post("editWithdraw", [RechargeController::class, "editWithdraw"])->middleware(['admin.response']);

            Route::get("areaList", [UserController::class, "areaList"]);
            Route::get("performance", [UserController::class, "performance"]);
            Route::post("editIdentity", [UserController::class, "editIdentity"])->middleware(['admin.response']);

            Route::get("blockList", [BlockController::class, "blockList"]);
            Route::get("tradeList", [BlockController::class, "tradeList"]);
            Route::get("destroyList", [BlockController::class, "destroyList"]);
            Route::get("asacInfo", [BlockController::class, "asacInfo"]);
            Route::get("addressList", [BlockController::class, "addressList"]);
            Route::post("editAsac", [BlockController::class, "editAsac"])->middleware(['admin.response']);

            Route::get("getConfig", [ConfigController::class, "getConfig"]);
            Route::post("editConfig", [ConfigController::class, "editConfig"])->middleware(["admin.response"]);

            Route::get("storeList", [StoreController::class, "storeList"]);
            Route::get("storeReviewList", [StoreController::class, "storeReviewList"]);
            Route::post("editStore", [StoreController::class, "editStore"])->middleware(["admin.response"]);
            Route::post("editReview", [StoreController::class, "editReview"])->middleware(["admin.response"]);
            Route::post("addAmount", [StoreController::class, "addAmount"])->middleware(["admin.response"]);

            Route::post("applyList", [SupplyController::class, "supplyList"])->middleware(["admin.response"]);
            Route::post("apply", [SupplyController::class, "apply"])->middleware(["admin.response"]);

            Route::get("orderList", [OrderController::class, "orderList"]);
            Route::get("shopOrderList", [OrderController::class, "shopOrderList"]);
            Route::post("sendSku", [OrderController::class, "sendSku"])->middleware(['admin.response']);

            Route::get("expList",[ExpController::class,"expList"]);
            Route::get("expAll",[ExpController::class,"expAll"]);
            Route::post("editExp",[ExpController::class,"editExp"])->middleware(['admin.response']);
            Route::post("addExp",[ExpController::class,"addExp"])->middleware(['admin.response']);
            Route::post("delExp",[ExpController::class,"delExp"])->middleware(['admin.response']);

            Route::get("userMoneyList",[MoneyController::class,"userMoneyList"]);
            Route::get("moneyTradeList",[MoneyController::class,"moneyTradeList"]);
            Route::post("editUserMoney",[MoneyController::class,"editUserMoney"])->middleware(['admin.response']);

            Route::get("coinList",[CoinController::class,"coinList"]);
            Route::post("addCoin",[CoinController::class,"addCoin"])->middleware(['admin.response']);
            Route::post("editCoin",[CoinController::class,"editCoin"])->middleware(['admin.response']);
        });
    });
});

