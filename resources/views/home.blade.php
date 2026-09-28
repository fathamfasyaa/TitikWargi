<x-layouts.app>
    @push('scripts')
        @vite('resources/js/report-map.js')
    @endpush

    <h1 class="text-3xl font-extrabold sm:text-4xl">Laporkan jalan rusak di Cianjur</h1>

    <p class="mt-3 max-w-prose">
        Foto kerusakan jalan di sekitar Anda, tandai lokasinya, dan dukung laporan warga lain.
    </p>

    <x-button :href="route('reports.create')" class="mt-4 w-full sm:w-auto">+ Laporkan jalan rusak</x-button>

    <section class="mt-6" aria-labelledby="map-title">
        <h2 id="map-title" class="sr-only">Peta laporan</h2>

        <div
            id="report-map"
            data-endpoint="{{ route('map.reports') }}"
            class="h-[60vh] min-h-80 w-full rounded-lg border-2 border-ink/10"
        ></div>

        <ul class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-base" aria-label="Keterangan warna titik">
            <li class="flex items-center gap-2">
                <span class="inline-block size-5 rounded-full border-2 border-white bg-accent shadow"></span>
                Belum diperbaiki
            </li>
            <li class="flex items-center gap-2">
                <span class="inline-block size-5 rounded-full border-[3px] border-repaired bg-white"></span>
                Sudah diperbaiki
            </li>
        </ul>
    </section>

    <section class="mt-10" aria-labelledby="latest-title">
        <h2 id="latest-title" class="text-2xl font-bold">Laporan terbaru</h2>

        @if ($latestReports->isEmpty())
            <p class="mt-4">Belum ada laporan.</p>
        @else
            <ul class="mt-4 flex flex-col gap-3">
                @foreach ($latestReports as $report)
                    <li class="rounded-lg border-2 border-ink/10 bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-xl font-bold">
                                <a href="{{ route('reports.show', $report) }}" class="underline decoration-accent decoration-2 underline-offset-4">
                                    {{ $report->category->label() }}
                                </a>
                            </h3>
                            <x-report-status :status="$report->status" />
                        </div>

                        @if ($report->address || $report->kelurahan)
                            <p class="mt-1">{{ collect([$report->address, $report->kelurahan])->filter()->join(', ') }}</p>
                        @endif

                        <p class="mt-1 text-base text-ink/80">
                            Kerusakan {{ strtolower($report->severity->label()) }}
                            · {{ $report->supporters_count }} warga terdampak
                            · {{ $report->created_at->diffForHumans() }}
                        </p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
