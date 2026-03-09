<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/',      [LoginController::class, 'showLogin'])->name('login');
Route::post('/login',[LoginController::class, 'handleLogin'])->name('login.post');
Route::get('/logout',[LoginController::class, 'logout'])->name('logout');

// Dashboard (protected by session check inside controller)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

