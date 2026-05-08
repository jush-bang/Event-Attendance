<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Session;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => 1, // Will be overridden when creating
            'session_number' => $this->faker->numberBetween(1, 3),
            'day_number' => $this->faker->numberBetween(1, 5),
            'session_date' => $this->faker->date(),
            'status' => 'upcoming',
            'start_time' => null,
            'end_time' => null,
            'user_id' => null,
        ];
    }
}