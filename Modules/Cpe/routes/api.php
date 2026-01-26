<?php

use Illuminate\Support\Facades\Route;
use Modules\Cpe\Http\Controllers\CpeController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('cpes', CpeController::class)->names('cpe');
});
