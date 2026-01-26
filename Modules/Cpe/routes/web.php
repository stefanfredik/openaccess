<?php

use Illuminate\Support\Facades\Route;
use Modules\Cpe\Http\Controllers\CpeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('cpe', CpeController::class);
});
