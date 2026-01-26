<?php

use Illuminate\Support\Facades\Route;
use Modules\Pop\Http\Controllers\PopController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('pops', PopController::class)->names('pop');
});
