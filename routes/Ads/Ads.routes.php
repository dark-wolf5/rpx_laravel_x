<?php

use App\Http\Controllers\Ads\AdsController;
use Illuminate\Support\Facades\Route;

Route::post('header-banner', [AdsController::class, 'headerBanner']);
Route::post('footer-banner', [AdsController::class, 'footerBanner']);
Route::post('get-by-uuid', [AdsController::class, 'getByUuid']);
Route::post('featured-ad-list', [AdsController::class, 'featuredAdList']);
Route::post('save-payment', [AdsController::class, 'savePayment']);
Route::post('upload-media', [AdsController::class, 'uploadMedia'])->middleware('auth');
Route::post('create', [AdsController::class, 'create'])->middleware('auth');
Route::post('update', [AdsController::class, 'updateModel'])->middleware('auth');
Route::post('delete', [AdsController::class, 'deleteModel'])->middleware('auth');
Route::post('index', [AdsController::class, 'index'])->middleware('auth');
