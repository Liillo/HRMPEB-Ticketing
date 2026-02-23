<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::create([
            'name' => 'Tech Conference 2026',
            'description' => 'Annual technology conference featuring the latest innovations and industry leaders.',
            'event_date' => now()->addDays(30),
            'location' => 'KICC, Nairobi',
            'individual_price' => 2500,
            'corporate_price' => 50000,
            'max_corporate_attendees' => 10,
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Music Festival 2026',
            'description' => 'Three-day music festival with top international and local artists.',
            'event_date' => now()->addDays(60),
            'location' => 'Uhuru Gardens, Nairobi',
            'individual_price' => 1500,
            'corporate_price' => 35000,
            'max_corporate_attendees' => 10,
            'is_active' => true,
        ]);

        Event::create([
            'name' => 'Business Summit',
            'description' => 'Network with business leaders and explore new opportunities.',
            'event_date' => now()->addDays(45),
            'location' => 'Radisson Blu, Nairobi',
            'individual_price' => 3000,
            'corporate_price' => 60000,
            'max_corporate_attendees' => 10,
            'is_active' => true,
        ]);
    }
}
