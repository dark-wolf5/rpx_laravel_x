<?php

use App\Http\Controllers\MyFavorites\MyFavoritesController;
use Illuminate\Support\Facades\Route;

Route::post('index', [MyFavoritesController::class, 'getFavorites'])->middleware('auth');
Route::post('save-favorite', [MyFavoritesController::class, 'saveFavorite'])->middleware('auth');
Route::post('is-a-favorite', [MyFavoritesController::class, 'isAFavorite'])->middleware('auth');
Route::delete('remove-favorite', [MyFavoritesController::class, 'removeFavorite'])->middleware('auth');
