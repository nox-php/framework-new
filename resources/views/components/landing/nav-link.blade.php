@props([
    'to' => null
])

@php
    $classes = 'font-medium text-white transition-colors hover:text-primary-600 dark:hover:text-primary-500';
@endphp

<li>
    @if($to === null)
        <button
            {{$attributes->class($classes)}}
        >
            {{ $slot }}
        </button>
    @else
        <a
            {{$attributes->merge(['href' => $to, 'target' => '_blank'])->class($classes)}}
        >
            {{ $slot }}
        </a>
    @endif
</li>
