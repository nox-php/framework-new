<?php

namespace Nox\Framework\Auth\Providers;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Nox\Framework\Auth\Http\Responses\LoginResponse;
use Nox\Framework\Auth\Http\Responses\LogoutResponse;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Auth\Policies\RolePolicy;
use Nox\Framework\Auth\Policies\UserPolicy;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;
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

        if (config('nox.auth.cache.enabled')) {
            BouncerFacade::cache();
        }

        BouncerFacade::runBeforePolicies();

        Filament::registerCustomAbilities('view_admin');

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
    }

    public function provides(): array
    {
        return [
            LoginResponse::class,
            LogoutResponse::class,
        ];
    }
}
