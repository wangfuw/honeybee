<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProductController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(UserController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::group(['middleware' => 'auth'], function () {
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::get('me', 'me');
    });
});

Route::group([],function (){
    //获取新闻资讯
    Route::post('news',[NewsController::class,'getNews']);
    Route::post('news_info',[NewsController::class,'getInfo']);
    //获取轮播图
    Route::post('banners',[BannerController::class,'getBanners']);
    Route::post('create',[BannerController::class,'create']);
    //获取公告

    Route::post('notices',[NoticeController::class,'getNotices']);
    Route::post('notice_info',[NoticeController::class,'getInfo']);
});



