<x-filament::dropdown placement="bottom-end">
    <x-slot name="trigger" @class([
        'ml-4' => __('filament::layout.direction') === 'ltr',
        'mr-4' => __('filament::layout.direction') === 'rtl',
    ])>
        <div
            class="flex items-center justify-center w-10 h-10 font-semibold text-white bg-center bg-cover rounded-full bg-primary-500 dark:bg-gray-900">
            {{ str(app()->getLocale())->length() > 2
                ? str(app()->getLocale())->substr(0, 2)->upper()
                : str(app()->getLocale())->upper() }}
        </div>
    </x-slot>
    <x-filament::dropdown.list class="">
        @foreach ($locales as $key => $locale)
            @if (!app()->isLocale($key))
                <x-filament::dropdown.list.item wire:click="changeLocale('{{ $key }}')" tag="button">
                    <span
                        class="w-6 h-6 flex items-center justify-center mr-4 flex-shrink-0 rtl:ml-4 @if (!app()->isLocale($key)) group-hover:bg-white group-hover:text-primary-600 group-hover:border group-hover:border-primary-500/10 group-focus:text-white @endif bg-primary-500/10 text-primary-600 font-semibold rounded-full p-1 text-xs"
                    >
                        {{ str($locale['name'])->snake()->upper()->explode('_')->map(function ($string) use ($locale) {
                            return str($locale['name'])->wordCount() > 1 ? str()->substr($string, 0, 1) : str()->substr($string, 0, 2);
                        })->take(2)->implode('') }}
                    </span>

                    <span class="hover:bg-transparent">
                        {{ str($locale['native'] ?? $locale['name'])->headline() }}
                    </span>
                </x-filament::dropdown.list.item>
            @endif
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>
