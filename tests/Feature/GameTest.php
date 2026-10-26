<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\WordPair;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        WordPair::factory()->create(['word_a' => 'Kopi', 'word_b' => 'Teh']);
    }

    public function test_pass_and_play_can_complete_reveal_for_every_player(): void
    {
        $room = $this->createPassRoom();

        $this->post(route('rooms.start', $room))->assertRedirect();
        $this->assertSame('reveal', $room->fresh()->status);
        $this->assertSame(4, $room->players()->whereNotNull('role')->count());
        $this->assertSame(1, $room->players()->where('role', 'mr_white')->count());
        $this->assertSame(1, $room->players()->where('role', 'undercover')->count());

        foreach ($room->players as $player) {
            $this->get(route('rooms.show', $room))->assertOk()->assertSee($player->name);
            $this->post(route('rooms.reveal', $room))->assertRedirect();
        }

        $this->assertSame('playing', $room->fresh()->status);
    }

    public function test_mr_white_can_win_by_guessing_the_civilian_word(): void
    {
        $room = $this->startedRoom();
        $white = $room->players()->where('role', 'mr_white')->firstOrFail();

        $this->post(route('players.eliminate', [$room, $white]));
        $this->assertSame('white_guess', $room->fresh()->status);

        $this->post(route('rooms.guess', $room), ['word' => '  '.mb_strtoupper($room->fresh()->civilian_word).'  ']);

        $this->assertSame('ended', $room->fresh()->status);
        $this->assertSame('mr_white', $room->fresh()->winner);
    }

    public function test_civilians_win_after_all_impostors_are_eliminated(): void
    {
        $room = $this->startedRoom();

        foreach ($room->players()->whereIn('role', ['undercover', 'mr_white'])->get() as $player) {
            $this->post(route('players.eliminate', [$room, $player]));
            if ($player->role === 'mr_white') {
                $this->post(route('rooms.guess', $room), ['word' => 'salah']);
            }
        }

        $this->assertSame('ended', $room->fresh()->status);
        $this->assertSame('civilians', $room->fresh()->winner);
    }

    public function test_undercover_wins_when_only_two_players_remain(): void
    {
        $room = $this->startedRoom();

        foreach ($room->players()->where('role', 'civilian')->get() as $civilian) {
            $this->post(route('players.eliminate', [$room, $civilian]));
        }

        $this->assertSame('ended', $room->fresh()->status);
        $this->assertSame('infiltrators', $room->fresh()->winner);
    }

    public function test_multiplayer_players_can_join_but_strangers_cannot_read_roles(): void
    {
        $hostResponse = $this->post(route('rooms.multiplayer'), ['name' => 'Host']);
        $room = Room::latest('id')->firstOrFail();
        $hostToken = session("rooms.{$room->id}");
        $hostResponse->assertRedirect(route('rooms.show', $room));

        $this->flushSession()->post(route('rooms.join'), ['code' => strtolower($room->code), 'name' => 'Siti'])
            ->assertRedirect(route('rooms.show', $room));
        $this->flushSession()->post(route('rooms.join'), ['code' => $room->code, 'name' => 'Andi']);
        $this->flushSession()->post(route('rooms.join'), ['code' => $room->code, 'name' => 'Dewi']);
        $this->assertCount(4, $room->players()->get());

        $this->flushSession()->get(route('rooms.show', $room))->assertNotFound();
        $this->withSession(["rooms.{$room->id}" => $hostToken])->post(route('rooms.start', $room));
        $this->assertSame('playing', $room->fresh()->status);
    }

    public function test_only_host_can_start_or_eliminate(): void
    {
        $room = $this->createPassRoom();
        $room->players()->where('is_host', false)->first()->update(['is_host' => true]);
        $host = $room->players()->where('turn_order', 0)->firstOrFail();
        $host->update(['is_host' => false]);

        $this->post(route('rooms.start', $room))->assertForbidden();
        $this->assertSame('lobby', $room->fresh()->status);
    }

    public function test_game_requires_at_least_four_unique_players(): void
    {
        $this->from(route('home'))->post(route('rooms.pass'), ['names' => ['Budi', 'Siti', 'Budi']])
            ->assertRedirect(route('home'))->assertSessionHasErrors('names');

        $this->assertSame(0, Room::count());
    }

    public function test_game_without_undercover_works_with_three_players(): void
    {
        $this->post(route('rooms.pass'), ['names' => ['Budi', 'Siti', 'Andi'], 'has_undercover' => '0']);
        $room = Room::latest('id')->firstOrFail();
        $this->assertFalse($room->has_undercover);

        $this->post(route('rooms.start', $room));
        $room = $room->fresh();
        $this->assertSame(0, $room->players()->where('role', 'undercover')->count());
        $this->assertSame(1, $room->players()->where('role', 'mr_white')->count());
        $this->assertSame(2, $room->players()->where('role', 'civilian')->count());
    }

    public function test_eliminated_player_and_role_are_shown_to_the_group(): void
    {
        $room = $this->startedRoom();
        $civilian = $room->players()->where('role', 'civilian')->firstOrFail();

        $this->post(route('players.eliminate', [$room, $civilian]));

        $this->get(route('rooms.show', $room))
            ->assertOk()
            ->assertSee('Pemain Tereliminasi')
            ->assertSee($civilian->name)
            ->assertSee('CREWMATE');
    }

    private function createPassRoom(): Room
    {
        $this->post(route('rooms.pass'), ['names' => ['Budi', 'Siti', 'Andi', 'Dewi']]);

        return Room::latest('id')->firstOrFail();
    }

    private function startedRoom(): Room
    {
        $room = $this->createPassRoom();
        $this->post(route('rooms.start', $room));
        $room->update(['status' => 'playing']);

        return $room;
    }
}
