<?php

use Illuminate\Support\Facades\Route;
use Modules\PassiveDevice\Http\Controllers\CableController;
use Modules\PassiveDevice\Http\Controllers\JointBoxController;
use Modules\PassiveDevice\Http\Controllers\OdfController;
use Modules\PassiveDevice\Http\Controllers\OdpController;
use Modules\PassiveDevice\Http\Controllers\PoleController;
use Modules\PassiveDevice\Http\Controllers\SlackController;
use Modules\PassiveDevice\Http\Controllers\SplicingPointController;
use Modules\PassiveDevice\Http\Controllers\SplitterController;
use Modules\PassiveDevice\Http\Controllers\TowerController;

Route::middleware(['auth', 'verified'])->prefix('pendataan/passive-devices')->name('passive-device.')->group(function () {
    Route::resource('odf', OdfController::class);
    Route::resource('pole', PoleController::class);
    Route::resource('odps', OdpController::class);
    Route::resource('cable', CableController::class);
    Route::resource('joint-box', JointBoxController::class);
    Route::resource('splicing-point', SplicingPointController::class);
    Route::resource('splitter', SplitterController::class);
    Route::resource('slack', SlackController::class);
    Route::resource('tower', TowerController::class);
});
