<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Resources\Pages\Page;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;

class BrowseModules extends Page
{
    protected static string $resource = ModuleResource::class;

    protected static string $view = 'nox::filament.resources.module-resource.pages.browse';
}
