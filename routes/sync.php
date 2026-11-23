<?php

use App\Http\Controllers\Sync\PullController;
use App\Http\Middleware\AuthenticateSyncToken;
use Illuminate\Support\Facades\Route;

/*
| Terminal sync routes. Registered with the /api prefix in bootstrap/app.php.
| These use a bearer token, not the manager session.
*/

Route::middleware(AuthenticateSyncToken::class)->group(function () {
    Route::post('sync/pull', PullController::class)->name('sync.pull');
});
