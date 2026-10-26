<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Room;
use App\Services\GameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GameController extends Controller
{
    public function home(): View
    {
        return view('home');
    }

    public function createPass(Request $request): RedirectResponse
    {
        $hasUndercover = $request->boolean('has_undercover', true);
        $data = $request->validate([
            'names' => ['required', 'array', 'min:'.($hasUndercover ? 4 : 3), 'max:16'],
            'names.*' => ['required', 'string', 'max:20', 'distinct'],
        ]);

        return $this->createRoom('pass', collect($data['names']), $hasUndercover);
    }

    public function createMultiplayer(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:20']]);

        return $this->createRoom('multiplayer', collect([$data['name']]));
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:4'],
            'name' => ['required', 'string', 'max:20'],
        ]);
        $room = Room::where('code', Str::upper($data['code']))->firstOrFail();
        abort_unless($room->mode === 'multiplayer' && $room->status === 'lobby', 422, 'Room sudah dimulai atau tidak tersedia.');
        abort_if($room->players()->count() >= 16, 422, 'Room sudah penuh.');

        $request->validate([
            'name' => [Rule::unique('players')->where('room_id', $room->id)],
        ]);

        $player = $room->players()->create([
            'name' => $data['name'],
            'token' => Str::uuid(),
            'turn_order' => $room->players()->count(),
        ]);
        $request->session()->put("rooms.{$room->id}", $player->token);
        $room->touch();

        return redirect()->route('rooms.show', $room);
    }

    public function show(Request $request, Room $room): View|array
    {
        if ($request->expectsJson()) {
            return ['updated_at' => $room->updated_at->timestamp, 'status' => $room->status];
        }

        $player = $this->player($request, $room);
        $room->load('players');
        $ejected = $room->players->where('is_alive', false)->sortByDesc('updated_at')->first();

        return view(match ($room->status) {
            'lobby' => 'lobby',
            'reveal' => 'reveal',
            'ended' => 'ended',
            default => 'play',
        }, compact('room', 'player', 'ejected'));
    }

    public function settings(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeHost($request, $room);
        abort_unless($room->status === 'lobby', 422);
        $data = $request->validate(['has_undercover' => ['required', 'boolean']]);
        $room->update(['has_undercover' => $data['has_undercover']]);

        return redirect()->route('rooms.show', $room);
    }

    public function start(Request $request, Room $room, GameService $game): RedirectResponse
    {
        $this->authorizeHost($request, $room);
        $game->start($room);

        return redirect()->route('rooms.show', $room);
    }

    public function nextReveal(Request $request, Room $room): RedirectResponse
    {
        $this->authorizeHost($request, $room);
        abort_unless($room->mode === 'pass' && $room->status === 'reveal', 422);

        if ($room->reveal_index + 1 >= $room->players()->count()) {
            $room->update(['status' => 'playing']);
        } else {
            $room->increment('reveal_index');
        }

        return redirect()->route('rooms.show', $room);
    }

    public function eliminate(Request $request, Room $room, Player $player, GameService $game): RedirectResponse
    {
        $this->authorizeHost($request, $room);
        $game->eliminate($room, $player);

        return redirect()->route('rooms.show', $room);
    }

    public function guess(Request $request, Room $room, GameService $game): RedirectResponse
    {
        $player = $this->player($request, $room);
        abort_unless($room->mode === 'pass' ? $player->is_host : $player->role === 'mr_white' && ! $player->is_alive, 403);
        $data = $request->validate(['word' => ['required', 'string', 'max:80']]);
        $game->guess($room, $data['word']);

        return redirect()->route('rooms.show', $room);
    }

    private function createRoom(string $mode, Collection $names, bool $hasUndercover = true): RedirectResponse
    {
        $room = Room::create(['code' => Room::generateCode(), 'mode' => $mode, 'has_undercover' => $hasUndercover]);

        foreach ($names->values() as $index => $name) {
            $player = $room->players()->create([
                'name' => trim($name),
                'token' => Str::uuid(),
                'is_host' => $index === 0,
                'turn_order' => $index,
            ]);
        }

        $host = $room->players()->where('is_host', true)->firstOrFail();
        session()->put("rooms.{$room->id}", $host->token);

        return redirect()->route('rooms.show', $room);
    }

    private function player(Request $request, Room $room): Player
    {
        return $room->players()
            ->where('token', $request->session()->get("rooms.{$room->id}"))
            ->firstOrFail();
    }

    private function authorizeHost(Request $request, Room $room): void
    {
        abort_unless($this->player($request, $room)->is_host, 403);
    }
}
