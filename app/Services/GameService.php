<?php

namespace App\Services;

use App\Models\Player;
use App\Models\Room;
use App\Models\WordPair;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GameService
{
    public function start(Room $room): void
    {
        DB::transaction(function () use ($room): void {
            $room = Room::whereKey($room)->lockForUpdate()->firstOrFail();
            $players = $room->players()->lockForUpdate()->get();

            if ($players->count() < ($room->has_undercover ? 4 : 3) || ! in_array($room->status, ['lobby', 'ended'], true)) {
                throw ValidationException::withMessages(['game' => 'Minimal '.($room->has_undercover ? '4' : '3').' pemain untuk memulai.']);
            }

            $pair = WordPair::inRandomOrder()->firstOrFail();
            [$civilianWord, $undercoverWord] = random_int(0, 1) === 1
                ? [$pair->word_a, $pair->word_b]
                : [$pair->word_b, $pair->word_a];

            $roles = [
                ...array_fill(0, max(1, intdiv($players->count(), 9) + 1), 'mr_white'),
            ];

            if ($room->has_undercover) {
                array_push($roles, ...array_fill(0, $players->count() >= 6 ? 2 : 1, 'undercover'));
            }

            $roles = array_merge($roles, array_fill(0, $players->count() - count($roles), 'civilian'));
            shuffle($roles);

            foreach ($players->values() as $index => $player) {
                $role = $roles[$index];
                $player->update([
                    'role' => $role,
                    'word' => $role === 'civilian' ? $civilianWord : ($role === 'undercover' ? $undercoverWord : null),
                    'is_alive' => true,
                    'turn_order' => $index,
                ]);
            }

            $room->update([
                'civilian_word' => $civilianWord,
                'undercover_word' => $room->has_undercover ? $undercoverWord : null,
                'status' => $room->mode === 'pass' ? 'reveal' : 'playing',
                'winner' => null,
                'reveal_index' => 0,
            ]);
        });
    }

    public function eliminate(Room $room, Player $target): void
    {
        DB::transaction(function () use ($room, $target): void {
            $room = Room::whereKey($room)->lockForUpdate()->firstOrFail();
            $target = Player::whereKey($target)->lockForUpdate()->firstOrFail();

            if ($room->status !== 'playing' || $target->room_id !== $room->id || ! $target->is_alive) {
                abort(422, 'Pemain tidak dapat dieliminasi sekarang.');
            }

            $target->update(['is_alive' => false]);
            $status = $target->role === 'mr_white' ? 'white_guess' : $this->winner($room);
            $room->update(['status' => $status]);
        });
    }

    public function guess(Room $room, string $guess): void
    {
        DB::transaction(function () use ($room, $guess): void {
            $room = Room::whereKey($room)->lockForUpdate()->firstOrFail();
            abort_if($room->status !== 'white_guess', 422);

            if (mb_strtolower(trim($guess)) === mb_strtolower($room->civilian_word)) {
                $room->update(['status' => 'ended', 'winner' => 'mr_white']);

                return;
            }

            $room->update(['status' => $this->winner($room)]);
        });
    }

    private function winner(Room $room): string
    {
        $alive = $room->players()->where('is_alive', true)->get();
        $undercoverCount = $alive->where('role', 'undercover')->count();
        $civilianCount = $alive->where('role', 'civilian')->count();
        $whiteCount = $alive->where('role', 'mr_white')->count();

        if ($undercoverCount === 0 && $whiteCount === 0) {
            $room->update(['winner' => 'civilians']);

            return 'ended';
        }

        if ($civilianCount === 0 || $alive->count() <= 2) {
            $room->update(['winner' => $undercoverCount > 0 ? 'infiltrators' : 'mr_white']);

            return 'ended';
        }

        return 'playing';
    }
}
