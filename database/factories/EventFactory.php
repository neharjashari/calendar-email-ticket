<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'event_id' => $this->faker->unique()->numberBetween(1, 100000),
            'title' => $this->faker->sentence,
            'changed' => $this->faker->dateTime,
            'start' => $this->faker->dateTime,
            'end' => $this->faker->dateTime,
        ];
    }
}
