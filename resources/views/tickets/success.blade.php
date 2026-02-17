@extends('layouts.app')

@section('title', 'Payment Successful')

@section('content')
<div class="container">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="max-width: 600px; width: 100%;">
            <div class="card" style="text-align: center;">
                <div style="margin-bottom: 20px;">
                    <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo" style="max-width: 180px; width: 100%; height: auto;">
                </div>
                <div style="width: 80px; height: 80px; margin: 0 auto 24px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                
                <h1 style="color: var(--color-primary); margin-bottom: 16px;">Payment Successful!</h1>
                <p style="color: var(--text-secondary); margin-bottom: 32px;">
                    Your ticket has been generated successfully.
                </p>
                
                <div style="background: var(--color-muted); padding: 24px; border-radius: 8px; margin-bottom: 24px;">
                    @if($ticket->type === 'individual')
                        <p style="margin-bottom: 8px;"><strong>Name:</strong> {{ $ticket->name }}</p>
                        <p style="margin-bottom: 8px;"><strong>Email:</strong> {{ $ticket->email }}</p>
                    @else
                        <p style="margin-bottom: 8px;"><strong>Company:</strong> {{ $ticket->company_name }}</p>
                        <p style="margin-bottom: 8px;"><strong>Email:</strong> {{ $ticket->company_email }}</p>
                        <p style="margin-bottom: 8px;"><strong>Phone:</strong> {{ $ticket->company_phone }}</p>
                        <p style="margin-bottom: 8px;"><strong>Attendees:</strong> {{ $ticket->number_of_attendees }} {{ $ticket->number_of_attendees == 1 ? 'Person' : 'People' }}</p>
                    @endif
                    <p style="margin-bottom: 8px;"><strong>Ticket ID:</strong> {{ $ticket->uuid }}</p>
                    <p><strong>Amount Paid:</strong> KES {{ number_format($ticket->amount, 0) }}</p>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <a href="{{ route('ticket.show', $ticket->uuid) }}" class="btn btn-primary">View Ticket</a>
                    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-secondary">Download PDF</a>
                </div>

                <p style="margin-top: 14px; color: var(--text-secondary); font-size: 14px;">
                    Closed this page by mistake?
                    <a href="{{ route('ticket.retrieve.form') }}" style="color: var(--color-primary); font-weight: 600;">Retrieve your ticket here</a>.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
