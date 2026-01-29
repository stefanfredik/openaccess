<?php

use Illuminate\Support\Facades\Route;
use Modules\ActiveDevice\Http\Controllers\AccessPointController;
use Modules\ActiveDevice\Http\Controllers\DeviceConnectionController;
use Modules\ActiveDevice\Http\Controllers\DeviceInterfaceController;
use Modules\ActiveDevice\Http\Controllers\OltController;
use Modules\ActiveDevice\Http\Controllers\OntController;
use Modules\ActiveDevice\Http\Controllers\RouterController;
use Modules\ActiveDevice\Http\Controllers\SwitchController;

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

        Route::post('service-ports', [\Modules\ActiveDevice\Http\Controllers\ServicePortController::class, 'store'])->name('service-ports.store');
        Route::delete('service-ports/{servicePort}', [\Modules\ActiveDevice\Http\Controllers\ServicePortController::class, 'destroy'])->name('service-ports.destroy');

        Route::get('device-interfaces/list', [DeviceInterfaceController::class, 'getByDevice'])->name('interfaces.list');
        Route::post('device-interfaces', [DeviceInterfaceController::class, 'store'])->name('interfaces.store');
        Route::patch('device-interfaces/{interface}', [DeviceInterfaceController::class, 'update'])->name('interfaces.update');
        Route::delete('device-interfaces/{interface}', [DeviceInterfaceController::class, 'destroy'])->name('interfaces.destroy');
    });
});
