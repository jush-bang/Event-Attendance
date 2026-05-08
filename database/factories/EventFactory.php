<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'e_name' => $this->faker->sentence(3),
            'start_date' => $this->faker->date(),
            'end_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'end_time' => $this->faker->time(),
            'sessions' => $this->faker->numberBetween(1, 3),
            'e_location' => $this->faker->address(),
            'require_action_prompts' => $this->faker->boolean(),
            'e_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}