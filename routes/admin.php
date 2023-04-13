<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;

Route::middleware(['admin.sign'])->prefix("hack")->group(function () {
    Route::controller(LoginController::class)->group(function () {
        Route::post("login", 'login');
    });
});

