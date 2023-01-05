<?php

namespace Nox\Framework\Extend\Contracts;

use Nox\Framework\Extend\Enums\ModuleStatus;
use Nox\Framework\Extend\Module;

interface ModuleRepository
{
    public function all(): array;

    public function enabled(): array;

    public function disabled(): array;

    public function find(string $name): ?Module;

    public function findOrFail(string $name): Module;

    public function enable(string|Module $module): ModuleStatus;

    public function disable(string|Module $module): ModuleStatus;

    public function boot(): void;

    public function install(string $path, ?string &$name = null): ModuleStatus;

    public function delete(string|Module $module): ModuleStatus;

    public function publish(string|Module $module, bool $migrate = true): ModuleStatus;
}
