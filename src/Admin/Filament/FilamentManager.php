<?php

namespace Nox\Framework\Admin\Filament;

use Filament\FilamentManager as FilamentManagerBase;
use Illuminate\Support\Str;
use Nox\Framework\Admin\Contracts\HasCustomAbilities;

class FilamentManager extends FilamentManagerBase
{
    private ?array $cachedNavigationGroups = null;

    private array $customAbilities = [];

    public function getNavigationGroups(): array
    {
        if ($this->cachedNavigationGroups !== null) {
            return $this->cachedNavigationGroups;
        }

        uksort($this->navigationGroups, function ($a, $b) {
            $aValue = $this->navigationGroups[$a];
            $bValue = $this->navigationGroups[$b];

            $isAString = is_string($aValue);
            $isBString = is_string($bValue);

            if (! $isAString && ! $isBString) {
                return $aValue - $bValue;
            }

            if ($isAString && $isBString) {
                return strcasecmp($a, $b);
            }

            if (! $isAString && $isBString) {
                return -1;
            }

            return 1;
        });

        return $this->cachedNavigationGroups = collect($this->navigationGroups)
            ->map(static fn ($value, $key) => is_string($key) ? $key : $value)
            ->values()
            ->all();
    }

    public function registerNavigationGroups(array $groups): void
    {
        parent::registerNavigationGroups($groups);

        $this->cachedNavigationGroups = null;
    }

    public function getResourceAbilities(): array
    {
        return collect($this->getResources())
            ->mapWithKeys(static function (string $resource) {
                $abilities = is_subclass_of($resource, HasCustomAbilities::class)
                    ? $resource::getCustomAbilities()
                    : [
                        'view',
                        'view_any',
                        'create',
                        'update',
                        'restore',
                        'restore_any',
                        'replicate',
                        'reorder',
                        'delete',
                        'delete_any',
                        'force_delete',
                        'force_delete_any',
                    ];

                return [
                    $resource => [
                        'name' => Str::of($resource)
                            ->replace('\\', '//')
                            ->toString(),
                        'abilities' => $abilities,
                        'model' => $resource::getModel(),
                    ],
                ];
            })
            ->all();
    }

    public function getCustomAbilities(): array
    {
        return collect($this->customAbilities)
            ->unique()
            ->all();
    }

    public function getPageAbilities(): array
    {
        return collect($this->getPages())
            ->mapWithKeys(static function (string $page) {
                $abilities = is_subclass_of($page, HasCustomAbilities::class)
                    ? $page::getCustomAbilities()
                    : [];

                return [
                    $page => [
                        'name' => Str::of($page)
                            ->replace('\\', '//')
                            ->toString(),
                        'abilities' => $abilities,
                    ],
                ];
            })
            ->filter(static fn ($data): bool => ! empty($data['abilities']))
            ->all();
    }

    public function registerCustomAbilities(string|array $abilities): void
    {
        $abilities = is_array($abilities) ? $abilities : [$abilities];

        $this->customAbilities = [
            ...$this->customAbilities,
            ...collect($abilities)
                ->map(static fn (string $ability): string => Str::replace('.', '-', $ability))
                ->all(),
        ];
    }
}
