<?php

namespace Nox\Framework\Admin\Filament\Pages;

use Carbon\Carbon;
use Filament\Pages\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Spatie\Health\Commands\RunHealthChecksCommand;
use Spatie\Health\Enums\Status;
use Spatie\Health\ResultStores\ResultStore;

class Health extends Page
{
    protected static string $view = 'nox::filament.pages.health';

    protected static ?string $slug = 'system/health';

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?int $navigationSort = 75;

    public array $backgroundColors = [];

    public array $icons = [];

    public array $iconColors = [];

    protected static function getNavigationLabel(): string
    {
        return __('nox::admin.pages.health.label');
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('nox::admin.groups.system');
    }

    protected function getTitle(): string
    {
        return __('nox::admin.pages.health.label');
    }

    public function mount(): void
    {
        Artisan::call(RunHealthChecksCommand::class);

        $this->backgroundColors = [
            Status::ok()->value => 'bg-success-100',
            Status::warning()->value => 'bg-warning-100',
            Status::skipped()->value => 'bg-success-100',
            Status::failed()->value => 'bg-danger-100',
            Status::crashed()->value => 'bg-danger-100',
            'default' => 'bg-gray-100',
        ];

        $this->icons = [
            Status::ok()->value => 'heroicon-s-check-circle',
            Status::warning()->value => 'heroicon-s-exclamation-circle',
            Status::skipped()->value => 'heroicon-s-arrow-right-circle',
            Status::failed()->value => 'heroicon-s-x-circle',
            Status::crashed()->value => 'heroicon-s-x-circle',
            'default' => 'heroicon-s-question-mark-circle',
        ];

        $this->iconColors = [
            Status::ok()->value => 'text-success-500',
            Status::warning()->value => 'text-warning-500',
            Status::skipped()->value => 'text-success-500',
            Status::failed()->value => 'text-danger-500',
            Status::crashed()->value => 'text-danger-500',
            'default' => 'text-gray-500',
        ];
    }

    protected function getActions(): array
    {
        return transformer(
            'nox.health.actions',
            [
                Action::make('refresh-health')
                    ->label(__('nox::admin.pages.health.actions.refresh'))
                    ->action('refresh'),
            ]
        );
    }

    public function refresh(): void
    {
        Artisan::call(RunHealthChecksCommand::class);

        $this->emitSelf('refreshComponent');
    }

    public function getBackgroundColor(string $status): ?string
    {
        return $this->backgroundColors[$status] ?? null;
    }

    public function getIcon(string $status): ?string
    {
        return $this->icons[$status] ?? null;
    }

    public function getIconColor(string $status): ?string
    {
        return $this->iconColors[$status] ?? null;
    }

    protected function getViewData(): array
    {
        $lastResults = app(ResultStore::class)
            ->latestResults();

        $storedCheckResults = $lastResults?->storedCheckResults ?? [];

        return [
            'lastRanAt' => Carbon::parse($lastResults?->finishedAt),
            'storedCheckResults' => $storedCheckResults,
        ];
    }
}
