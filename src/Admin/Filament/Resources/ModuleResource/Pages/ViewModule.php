<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Module\Contracts\ModuleRepository;
use Nox\Framework\Module\Enums\ModuleStatus;

class ViewModule extends ViewRecord
{
    protected static string $resource = ModuleResource::class;

    public function deleteModule(ModuleRepository $modules)
    {
        if (
            ($status = $modules->delete($this->record->name)) &&
            $status === ModuleStatus::DeletePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.modules.delete.pending.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.modules.delete.failed.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make()
                ->action('deleteModule'),
        ];
    }
}
