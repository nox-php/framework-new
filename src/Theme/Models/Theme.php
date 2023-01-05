<?php

namespace Nox\Framework\Theme\Models;

use Illuminate\Database\Eloquent\Model;
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
            'parent' => 'string',
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
        return collect(Themes::all())
            ->map(static fn ($theme): array => [
                'id' => Str::replace(' / ', ' - ', $theme->getName()),
                'name' => $theme->getName(),
                'description' => $theme->getDescription(),
                'version' => $theme->getVersion(),
                'path' => $theme->getPath(),
                'parent' => $theme->getParent(),
                'enabled' => $theme->isEnabled(),
            ])
            ->values()
            ->all();
    }
}
