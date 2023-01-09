<?php

namespace Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Nox\Framework\Admin\Filament\Resources\ThemeResource;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Nox\Framework\Theme\Enums\ThemeStatus;

class ViewTheme extends ViewRecord
{
    protected static string $resource = ThemeResource::class;

    public function deleteTheme(ThemeRepository $themes)
    {
        if (
            ($status = $themes->delete($this->record->name)) &&
            $status === ThemeStatus::DeletePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.delete.pending.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.delete.failed.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    protected function getActions(): array
    {
        return [
            DeleteAction::make()
                ->action('deleteTheme'),
        ];
    }
}
