{{--
    Button with a minimum height of 44px, easy to tap and read.

    Usage:
      <x-button>Kirim</x-button>
      <x-button href="/lapor">Lapor</x-button>
      <x-button variant="secondary">Batal</x-button>
--}}
@props([
    'href' => null,
    'variant' => 'primary',
])

@php
    $variants = [
        'primary' => 'bg-accent text-white hover:bg-accent-dark',
        'secondary' => 'border-2 border-ink bg-white text-ink hover:bg-ink hover:text-white',
    ];

    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg px-5 py-2 text-lg font-semibold transition-colors '
        .$variants[$variant];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit'])->class($classes) }}>{{ $slot }}</button>
@endif
