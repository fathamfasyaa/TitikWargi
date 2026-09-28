@php
    $place = collect([$report->address, $report->kelurahan])->filter()->join(', ');
    $days = $report->daysSinceReported();
    $firstPhoto = $report->photos->first();
    $reportUrl = route('reports.show', $report);

    $shareText = $report->category->label().' di '.($place ?: 'Cianjur')
        .'. Lihat dan dukung laporannya di TitikWargi: '.$reportUrl;
@endphp

<x-layouts.app :title="$report->category->label()">
    @push('meta')
        <meta property="og:title" content="{{ $report->category->label() }}{{ $place ? ' di '.$place : '' }}">
        <meta property="og:description" content="Laporan jalan rusak dari warga Cianjur. {{ $report->supporters_count }} warga terdampak.">
        <meta property="og:url" content="{{ $reportUrl }}">
        @if ($firstPhoto)
            <meta property="og:image" content="{{ $firstPhoto->url() }}">
        @endif
    @endpush

    @push('scripts')
        @vite('resources/js/report-detail.js')
    @endpush

    @if ($report->isHidden())
        <p role="status" class="mb-6 rounded-lg border-2 border-ink bg-white p-4 font-medium">
            Laporan ini sedang disembunyikan. Hanya admin yang bisa melihatnya.
        </p>
    @endif

    {{-- Photos: swipe sideways when there is more than one --}}
    @if ($report->photos->isNotEmpty())
        <div class="-mx-4 flex snap-x snap-mandatory gap-2 overflow-x-auto px-4 pb-2">
            @foreach ($report->photos as $photo)
                <img
                    src="{{ $photo->url() }}"
                    width="{{ $photo->width }}"
                    height="{{ $photo->height }}"
                    alt="Foto kerusakan {{ $loop->iteration }} dari {{ $loop->count }}"
                    loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                    @class([
                        'h-72 shrink-0 snap-center rounded-lg bg-ink/5 object-cover sm:h-96',
                        'w-full' => $loop->count === 1,
                        'w-[85%]' => $loop->count > 1,
                    ])
                >
            @endforeach
        </div>
    @endif

    <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
        <h1 class="text-3xl font-extrabold">{{ $report->category->label() }}</h1>
        <x-report-status :status="$report->status" />
    </div>

    <dl class="mt-4 grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
        <dt class="font-semibold">Keparahan</dt>
        <dd>{{ $report->severity->label() }}</dd>

        <dt class="font-semibold">Dilaporkan</dt>
        <dd>
            {{ $days === 0 ? 'Hari ini' : "Sudah {$days} hari" }}
            <span class="text-ink/80">({{ $report->created_at->translatedFormat('j F Y') }})</span>
        </dd>

        <dt class="font-semibold">Lokasi</dt>
        <dd>{{ $place ?: 'Tidak ada alamat' }}</dd>

        <dt class="font-semibold">Terdampak</dt>
        <dd><strong class="text-2xl">{{ $report->supporters_count }}</strong> warga</dd>
    </dl>

    @if ($report->description)
        <p class="mt-4 whitespace-pre-line rounded-lg bg-white p-4">{{ $report->description }}</p>
    @endif

    {{-- Support ("Saya juga terdampak") --}}
    <div class="mt-6">
        @auth
            @can('support', $report)
                <form method="POST" action="{{ route('reports.support', $report) }}">
                    @csrf
                    @if ($hasSupported)
                        <x-button variant="secondary" class="w-full">✓ Anda terdampak · Batalkan</x-button>
                    @else
                        <x-button class="w-full">Saya juga terdampak</x-button>
                    @endif
                </form>
            @elseif ($report->user_id === auth()->id())
                <p class="rounded-lg bg-white p-4">Ini laporan Anda. Bagikan supaya warga lain ikut mendukung.</p>
            @endcan
        @else
            <x-button :href="route('login', ['back' => '/reports/'.$report->id])" class="w-full">
                Masuk untuk mendukung
            </x-button>
        @endauth
    </div>

    {{-- Share to WhatsApp --}}
    <x-button
        :href="'https://wa.me/?text='.rawurlencode($shareText)"
        variant="secondary"
        target="_blank"
        rel="noopener"
        class="mt-3 w-full"
    >
        Bagikan ke WA
    </x-button>

    {{-- Small map --}}
    <section class="mt-8" aria-labelledby="location-title">
        <h2 id="location-title" class="text-2xl font-bold">Lokasi di peta</h2>
        <div
            id="report-location-map"
            data-latitude="{{ $report->latitude }}"
            data-longitude="{{ $report->longitude }}"
            data-status="{{ $report->status->value }}"
            class="mt-3 h-64 w-full rounded-lg border-2 border-ink/10"
        ></div>
    </section>

    {{-- Flag ("Laporkan konten ini") --}}
    <section class="mt-8 border-t-2 border-ink/10 pt-6">
        @auth
            @if ($hasFlagged)
                <p class="text-base">Anda sudah melaporkan konten ini. Terima kasih, moderator akan memeriksanya.</p>
            @elsecan('flag', $report)
                <details @if ($errors->hasAny(['reason', 'note'])) open @endif>
                    <summary class="inline-flex min-h-11 cursor-pointer items-center font-semibold underline">
                        Laporkan konten ini
                    </summary>

                    <form method="POST" action="{{ route('reports.flag', $report) }}" class="mt-4 flex flex-col gap-4">
                        @csrf

                        <fieldset>
                            <legend class="font-semibold">Apa masalahnya?</legend>

                            <div class="mt-2 flex flex-col gap-2">
                                @foreach ($flagReasons as $reason)
                                    <label class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border-2 border-ink/20 bg-white px-4 py-2 has-checked:border-accent">
                                        <input
                                            type="radio"
                                            name="reason"
                                            value="{{ $reason->value }}"
                                            class="size-5 shrink-0 accent-accent"
                                            @checked(old('reason') === $reason->value)
                                        >
                                        {{ $reason->label() }}
                                    </label>
                                @endforeach
                            </div>

                            @error('reason')
                                <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                            @enderror
                        </fieldset>

                        <div>
                            <label for="note" class="font-semibold">Catatan <span class="font-normal">(opsional)</span></label>
                            <textarea
                                id="note"
                                name="note"
                                rows="3"
                                maxlength="500"
                                class="mt-2 block w-full rounded-lg border-2 border-ink/20 bg-white px-4 py-2"
                            >{{ old('note') }}</textarea>

                            @error('note')
                                <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-button variant="secondary">Kirim ke moderator</x-button>
                    </form>
                </details>
            @endif
        @else
            <a href="{{ route('login', ['back' => '/reports/'.$report->id]) }}" class="inline-flex min-h-11 items-center font-semibold underline">
                Masuk untuk melaporkan konten ini
            </a>
        @endauth
    </section>
</x-layouts.app>
