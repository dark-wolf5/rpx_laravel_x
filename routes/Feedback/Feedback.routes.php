<?php

use App\Http\Controllers\Feedback\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::post('store', [FeedbackController::class, 'store'])->middleware('auth');
Route::patch('update/{feedback}', [FeedbackController::class, 'update'])->middleware('auth');
Route::get('show/{feedback}', [FeedbackController::class, 'show'])->middleware('auth');
Route::get('index', [FeedbackController::class, 'index'])->middleware('auth');
