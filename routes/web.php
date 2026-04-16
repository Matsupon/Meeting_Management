<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

// Login
Route::get('/',      [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'handleLogin'])->middleware('throttle:6,1')->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// Register
Route::get('/register', [\App\Http\Controllers\RegisterController::class, 'showRegister'])->name('register');
Route::post('/register', [\App\Http\Controllers\RegisterController::class, 'handleRegister'])->middleware('throttle:6,1')->name('register.post');

// Events API (protected, uses session from web middleware)
Route::prefix('api')->middleware(['auth.session', 'throttle:60,1'])->group(function () {
    Route::get('/events',        [EventController::class, 'index']);
    Route::get('/events/ical',   [EventController::class, 'ical']);
    Route::post('/events',       [EventController::class, 'store']);
    Route::delete('/events/{id}',[EventController::class, 'destroy']);
});

// Dashboard (protected by auth.session middleware)
Route::middleware('auth.session')->group(function () {
    Route::get('/dashboard',     [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedule-list', [DashboardController::class, 'scheduleList'])->name('schedule-list');
    Route::post('/export-summary', [DashboardController::class, 'exportSummary'])->name('export.summary');

    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/upload-picture', [App\Http\Controllers\ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-picture');
    Route::post('/profile/add-user', [App\Http\Controllers\ProfileController::class, 'addUser'])->name('profile.add-user');
    Route::delete('/profile/delete-user/{id}', [App\Http\Controllers\ProfileController::class, 'deleteUser'])->name('profile.delete-user');
});
