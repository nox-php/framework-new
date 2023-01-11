<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Admin\Models\PackagistPackage;
use Nox\Framework\Module\Contracts\ModuleRepository;
use Nox\Framework\Module\Enums\ModuleStatus;
use Nox\Framework\Module\Facades\Modules;
use Nox\Framework\Support\Packagist;

class BrowseModules extends ListRecords
{
    protected static string $resource = ModuleResource::class;

    private static array $tagsBlacklist = [
        'nox-module',
        'php',
    ];

    public function installModule(
        ModuleRepository $modules,
        PackagistPackage $record
    ) {
        if (
            ($status = $modules->install($record->name)) &&
            $status === ModuleStatus::InstallPending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.modules.install.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.modules.install.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function getTableRecords(): Collection|Paginator
    {
        $modules = $this->getPackagistModules();

        return new LengthAwarePaginator(
            $modules['results'],
            $modules['total'],
            $this->tableRecordsPerPage,
            $this->page
        );
    }

    private function getPackagistModules(): array
    {
        $response = $this->loadPackageModules();

        if (
            $response['total'] === 0 ||
            ! $this->isManifestLoadingEnabled()
        ) {
            return $response;
        }

        $names = collect($response['results'])
            ->filter(static fn (PackagistPackage $module): bool => $module['manifest'] === null)
            ->keys()
            ->all();

        $manifests = Packagist::packages($names);

        foreach ($manifests as $name => $manifest) {
            $response['results'][$name]->forceFill([
                'manifest' => $manifest,
            ]);

            Cache::set('packagist.manifest.'.$name, $manifest, now()->addDay());
        }

        return $response;
    }

    private function loadPackageModules()
    {
        return rescue(
            function () {
                $response = Packagist::search(
                    $this->tableSearchQuery,
                    ['nox-module', 'filament'],
                    $this->page,
                    $this->tableRecordsPerPage
                );

                $response['results'] = collect($response['results'])
                    ->filter(static fn (array $module): bool => isset($module['downloads']))
                    ->mapWithKeys(static fn (array $module): array => [
                        $module['name'] => (new PackagistPackage())->forceFill([
                            'name' => $module['name'],
                            'description' => $module['description'],
                            'url' => $module['url'],
                            'downloads' => $module['downloads'],
                            'manifest' => Cache::get('packagist.manifest.'.$module['name']),
                        ]),
                    ])
                    ->all();

                return $response;
            },
            static fn () => [
                'results' => [],
                'total' => 0,
            ]
        );
    }

    private function isManifestLoadingEnabled(): bool
    {
        return $this->getCachedTableFilters()['load_manifests']->getState()['isActive'];
    }

    protected function getActions(): array
    {
        return [
            Pages\Actions\Action::make('go-back')
                ->label(__('nox::admin.resources.module.actions.go_back'))
                ->url(ModuleResource::getUrl()),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('load_manifests')
                ->label(__('nox::admin.resources.module.table.filters.load_manifests.label'))
                ->indicator(__('nox::admin.resources.module.table.filters.load_manifests.indicator'))
                ->default(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\Layout\Split::make([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\BadgeColumn::make('name')
                        ->searchable()
                        ->wrap(),
                    Tables\Columns\TextColumn::make('description')
                        ->wrap(),
                ])->space(),
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\BadgeColumn::make('downloads')
                        ->color('success')
                        ->icon('heroicon-o-download')
                        ->formatStateUsing(static fn (int $state): string => number_format($state)),
                    Tables\Columns\TagsColumn::make('manifest.keywords')
                        ->getStateUsing(static fn (PackagistPackage $record): array => $record->manifest === null
                            ? []
                            : collect($record->manifest['keywords'])
                                ->filter(static fn (string $tag): bool => ! in_array($tag, static::$tagsBlacklist))
                                ->all()
                        )
                        ->limit()
                        ->hidden(fn () => ! $this->isManifestLoadingEnabled()),
                ])->space(2),
                Tables\Columns\TagsColumn::make('manifest.authors')
                    ->getStateUsing(static fn (PackagistPackage $record): array => collect($record->manifest['authors'])->pluck('name')->all())
                    ->limit()
                    ->hidden(fn () => ! $this->isManifestLoadingEnabled()),
            ]),
            Tables\Columns\Layout\Panel::make([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\BadgeColumn::make('manifest.version'),
                        Tables\Columns\TagsColumn::make('manifest.license'),
                        Tables\Columns\TextColumn::make('manifest.time')
                            ->formatStateUsing(static fn (?string $state): string => 'Last updated '.Carbon::parse($state)->diffForHumans()),
                    ]),
                ])->space(),
            ])
                ->collapsible()
                ->hidden(fn () => ! $this->isManifestLoadingEnabled()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('install-module')
                ->label(__('nox::admin.resources.module.table.actions.install'))
                ->button()
                ->icon('heroicon-o-download')
                ->action('installModule')
                ->requiresConfirmation()
                ->hidden(static fn(PackagistPackage $record): bool => Modules::find($record->name) !== null),
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('view-module')
                    ->label(__('nox::admin.resources.module.table.actions.view'))
                    ->icon('heroicon-o-external-link')
                    ->url(static fn (PackagistPackage $record): string => $record->url)
                    ->openUrlInNewTab(),
            ]),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }

    protected function resolveTableRecord(?string $key): ?Model
    {
        return (new PackagistPackage())
            ->forceFill([
                'name' => $key,
            ]);
    }
}
