<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class TicketPurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;
    public $payment;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
        $this->payment = $this->resolveBookingPayment($ticket);
    }

    public function build()
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('tickets.pdf', [
            'ticket' => $this->ticket,
            'payment' => $this->payment,
        ]);
        
        return $this->subject('Your Ticket for ' . $this->ticket->event->name)
                    ->view('emails.ticket-purchased')
                    ->attachData($pdf->output(), 'ticket-' . $this->ticket->uuid . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

    private function resolveBookingPayment(Ticket $ticket): ?Payment
    {
        $ticket->loadMissing('payment');

        if ($ticket->payment) {
            return $ticket->payment;
        }

        if (!Schema::hasColumn('tickets', 'corporate_booking_ref') || !$ticket->corporate_booking_ref) {
            return null;
        }

        $paymentTicket = Ticket::with('payment')
            ->where('corporate_booking_ref', $ticket->corporate_booking_ref)
            ->whereHas('payment')
            ->first();

        return $paymentTicket?->payment;
    }
}
