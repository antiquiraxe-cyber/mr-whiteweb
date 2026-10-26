@extends('layouts.app')
@section('content')
<div class="flex flex-col items-center gap-8 text-center">
    {{-- logo --}}
    <div class="flex items-center gap-4">
        <svg viewBox="0 0 64 72" class="h-20 w-18 drop-shadow-[3px_3px_0_#000]" aria-hidden="true">
            <circle cx="32" cy="24" r="16" fill="#f8fafc" stroke="#020617" stroke-width="4"/>
            <path d="M8 68c0-14 11-22 24-22s24 8 24 22z" fill="#f8fafc" stroke="#020617" stroke-width="4" stroke-linejoin="round"/>
            <text x="32" y="33" text-anchor="middle" font-family="Trebuchet MS, sans-serif" font-size="26" font-weight="900" fill="#0f172a">?</text>
        </svg>
        <div>
            <h1 class="text-5xl font-black text-white drop-shadow-[3px_3px_0_#000]">MR. WHITE</h1>
            <p class="eyebrow mt-1">find the imposter</p>
        </div>
    </div>

    {{-- mode tabs --}}
    <div x-data="{ mode: 'pass' }" class="w-full space-y-5">
        <div class="flex gap-2">
            <button @click="mode='pass'" :class="mode==='pass' ? 'bg-cyan-500' : 'bg-slate-700'" class="btn flex-1 text-sm">Main 1 HP</button>
            <button @click="mode='multi'" :class="mode==='multi' ? 'bg-yellow-400 !text-black' : 'bg-slate-700'" class="btn flex-1 text-sm">Multiplayer</button>
        </div>

        {{-- pass & play --}}
        <div x-show="mode==='pass'" x-data="passPlay()" class="card space-y-4">
            <p class="eyebrow">Masukkan Nama Pemain</p>
            <p class="text-xs text-slate-400" x-text="undercover ? 'Minimal 4 pemain' : 'Minimal 3 pemain'"></p>
            <div class="flex gap-2">
                <input x-model="name" @keydown.enter="add" class="input flex-1" placeholder="Nama..." maxlength="20">
                <button type="button" @click="add" class="btn w-auto bg-emerald-500 px-4 text-2xl">+</button>
            </div>
            <ul class="space-y-2">
                <template x-for="(n, i) in names" :key="i">
                    <li class="flex items-center justify-between rounded-xl border-2 border-black bg-slate-700 px-4 py-2 font-bold text-white">
                        <span x-text="(i+1) + '. ' + n"></span>
                        <button type="button" @click="names.splice(i,1)" class="text-red-400 hover:text-red-200 text-xl">&times;</button>
                    </li>
                </template>
            </ul>
            <form method="POST" action="{{ route('rooms.pass') }}">
                @csrf
                <template x-for="(n, i) in names" :key="'h'+i">
                    <input type="hidden" name="names[]" :value="n">
                </template>
                <label class="mb-3 flex cursor-pointer items-center justify-between text-sm font-bold text-white">
                    <span>Pakai Undercover</span>
                    <input type="hidden" name="has_undercover" value="0">
                    <input type="checkbox" name="has_undercover" value="1" x-model="undercover" class="size-5 rounded border-2 border-black accent-emerald-500">
                </label>
                <button type="submit" :disabled="names.length < (undercover ? 4 : 3)" class="btn bg-cyan-500">Mulai Game</button>
            </form>
        </div>

        {{-- multiplayer --}}
        <div x-show="mode==='multi'" class="space-y-4">
            {{-- create room --}}
            <form method="POST" action="{{ route('rooms.multiplayer') }}" class="card space-y-4">
                @csrf
                <p class="eyebrow">Bikin Room Baru</p>
                <input name="name" class="input" placeholder="Nama kamu..." maxlength="20" required>
                <button type="submit" class="btn bg-yellow-400 !text-black">Bikin Room</button>
            </form>
            {{-- join room --}}
            <form method="POST" action="{{ route('rooms.join') }}" class="card space-y-4">
                @csrf
                <p class="eyebrow">Gabung Room</p>
                <input name="code" class="input text-center tracking-[0.5em] uppercase" placeholder="KODE" maxlength="4" required>
                <input name="name" class="input" placeholder="Nama kamu..." maxlength="20" required>
                <button type="submit" class="btn bg-orange-500">Gabung</button>
            </form>
        </div>
    </div>

    {{-- errors --}}
    @if ($errors->any())
        <div class="card w-full border-red-500 bg-red-900/60 text-sm text-red-200">
            @foreach ($errors->all() as $e)
                <p>{{ $e }}</p>
            @endforeach
        </div>
    @endif
</div>
<script>
    function passPlay() {
        return {
            name: '', names: [], undercover: true,
            add() {
                const n = this.name.trim();
                if (n && !this.names.includes(n) && this.names.length < 16) {
                    this.names.push(n);
                    this.name = '';
                }
            },
        };
    }
</script>
@endsection
