<?php

use Illuminate\Support\Facades\Route;
use Modules\Map\Http\Controllers\MapController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('maps/data', [MapController::class, 'data'])->name('map.data');
    Route::post('maps/relocate', [MapController::class, 'relocate'])->name('map.relocate');
    Route::get('maps', [MapController::class, 'index'])->name('map.index');
});
