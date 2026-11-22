<?php

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\InternController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Enrollment (T4): a signed-in manager creates interns, sites and assignments.
    Route::resource('interns', InternController::class)->only(['index', 'store']);
    Route::resource('sites', SiteController::class)->only(['index', 'store']);
    Route::resource('assignments', AssignmentController::class)->only(['index', 'store']);
});

require __DIR__.'/settings.php';
