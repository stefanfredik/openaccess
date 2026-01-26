<?php

use Illuminate\Support\Facades\Route;
use Modules\PassiveDevice\Http\Controllers\PassiveDeviceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('passivedevices', PassiveDeviceController::class)->names('passivedevice');
});
