<?php

namespace Nox\Framework\Admin\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Nox\Framework\Admin\Filament\Resources\UserResource;
use Silber\Bouncer\BouncerFacade;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function afterSave(): void
    {
        BouncerFacade::refreshFor($this->getRecord());
    }
}
