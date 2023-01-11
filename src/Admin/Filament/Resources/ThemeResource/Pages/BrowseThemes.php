<?php

namespace Nox\Framework\Admin\Filament\Resources\ThemeResource\Pages;

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
use Nox\Framework\Admin\Filament\Resources\ThemeResource;
use Nox\Framework\Admin\Models\PackagistPackage;
use Nox\Framework\Support\Packagist;
use Nox\Framework\Theme\Contracts\ThemeRepository;
use Nox\Framework\Theme\Enums\ThemeStatus;
use Nox\Framework\Theme\Facades\Themes;

class BrowseThemes extends ListRecords
{
    protected static string $resource = ThemeResource::class;

    private static array $tagsBlacklist = [
        'nox-theme',
        'php',
    ];

    public function installTheme(
        ThemeRepository $themes,
        PackagistPackage $record
    )
    {
        if (
            ($status = $themes->install($record->name)) &&
            $status === ThemeStatus::InstallPending
        ) {
            Notification::make()
                ->success()
                ->title(__('nox::admin.notifications.themes.install.success.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title(__('nox::admin.notifications.themes.install.failed.title', ['name' => $record->name]))
                ->body(__($status->value))
                ->send();
        }
    }

    public function getTableRecords(): Collection|Paginator
    {
        $themes = $this->getPackagistThemes();

        return new LengthAwarePaginator(
            $themes['results'],
            $themes['total'],
            $this->tableRecordsPerPage,
            $this->page
        );
    }

    private function getPackagistThemes(): array
    {
        $response = $this->loadPackageThemes();

        if (
            $response['total'] === 0 ||
            !$this->isManifestLoadingEnabled()
        ) {
            return $response;
        }

        $names = collect($response['results'])
            ->filter(static fn(PackagistPackage $theme): bool => $theme['manifest'] === null)
            ->keys()
            ->all();

        $manifests = Packagist::packages($names);

        foreach ($manifests as $name => $manifest) {
            $response['results'][$name]->forceFill([
                'manifest' => $manifest,
            ]);

            Cache::set('packagist.manifest.' . $name, $manifest, now()->addDay());
        }

        return $response;
    }

    private function loadPackageThemes()
    {
        return rescue(
            function () {
                $response = Packagist::search(
                    $this->tableSearchQuery,
                    ['nox-theme'],
                    $this->page,
                    $this->tableRecordsPerPage
                );

                $response['results'] = collect($response['results'])
                    ->filter(static fn(array $theme): bool => isset($theme['downloads']))
                    ->mapWithKeys(static fn(array $theme): array => [
                        $theme['name'] => (new PackagistPackage())->forceFill([
                            'name' => $theme['name'],
                            'description' => $theme['description'],
                            'url' => $theme['url'],
                            'downloads' => $theme['downloads'],
                            'manifest' => Cache::get('packagist.manifest.' . $theme['name']),
                        ]),
                    ])
                    ->all();

                return $response;
            },
            static fn() => [
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
                ->label(__('nox::admin.resources.theme.actions.go_back'))
                ->url(ThemeResource::getUrl()),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('load_manifests')
                ->label(__('nox::admin.resources.theme.table.filters.load_manifests.label'))
                ->indicator(__('nox::admin.resources.theme.table.filters.load_manifests.indicator'))
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
                        ->formatStateUsing(static fn(int $state): string => number_format($state)),
                    Tables\Columns\TagsColumn::make('manifest.keywords')
                        ->getStateUsing(static fn(PackagistPackage $record): array => $record->manifest === null
                            ? []
                            : collect($record->manifest['keywords'])
                                ->filter(static fn(string $tag): bool => !in_array($tag, static::$tagsBlacklist))
                                ->all()
                        )
                        ->limit()
                        ->hidden(fn() => !$this->isManifestLoadingEnabled()),
                ])->space(2),
                Tables\Columns\TagsColumn::make('manifest.authors')
                    ->getStateUsing(static fn(PackagistPackage $record): array => collect($record->manifest['authors'])->pluck('name')->all())
                    ->limit()
                    ->hidden(fn() => !$this->isManifestLoadingEnabled()),
            ]),
            Tables\Columns\Layout\Panel::make([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\BadgeColumn::make('manifest.version'),
                        Tables\Columns\TagsColumn::make('manifest.license'),
                        Tables\Columns\TextColumn::make('manifest.time')
                            ->formatStateUsing(static fn(?string $state): string => 'Last updated ' . Carbon::parse($state)->diffForHumans()),
                    ]),
                ])->space(),
            ])
                ->collapsible()
                ->hidden(fn() => !$this->isManifestLoadingEnabled()),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('install-theme')
                ->label(__('nox::admin.resources.theme.table.actions.install'))
                ->button()
                ->icon('heroicon-o-download')
                ->action('installTheme')
                ->requiresConfirmation()
                ->hidden(static fn(PackagistPackage $record): bool => Themes::find($record->name) !== null),
            Tables\Actions\ActionGroup::make([
                Tables\Actions\Action::make('view-theme')
                    ->label(__('nox::admin.resources.theme.table.actions.view'))
                    ->icon('heroicon-o-external-link')
                    ->url(static fn(PackagistPackage $record): string => $record->url)
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
