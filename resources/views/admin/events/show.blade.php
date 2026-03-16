@extends('layouts.admin')
@section('title', 'Event Details')
@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('admin.events.index') }}" style="color: var(--color-primary); text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Events
    </a>
</div>

<h1 style="color: var(--color-primary); margin-bottom: 32px;">{{ $event->name }}</h1>

@php
    $corporateStatusClass = $stats['is_corporate_sold_out'] ? 'status-danger' : 'status-success';
    $eventStatusClass = $stats['is_sold_out'] ? 'status-danger' : 'status-success';
@endphp

<div class="card" style="margin-bottom: 20px;">
    <h2 style="margin-bottom: 14px;"><i class="fas fa-info-circle"></i> Event Details</h2>
    @if($event->poster_path)
        <div style="margin-bottom: 16px;">
            <img src="{{ asset('storage/' . $event->poster_path) }}" alt="Event Poster" style="width: 100%; max-width: 520px; border-radius: 12px; border: 1px solid var(--color-border);">
        </div>
    @endif
    <div class="event-details-grid">
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Date</div>
            <div style="font-weight: 600;">{{ $event->event_date->format('M d, Y') }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Location</div>
            <div style="font-weight: 600;">{{ $event->location ?: 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Max Capacity</div>
            <div style="font-weight: 700;">{{ $stats['max_capacity'] }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Reserved</div>
            <div style="font-weight: 700;">{{ $stats['paid_attendees'] + $stats['pending_attendees'] }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Max Corporate Tables</div>
            <div style="font-weight: 700;">{{ $stats['max_corporate_tables'] }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Corporate Tables Used</div>
            <div style="font-weight: 700;">{{ $stats['paid_corporate_tables'] + $stats['pending_corporate_tables'] }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Corporate Table Status</div>
            <div class="{{ $corporateStatusClass }}" style="font-weight: 700;">
                {{ $stats['is_corporate_sold_out'] ? 'Sold Out' : $stats['remaining_corporate_tables'] . ' table slots left' }}
            </div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Status</div>
            <div class="{{ $eventStatusClass }}" style="font-weight: 700;">
                {{ $stats['is_sold_out'] ? 'Sold Out' : $stats['remaining_capacity'] . ' slots left' }}
            </div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Created By</div>
            <div style="font-weight: 600;">{{ $event->createdBy?->name ?? 'N/A' }}</div>
        </div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">Last Updated By</div>
            <div style="font-weight: 600;">{{ $event->updatedBy?->name ?? 'N/A' }}</div>
        </div>
    </div>
</div>

<div class="event-stats-grid">
    <div class="event-stat-card event-stat-card--total">
        <div class="event-stat-icon"><i class="fas fa-ticket-alt"></i></div>
        <div class="event-stat-label">Total Tickets</div>
        <div class="event-stat-value">{{ $stats['total_tickets'] }}</div>
    </div>
    <div class="event-stat-card event-stat-card--paid">
        <div class="event-stat-icon"><i class="fas fa-circle-check"></i></div>
        <div class="event-stat-label">Paid Tickets</div>
        <div class="event-stat-value">{{ $stats['paid_tickets'] }}</div>
    </div>
    <div class="event-stat-card event-stat-card--scans">
        <div class="event-stat-icon"><i class="fas fa-qrcode"></i></div>
        <div class="event-stat-label">Scanned</div>
        <div class="event-stat-value">{{ $stats['scanned_tickets'] }}</div>
    </div>
    <div class="event-stat-card event-stat-card--pending">
        <div class="event-stat-icon"><i class="fas fa-user-clock"></i></div>
        <div class="event-stat-label">Unscanned</div>
        <div class="event-stat-value">{{ $stats['unscanned_tickets'] }}</div>
    </div>
    <div class="event-stat-card event-stat-card--revenue">
        <div class="event-stat-icon"><i class="fas fa-coins"></i></div>
        <div class="event-stat-label">Revenue</div>
        <div class="event-stat-value">{{ number_format($stats['total_revenue'], 0) }}</div>
        <div class="event-stat-note">KES</div>
    </div>
</div>

<style>
    .event-details-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .event-stats-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 40px;
    }

    .event-stat-card {
        position: relative;
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 16px 18px;
        background: #fff;
        min-height: 108px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .event-stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 12px 0 0 12px;
        background: var(--stat-accent, var(--color-primary));
    }

    .event-stat-card--total {
        --stat-accent: var(--color-accent);
        background: linear-gradient(180deg, #faf4eb 0%, #ffffff 100%);
    }

    .event-stat-card--paid {
        --stat-accent: var(--color-success);
        background: linear-gradient(180deg, #eef8f1 0%, #ffffff 100%);
    }

    .event-stat-card--pending {
        --stat-accent: #b07a2f;
        background: linear-gradient(180deg, #fff7ea 0%, #ffffff 100%);
    }

    .event-stat-card--scans {
        --stat-accent: #4f7388;
        background: linear-gradient(180deg, #eef5f8 0%, #ffffff 100%);
    }

    .event-stat-card--revenue {
        --stat-accent: var(--color-primary);
        background: linear-gradient(180deg, #fcf7ee 0%, #ffffff 100%);
    }

    .event-stat-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(45, 36, 22, 0.08);
    }

    .event-stat-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: var(--color-primary);
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .event-stat-label {
        color: var(--text-secondary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }

    .event-stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: var(--color-primary);
    }

    .event-stat-note {
        margin-top: 6px;
        font-size: 12px;
        color: var(--text-secondary);
    }

    .status-success {
        color: #1f5130;
    }

    .status-danger {
        color: #8a2d25;
    }

    @media (max-width: 1200px) {
        .event-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .event-stats-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .event-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .event-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }
    }

    @media (max-width: 520px) {
        .event-details-grid {
            grid-template-columns: 1fr;
        }

        .event-stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

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
                <td>{{ $ticket->name ?: $ticket->company_name }}</td>
                <td>{{ $ticket->email ?: $ticket->company_email }}</td>
                <td>KES {{ number_format($ticket->amount, 0) }}</td>
                <td><span class="badge">{{ ucfirst($ticket->status) }}</span></td>
                <td>{{ $ticket->scan_count }} / {{ $ticket->max_scans }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
