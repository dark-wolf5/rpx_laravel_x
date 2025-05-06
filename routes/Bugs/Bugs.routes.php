<?php

use App\Http\Controllers\Bugs\BugsController;
use Illuminate\Support\Facades\Route;

Route::post('insert', [BugsController::class, 'insert']);
