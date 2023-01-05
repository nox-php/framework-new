@php
    use Filament\Facades\Filament;
    use Illuminate\Support\Facades\Route;
@endphp

<x-filament::layouts.base :title="$title">
    <div class="h-screen w-full flex flex-col items-center justify-between">
        <div class="w-full py-4 px-6 flex items-center justify-center sm:justify-end">
            <ul class="flex items-center flex-wrap gap-4">
                @guest
                    @if(Route::has('auth.discord.redirect'))
                        <x-nox::landing.nav-link :to="route('auth.discord.redirect')">
                            Login
                        </x-nox::landing.nav-link>
                    @endif
                @else
                    @if(auth()->user()->can('view_admin') && $filamentUrl = Filament::getUrl())
                        <x-nox::landing.nav-link :to="$filamentUrl">
                            Administration
                        </x-nox::landing.nav-link>
                    @endif

                    @if(Route::has('filament.auth.logout'))
                        <form method="POST" action="{{ route('filament.auth.logout') }}">
                            @csrf

                            <x-nox::landing.nav-link type="submit">
                                Logout
                            </x-nox::landing.nav-link>
                        </form>
                    @endif
                @endguest
            </ul>
        </div>

        <div>
            <h1 class="text-8xl font-bold text-primary-600 dark:text-primary-500 motion-safe:animate-pulse">
                {{ $title }}
            </h1>
        </div>

        <div class="w-full py-4 px-6 flex items-center justify-center sm:justify-end">
            <ul class="flex items-center flex-wrap gap-4">
                <x-nox::landing.nav-link to="https://github.com/nox-php/framework">
                    Documentation
                </x-nox::landing.nav-link>

                <x-nox::landing.nav-link to="https://github.com/nox-php/framework">
                    <x-nox::icons.github class="h-6 w-6"/>
                </x-nox::landing.nav-link>
            </ul>
        </div>
    </div>
</x-filament::layouts.base>
