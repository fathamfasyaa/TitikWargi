{{--
    Main page layout.

    Usage:
      <x-layouts.app title="Judul halaman">
          ...content...
      </x-layouts.app>
--}}
@props(['title' => null])

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f3f1ec">

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-paper font-sans text-lg leading-relaxed text-ink antialiased">
        <header class="border-b-2 border-ink/10 bg-paper">
            <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-3">
                <a href="{{ url('/') }}" class="font-heading text-2xl font-extrabold">
                    Titik<span class="text-accent">Wargi</span>
                </a>

                @auth
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->avatar)
                            <img
                                src="{{ auth()->user()->avatar }}"
                                alt=""
                                class="size-10 rounded-full"
                                referrerpolicy="no-referrer"
                            >
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-button variant="secondary">Keluar</x-button>
                        </form>
                    </div>
                @else
                    <x-button :href="route('login')">Masuk</x-button>
                @endauth
            </div>
        </header>

        <main class="mx-auto w-full max-w-3xl flex-1 px-4 py-6">
            @if (session('error'))
                <p role="alert" class="mb-6 rounded-lg border-2 border-accent bg-white p-4 font-medium">
                    {{ session('error') }}
                </p>
            @endif

            @if (session('status'))
                <p role="status" class="mb-6 rounded-lg border-2 border-repaired bg-white p-4 font-medium">
                    {{ session('status') }}
                </p>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t-2 border-ink/10">
            <p class="mx-auto max-w-3xl px-4 py-6 text-base text-ink/80">
                Dibuat oleh warga bersama Velvorfa. Tidak terafiliasi dengan pemerintah atau partai mana pun.
            </p>
        </footer>
    </body>
</html>
