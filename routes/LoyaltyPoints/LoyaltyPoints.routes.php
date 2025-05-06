<?php

use App\Http\Controllers\LoyaltyPointBalance\LoyaltyPointBalanceController;
use App\Http\Controllers\LoyaltyPointBalance\LoyaltyPointBalanceAggregatorController;
use Illuminate\Support\Facades\Route;

Route::post('show', [LoyaltyPointBalanceAggregatorController::class, 'show'])->middleware('auth');
Route::get('ledger', [LoyaltyPointBalanceController::class, 'index'])->middleware('auth');
Route::post('set-lp-rate', [LoyaltyPointBalanceController::class, 'setLpRate'])->middleware('auth');
