<?php

use App\Http\Controllers\UserLocation\UserLocationController;
use Illuminate\Support\Facades\Route;

Route::post('get-current-location', [UserLocationController::class, 'show'])->middleware('auth');
Route::post('save-current-location', [UserLocationController::class, 'saveCurrentLocation'])->middleware('auth');
Route::post('retrieve-surroundings', [UserLocationController::class, 'retrieveSurroundings']);
