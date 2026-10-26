<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlayerFactory extends Factory
{
    protected $model = Player::class;

    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'name' => $this->faker->firstName(),
            'token' => Str::uuid(),
            'is_host' => false,
            'is_alive' => true,
            'turn_order' => 0,
        ];
    }
}
