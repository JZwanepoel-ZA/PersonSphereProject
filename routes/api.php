<?php

use App\Http\Controllers\InterestsController;
use App\Http\Controllers\LanguagesController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

Route::apiResource('people', PersonController::class);
Route::get('languages', [LanguagesController::class, 'index']);
Route::get('interests', [InterestsController::class, 'index']);
