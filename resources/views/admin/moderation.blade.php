<x-layouts.app title="Moderasi">
    <h1 class="text-3xl font-extrabold">Moderasi</h1>

    @if ($errors->any())
        <div role="alert" class="mt-6 rounded-lg border-2 border-accent bg-white p-4">
            @foreach ($errors->all() as $message)
                <p class="font-medium">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    {{-- Flagged reports --}}
    <section class="mt-8" aria-labelledby="flagged-title">
        <h2 id="flagged-title" class="text-2xl font-bold">Laporan yang di-flag ({{ $flaggedReports->count() }})</h2>

        @forelse ($flaggedReports as $report)
            <article class="mt-4 rounded-lg border-2 border-ink/10 bg-white p-4">
                <div class="flex gap-3">
                    @if ($photo = $report->photos->first())
                        <img src="{{ $photo->url() }}" alt="" class="size-20 shrink-0 rounded-lg object-cover">
                    @endif

                    <div class="min-w-0">
                        <h3 class="text-xl font-bold">
                            <a href="{{ route('reports.show', $report) }}" class="underline">
                                #{{ $report->id }} {{ $report->category->label() }}
                            </a>
                        </h3>
                        <p class="text-base">
                            {{ $report->open_flags_count }} flag terbuka
                            @if ($report->isHidden())
                                · <strong>disembunyikan</strong>
                            @endif
                        </p>
                    </div>
                </div>

                <ul class="mt-3 flex flex-col gap-2 text-base">
                    @foreach ($report->flags as $flag)
                        <li class="rounded-lg bg-paper p-3">
                            <strong>{{ $flag->reason->label() }}</strong>
                            @if ($flag->note)
                                — {{ $flag->note }}
                            @endif
                            <span class="block text-ink/80">oleh {{ $flag->user->name }}, {{ $flag->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-3 text-base">
                    Pelapor: <strong>{{ $report->user->name }}</strong> ({{ $report->user->email }})
                    · {{ $report->user->strikes }} peringatan
                    @if ($report->user->isBanned())
                        · <strong>sedang diblokir</strong>
                    @endif
                </p>

                <div class="mt-3 flex flex-col gap-2">
                    @if ($report->isHidden())
                        <x-admin.action-form :action="route('admin.reports.unhide', $report)" label="Tampilkan lagi" />
                    @else
                        <x-admin.action-form :action="route('admin.reports.hide', $report)" label="Sembunyikan laporan" danger />
                    @endif

                    <x-admin.action-form :action="route('admin.reports.resolve-flags', $report)" label="Tandai flag selesai (tidak ada masalah)" />

                    @can('moderate-user', $report->user)
                        <x-admin.action-form :action="route('admin.users.warn', $report->user)" label="Beri peringatan ke pelapor" />

                        @unless ($report->user->isBanned())
                            <x-admin.action-form :action="route('admin.users.ban', $report->user)" label="Blokir pelapor" danger>
                                <x-admin.ban-duration :durations="$banDurations" />
                            </x-admin.action-form>
                        @endunless
                    @endcan
                </div>
            </article>
        @empty
            <p class="mt-4">Tidak ada flag yang perlu ditinjau.</p>
        @endforelse
    </section>

    {{-- Hidden reports --}}
    <section class="mt-10" aria-labelledby="hidden-title">
        <h2 id="hidden-title" class="text-2xl font-bold">Laporan disembunyikan</h2>

        @forelse ($hiddenReports as $report)
            <article class="mt-4 rounded-lg border-2 border-ink/10 bg-white p-4">
                <h3 class="text-xl font-bold">
                    <a href="{{ route('reports.show', $report) }}" class="underline">#{{ $report->id }} {{ $report->category->label() }}</a>
                </h3>
                <p class="text-base">
                    Oleh {{ $report->user->name }} · disembunyikan {{ $report->hidden_at->diffForHumans() }}
                </p>

                <div class="mt-3">
                    <x-admin.action-form :action="route('admin.reports.unhide', $report)" label="Tampilkan lagi" />
                </div>
            </article>
        @empty
            <p class="mt-4">Tidak ada.</p>
        @endforelse
    </section>

    {{-- Banned users --}}
    <section class="mt-10" aria-labelledby="banned-title">
        <h2 id="banned-title" class="text-2xl font-bold">Pengguna diblokir</h2>

        @forelse ($bannedUsers as $user)
            <article class="mt-4 rounded-lg border-2 border-ink/10 bg-white p-4">
                <h3 class="text-xl font-bold">{{ $user->name }}</h3>
                <p class="text-base">
                    {{ $user->email }} · {{ $user->strikes }} peringatan ·
                    {{ $user->isBannedPermanently() ? 'diblokir permanen' : 'sampai '.$user->banned_until->translatedFormat('j F Y H:i') }}
                </p>

                <div class="mt-3">
                    <x-admin.action-form :action="route('admin.users.unban', $user)" label="Buka blokir" />
                </div>
            </article>
        @empty
            <p class="mt-4">Tidak ada.</p>
        @endforelse
    </section>

    {{-- Recent actions --}}
    <section class="mt-10" aria-labelledby="log-title">
        <h2 id="log-title" class="text-2xl font-bold">Riwayat moderasi terbaru</h2>

        @if ($recentLogs->isEmpty())
            <p class="mt-4">Belum ada tindakan.</p>
        @else
            <ul class="mt-4 flex flex-col gap-2 text-base">
                @foreach ($recentLogs as $log)
                    <li class="rounded-lg border-2 border-ink/10 bg-white p-3">
                        <strong>{{ $log->moderator->name }}</strong>
                        {{ strtolower($log->action->label()) }}
                        @if ($log->target instanceof \App\Models\Report)
                            <a href="{{ route('reports.show', $log->target) }}" class="underline">laporan #{{ $log->target->id }}</a>
                        @elseif ($log->target instanceof \App\Models\User)
                            {{ $log->target->name }}
                        @else
                            (data sudah dihapus)
                        @endif
                        <span class="block">Alasan: {{ $log->reason }}</span>
                        <span class="block text-ink/80">{{ $log->created_at->translatedFormat('j F Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
