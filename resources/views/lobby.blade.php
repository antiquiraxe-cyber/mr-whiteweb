@extends('layouts.app')
@section('title', 'Lobby — ' . $room->code)
@section('content')
<div class="flex flex-col items-center gap-6 text-center" x-data="lobby('{{ route('rooms.show', $room) }}')">
    <h1 class="text-4xl font-black text-white drop-shadow-[3px_3px_0_#000]">LOBBY</h1>
    <div class="card w-full">
        <p class="eyebrow">Kode Room</p>
        <p class="mt-2 select-all text-center text-5xl font-black tracking-[0.4em] text-yellow-300 drop-shadow-[3px_3px_0_#000]">{{ $room->code }}</p>
        <button @click="navigator.clipboard.writeText('{{ $room->code }}')" class="btn mt-3 bg-slate-600 text-sm">Copy Kode</button>
    </div>

    <div class="card w-full">
        <p class="eyebrow">Pemain ({{ $room->players->count() }})</p>
        <div class="mt-3 grid grid-cols-2 gap-2" id="player-grid">
            @foreach ($room->players as $p)
                <div class="flex items-center gap-2 rounded-xl border-2 border-black px-3 py-2 font-bold text-white {{ $p->is_host ? 'bg-yellow-600' : 'bg-slate-700' }}">
                    @if ($p->is_host) <span class="rounded border border-black bg-black/25 px-1.5 py-0.5 text-[10px] tracking-widest">HOST</span> @endif
                    <span>{{ $p->name }}</span>
                </div>
            @endforeach
        </div>
    </div>

    @if ($player->is_host)
        <div class="card w-full">
            <p class="eyebrow">Pengaturan</p>
            <form method="POST" action="{{ route('rooms.settings', $room) }}" class="mt-3 flex items-center justify-between">
                @csrf
                <label for="undercover-toggle" class="text-sm font-bold text-white">Pakai Undercover</label>
                <input type="hidden" name="has_undercover" value="0">
                <button type="submit" name="has_undercover" value="{{ $room->has_undercover ? '0' : '1' }}" id="undercover-toggle"
                        class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer rounded-full border-2 border-black transition-colors {{ $room->has_undercover ? 'bg-emerald-500' : 'bg-slate-600' }}">
                    <span class="pointer-events-none inline-block size-5 translate-x-0.5 translate-y-0.5 rounded-full border border-black bg-white shadow transition-transform {{ $room->has_undercover ? 'translate-x-5' : '' }}"></span>
                </button>
            </form>
        </div>

        @php $minPlayers = $room->has_undercover ? 4 : 3; @endphp
        <form method="POST" action="{{ route('rooms.start', $room) }}">
            @csrf
            <button type="submit" {{ $room->players->count() < $minPlayers ? 'disabled' : '' }} class="btn bg-emerald-500 text-lg">MULAI GAME</button>
        </form>
    @else
        <p class="animate-pulse text-sm font-bold text-cyan-300">Menunggu host memulai game...</p>
    @endif
</div>

@if ($room->mode === 'multiplayer')
<script>
    function lobby(url) {
        return {
            init() {
                setInterval(async () => {
                    const r = await fetch(url, { headers: { Accept: 'application/json' } });
                    const d = await r.json();
                    if (d.status !== 'lobby' || d.updated_at > {{ $room->updated_at->timestamp }}) { window.location.reload(); }
                }, 2500);
            },
        };
    }
</script>
@endif
@endsection
