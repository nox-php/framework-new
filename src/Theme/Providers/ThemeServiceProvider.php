<?php

namespace Nox\Framework\Theme\Providers;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Theme\Contracts\ThemeRepository as ThemeRepositoryContract;
use Nox\Framework\Theme\Facades\Themes;
use Nox\Framework\Theme\Repository\ThemeRepository;

class ThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(ThemeRepositoryContract::class, 'themes');
        $this->app->singleton(ThemeRepositoryContract::class, ThemeRepository::class);
    }

    public function boot(): void
    {
        Themes::boot();

        if (Themes::enabled() === null) {
            $this->loadRoutesFrom(__DIR__.'/../../../routes/landing.php');
        }
    }
}
