<?php

namespace Nox\Framework\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Nox\Framework\Theme\Facades\Themes;
use Sushi\Sushi;

class Theme extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    public function getSchema(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'version' => 'string',
            'pretty_version' => 'string',
            'enabled' => 'boolean',
            'update' => 'string',
        ];
    }

    public function getCasts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function getRows(): array
    {
        return collect(Themes::all())
            ->map(static fn ($theme): array => [
                'id' => Str::replace('/', '-', $theme->name()),
                'name' => $theme->name(),
                'description' => $theme->description(),
                'version' => $theme->version(),
                'pretty_version' => $theme->prettyVersion(),
                'path' => $theme->path(),
                'enabled' => $theme->enabled(),
                'update' => Cache::get('nox.themes.updates', [])[$theme->name()] ?? null,
            ])
            ->values()
            ->all();
    }
}
