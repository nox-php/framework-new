<?php

use Illuminate\Support\Facades\Route;
use Nox\Framework\Updater\Http\Controllers\NoxUpdaterController;

Route::middleware([
    ...config('filament.middleware.base'),
    ...config('filament.middleware.auth'),
])
    ->prefix(config('filament.path'))
    ->name('nox.updater')
    ->get('/nox/updater/{version}', NoxUpdaterController::class);
