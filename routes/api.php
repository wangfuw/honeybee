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
use App\Http\Controllers\Api\AsacController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\AreaController;
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
    Route::post('forget_password', 'forget_password');
    Route::group(['middleware' => 'auth'], function () {
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::post('me', 'me');
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
        //申请商家
        Route::post('add_store',[StoreController::class,'add_store']);
        //查看我的申请
        Route::post('store_info',[StoreController::class,'get_store']);
        //修改申请
        Route::post('store_update',[StoreController::class,'update']);
    });
    Route::group(['prefix'=>'asac'],function (){
       Route::post('index',[AsacController::class,'index']);
       Route::post('login',[AsacController::class,'asac_login']);
       Route::post('search',[AsacController::class,'search']);
       Route::post('info',[AsacController::class,'info']);
       Route::post('block_info',[AsacController::class,'block_info']);
       Route::post('blocks',[AsacController::class,'blocks']);
       Route::post('owners',[AsacController::class,'owners']);
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

        //获取地址
        Route::post('areas',[AreaController::class,'get_area']);
    });
});





