<?php

namespace Nox\Framework\Admin\Filament\Resources\RoleResource\Pages;

use Filament\Pages\Actions\ActionGroup;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Nox\Framework\Admin\Filament\Resources\RoleResource;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make(),
            ActionGroup::make([
                DeleteAction::make(),
            ]),
        ];
    }
}
