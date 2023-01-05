<x-filament::page>
    <div wire:poll.60s>
        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($storedCheckResults as $result)
                @php
                    $backgroundColor = $this->getBackgroundColor($result->status);
                    $icon = $this->getIcon($result->status);
                    $iconColor = $this->getIconColor($result->status);
                @endphp

                <div
                    class="flex items-center items-start p-6 space-x-2 rtl:space-x-reverse overflow-hidden text-opacity-0 transform bg-white rounded-xl shadow @if(config('filament.dark_mode')) dark:bg-gray-800 @endif">
                    <div class="flex justify-center rounded-full p-2.5 {{ $backgroundColor }}">
                        @if ($icon)
                            @svg($icon, "w-5 h-5 {$iconColor}")
                        @endif
                    </div>

                    <div>
                        <dd class="-mt-1 font-semibold text-gray-800 md:mt-1 md:text-xl @if(config('filament.dark_mode')) dark:text-gray-200 @endif">
                            {{ $result->label }}
                        </dd>
                        <dt class="mt-0 text-sm font-medium text-gray-600 md:mt-1 @if(config('filament.dark_mode'))dark:text-gray-400 @endif">
                            @if (!empty($result->notificationMessage))
                                {{ $result->notificationMessage }}
                            @else
                                {{ $result->shortSummary }}
                            @endif
                        </dt>
                    </div>
                </div>
            @endforeach
        </dl>

        @if ($lastRanAt)
            <div
                class="{{ $lastRanAt->diffInMinutes() > 5 ? 'text-red-400' : 'text-gray-500' }} text-md text-center font-medium">
                {{ __('nox::admin.pages.health.last_updated') }} {{ $lastRanAt->diffForHumans() }}
            </div>
        @endif
    </div>
</x-filament::page>
