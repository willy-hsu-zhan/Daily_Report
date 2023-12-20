<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthorizedController;
use App\Http\Controllers\SocialiteController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['web']], function () {
    //google登入
    Route::get('/', [SocialiteController::class, 'googleLoginCallback']);
    // //google登入
    // Route::get('/google-login', [SocialiteController::class, 'googleLogin'])->name('googleLogin');
    //google登入回傳
    Route::get('/auth/google/callback', [SocialiteController::class, 'googleLoginCallback'])->name('googleLoginCallback');
    //首頁
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    //未授權頁面
    Route::get('/unauthorized', [AuthorizedController::class, 'index'])->name('unauthorized');
});
