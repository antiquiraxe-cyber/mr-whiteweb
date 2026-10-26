@extends('layouts.app')
@section('title', 'Intip Kata — ' . $room->code)
@section('content')
@php $current = $room->players[$room->reveal_index]; @endphp
<div class="flex flex-col items-center gap-6 text-center">
    <h1 class="animate-pulse text-4xl font-black text-white drop-shadow-[3px_3px_0_#000]">SHHH...</h1>

    <div class="card w-full">
        <p class="eyebrow">Oper HP ke</p>
        <p class="mt-2 text-3xl font-black text-yellow-300">{{ $current->name }}</p>
        <p class="mt-1 text-xs text-slate-400">Pemain {{ $room->reveal_index + 1 }} / {{ $room->players->count() }}</p>
    </div>

    <div class="card w-full" x-data="{ peeking: false }">
        <div class="relative overflow-hidden rounded-xl border-4 border-black bg-slate-950 p-8 select-none">
            <p x-show="!peeking" class="text-lg font-bold text-slate-500">Tahan tombol di bawah untuk intip</p>
            <div x-show="peeking" x-cloak class="space-y-2">
                @if ($current->role === 'mr_white')
                    <p class="text-sm font-bold text-red-400">PERANMU</p>
                    <p class="text-4xl font-black text-red-500 drop-shadow-[2px_2px_0_#000]">MR. WHITE</p>
                    <p class="text-sm text-red-300">Kamu tidak punya kata.<br>Menyamarlah!</p>
                @elseif ($current->role === 'undercover')
                    <p class="text-sm font-bold text-yellow-400">PERANMU: UNDERCOVER</p>
                    <p class="text-4xl font-black text-yellow-300 drop-shadow-[2px_2px_0_#000]">{{ $current->word }}</p>
                @else
                    <p class="text-sm font-bold text-cyan-400">PERANMU: CREWMATE</p>
                    <p class="text-4xl font-black text-cyan-300 drop-shadow-[2px_2px_0_#000]">{{ $current->word }}</p>
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
                class="btn mt-4 bg-indigo-600 text-sm select-none">TAHAN UNTUK LIHAT KATA</button>
    </div>

    @if ($player->is_host)
        <form method="POST" action="{{ route('rooms.reveal', $room) }}">
            @csrf
            <button type="submit" class="btn bg-cyan-500">Pemain Berikutnya</button>
        </form>
    @endif
</div>
@endsection
