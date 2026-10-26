@extends('layouts.app')
@section('title', 'Game Over — ' . $room->code)
@section('content')
<div class="flex flex-col items-center gap-6 text-center"
     @if($room->mode === 'multiplayer') x-data="poll('{{ route('rooms.show', $room) }}')" @endif>

    @if ($room->winner === 'civilians')
        <h1 class="text-5xl font-black text-cyan-400 drop-shadow-[3px_3px_0_#000]">VICTORY!</h1>
        <p class="text-lg font-bold text-cyan-200">Crewmates berhasil!</p>
    @elseif ($room->winner === 'mr_white')
        <h1 class="text-5xl font-black text-red-500 drop-shadow-[3px_3px_0_#000]">DEFEAT!</h1>
        <p class="text-lg font-bold text-red-300">Mr. White berhasil menipu semuanya!</p>
    @else
        <h1 class="text-5xl font-black text-yellow-400 drop-shadow-[3px_3px_0_#000]">INFILTRATED!</h1>
        <p class="text-lg font-bold text-yellow-200">Undercover & Mr. White menang!</p>
    @endif

    {{-- reveal all roles --}}
    <div class="card w-full">
        <p class="eyebrow">Kartu Terbuka</p>
        <div class="mt-3 space-y-2">
            @foreach ($room->players as $p)
                <div class="flex items-center justify-between rounded-xl border-2 border-black px-4 py-3 font-bold text-white
                    {{ $p->role === 'mr_white' ? 'bg-red-700' : ($p->role === 'undercover' ? 'bg-yellow-700' : 'bg-cyan-700') }}">
                    <span>{{ $p->name }}</span>
                    <span class="text-xs opacity-80">
                        @if ($p->role === 'mr_white')
                            MR. WHITE
                        @elseif ($p->role === 'undercover')
                            UNDERCOVER · {{ $p->word }}
                        @else
                            CREWMATE · {{ $p->word }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card w-full">
        <p class="eyebrow">Pasangan Kata</p>
        <p class="mt-2 text-lg font-bold text-white">
            <span class="text-cyan-300">{{ $room->civilian_word }}</span> vs <span class="text-yellow-300">{{ $room->undercover_word }}</span>
        </p>
    </div>

    {{-- play again --}}
    @if ($player->is_host)
        <form method="POST" action="{{ route('rooms.start', $room) }}">
            @csrf
            <button type="submit" class="btn bg-emerald-500 text-lg">MAIN LAGI</button>
        </form>
    @else
        <p class="animate-pulse text-sm font-bold text-cyan-300">Menunggu host untuk main lagi...</p>
    @endif

    <a href="{{ route('home') }}" class="btn bg-slate-600">Kembali ke Home</a>
</div>

@if ($room->mode === 'multiplayer')
<script>
    function poll(url) {
        return {
            init() {
                setInterval(async () => {
                    const r = await fetch(url, { headers: { Accept: 'application/json' } });
                    const d = await r.json();
                    if (d.updated_at > {{ $room->updated_at->timestamp }}) { window.location.reload(); }
                }, 2500);
            },
        };
    }
</script>
@endif
@endsection
