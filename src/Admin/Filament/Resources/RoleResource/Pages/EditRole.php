<?php

namespace Nox\Framework\Admin\Filament\Resources\RoleResource\Pages;

use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nox\Framework\Admin\Filament\Resources\RoleResource;
use Silber\Bouncer\BouncerFacade;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    private bool $isSuperAdmin;

    private Collection $abilities;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->isSuperAdmin = $data['is_super_admin'];
        $this->abilities = collect($data)->except(['name', 'title', 'is_super_admin'])->keys();

        return Arr::only($data, ['name', 'title']);
    }

    protected function afterSave(): void
    {
        $this->record->abilities()->detach();

        if ($this->isSuperAdmin) {
            BouncerFacade::allow($this->record)->everything();
        }

        $this->abilities->each(function (string $ability): void {
            if (! Str::contains($ability, '//')) {
                BouncerFacade::allow($this->record)->to($ability);

                return;
            }

            $name = Str::before($ability, '//');
            $resource = Str::of($ability)
                ->after('//')
                ->replace('//', '\\')
                ->toString();

            rescue(
                fn () => BouncerFacade::allow($this->record)->to($name, $resource::getModel()),
                fn () => BouncerFacade::allow($this->record)->to($name),
                false
            );
        });

        BouncerFacade::refresh();

        $this->record->touch();
    }
}
