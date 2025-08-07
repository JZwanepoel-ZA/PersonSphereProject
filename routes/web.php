<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('peopleManagement', function () {
        return Inertia::render('peopleManagement');
    })->name('peopleManagement');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
