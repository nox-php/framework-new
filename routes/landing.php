<?php

use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    Filament::registerTheme(mix('css/nox.css', 'nox'));

    return view('nox::landing', [
        'title' => config('app.name', 'Nox')
    ]);
})->middleware('web');
