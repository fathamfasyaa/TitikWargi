{{--
    Status badge for a report.

    Usage: <x-report-status :status="$report->status" />
--}}
@props(['status'])

@php
    $classes = $status === \App\Enums\ReportStatus::Repaired
        ? 'border-repaired bg-white text-repaired'
        : 'border-accent bg-accent text-white';
@endphp

<span {{ $attributes->class("inline-block rounded-full border-2 px-3 py-0.5 text-base font-semibold $classes") }}>
    {{ $status->label() }}
</span>
