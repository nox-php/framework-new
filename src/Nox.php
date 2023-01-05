<?php

namespace Nox\Framework;

use Illuminate\Support\Facades\Storage;

class Nox
{
    public static function installed(): bool
    {
        return Storage::exists('nox.installed');
    }

    public static function enabledLocales(): array
    {
        return collect(config('localisation', []))
            ->filter(static fn (array $locale): bool => $locale['enabled'] ?? false)
            ->all();
    }
}
