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
            if (
                ($status = $themes->delete($record->name)) &&
                $status === ThemeStatus::DeletePending
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::admin.notifications.themes.delete.pending.title', ['name' => $record->name]))
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
