<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' Conference',
            'description' => fake()->paragraph(),
            'event_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'location' => fake()->city() . ', Kenya',
            'individual_price' => fake()->randomElement([1000, 2500, 5000]),
            'corporate_price' => fake()->randomElement([10000, 15000, 20000]),
            'max_corporate_attendees' => 8,
            'is_active' => true,
        ];
    }
}