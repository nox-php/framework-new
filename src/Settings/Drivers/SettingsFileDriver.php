<?php

namespace Nox\Framework\Settings\Drivers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Nox\Framework\Settings\Contracts\SettingsDriver;

class SettingsFileDriver implements SettingsDriver
{
    protected array $data = [];

    protected bool $stale = false;

    public function __construct()
    {
        $this->data = $this->load();
    }

    public function get(array|int|string|null $key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return Arr::get($this->data, $key, $default);
    }

    public function has(array|int|string $keys): bool
    {
        return Arr::has($this->data, $keys);
    }

    public function set(array|int|string $key, $value = null): void
    {
        $this->stale = true;

        $data = is_array($key) ? $key : [$key => $value];

        foreach ($data as $k => $v) {
            Arr::set($this->data, $k, $v);
        }

        $this->data = collect($this->data)
            ->jsonSerialize();
    }

    public function pull(int|string $key, $default = null)
    {
        $this->stale = true;

        return Arr::pull($this->data, $key, $default);
    }

    public function forget(int|string $key): void
    {
        $this->stale = true;

        Arr::forget($this->data, $key);
    }

    public function save(): void
    {
        if (! $this->stale) {
            return;
        }

        $this->stale = false;

        $storage = Storage::disk(config('nox.settings.drivers.file.disk'));
        $path = config('nox.settings.drivers.file.path');

        $storage->put($path, $this->encode($this->data));
    }

    protected function load(): array
    {
        $storage = Storage::disk(config('nox.settings.drivers.file.disk'));
        $path = config('nox.settings.drivers.file.path');

        if (! $data = $storage->get($path)) {
            return [];
        }

        return $this->decode($data);
    }

    protected function decode(string $data): array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

    protected function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
