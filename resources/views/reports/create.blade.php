<x-layouts.app title="Buat laporan">
    @push('scripts')
        @vite('resources/js/report-form.js')
    @endpush

    <h1 class="text-3xl font-extrabold">Laporkan jalan rusak</h1>

    @if ($deniedMessage)
        <p role="alert" class="mt-6 rounded-lg border-2 border-accent bg-white p-4 font-medium">
            {{ $deniedMessage }}
        </p>

        <x-button :href="route('home')" variant="secondary" class="mt-6">Kembali ke beranda</x-button>
    @else
        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-lg border-2 border-accent bg-white p-4">
                <p class="font-semibold">Laporan belum terkirim. Periksa bagian yang ditandai merah.</p>
                <p class="mt-1 text-base">Foto perlu dipilih ulang.</p>
            </div>
        @endif

        <form
            id="report-form"
            method="POST"
            action="{{ route('reports.store') }}"
            enctype="multipart/form-data"
            class="mt-6 flex flex-col gap-8"
        >
            @csrf

            {{-- 1. Photos --}}
            <fieldset>
                <legend class="font-heading text-xl font-bold">1. Foto kerusakan</legend>
                <p class="mt-1 text-base text-ink/80">Minimal 1, maksimal 3 foto. Boleh dari kamera atau galeri.</p>

                <ul id="photo-previews" class="mt-3 grid grid-cols-3 gap-2"></ul>

                <label id="add-photo-button" class="mt-3 inline-flex min-h-11 cursor-pointer items-center justify-center rounded-lg border-2 border-ink bg-white px-5 py-2 text-lg font-semibold">
                    + Tambah foto
                    <input id="photos" type="file" name="photos[]" accept="image/*" multiple class="sr-only">
                </label>
                <p id="photo-counter" class="mt-2 text-base" aria-live="polite">0 dari 3 foto</p>

                @foreach (collect($errors->get('photos'))->merge(collect($errors->get('photos.*'))->flatten())->unique() as $message)
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @endforeach
            </fieldset>

            {{-- 2. Location --}}
            <fieldset>
                <legend class="font-heading text-xl font-bold">2. Lokasi</legend>
                <p class="mt-1 text-base text-ink/80">Geser pin atau ketuk peta tepat di titik kerusakan.</p>

                <div
                    id="location-map"
                    data-area='@json($area)'
                    class="mt-3 h-80 w-full rounded-lg border-2 border-ink/10"
                ></div>

                <input type="hidden" name="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" value="{{ old('longitude') }}">

                <p id="location-status" class="mt-2 text-base" aria-live="polite"></p>

                <x-button id="gps-button" type="button" variant="secondary" class="mt-2">Gunakan lokasi saya (GPS)</x-button>

                @error('latitude')
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @enderror
                @error('longitude')
                    @unless ($errors->has('latitude'))
                        <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                    @endunless
                @enderror
            </fieldset>

            {{-- 3. Category --}}
            <fieldset>
                <legend class="font-heading text-xl font-bold">3. Jenis kerusakan</legend>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    @foreach ($categories as $category)
                        <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-lg border-2 border-ink/20 bg-white px-4 py-2 font-semibold has-checked:border-accent has-checked:bg-accent/10">
                            <input
                                type="radio"
                                name="category"
                                value="{{ $category->value }}"
                                class="size-5 accent-accent"
                                @checked(old('category') === $category->value)
                            >
                            {{ $category->label() }}
                        </label>
                    @endforeach
                </div>

                @error('category')
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- 4. Severity --}}
            <fieldset>
                <legend class="font-heading text-xl font-bold">4. Tingkat keparahan</legend>

                <div class="mt-3 grid grid-cols-3 gap-2">
                    @foreach ($severities as $severity)
                        <label class="flex min-h-14 cursor-pointer items-center gap-3 rounded-lg border-2 border-ink/20 bg-white px-4 py-2 font-semibold has-checked:border-accent has-checked:bg-accent/10">
                            <input
                                type="radio"
                                name="severity"
                                value="{{ $severity->value }}"
                                class="size-5 accent-accent"
                                @checked(old('severity') === $severity->value)
                            >
                            {{ $severity->label() }}
                        </label>
                    @endforeach
                </div>

                @error('severity')
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- 5. Address (optional) --}}
            <div>
                <label for="address" class="font-heading text-xl font-bold">5. Alamat atau patokan <span class="font-sans text-base font-normal">(opsional)</span></label>
                <input
                    id="address"
                    type="text"
                    name="address"
                    value="{{ old('address') }}"
                    maxlength="255"
                    placeholder="Contoh: Jl. Siliwangi, depan SD 1"
                    class="mt-3 block min-h-11 w-full rounded-lg border-2 border-ink/20 bg-white px-4 py-2"
                >

                @error('address')
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            {{-- 6. Description (optional) --}}
            <div>
                <label for="description" class="font-heading text-xl font-bold">6. Keterangan <span class="font-sans text-base font-normal">(opsional)</span></label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    maxlength="1000"
                    placeholder="Contoh: Lubang cukup dalam, berbahaya untuk motor saat hujan."
                    class="mt-3 block w-full rounded-lg border-2 border-ink/20 bg-white px-4 py-2"
                >{{ old('description') }}</textarea>

                @error('description')
                    <p class="mt-1 font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <p class="text-base">
                Dengan mengirim laporan, Anda setuju dengan
                <a href="{{ route('community-guidelines') }}" class="underline" target="_blank">Aturan Komunitas</a>
                dan <a href="{{ route('privacy') }}" class="underline" target="_blank">Kebijakan Privasi</a>.
                Foto, lokasi, dan keterangan akan tampil untuk publik. Nama Anda tidak ditampilkan.
            </p>

            <x-button class="w-full">Kirim laporan</x-button>
        </form>
    @endif
</x-layouts.app>
