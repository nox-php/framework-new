<?php

namespace Nox\Framework\Admin\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Nox\Framework\Admin\Filament\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}
