<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\api\UploadController;
use App\Http\Controllers\api\UserCompleteController;
use App\Http\Controllers\Api\AddressController;
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
        Route::post('change_password', 'change');
        //设置通证密码
        Route::post('change_sale_password', 'change_sale_password');
        //完善个人信息
        Route::post('complete_self','complete_self');
        //注销账号
        Route::post('del_owner','del_self');

    });
    Route::middleware('auth')->group(function (){
        //上传身份证
        Route::post('upload_card',[UploadController::class,'uploadCard']);
        //头像
        Route::post('upload_header',[UploadController::class,'uploadHeader']);
        //提交实名认证
        Route::post('identity',[UserCompleteController::class,'identity']);
        //地址列表
        Route::post('get_address',[AddressController::class,'get_Address']);
        //新增地址
        Route::post('add_address',[AddressController::class,'create_address']);
        //删除地址
        Route::post('del_address',[AddressController::class,'del_address']);
        //编辑地址
        Route::post('edit_address',[AddressController::class,'update_address']);
        //设为默认地址
        Route::post('default_address',[AddressController::class,'set_def']);
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
});





