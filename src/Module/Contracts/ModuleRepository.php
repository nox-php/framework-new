<?php

namespace Nox\Framework\Module\Contracts;

use Nox\Framework\Module\Enums\ModuleStatus;
use Nox\Framework\Module\Module;

interface ModuleRepository
{
    public function all(): array;

    public function find(string $name): ?Module;

    public function findOrFail(string $name): Module;

    public function install(string $name): ModuleStatus;

    public function delete(string|Module $module): ModuleStatus;

    public function publish(string|Module $module, bool $migrate = true): ModuleStatus;

    public function clear(): void;

    public function clearCache(): void;
}
