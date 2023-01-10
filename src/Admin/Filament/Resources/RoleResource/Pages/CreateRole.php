<?php

namespace Nox\Framework\Admin\Filament\Resources\RoleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Nox\Framework\Admin\Filament\Resources\RoleResource;
use Silber\Bouncer\BouncerFacade;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    private bool $isSuperAdmin;

    private Collection $abilities;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->isSuperAdmin = $data['is_super_admin'];
        $this->abilities = collect($data)->except(['name', 'title', 'is_super_admin'])->keys();

        return Arr::only($data, ['name', 'title']);
    }

    protected function afterCreate(): void
    {
        if ($this->isSuperAdmin) {
            BouncerFacade::allow($this->record)->everything();
        }

        $this->abilities->each(function (string $ability): void {
            if (!Str::contains($ability, '//')) {
                BouncerFacade::allow($this->record)->to($ability);
                return;
            }

            $name = Str::before($ability, '//');
            $resource = Str::of($ability)
                ->after('//')
                ->replace('//', '\\')
                ->toString();

            BouncerFacade::allow($this->record)->to($name, $resource::getModel());
        });

        BouncerFacade::refresh();
    }
}
