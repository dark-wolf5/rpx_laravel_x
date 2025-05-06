<?php

use App\Http\Controllers\CustomerManager;
use Illuminate\Support\Facades\Route;

Route::get('index', [CustomerManager::class, 'index'])->middleware('auth');
Route::post('sms', [CustomerManager::class, 'sms'])->middleware('auth');
Route::get('sms-group-list', [CustomerManager::class, 'smsGroupList'])->middleware('auth');
Route::post('email', [CustomerManager::class, 'email'])->middleware('auth');
// Route::post('send-promotion', [CustomerManager::class, 'sendPromotion'])->middleware('auth');
Route::get('email-group-list', [CustomerManager::class, 'emailGroupList'])->middleware('auth');
Route::get('get-promotion', [CustomerManager::class, 'getPromotion'])->middleware('auth');
