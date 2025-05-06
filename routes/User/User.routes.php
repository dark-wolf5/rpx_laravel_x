<?php

use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::post('sign-up', [UserController::class, 'signUp']);
Route::post('login', [UserController::class, 'logIn']);
Route::post('logout', [UserController::class, 'logOut'])->middleware('auth');
Route::post('check-user-auth', [UserController::class, 'checkAuth']);
Route::post('close-browser', [UserController::class, 'closeBrowser'])->middleware('auth');
Route::post('settings', [UserController::class, 'settings'])->middleware('auth');
Route::delete('deactivate', [UserController::class, 'deactivate'])->middleware('auth');
Route::put('update', [UserController::class, 'update'])->middleware('auth');
Route::get('get-user', [UserController::class, 'getUser'])->middleware('auth');
Route::put('complete-pass-reset', [UserController::class, 'completePassReset']);
Route::put('change-password', [UserController::class, 'changePassword'])->middleware('auth');

Route::post('unique-email', [UserController::class, 'uniqueEmail']);
Route::post('send-code', [UserController::class, 'sendCode']);
Route::post('send-pass-email', [UserController::class, 'sendPassEmail']);
Route::post('check-confirm', [UserController::class, 'checkConfirm']);
Route::post('create-user', [UserController::class, 'createUser'])->middleware('auth');;

Route::post('business-membership', [UserController::class, 'businessMembership']);
Route::post('membership-status', [UserController::class, 'membershipStatus']);
Route::post('cancel-membership', [UserController::class, 'cancelMembership'])->middleware('auth');

Route::put('update_place', [UserController::class, 'updatePlace'])->middleware('auth');
Route::put('update-sms-opt-in', [UserController::class, 'updateSmsOptIn'])->middleware('auth');
