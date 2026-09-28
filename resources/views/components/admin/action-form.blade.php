{{--
    A moderation action: a button that opens a small form asking for the reason.

    Usage:
      <x-admin.action-form :action="route('admin.reports.hide', $report)" label="Sembunyikan" />
      <x-admin.action-form ...>
          extra fields (for example the ban duration)
      </x-admin.action-form>
--}}
@props([
    'action',
    'label',
    'danger' => false,
])

<details class="rounded-lg border-2 border-ink/20 bg-white">
    <summary @class([
        'flex min-h-11 cursor-pointer items-center px-4 font-semibold',
        'text-accent' => $danger,
    ])>
        {{ $label }}
    </summary>

    <form method="POST" action="{{ $action }}" class="flex flex-col gap-3 border-t-2 border-ink/10 p-4">
        @csrf

        {{ $slot }}

        <label class="flex flex-col gap-1">
            <span class="font-semibold">Alasan (wajib, dicatat di riwayat)</span>
            <textarea name="reason" rows="2" maxlength="500" required class="rounded-lg border-2 border-ink/20 px-3 py-2"></textarea>
        </label>

        <x-button :variant="$danger ? 'primary' : 'secondary'">{{ $label }}</x-button>
    </form>
</details>
