<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AppVersionController;

Route::get('download', [AppVersionController::class, 'download']);
Route::put('check', [AppVersionController::class, 'check'])->middleware('auth');

?>
