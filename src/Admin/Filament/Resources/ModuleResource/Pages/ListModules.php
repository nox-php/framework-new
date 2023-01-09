<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Module\Contracts\ModuleRepository;
use Nox\Framework\Module\Enums\ModuleStatus;
use Nox\Framework\Module\Models\Module;

class ListModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    public function deleteModule(
        ModuleRepository $modules,
        Module $record
    ) {
        if (
            ($status = $modules->delete($record->name)) &&
            $status === ModuleStatus::DeletePending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.modules.delete.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();

            return redirect(ModuleResource::getUrl());
        }

        Notification::make()
            ->danger()
            ->title(__('nox::admin.notifications.modules.delete.failed.title', ['name' => $record->name]))
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
                $status === ModuleStatus::DeletePending
            ) {
                Notification::make()
                    ->success()
                    ->title(__('nox::admin.notifications.modules.delete.pending.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title(__('nox::admin.notifications.modules.delete.failed.title', ['name' => $record->name]))
                    ->body(__($status->value))
                    ->send();
            }
        }

        return redirect(ModuleResource::getUrl());
    }

    protected function getActions(): array
    {
        return [
            Action::make('browse-modules')
                ->label(__('nox::admin.resources.module.actions.browse'))
                ->url(ModuleResource::getUrl('browse')),
        ];
    }
}
