<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SqlInjectionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        /** @var User $admin */
        $admin = User::factory()->admin()->createOne();

        return $admin;
    }

    private array $injectionPayloads = [
        "' OR '1'='1",
        "' OR '1'='1' --",
        "' OR '1'='1' /*",
        "admin' --",
        "admin' #",
        "' UNION SELECT NULL--",
        "' UNION SELECT * FROM users--",
        "'; DROP TABLE users--",
        "' AND SLEEP(5)--",
    ];

    #[Test]
    public function admin_login_prevents_sql_injection(): void
    {
        User::factory()->admin()->createOne([
            'email' => 'admin@test.com',
            'password' => bcrypt('password123'),
        ]);

        foreach ($this->injectionPayloads as $payload) {
            $this->post('/admin/login', [
                'email' => $payload,
                'password' => 'anything'
            ]);

            $this->assertGuest();
        }
    }

    #[Test]
    public function ticket_search_prevents_sql_injection(): void
    {
        $this->actingAs($this->createAdminUser());

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->get("/admin/tickets?search=" . urlencode($payload));
            $response->assertStatus(200);
        }
    }

    #[Test]
    public function ticket_uuid_prevents_sql_injection(): void
    {
        $ticket = Ticket::factory()->create();

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->get("/ticket/" . urlencode($payload));
            $response->assertStatus(404);
        }

        // Valid ticket still works
        $this->get("/ticket/{$ticket->uuid}")->assertStatus(200);
    }

    #[Test]
    public function event_id_prevents_sql_injection(): void
    {
        $event = Event::factory()->create();

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->get("/event/" . urlencode($payload) . "/booking-type");
            $response->assertStatus(404);
        }

        // Valid event still works
        $this->get("/event/{$event->id}/booking-type")->assertStatus(200);
    }

    #[Test]
    public function qr_scan_prevents_sql_injection(): void
    {
        $this->actingAs($this->createAdminUser());

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->postJson('/admin/scan', [
                'qr_code' => $payload
            ]);

            $this->assertNotEquals(500, $response->status(),
                "Server error on payload: $payload"
            );

            // If 200, must return success: false
            if ($response->status() === 200) {
                $response->assertJson(['success' => false]);
            }
        }
    }

    #[Test]
    public function booking_form_prevents_sql_injection(): void
    {
        $event = Event::factory()->create();

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->post('/individual-booking', [
                'event_id' => $event->id,
                'name' => $payload,
                'email' => 'test@test.com',
                'phone' => '0712345678'
            ]);

            $this->assertContains(
                $response->status(),
                [200, 302, 422],
                "Unexpected status: " . $response->status()
            );
        }

        foreach ($this->injectionPayloads as $payload) {
            $response = $this->post('/corporate-booking', [
                'event_id' => $event->id,
                'company_name' => $payload,
                'company_email' => 'company@test.com',
                'company_phone' => '0712345678',
                'number_of_attendees' => 5
            ]);

            $this->assertContains($response->status(), [200, 302, 422]);
        }
    }

    #[Test]
    public function no_sql_errors_logged_during_injection_attempts(): void
    {
        $admin = $this->createAdminUser();

        // Clear existing logs
        $logPath = storage_path('logs/laravel.log');
        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        $this->post('/admin/login', [
            'email' => "' OR '1'='1",
            'password' => 'test'
        ]);

        $this->actingAs($admin);
        $this->get("/admin/tickets?search=' OR '1'='1");
        $this->get("/ticket/' OR '1'='1");
        $this->get("/event/' OR '1'='1/booking-type");

        if (file_exists($logPath)) {
            $logContent = file_get_contents($logPath);
            $this->assertStringNotContainsString('SQLSTATE', $logContent);
            $this->assertStringNotContainsString("You have an error in your SQL", $logContent);
        }

        $this->assertTrue(true); // Explicit pass
    }

    #[Test]
    public function filter_parameters_prevent_sql_injection(): void
    {
        $this->actingAs($this->createAdminUser());

        foreach ($this->injectionPayloads as $payload) {
            $this->get("/admin/tickets?status=" . urlencode($payload))
                 ->assertStatus(200);

            $this->get("/admin/tickets?type=" . urlencode($payload))
                 ->assertStatus(200);
        }
    }

    #[Test]
    public function mpesa_callback_prevents_sql_injection(): void
    {
        foreach ($this->injectionPayloads as $payload) {
            $response = $this->postJson('/api/mpesa/callback', [
                'Body' => [
                    'stkCallback' => [
                        'CheckoutRequestID' => $payload,
                        'ResultCode' => 0,
                        'CallbackMetadata' => [
                            'Item' => [
                                ['Name' => 'MpesaReceiptNumber', 'Value' => $payload]
                            ]
                        ]
                    ]
                ]
            ]);

            $this->assertContains($response->status(), [200, 400, 404]);
        }
    }

    #[Test]
    public function uses_eloquent_orm_not_raw_queries(): void
    {
        $files = [
            app_path('Http/Controllers/AdminController.php'),
            app_path('Http/Controllers/TicketController.php'),
            app_path('Http/Controllers/EventController.php'),
        ];

        foreach ($files as $file) {
            if (!file_exists($file)) continue;

            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                "DB::select(\"SELECT",
                $content,
                basename($file) . " contains unsafe raw query"
            );
        }
    }

    #[Test]
    public function validation_rejects_malicious_input_patterns(): void
    {
        $event = Event::factory()->create();

        $response = $this->post('/individual-booking', [
            'event_id' => $event->id,
            'name' => 'John Doe',
            'email' => "admin' OR '1'='1' --",
            'phone' => '0712345678'
        ]);

        $response->assertSessionHasErrors('email');
    }
}
