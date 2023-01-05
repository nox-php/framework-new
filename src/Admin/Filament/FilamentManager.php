<?php

namespace Nox\Framework\Admin\Filament;

use Filament\FilamentManager as FilamentManagerBase;

class FilamentManager extends FilamentManagerBase
{
    public function getNavigationGroups(): array
    {
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

        return collect($this->navigationGroups)
            ->map(static fn ($value, $key) => is_string($key) ? $key : $value)
            ->values()
            ->all();
    }
}
