<?php

use App\Http\Controllers\PromoterDeviceAlternatorController;
use Illuminate\Support\Facades\Route;

Route::get('surrounding-business', [PromoterDeviceAlternatorController::class, 'retrieveBusinessList'])->middleware('auth');
Route::post('update-location', [PromoterDeviceAlternatorController::class, 'updateDeviceLocation'])->middleware('auth');
