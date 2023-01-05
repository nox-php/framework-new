<?php

namespace Nox\Framework\Auth\Providers;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Nox\Framework\Auth\Http\Responses\LoginResponse;
use Nox\Framework\Auth\Http\Responses\LogoutResponse;
use SocialiteProviders\Discord\DiscordExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->booting(function () {
            Event::listen(SocialiteWasCalled::class, [DiscordExtendSocialite::class, 'handle']);
        });

        $this->app->bind(LoginResponseContract::class, LoginResponse::class);
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../../routes/auth.php');
    }

    public function provides(): array
    {
        return [
            LoginResponse::class,
            LogoutResponse::class,
        ];
    }
}
