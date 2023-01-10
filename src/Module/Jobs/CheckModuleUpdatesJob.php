<?php

namespace Nox\Framework\Module\Jobs;

use Nox\Framework\Module\Facades\Modules;
use Nox\Framework\Module\Module;

class CheckModuleUpdatesJob
{
    public function handle()
    {
        $versions = $this->getLatestVersions();

        dd($versions);
    }

    private function getLatestVersions(): array
    {
        $modules = collect(Modules::all())
            ->map(static fn(Module $module): string => $module->name())
            ->all();

        dd($modules);
    }
}
