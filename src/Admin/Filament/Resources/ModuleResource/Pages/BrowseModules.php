<?php

namespace Nox\Framework\Admin\Filament\Resources\ModuleResource\Pages;

use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Nox\Framework\Admin\Filament\Resources\ModuleResource;
use Nox\Framework\Extend\Models\PackagistModule;

class BrowseModules extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ModuleResource::class;

    protected static string $view = 'nox::filament.resources.module-resource.pages.browse';

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
        $query = [
            'page' => $this->page,
            'per_page' => $this->tableRecordsPerPage,
            //            'tags' => 'nox-module',
        ];

        if (!empty($this->tableSearchQuery)) {
            $query['q'] = $this->tableSearchQuery;
        }

        return rescue(
            static function () use ($query) {
                $response = Http::asJson()
                    ->get('https://packagist.org/search.json', $query)
                    ->json();

                $response['results'] = collect($response['results'])
                    ->filter(static fn(array $module): bool => isset($module['downloads']))
                    ->map(static fn(array $module): PackagistModule => (new PackagistModule())->forceFill([
                        'name' => $module['name'],
                        'description' => $module['description'],
                        'url' => $module['url'],
                        'downloads' => $module['downloads'],
                    ]))
                    ->all();

                return $response;
            },
            static fn() => [
                'results' => [],
                'total' => 0
            ]
        );
    }

    protected function getTableColumns(): array
    {
        return [
            Split::make([
                Stack::make([
                    BadgeColumn::make('name')
                        ->label('Name')
                        ->searchable()
                        ->wrap(),
                    TextColumn::make('description')
                        ->label('Description')
                        ->wrap()
                ]),
                BadgeColumn::make('downloads')
                    ->label('Downloads')
                    ->color('success')
                    ->icon('heroicon-o-download')
                    ->formatStateUsing(static fn(int $state): string => number_format($state))
            ])
        ];
    }
}
