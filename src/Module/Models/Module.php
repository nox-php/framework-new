<?php

namespace Nox\Framework\Module\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nox\Framework\Module\Facades\Modules;
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
        return collect(Modules::all())
            ->map(static fn ($module): array => [
                'id' => Str::replace('/', '-', $module->name()),
                'name' => $module->name(),
                'description' => $module->description(),
                'version' => $module->version(),
                'pretty_version' => $module->prettyVersion(),
                'path' => $module->path(),
            ])
            ->values()
            ->all();
    }
}
