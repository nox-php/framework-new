@php
    $record = $getRecord();
    $homepageUrl = data_get($record->manifest, 'homepage', 'https://packagist.org/packages/' . $record->name);
@endphp

<div class="flex items-center justify-between gap-4 flex-wrap md:flex-nowrap">
    <div>
        A new update <span class="text-primary-600 dark:text-primary-500">(v{{ $record->update }})</span> for <span
            class="text-primary-600 dark:text-primary-500">{{ $record->name }}</span> is available to install. You
        currently have <span class="text-primary-600 dark:text-primary-500">v{{ $record->version }}</span> installed.
    </div>

    <div class="flex items-center justify-end">
        <x-filament::button tag="a" href="{{ $homepageUrl }}" target="_blank">
            View
        </x-filament::button>
    </div>
</div>
