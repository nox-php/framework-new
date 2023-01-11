<?php

namespace Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Nox\Framework\Admin\Filament\Resources\ThemeResource;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Models\Theme;

class ListThemes extends ListRecords
{
    protected static string $resource = ThemeResource::class;

    public function updateTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->update($record->name)) &&
            $status === ThemeStatus::UpdatePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.update.pending.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.update.pending.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function bulkUpdateThemes(
        ThemeRepository $themes,
        Collection $records
    ) {
        foreach ($records as $record) {
            $this->updateTheme($themes, $record);
        }
    }

    public function enableTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->enable($record->name)) &&
            $status === ThemeStatus::EnableSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.enable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.enable.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function disableTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->disable()) &&
            $status === ThemeStatus::DisableSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.disable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.disable.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function deleteTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->delete($record->name)) &&
            $status === ThemeStatus::DeletePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.delete.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.delete.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function bulkDeleteThemes(
        ThemeRepository $themes,
        Collection $records
    ) {
        foreach ($records as $record) {
            $this->deleteTheme($themes, $record);
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('browse-themes')
                ->label(__('nox::admin.resources.theme.actions.browse'))
                ->url(ThemeResource::getUrl('browse')),
        ];
    }
}
