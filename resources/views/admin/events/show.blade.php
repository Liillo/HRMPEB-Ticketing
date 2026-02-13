@extends('layouts.admin')
@section('title', 'Event Details')
@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('admin.events.index') }}" style="color: var(--color-primary); text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Events
    </a>
</div>

<h1 style="color: var(--color-primary); margin-bottom: 32px;">{{ $event->name }}</h1>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Tickets</h3>
        <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ $stats['total_tickets'] }}</p>
    </div>
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Paid Tickets</h3>
        <p style="font-size: 36px; font-weight: 600; color: var(--color-success);">{{ $stats['paid_tickets'] }}</p>
    </div>
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Scanned</h3>
        <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ $stats['scanned_tickets'] }}</p>
    </div>
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Unscanned</h3>
        <p style="font-size: 36px; font-weight: 600; color: #f0ad4e;">{{ $stats['unscanned_tickets'] }}</p>
    </div>
    <div class="card">
        <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Revenue</h3>
        <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ number_format($stats['total_revenue'], 0) }}</p>
        <small>KES</small>
    </div>
</div>

<div class="card">
    <h2>Event Tickets</h2>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Customer</th>
                <th>Email</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Scans</th>
            </tr>
        </thead>
        <tbody>
            @foreach($event->tickets as $ticket)
            <tr>
                <td>{{ ucfirst($ticket->type) }}</td>
                <td>{{ $ticket->type === 'individual' ? $ticket->name : $ticket->company_name }}</td>
                <td>{{ $ticket->type === 'individual' ? $ticket->email : $ticket->company_email }}</td>
                <td>KES {{ number_format($ticket->amount, 0) }}</td>
                <td><span class="badge">{{ ucfirst($ticket->status) }}</span></td>
                <td>{{ $ticket->scan_count }} / {{ $ticket->max_scans }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection