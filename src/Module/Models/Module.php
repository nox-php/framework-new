<?php

namespace Nox\Framework\Module\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Nox\Framework\Module\Facades\Modules;
use Nox\Framework\Support\Composer;
use Sushi\Sushi;

class Module extends Model
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
            'update' => 'string',
            'manifest' => 'string',
        ];
    }

    public function getCasts(): array
    {
        return [
            'manifest' => 'array',
        ];
    }

    public function getRows(): array
    {
        $composer = app(Composer::class);

        return collect(Modules::all())
            ->map(static fn ($module): array => [
                'id' => Str::replace('/', '-', $module->name()),
                'name' => $module->name(),
                'description' => $module->description(),
                'version' => $module->version(),
                'pretty_version' => $module->prettyVersion(),
                'path' => $module->path(),
                'update' => Cache::get('nox.modules.updates', [])[$module->name()] ?? null,
                'manifest' => json_encode(Cache::remember(
                    'nox.modules.manifests.'.$module->name(),
                    now()->week(),
                    static fn () => $composer->manifest($module->name())
                ), JSON_THROW_ON_ERROR),
            ])
            ->values()
            ->all();
    }
}
