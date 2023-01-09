<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

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

    public function deleteModule(
        ModuleRepository $modules,
        Module $record
    )
    {
        if (
            ($status = $modules->delete($record->name)) &&
            $status === ModuleStatus::DeletePending
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
    )
    {
        foreach ($records as $record) {
            if (
                ($status = $modules->delete($record->name)) &&
                $status === ModuleStatus::DeletePending
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
            Action::make('browse-modules')
                ->label('Browse modules')
                ->url(ModuleResource::getUrl('browse'))
        ];
    }
}
