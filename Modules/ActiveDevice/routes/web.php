<?php

use Illuminate\Support\Facades\Route;
use Modules\ActiveDevice\Http\Controllers\OltController;
use Modules\ActiveDevice\Http\Controllers\OntController;
use Modules\ActiveDevice\Http\Controllers\SwitchController;
use Modules\ActiveDevice\Http\Controllers\RouterController;
use Modules\ActiveDevice\Http\Controllers\AccessPointController;
use Modules\ActiveDevice\Http\Controllers\DeviceConnectionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('topology')->name('active-device.')->group(function () {
        Route::get('/', [DeviceConnectionController::class, 'topology'])->name('topology');
        Route::post('positions', [DeviceConnectionController::class, 'updatePositions'])->name('topology.positions.update');
        Route::get('details', [DeviceConnectionController::class, 'details'])->name('topology.details');
        Route::post('connections', [DeviceConnectionController::class, 'store'])->name('connections.store');
        Route::delete('connections/{deviceConnection}', [DeviceConnectionController::class, 'destroy'])->name('connections.destroy');
    });
    Route::prefix('pendataan/active-devices')->name('active-device.')->group(function () {
        Route::resource('olt', OltController::class);
        Route::resource('ont', OntController::class);
        Route::resource('switch', SwitchController::class);
        Route::resource('router', RouterController::class);
        Route::resource('access-point', AccessPointController::class);
    });
});
