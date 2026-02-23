<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'uuid' => Str::uuid(),
            'type' => 'individual',
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '07' . fake()->numerify('########'),
            'staff_no' => fake()->optional()->bothify('STF-####'),
            'ihrm_no' => fake()->optional()->bothify('IHRM-#####'),
            'company_name' => null,
            'company_email' => null,
            'company_phone' => null,
            'number_of_attendees' => 1,
            'amount' => 2500,
            'status' => 'paid',
            'max_scans' => 1,
            'scan_count' => 0,
        ];
    }

    public function corporate(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'corporate',
            'name' => null,
            'email' => null,
            'phone' => null,
            'staff_no' => null,
            'ihrm_no' => null,
            'company_name' => fake()->company(),
            'company_email' => fake()->companyEmail(),
            'company_phone' => '07' . fake()->numerify('########'),
            'number_of_attendees' => fake()->numberBetween(2, 8),
            'amount' => 15000,
            'max_scans' => 5,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
