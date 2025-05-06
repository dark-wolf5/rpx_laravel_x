<?php

use App\Http\Controllers\FriendshipController;
use Illuminate\Support\Facades\Route;

Route::get('index', [FriendshipController::class, 'index'])->middleware('auth');
Route::post('request-friendship', [FriendshipController::class, 'requestFriendship'])->middleware('auth');
Route::delete('', [FriendshipController::class, 'deleteFriendship'])->middleware('auth');
Route::post('accept-friendship', [FriendshipController::class, 'acceptFriendship'])->middleware('auth');
Route::post('block-friendship', [FriendshipController::class, 'blockFriendship'])->middleware('auth');
Route::post('search-for-user', [FriendshipController::class, 'searchForUser'])->middleware('auth');
Route::post('search-for-friends', [FriendshipController::class, 'searchForFriends'])->middleware('auth');
Route::post('invite-contact', [FriendshipController::class, 'inviteContact'])->middleware('auth');
// Route::post('random-nearby', [FriendshipController::class, 'randomNearby'])->middleware('auth');
