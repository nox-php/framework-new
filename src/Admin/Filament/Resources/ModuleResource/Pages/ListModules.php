<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Extend\Contracts\ModuleRepository;
use Nox\Framework\Extend\Enums\ModuleStatus;
use Nox\Framework\Extend\Models\Module;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    public function installModules(
        ModuleRepository $modules,
        ComponentContainer $form,
        array $data
    ) {
        [$component] = $form->getComponents();

        $storage = $component->getDisk();

        foreach ($data['modules'] as $path) {
            $file = $storage->path($path);

            if (
                ($status = $modules->install($file, $name)) &&
                $status === ModuleStatus::InstallSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.install.success.title', ['name' => $name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title(__('nox::module.install.failed.title'))
                    ->body(__($status->value))
                    ->send();
            }
        }
    }

    public function enableModule(
        ModuleRepository $modules,
        Module $record
    ) {
        if (
            ($status = $modules->enable($record->name)) &&
            $status === ModuleStatus::EnabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.enable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.enable.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function bulkEnableModules(
        ModuleRepository $modules,
        Collection $records
    ) {
        foreach ($records as $record) {
            if (
                ($status = $modules->enable($record->name)) &&
                $status === ModuleStatus::EnabledSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.enable.success.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.enable.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }

        return redirect(ModuleResource::getUrl());
    }

    public function disableModule(
        ModuleRepository $modules,
        Module $record
    ) {
        if (
            ($status = $modules->disable($record->name)) &&
            $status === ModuleStatus::DisabledSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.disable.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.disable.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function bulkDisableModules(
        ModuleRepository $modules,
        Collection $records
    ) {
        foreach ($records as $record) {
            if (
                ($status = $modules->disable($record->name)) &&
                $status === ModuleStatus::DisabledSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.disable.success.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.disable.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }

        return redirect(ModuleResource::getUrl());
    }

    public function deleteModule(
        ModuleRepository $modules,
        Module $record
    ) {
        if (
            ($status = $modules->delete($record->name)) &&
            $status === ModuleStatus::DeleteSuccess
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::module.delete.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->success()
            ->title(__('nox::module.delete.failed.title', ['name' => $record->name]))
            ->body(__($status->value))
            ->send();
    }

    public function bulkDeleteModules(
        ModuleRepository $modules,
        Collection $records
    ) {
        foreach ($records as $record) {
            if (
                ($status = $modules->delete($record->name)) &&
                $status === ModuleStatus::DeleteSuccess
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.delete.success.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->success()
                    ->title(__('nox::module.delete.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }

        return redirect(ModuleResource::getUrl());
    }

    protected function getActions(): array
    {
        return [
            Action::make('install-module')
                ->label(__('nox::admin.resources.module.actions.install'))
                ->action('installModules')
                ->form([
                    FileUpload::make('modules')
                        ->disableLabel()
                        ->multiple()
                        ->directory('modules-tmp')
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
