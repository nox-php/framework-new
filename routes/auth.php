<?php

use Illuminate\Support\Facades\Route;
use Nox\Framework\Auth\Http\Controllers\DiscordController;

Route::middleware('web')->group(static function () {
    Route::middleware('guest')->group(static function () {
        $loginRoute = config('nox.auth.routes.login');
        if ($loginRoute !== null) {
            Route::get($loginRoute, static function () {
                return redirect()->route('auth.discord.redirect');
            });
        }

        Route::prefix(config('filament.path'))->get('/login', static function () {
            return redirect()->route('auth.discord.redirect');
        })->name('filament.auth.login');

        Route::name('auth.')->group(function () {
            $discordRoutes = config('nox.auth.discord.routes');
            $discordRedirectRoute = $discordRoutes['redirect'];
            $discordCallbackRoute = $discordRoutes['callback'];

            if ($discordRedirectRoute !== null) {
                Route::get($discordRedirectRoute, [DiscordController::class, 'redirect'])->name('discord.redirect');
            }

            if ($discordCallbackRoute !== null) {
                Route::get($discordCallbackRoute, [DiscordController::class, 'callback'])->name('discord.callback');
            }
        });
    });
});
