<?php

use Illuminate\Support\Facades\Route;
use Modules\Server\Http\Controllers\ServerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('servers', ServerController::class)->names('server');
});
