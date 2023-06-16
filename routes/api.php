<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserCompleteController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AsacController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\AreaController;
use App\Http\Controllers\Api\ZoneController;
use App\Http\Controllers\Api\ScoreController;
use App\Http\Controllers\Api\SpuController;
use App\Http\Controllers\Api\SkuController;
use App\Http\Controllers\Api\ShopController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\DownController;
use App\Http\Controllers\Api\UserMoneyController;
use App\Http\Controllers\Api\PayController;

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
    Route::post('invite', 'invite');
    //发送登录验证码
    Route::post('send_message', 'send_message');
    Route::post('seed_message_forget', 'seed_message_forget');
    Route::group(['middleware' => 'auth'], function () {
        Route::post('logout', 'logout');
        Route::post('refresh', 'refresh');
        Route::post('me', 'me');
        //修改用户密码
        Route::post('change_password', 'change');
        //设置通证密码
        Route::post('change_sale_password', 'change_sale_password');
        //完善个人信息
        Route::post('complete_self', 'complete_self');
        //注销账号
        Route::post('del_owner', 'del_self');
        //我的团队
        Route::post('teams', 'teams');
        //获取私钥
        Route::post('get_private_key', 'get_private_key');
        //通证地址
        Route::post('asac_url', 'asac_url');
        //池子数量
        Route::post('get_coin', 'get_bold_coin');
        //修改交易密码验证短信
        Route::post('send_sale_code', 'send_sale_code');
    });
    Route::middleware('auth')->group(function () {
        //上传身份证
        Route::post('upload_card', [UploadController::class, 'uploadCard']);
        //头像
        Route::post('upload_header', [UploadController::class, 'uploadHeader']);
        //提交实名认证
        Route::post('identity', [UserCompleteController::class, 'identity']);
        //我的实名认证详情
        Route::post('identity_info', [UserCompleteController::class, 'identityInfo']);
        //重新提交实名认证
        Route::post('identity_update', [UserCompleteController::class, 'identityUpdate']);
        //地址列表
        Route::post('get_address', [AddressController::class, 'get_Address']);
        //新增地址
        Route::post('add_address', [AddressController::class, 'create_address']);
        //删除地址
        Route::post('del_address', [AddressController::class, 'del_address']);
        //编辑地址
        Route::post('edit_address', [AddressController::class, 'update_address']);
        //获取默认地址
        Route::post('def_address', [AddressController::class, 'get_def_address']);
        //设为默认地址
        Route::post('default_address', [AddressController::class, 'set_def']);
        //申请商家
        Route::post('add_store', [StoreController::class, 'add_store']);
        //查看我的申请
        Route::post('store_info', [StoreController::class, 'get_store']);
        //附近商家
        Route::post('get_near_store', [StoreController::class, 'get_near_store']);
        //修改申请
        Route::post('store_update', [StoreController::class, 'update']);
        //
        Route::post('coin_info', [AsacController::class, 'coin_info']);
        //流动池 於挖池记录
        Route::post('get_flue', [AsacController::class, 'get_flue']);
        //提现
        Route::post('withdraw', [AsacController::class, 'withdraw']);
        //转账
        Route::post('transfer', [AsacController::class, 'change']);
    });
    //区块浏览
    Route::group(['prefix' => 'asac'], function () {
        Route::post('index', [AsacController::class, 'index']);
        Route::post('login', [AsacController::class, 'asac_login']);
        Route::post('search', [AsacController::class, 'search']);
        Route::post('info', [AsacController::class, 'info']);
        Route::post('block_info', [AsacController::class, 'block_info']);
        Route::post('blocks', [AsacController::class, 'blocks']);
        Route::post('owners', [AsacController::class, 'owners']);
        Route::post('get_notices', [AsacController::class, 'get_notices']);
        Route::post('get_destory', [AsacController::class, 'get_destory']);
        Route::post('excharge', [AsacController::class, 'excharge']);

    });
    Route::middleware([])->group(function () {
        //获取新闻资讯
        Route::post('news', [NewsController::class, 'getNews']);
        Route::post('news_info', [NewsController::class, 'getInfo']);
        //获取轮播图
        Route::post('banners', [BannerController::class, 'getBanners']);
        Route::post('create', [BannerController::class, 'create']);
        //获取公告

        Route::post('notices', [NoticeController::class, 'getNotices']);
        Route::post('notice_info', [NoticeController::class, 'getInfo']);

        //获取地址
        Route::post('areas', [AreaController::class, 'get_area']);

        Route::get('update', [DownController::class, 'update']);
        Route::post('download', [DownController::class, 'download']);
        Route::post('url_asac', [DownController::class, 'url_asac']);

    });
    //order
    Route::group(['middleware' => 'auth'], function () {
        //新增订单
        Route::post('add_order', [OrderController::class, 'create_order']);
        //撤单
        Route::post('del_order', [OrderController::class, 'del_order']);
        //支付
        Route::post('pay_order', [OrderController::class, 'pay_order']);
        //签收
        Route::post('sign_order', [OrderController::class, 'sign_order']);
        //订单列
        Route::post('order_list', [OrderController::class, 'order_list']);
        //订单详情
        Route::post('order_info', [OrderController::class, 'info']);
        //申请换货
        Route::post('apply_revoke', [OrderController::class, 'apply_revoke']);
        //换货列表
        Route::post('revokes', [OrderController::class, 'revokes']);
        //取消换货
        Route::post('del_revoke', [OrderController::class, 'del_revoke']);
        //钱包明细
        Route::post('list', [WalletController::class, 'list']);
        //充值记录
        Route::post('coin_log', [WalletController::class, 'coin_log']);
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::post('welfare', [ZoneController::class, 'welfareZone']);
        Route::post('happiness', [ZoneController::class, 'happinessZone']);
        Route::post('consume', [ZoneController::class, 'consumeZone']);
        Route::post('preferred', [ZoneController::class, 'preferredZone']);
        //积分
        Route::post('green_score', [ScoreController::class, 'get_green_sore']);
        Route::post('consume_score', [ScoreController::class, 'get_sale_sore']);
        Route::post('ticket_score', [ScoreController::class, 'get_ticket_sore']);
        Route::post('lucky_score', [ScoreController::class, 'get_lucky_sore']);
        //商品
        //搜索商品页面
        Route::post('search', [SpuController::class, 'search']);
        //获取asac现价
        Route::post('get_price', [SpuController::class, 'get_last_price']);
        //搜索关键词
        Route::post('get_search_keys', [SpuController::class, 'get_search_keys']);
        //商品详情
        Route::post('spu_info', [SpuController::class, 'get_spu_first']);
        Route::post('category', [SpuController::class, 'get_category']);

        Route::post('get_store_category', [SpuController::class, 'get_store_category']);

        //切换商品
        Route::post('get_product', [SkuController::class, 'get_product']);

        //店铺详情
        Route::post('get_store_info', [StoreController::class, 'store']);

        //购物车
        Route::post('add_cart', [ShopController::class, 'add_shop_car']);
        //分类
        Route::post('category', [ShopController::class, 'categoryList']);

        Route::post('carts', [ShopController::class, 'show_shop_car']);

        Route::post('del_carts', [ShopController::class, 'del_from_car']);
    });
    Route::group(['middleware' => 'auth'], function () {
        //充值申请
        Route::post('apply_money', [UserMoneyController::class, 'apply']);
        //转账申请
        Route::post('trade_money', [UserMoneyController::class, 'trade']);
        //余额交易记录
        Route::post('money_trades', [UserMoneyController::class, 'money_trades']);

        Route::post('coins', [UserMoneyController::class, 'get_coins']);
    });

    Route::group([],function(){
        Route::post('to_pay',[PayController::class,'to_pey']);
    });
});





