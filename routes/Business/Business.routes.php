<?php

use App\Http\Controllers\Business\BusinessController;

use Illuminate\Support\Facades\Route;

Route::post('verify', [BusinessController::class, 'verify'])->middleware('auth');

Route::post('save-business', [BusinessController::class, 'saveBusiness'])->middleware('auth');

Route::post('google', [BusinessController::class, 'getGooglePlacesToEat'])->middleware('auth');

Route::post('show', [BusinessController::class, 'show']);