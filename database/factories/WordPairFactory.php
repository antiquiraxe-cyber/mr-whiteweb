<?php

namespace Database\Factories;

use App\Models\WordPair;
use Illuminate\Database\Eloquent\Factories\Factory;

class WordPairFactory extends Factory
{
    protected $model = WordPair::class;

    public function definition(): array
    {
        return [
            'word_a' => $this->faker->word(),
            'word_b' => $this->faker->word(),
        ];
    }
}
