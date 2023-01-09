<?php

namespace Nox\Framework\Extend;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;

class Module implements Arrayable
{
    public function __construct(
        protected string $name,
        protected string $version,
        protected string $path,
        protected array $config
    )
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function path(?string $path = null): string
    {
        if ($path === null) {
            return $this->path;
        }

        return $this->path . '/' . rtrim($path);
    }

    public function config(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return Arr::get($this->config, $key, $default);
    }

    public static function fromArray(array $module): static
    {
        return new static(
            $module['name'],
            $module['version'],
            $module['path'],
            $module['config']
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'version' => $this->version,
            'path' => $this->path,
            'config' => $this->config
        ];
    }
}
