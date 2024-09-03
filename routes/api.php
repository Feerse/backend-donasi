<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DonationController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\SliderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);

Route::get('/category', [CategoryController::class, 'index']);
Route::get('/category/{slug}', [CategoryController::class, 'show']);
Route::get('/categoryHome', [CategoryController::class, 'categoryHome']);

Route::get('/campaign', [CampaignController::class, 'index']);
Route::get('/campaign/{slug}', [CampaignController::class, 'show']);

Route::get('/slider', [SliderController::class, 'index']);

// `middleware('auth:api')` artinya route ini hanya bisa diakses jika user/donatur sudah memiliki akses otentikasi API terlebih dahulu
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth:api');
Route::post('/profile', [ProfileController::class, 'update'])->middleware('auth:api');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth:api');

Route::get('/donation', [DonationController::class, 'index'])->middleware('auth:api');
Route::post('/donation', [DonationController::class, 'store'])->middleware('auth:api');
Route::post('/donation/notification', [DonationController::class, 'notificationHandler']);
