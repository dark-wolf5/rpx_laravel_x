<?php

use App\Http\Controllers\RedeemableItems\RedeemableItemsController;
use App\Http\Controllers\LoyaltyPointLedger\LoyaltyPointLedgerController;
use App\Http\Controllers\LoyaltyPointBalance\LoyaltyPointBalanceController;
use Illuminate\Support\Facades\Route;

Route::post('create', [RedeemableItemsController::class, 'create'])->middleware('auth');
Route::post('redeem', [RedeemableItemsController::class, 'redeem'])->middleware('auth');

Route::get('index', [RedeemableItemsController::class, 'index'])->middleware('auth');
Route::get('lp-redeemed', [RedeemableItemsController::class, 'lpRedeemed'])->middleware('auth');
Route::get('ledger', [LoyaltyPointLedgerController::class, 'index'])->middleware('auth');
Route::get('balance-list', [LoyaltyPointBalanceController::class, 'balanceList'])->middleware('auth');
Route::post('promoter-lp', [LoyaltyPointBalanceController::class, 'promoterLp'])->middleware('auth');
Route::post('scan-receipt', [RedeemableItemsController::class, 'scanReceipt'])->middleware('auth');
