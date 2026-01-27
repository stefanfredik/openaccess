<?php

use Illuminate\Support\Facades\Route;
use Modules\Cpe\Http\Controllers\CpeController;

Route::middleware(['auth', 'verified'])->prefix('pendataan')->group(function () {
    Route::resource('cpes', CpeController::class)->names('cpe');
});
