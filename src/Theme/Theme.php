<?php

namespace Nox\Framework\Theme;

use Illuminate\Support\Arr;

class Theme
{
    public function __construct(
        protected string $name,
        protected ?string $description,
        protected string $version,
        protected string $prettyVersion,
        protected string $path,
        protected array $config,
        protected bool $enabled
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function prettyVersion(): string
    {
        return $this->prettyVersion;
    }

    public function path(?string $path = null): string
    {
        if ($path === null) {
            return $this->path;
        }

        return $this->path.'/'.rtrim($path);
    }

    public function config(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        return Arr::get($this->config, $key, $default);
    }

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function enable(bool $enable = true): void
    {
        $this->enabled = $enable;
    }

    public static function fromArray(array $module): static
    {
        return new static(
            $module['name'],
            $module['description'] ?? null,
            $module['version'],
            $module['pretty_version'],
            $module['path'],
            $module['config'],
            $module['enabled'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'pretty_version' => $this->prettyVersion,
            'path' => $this->path,
            'config' => $this->config,
            'enabled' => $this->enabled
        ];
    }
}
