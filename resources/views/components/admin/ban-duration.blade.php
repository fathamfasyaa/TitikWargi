{{-- Choice of ban length, used inside the "Blokir" action form. --}}
@props(['durations'])

<fieldset>
    <legend class="font-semibold">Lama blokir</legend>

    <div class="mt-2 flex flex-wrap gap-2">
        @foreach ($durations as $duration)
            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border-2 border-ink/20 px-4 has-checked:border-accent">
                <input type="radio" name="duration" value="{{ $duration }}" required class="size-5 accent-accent" @checked($loop->first)>
                {{ $duration === 'permanent' ? 'Permanen' : $duration.' hari' }}
            </label>
        @endforeach
    </div>
</fieldset>
