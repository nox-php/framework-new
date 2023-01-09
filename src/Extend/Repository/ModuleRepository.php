<?php

namespace Nox\Framework\Extend\Repository;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Nox\Framework\Extend\Contracts\ModuleRepository as ModuleRepositoryContract;
use Nox\Framework\Extend\Discovery\ModuleDiscovery;
use Nox\Framework\Extend\Enums\ModuleStatus;
use Nox\Framework\Extend\Exceptions\ModuleNotFoundException;
use Nox\Framework\Extend\Jobs\DeleteModuleJob;
use Nox\Framework\Extend\Jobs\InstallModuleJob;
use Nox\Framework\Extend\Module;

class ModuleRepository implements ModuleRepositoryContract
{
    protected ?array $modules = null;

    public function __construct(
        protected ModuleDiscovery $discovery
    ) {
    }

    public function findOrFail(string $name): Module
    {
        if ($module = $this->find($name)) {
            return $module;
        }

        throw ModuleNotFoundException::module($name);
    }

    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    public function all(): array
    {
        if ($this->modules !== null) {
            return $this->modules;
        }

        if (! $this->loadCache()) {
            $this->load();
        }

        return $this->modules;
    }

    protected function loadCache(): bool
    {
        if (! $this->isCacheEnabled()) {
            return false;
        }

        $key = $this->getCacheKey();

        if ((! $cache = Cache::get($key)) || ! is_array($cache)) {
            return false;
        }

        $this->modules = collect($cache)
            ->map(static fn ($module): Module => Module::fromArray($module))
            ->all();

        return true;
    }

    protected function isCacheEnabled(): bool
    {
        return (bool) config('nox.modules.cache.enabled');
    }

    protected function getCacheKey(): string
    {
        return config('nox.modules.cache.key');
    }

    protected function load(): void
    {
        $this->modules = collect($this->discovery->discover())
            ->map(static fn (array $manifest): Module => Module::fromArray($manifest))
            ->all();

        $this->updateCache();
    }

    protected function updateCache(): void
    {
        if (! $this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forever(
            $key,
            collect($this->all())
                ->jsonSerialize()
        );
    }

    public function install(string $name): ModuleStatus
    {
        if ($this->getModule($name) !== null) {
            return ModuleStatus::InstallAlreadyInstalled;
        }

        InstallModuleJob::dispatch($name, auth()->user());

        return ModuleStatus::DeletePending;
    }

    protected function getModule(string|Module $module): ?Module
    {
        if ($module instanceof Module) {
            return $module;
        }

        return $this->find($module);
    }

    public function publish(string|Module $module, bool $migrate = true): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        return rescue(function () use ($module, $migrate) {
            $this->publishAssets($module);

            if ($migrate) {
                Artisan::call('migrate');
            }

            return ModuleStatus::PublishSuccess;
        }, ModuleStatus::PublishFailed);
    }

    protected function publishAssets(Module $module): void
    {
        $providers = [
            ...$module->config('laravel.providers', []),
            ...$module->config('nox.providers', []),
        ];

        foreach ($providers as $provider) {
            if (! class_exists($provider)) {
                continue;
            }

            $providerInstance = app($provider, [
                'app' => app(),
            ]);

            if (! method_exists($provider, 'getInstallerTags')) {
                continue;
            }

            $tags = (array) (app()->call([$providerInstance, 'getInstallerTags']) ?? []);

            foreach ($tags as $tag => $force) {
                $tag = is_string($tag) ? $tag : $force;
                $force = is_bool($force) ? $force : true;

                Artisan::call('vendor:publish', [
                    '--provider' => $provider,
                    '--tag' => $tag,
                    '--force' => $force,
                ]);
            }
        }
    }

    public function delete(string|Module $module): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        DeleteModuleJob::dispatch($module->name(), auth()->user());

        return ModuleStatus::DeletePending;
    }

    protected function clear(): void
    {
        $this->clearCache();

        $this->modules = null;
    }

    protected function clearCache(): void
    {
        if (! $this->isCacheEnabled()) {
            return;
        }

        $key = $this->getCacheKey();

        Cache::forget($key);
    }
}
