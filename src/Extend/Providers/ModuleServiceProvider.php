<?php

namespace Nox\Framework\Extend\Providers;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Extend\Contracts\ModuleRepository as ModuleRepositoryContract;
use Nox\Framework\Extend\Facades\Modules;
use Nox\Framework\Extend\Repository\ModuleRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(ModuleRepositoryContract::class, 'modules');
        $this->app->singleton(ModuleRepositoryContract::class, ModuleRepository::class);
    }

    public function boot(): void
    {
        Modules::boot();
    }
}
