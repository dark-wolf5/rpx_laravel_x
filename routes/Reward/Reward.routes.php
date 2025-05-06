<?php

use App\Http\Controllers\Reward\RewardController;
use Illuminate\Support\Facades\Route;

Route::post('create', [RewardController::class, 'create'])->middleware('auth');
Route::post('claim', [RewardController::class, 'claim'])->middleware('auth');
Route::post('index', [RewardController::class, 'index']);
Route::post('update', [RewardController::class, 'update'])->middleware('auth');
Route::post('delete', [RewardController::class, 'delete'])->middleware('auth');
Route::post('upload-media', [RewardController::class, 'uploadMedia'])->middleware('auth');
