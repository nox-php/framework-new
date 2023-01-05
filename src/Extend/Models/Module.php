<?php

namespace Nox\Framework\Extend\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nox\Framework\Extend\Facades\Modules;
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
            'enabled' => 'boolean',
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
                'id' => Str::replace('/', '-', $module->getName()),
                'name' => $module->getName(),
                'description' => $module->getDescription(),
                'version' => $module->getVersion(),
                'path' => $module->getPath(),
                'enabled' => $module->isEnabled(),
            ])
            ->values()
            ->all();
    }
}
