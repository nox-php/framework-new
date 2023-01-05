<?php

namespace Nox\Framework\Extend;

use Illuminate\Contracts\Support\Arrayable;

class Module implements Arrayable
{
    public static string $MANIFEST_FILE = 'module.json';

    public function __construct(
        protected string $name,
        protected string $description,
        protected string $version,
        protected string $path,
        protected array $files,
        protected array $providers,
        protected int $priority,
        protected bool $enabled
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getPath(?string $path = null): string
    {
        if ($path === null) {
            return $this->path;
        }

        return $this->path.'/'.ltrim($path, '/');
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function getProviders(): array
    {
        return $this->providers;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isDisabled(): bool
    {
        return ! $this->enabled;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'version' => $this->version,
            'path' => $this->path,
            'files' => $this->files,
            'providers' => $this->providers,
            'priority' => $this->priority,
            'enabled' => $this->enabled,
        ];
    }
}
