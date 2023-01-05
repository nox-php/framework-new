<?php

namespace Nox\Framework\Settings\Contracts;

interface SettingsDriver
{
    public function get(int|string|null $key = null, $default = null);

    public function has(array|int|string $keys): bool;

    public function set(array|int|string $key, $value = null): void;

    public function pull(int|string $key, $default = null);

    public function forget(int|string $key): void;

    public function save(): void;
}
