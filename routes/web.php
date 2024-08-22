<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::prefix('admin')->group(function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard.index');
        Route::resource('/category', CategoryController::class, [
            'as' => 'admin'
        ]);
        Route::resource('/campaign', CampaignController::class, [
            'as' => 'admin'
        ]);
        Route::get('/donatur', [DonaturController::class, 'index'])->name('admin.donatur.index');
    });
});
