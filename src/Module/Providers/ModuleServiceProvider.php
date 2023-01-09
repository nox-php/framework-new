<?php

namespace Nox\Framework\Module\Providers;

use Illuminate\Support\ServiceProvider;
use Nox\Framework\Module\Contracts\ModuleRepository as ModuleRepositoryContract;
use Nox\Framework\Module\Repository\ModuleRepository;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias(ModuleRepositoryContract::class, 'modules');
        $this->app->singleton(ModuleRepositoryContract::class, ModuleRepository::class);
    }
}
