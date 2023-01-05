<?php

namespace Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Nox\Framework\Admin\Filament\Resources\ThemeResource;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Nox\Framework\Theme\Enums\ThemeStatus;

class ViewTheme extends ViewRecord
{
    protected static string $resource = ThemeResource::class;

    public function enableTheme(ThemeRepository $themes)
    {
        if (
            ($status = $themes->enable($this->record->name)) &&
            $status === ThemeStatus::EnabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.enable.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl('view', ['record' => $this->record]));
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.enable.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function disableTheme(ThemeRepository $themes)
    {
        if (
            ($status = $themes->disable($this->record->name)) &&
            $status === ThemeStatus::DisabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.disable.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.disable.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function deleteTheme(ThemeRepository $themes)
    {
        if (
            ($status = $themes->delete($this->record->name)) &&
            $status === ThemeStatus::DeleteSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.delete.success.title', ['name' => $this->record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.delete.failed.title', ['name' => $this->record->name]))
            ->body(__($status->value))
            ->send();
    }

    protected function getActions(): array
    {
        return [
            Action::make('enable-theme')
                ->label(__('nox::admin.resources.theme.actions.enable'))
                ->requiresConfirmation()
                ->action('enableTheme')
                ->hidden(fn (): bool => $this->record->enabled),
            Action::make('disable-theme')
                ->label(__('nox::admin.resources.theme.actions.disable'))
                ->action('disableTheme')
                ->requiresConfirmation()
                ->color('danger')
                ->hidden(fn (): bool => ! $this->record->enabled),
            DeleteAction::make()
                ->action('deleteTheme'),
        ];
    }
}
