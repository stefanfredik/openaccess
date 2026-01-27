<?php

use Illuminate\Support\Facades\Route;
use Modules\Area\Http\Controllers\AreaController;

Route::middleware(['auth', 'verified'])->prefix('pendataan')->group(function () {
    Route::resource('areas', AreaController::class)->names('area');
    
    // API routes for dropdowns
    Route::get('api/regencies', [AreaController::class, 'regencies'])->name('area.regencies');
    Route::get('api/districts', [AreaController::class, 'districts'])->name('area.districts');
    Route::get('api/villages', [AreaController::class, 'villages'])->name('area.villages');
});
