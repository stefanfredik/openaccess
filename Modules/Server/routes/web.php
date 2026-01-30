<?php

use Illuminate\Support\Facades\Route;
use Modules\Server\Http\Controllers\RackController;
use Modules\Server\Http\Controllers\ServerController;

Route::middleware(['auth', 'verified'])->prefix('pendataan')->group(function () {
    Route::resource('servers', ServerController::class)->names('server');

    Route::post('servers/{server}/racks', [RackController::class, 'store'])->name('server.racks.store');
    Route::put('racks/{rack}', [RackController::class, 'update'])->name('racks.update');
    Route::delete('racks/{rack}', [RackController::class, 'destroy'])->name('racks.destroy');

    Route::get('servers/{server}/available-devices', [RackController::class, 'availableDevices'])->name('server.available-devices');
    Route::post('racks/{rack}/mount', [RackController::class, 'mountDevice'])->name('racks.mount');
    Route::delete('rack-contents/{content}', [RackController::class, 'unmountDevice'])->name('racks.unmount');
});
