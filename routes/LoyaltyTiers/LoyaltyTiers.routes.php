<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoyaltyTierController;

Route::get('index', [LoyaltyTierController::class, 'index'])->middleware('auth');
Route::post('store', [LoyaltyTierController::class, 'store'])->middleware('auth');
Route::patch('update/{loyaltyTier}', [LoyaltyTierController::class, 'update'])->middleware('auth');
Route::delete('destroy/{loyaltyTier}', [LoyaltyTierController::class, 'destroy'])->middleware('auth');
