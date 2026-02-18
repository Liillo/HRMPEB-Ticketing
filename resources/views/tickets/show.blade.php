@extends('layouts.app')

@section('title', 'Your Ticket')

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 600px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo" style="max-width: 180px; width: 100%; height: auto;">
            </div>
            <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">Your Ticket</h1>

            @php
                $ticketEmail = $ticket->type === 'individual' ? $ticket->email : $ticket->company_email;
            @endphp
            @if($ticketEmail && !session('hide_ticket_sent_notice'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i class="fas fa-envelope"></i> Your ticket has been sent to <strong>{{ $ticketEmail }}</strong>.
                </div>
            @endif
            
            <div class="card" style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%); color: white; padding: 32px;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <h2 style="margin-bottom: 8px; font-size: 32px;">Event Ticket</h2>
                    @if($ticket->type === 'individual')
                        <p style="opacity: 0.9;">Individual Ticket</p>
                    @else
                        <p style="opacity: 0.9;">Corporate Ticket (Up to 8 People)</p>
                    @endif
                </div>
                
                <div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 24px; color: var(--text-primary);">
                    @if($ticket->type === 'individual')
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Attendee Name</span>
                            <p style="font-weight: 600; font-size: 18px; margin-top: 4px;">{{ $ticket->name }}</p>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Email</span>
                            <p style="font-weight: 500; margin-top: 4px;">{{ $ticket->email }}</p>
                        </div>
                    @else
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Company Name</span>
                            <p style="font-weight: 600; font-size: 18px; margin-top: 4px;">{{ $ticket->company_name }}</p>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Number of Attendees</span>
                            <p style="font-weight: 500; margin-top: 4px;">{{ $ticket->number_of_attendees }} {{ $ticket->number_of_attendees == 1 ? 'Person' : 'People' }}</p>
                        </div>
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Email</span>
                            <p style="font-weight: 500; margin-top: 4px;">{{ $ticket->company_email }}</p>
                        </div>
                        
                    @endif
                    <div style="margin-top: 12px;">
                        <span style="color: var(--text-secondary); font-size: 14px;">Ticket ID</span>
                        <p style="font-weight: 500; font-family: monospace; margin-top: 4px;">{{ $ticket->uuid }}</p>
                    </div>
                </div>
                
                <div style="background: white; padding: 24px; border-radius: 12px; text-align: center;">
                    <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px;">Scan this QR code at the event</p>
                    <div style="background: white; padding: 16px; border-radius: 8px; display: inline-block;">
                        <img src="{{ asset('storage/qrcodes/' . $ticket->uuid . '.svg') }}" alt="QR Code" style="width: 200px; height: 200px;">
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-primary" style="margin-right: 12px;">Download PDF</a>
                <a href="{{ route('home') }}" class="btn btn-secondary">Back to Home</a>
            </div>

        </div>
    </div>
</div>
@endsection
