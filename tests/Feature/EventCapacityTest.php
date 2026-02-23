<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCapacityTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_booking_is_blocked_when_event_is_sold_out(): void
    {
        $event = Event::factory()->create(['max_capacity' => 1]);

        Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'number_of_attendees' => 1,
        ]);

        $response = $this->post('/individual-booking', [
            'event_id' => $event->id,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0712345678',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This event is sold out.');
    }

    public function test_corporate_booking_cannot_exceed_remaining_capacity(): void
    {
        $event = Event::factory()->create(['max_capacity' => 5]);

        Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'number_of_attendees' => 4,
        ]);

        $response = $this->post('/corporate-booking', [
            'event_id' => $event->id,
            'company_name' => 'Acme Ltd',
            'company_email' => 'corp@example.com',
            'company_phone' => '0712345678',
            'number_of_attendees' => 2,
            'attendee_names' => ['A One', 'B Two'],
            'attendee_emails' => ['a@example.com', 'b@example.com'],
            'attendee_phones' => ['0711111111', '0722222222'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['number_of_attendees']);
    }

    public function test_payment_initiation_is_blocked_when_capacity_is_already_reached(): void
    {
        $event = Event::factory()->create(['max_capacity' => 1]);

        $pendingTicket = Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => 'pending',
            'number_of_attendees' => 1,
        ]);

        Ticket::factory()->create([
            'event_id' => $event->id,
            'status' => 'paid',
            'number_of_attendees' => 1,
        ]);

        $response = $this->postJson('/payment/' . $pendingTicket->uuid . '/initiate', [
            'phone' => '0712345678',
        ]);

        $response->assertStatus(409);
        $response->assertJson([
            'success' => false,
            'message' => 'This event is sold out.',
        ]);
    }
}
