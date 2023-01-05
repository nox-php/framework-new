<?php

namespace Nox\Framework\Extend\Repository;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Nox\Framework\Extend\Contracts\ModuleRepository as ModuleRepositoryContract;
use Nox\Framework\Extend\Enums\ModuleStatus;
use Nox\Framework\Extend\Exceptions\ModuleNotFoundException;
use Nox\Framework\Extend\Installer\ModuleInstaller;
use Nox\Framework\Extend\Loader\ModuleLoader;
use Nox\Framework\Extend\Module;

class ModuleRepository implements ModuleRepositoryContract
{
    protected ?array $modules = null;

    protected string $directory;

    public function __construct(
        protected ModuleLoader $loader,
        protected ModuleInstaller $installer
    ) {
        $this->directory = base_path('modules');
        $this->installer->setPath($this->directory);
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

    public function enabled(): array
    {
        return collect($this->all())
            ->filter(static fn (Module $module): bool => $module->isEnabled())
            ->all();
    }

    public function disabled(): array
    {
        return collect($this->all())
            ->filter(static fn (Module $module): bool => $module->isDisabled())
            ->all();
    }

    public function find(string $name): ?Module
    {
        return $this->all()[$name] ?? null;
    }

    public function findOrFail(string $name): Module
    {
        if ($module = $this->find($name)) {
            return $module;
        }

        throw ModuleNotFoundException::module($name);
    }

    public function enable(string|Module $module): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        if (! $this->bootModule($module)) {
            return ModuleStatus::BootFailed;
        }

        $module->setEnabled(true);

        settings()->set('nox.modules.'.$module->getName(), true);

        $this->updateCache();

        return ModuleStatus::EnabledSuccess;
    }

    public function disable(string|Module $module): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        $module->setEnabled(false);

        settings()->set('nox.modules.'.$module->getName(), false);

        $this->updateCache();

        return ModuleStatus::DisabledSuccess;
    }

    public function boot(): void
    {
        foreach ($this->enabled() as $module) {
            $this->bootModule($module);
        }
    }

    public function install(string $path, ?string &$name = null): ModuleStatus
    {
        if (! $name = $this->installer->install($path, $status)) {
            return $status;
        }

        $this->clear();

        if (! $module = $this->find($name)) {
            return ModuleStatus::NotFound;
        }

        if (! $this->bootModule($module)) {
            return ModuleStatus::BootFailed;
        }

        if (! $this->installer->publish($module->getProviders())) {
            return ModuleStatus::PublishFailed;
        }

        return ModuleStatus::InstallSuccess;
    }

    public function delete(string|Module $module): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        $path = $module->getPath();

        if (File::exists($path) && ! File::deleteDirectory($path)) {
            return ModuleStatus::DeleteFailed;
        }

        settings()->forget('nox.modules.'.$module->getName());

        $this->clear();

        return ModuleStatus::DeleteSuccess;
    }

    protected function bootModule(Module $module): bool
    {
        return rescue(function () use ($module) {
            $this->loadFiles($module->getFiles());
            $this->bootProviders($module->getProviders());

            return true;
        }, false);
    }

    public function publish(string|Module $module, bool $migrate = true): ModuleStatus
    {
        if (! $module = $this->getModule($module)) {
            return ModuleStatus::NotFound;
        }

        if ($this->installer->publish($module->getProviders(), $migrate)) {
            return ModuleStatus::PublishFailed;
        }

        return ModuleStatus::PublishSuccess;
    }

    protected function loadFiles(array $files): void
    {
        foreach ($files as $file) {
            if (File::exists($file)) {
                require_once $file;
            }
        }
    }

    protected function bootProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            if(class_exists($provider)) {
                app()->register($provider);
            }
        }
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

        $this->modules = [];

        foreach ($cache as $cachedModule) {
            if ($module = $this->loader->fromArray($cachedModule)) {
                $this->modules[$module->getName()] = $module;
            }
        }

        return true;
    }

    protected function load(): void
    {
        $this->modules = [];

        $manifests = File::search($this->directory, Module::$MANIFEST_FILE);

        foreach ($manifests as $manifest) {
            if ($module = $this->loader->fromPath($manifest)) {
                $this->modules[$module->getName()] = $module;
            }
        }

        foreach (settings('nox.modules', []) as $name => $enabled) {
            $this->find($name)?->setEnabled($enabled);
        }

        $this->updateCache();
    }

    protected function isCacheEnabled(): bool
    {
        return (bool) config('nox.modules.cache.enabled');
    }

    protected function getCacheKey(): string
    {
        return config('nox.modules.cache.key');
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

    protected function getModule(string|Module $module): ?Module
    {
        if ($module instanceof Module) {
            return $module;
        }

        return $this->find($module);
    }
}
