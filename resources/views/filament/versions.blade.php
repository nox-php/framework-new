<div @class([
    'ml-auto text-xs filament-versions-footer'
])>
    <ul class="flex items-center gap-x-4 gap-y-2">
        <li>Nox {{ $versions['nox'] }}</li>
        <li>PHP v{{ $versions['php'] }}</li>
    </ul>
</div>
