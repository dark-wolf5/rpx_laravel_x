<?php

use App\Http\Controllers\MeetUpController;
use Illuminate\Support\Facades\Route;

Route::get('index', [MeetUpController::class, 'index'])->middleware('auth');
Route::get('show/{id}', [MeetUpController::class, 'show'])->middleware('auth');
Route::delete('', [MeetUpController::class, 'destroy'])->middleware('auth');
Route::post('', [MeetUpController::class, 'createMeetUp'])->middleware('auth');
Route::put('accept-invitation/{meet_up_invitations:uuid}', [MeetUpController::class, 'acceptInvitation']);
Route::put('{id}', [MeetUpController::class, 'editMeetUp'])->middleware('auth');

Route::get('invites/show/{meet_up_invitations:uuid}', [MeetUpController::class, 'showMui']);
