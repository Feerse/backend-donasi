<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->middleware('guest');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.dashboard.index');

Route::prefix('admin')->group(function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::resource('/category', CategoryController::class, [
            'as' => 'admin'
        ]);
        Route::resource('/campaign', CampaignController::class, [
            'as' => 'admin'
        ]);
        Route::get('/donatur', [DonaturController::class, 'index'])->name('admin.donatur.index');
        Route::get('/donation', [DonationController::class, 'index'])->name('admin.donation.index');
        Route::get('/donation/filter', [DonationController::class, 'filter'])->name('admin.donation.filter');
        Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile.index');
        Route::resource('/slider', SliderController::class, [
            'except' => ['show', 'create', 'edit', 'update'],
            'as' => 'admin',
        ]);
    });
});
