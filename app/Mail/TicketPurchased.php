<?php

namespace App\Mail;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketPurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $ticket;

    public function __construct(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function build()
    {
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('tickets.pdf', ['ticket' => $this->ticket]);
        
        return $this->subject('Your Ticket for ' . $this->ticket->event->name)
                    ->view('emails.ticket-purchased')
                    ->attachData($pdf->output(), 'ticket-' . $this->ticket->uuid . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}