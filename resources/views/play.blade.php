@extends('layouts.app')
@section('title', 'Game — ' . $room->code)
@section('content')
<div class="flex flex-col items-center gap-6 text-center"
     @if($room->mode === 'multiplayer') x-data="poll('{{ route('rooms.show', $room) }}')" @endif>
    <h1 class="text-3xl font-black text-white drop-shadow-[3px_3px_0_#000]">
        @if ($room->status === 'white_guess')
            MR. WHITE, TEBAK KATA!
        @else
            DISKUSI &amp; VOTING
        @endif
    </h1>

    @if ($ejected)
        <div class="card w-full border-red-500 bg-red-950/80">
            <p class="eyebrow !text-red-300">Pemain Tereliminasi</p>
            <p class="mt-2 text-xl font-black text-white">{{ $ejected->name }}</p>
            <p class="mt-1 text-sm font-bold text-red-200">
                @if ($ejected->role === 'mr_white')
                    Dia adalah <span class="text-red-400">MR. WHITE</span>.
                @elseif ($ejected->role === 'undercover')
                    Dia adalah <span class="text-yellow-300">UNDERCOVER</span>.
                @else
                    Dia adalah <span class="text-cyan-300">CREWMATE</span>.
                @endif
            </p>
        </div>
    @endif

    {{-- multiplayer: my role card --}}
    @if ($room->mode === 'multiplayer')
    <div class="card w-full" x-data="{ peeking: false }">
        <p class="eyebrow">Kata Kamu</p>
        <div class="relative mt-2 overflow-hidden rounded-xl border-4 border-black bg-slate-950 p-6 select-none">
            <p x-show="!peeking" class="font-bold text-slate-500">Tahan untuk intip</p>
            <div x-show="peeking" x-cloak>
                @if ($player->role === 'mr_white')
                    <p class="text-2xl font-black text-red-500">MR. WHITE</p>
                @elseif ($player->role === 'undercover')
                    <p class="text-2xl font-black text-yellow-300">{{ $player->word }}</p>
                @else
                    <p class="text-2xl font-black text-cyan-300">{{ $player->word }}</p>
                @endif
            </div>
        </div>
        <button type="button"
                @pointerdown.prevent="$event.currentTarget.setPointerCapture($event.pointerId); peeking=true"
                @pointerup="peeking=false" @pointercancel="peeking=false" @lostpointercapture="peeking=false"
                @contextmenu.prevent
                @keydown.space.prevent="peeking=true" @keyup.space="peeking=false"
                @keydown.enter.prevent="peeking=true" @keyup.enter="peeking=false"
                style="touch-action:none"
                class="btn mt-3 bg-indigo-600 text-xs select-none">TAHAN UNTUK LIHAT</button>
    </div>
    @endif

    {{-- speaking order --}}
    <div class="card w-full">
        <p class="eyebrow">Urutan Bicara</p>
        <div class="mt-3 space-y-2">
            @foreach ($room->players as $p)
                <div class="flex items-center justify-between rounded-xl border-2 border-black px-4 py-2 font-bold
                    {{ $p->is_alive ? 'bg-slate-700 text-white' : 'bg-red-900/40 text-red-400 line-through' }}">
                    <span class="inline-flex items-center gap-2"><span class="inline-block size-2.5 rounded-full {{ $p->is_alive ? 'bg-emerald-400' : 'bg-red-500' }}"></span>{{ $p->name }}</span>
                    @if ($player->is_host && $p->is_alive && $room->status === 'playing')
                        <form method="POST" action="{{ route('players.eliminate', [$room, $p]) }}"
                              onsubmit="return confirm('Eliminasi {{ $p->name }}?')">
                            @csrf
                            <button type="submit" class="rounded-lg bg-red-600 border-2 border-black px-3 py-1 text-xs font-black text-white shadow-[2px_2px_0_#000] active:translate-x-0.5 active:translate-y-0.5 active:shadow-none">
                                Eject
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- white guess phase --}}
    @if ($room->status === 'white_guess')
        @php $isWhiteOrHost = ($room->mode === 'pass' && $player->is_host) || ($room->mode === 'multiplayer' && $player->role === 'mr_white' && ! $player->is_alive); @endphp
        @if ($isWhiteOrHost)
        <form method="POST" action="{{ route('rooms.guess', $room) }}" class="card w-full space-y-4 border-red-500">
            @csrf
            <p class="text-sm font-bold text-red-300">Mr. White punya 1 kesempatan menebak kata Civilian!</p>
            <input name="word" class="input" placeholder="Tebak kata..." maxlength="80" required autofocus>
            <button type="submit" class="btn bg-red-600">Tebak!</button>
        </form>
        @else
        <div class="card w-full border-red-500">
            <p class="animate-pulse text-sm font-bold text-red-300">Menunggu Mr. White menebak kata...</p>
        </div>
        @endif
    @endif
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
