<?php

use Illuminate\Support\Facades\Route;
use Modules\Server\Http\Controllers\ServerController;

Route::middleware(['auth', 'verified'])->prefix('pendataan')->group(function () {
    Route::resource('servers', ServerController::class)->names('server');
});
