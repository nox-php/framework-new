<?php

namespace Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
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

    public function installThemes(
        ThemeRepository $themes,
        ComponentContainer $form,
        array $data
    ) {
        [$component] = $form->getComponents();

        $storage = $component->getDisk();

        foreach ($data['themes'] as $path) {
            $file = $storage->path($path);

            if (
                ($status = $themes->install($file, $name)) &&
                $status === ThemeStatus::InstallSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::theme.install.success.title', ['name' => $name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title(__('nox::theme.install.failed.title'))
                    ->body(__($status->value))
                    ->send();
            }
        }
    }

    public function enableTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->enable($record->name)) &&
            $status === ThemeStatus::EnabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.enable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.enable.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function disableTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->disable($record->name)) &&
            $status === ThemeStatus::DisabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.disable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.disable.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function deleteTheme(
        ThemeRepository $themes,
        Theme $record
    ) {
        if (
            ($status = $themes->delete($record->name)) &&
            $status === ThemeStatus::DeleteSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::theme.delete.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ThemeResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::theme.delete.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function bulkDeleteThemes(
        ThemeRepository $themes,
        Collection $records
    ) {
        foreach ($records as $record) {
            if (
                ($status = $themes->delete($record->name)) &&
                $status === ThemeStatus::DeleteSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::theme.delete.success.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('nox::theme.delete.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }

        return redirect(ThemeResource::getUrl());
    }

    protected function getActions(): array
    {
        return [
            Action::make('install-theme')
                ->label(__('nox::admin.resources.theme.actions.install'))
                ->action('installThemes')
                ->form([
                    FileUpload::make('themes')
                        ->disableLabel()
                        ->multiple()
                        ->directory('themes-tmp')
                        ->minFiles(1)
                        ->acceptedFileTypes([
                            'application/zip',
                            'application/x-zip-compressed',
                            'multipart/x-zip',
                        ]),
                ]),
        ];
    }
}
