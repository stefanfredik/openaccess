<?php

use Illuminate\Support\Facades\Route;
use Modules\Pop\Http\Controllers\PopController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('pops', PopController::class)->names('pop');
});
