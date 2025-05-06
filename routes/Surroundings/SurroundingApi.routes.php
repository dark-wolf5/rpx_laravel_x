<?php

use App\Http\Controllers\Surrounding\SurroundingsController;
use Illuminate\Support\Facades\Route;

Route::get('get-classifications', [SurroundingsController::class, 'getClassifications']);

Route::post('search-events', [SurroundingsController::class, 'searchEvents']);
Route::post('search-businesses', [SurroundingsController::class, 'searchBusinesses']);
Route::post('pull-info-object', [SurroundingsController::class, 'pullInfoObject']);
Route::post('get-event', [SurroundingsController::class, 'getEvent']);
Route::post('get-community-members', [SurroundingsController::class, 'getSbCommunityMembers']);
