<?php

namespace Nox\Framework\Theme\Contracts;

use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Theme;

interface ThemeRepository
{
    public function all(): array;

    public function enabled(): ?Theme;

    public function disabled(): array;

    public function find(string $name): ?Theme;

    public function findOrFail(string $name): Theme;

    public function enable(string|Theme $theme): ThemeStatus;

    public function disable(): ThemeStatus;

    public function boot(): void;

    public function install(string $path, ?string &$name = null): ThemeStatus;

    public function delete(string|Theme $theme): ThemeStatus;

    public function publish(string|Theme $theme, bool $migrate = true): ThemeStatus;
}
