<?php

use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// Events API Routes
Route::get('/events',        [EventController::class, 'index']);
Route::post('/events',       [EventController::class, 'store']);
Route::delete('/events/{id}',[EventController::class, 'destroy']);
