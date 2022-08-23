<?php

use Illuminate\Support\Facades\Route;
use MGK\Auth\Http\Controllers\AuthController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/auth/redirect', [AuthController::class, 'auth'])->name('mgk-auth.redirect');
    Route::get('/auth/callback', [AuthController::class, 'callback'])->name('mgk-auth.callback');
});
