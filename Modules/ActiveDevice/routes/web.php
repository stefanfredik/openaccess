<?php

use Illuminate\Support\Facades\Route;
use Modules\ActiveDevice\Http\Controllers\OltController;
use Modules\ActiveDevice\Http\Controllers\OntController;
use Modules\ActiveDevice\Http\Controllers\SwitchController;
use Modules\ActiveDevice\Http\Controllers\RouterController;
use Modules\ActiveDevice\Http\Controllers\AccessPointController;
use Modules\ActiveDevice\Http\Controllers\DeviceConnectionController;

Route::middleware(['auth', 'verified'])->prefix('active-devices')->name('active-device.')->group(function () {
    Route::get('topology', [DeviceConnectionController::class, 'topology'])->name('topology');
    Route::resource('olt', OltController::class);
    Route::resource('ont', OntController::class);
    Route::resource('switch', SwitchController::class);
    Route::resource('router', RouterController::class);
    Route::resource('access-point', AccessPointController::class);
    Route::post('connections', [DeviceConnectionController::class, 'store'])->name('connection.store');
    Route::delete('connections/{connection}', [DeviceConnectionController::class, 'destroy'])->name('connection.destroy');
    Route::post('topology/positions', [DeviceConnectionController::class, 'updatePositions'])->name('topology.positions.update');
    Route::get('topology/details', [DeviceConnectionController::class, 'details'])->name('topology.details');
});
