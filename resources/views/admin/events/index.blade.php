@extends('layouts.admin')

@section('title', 'Events Management')

@push('styles')
<style>
    .events-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        gap: 12px;
        flex-wrap: wrap;
    }

    .events-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .event-card {
        position: relative;
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 16px 18px;
        background: #fff;
        min-height: 168px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }

    .event-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 12px 0 0 12px;
        background: var(--event-accent, var(--color-primary));
    }

    .event-card--active {
        --event-accent: var(--color-success);
        background: linear-gradient(180deg, #eef8f1 0%, #ffffff 100%);
    }

    .event-card--inactive {
        --event-accent: var(--color-error);
        background: linear-gradient(180deg, #fdf1ef 0%, #ffffff 100%);
    }

    .event-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(45, 36, 22, 0.08);
    }

    .event-title-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        padding-right: 40px;
    }

    .event-card .event-title {
        font-size: 17px;
        font-weight: 600;
        color: var(--color-primary);
        margin: 0;
    }

    .event-status-badge {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-radius: 999px;
        padding: 4px 10px;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .event-status-badge--soldout {
        color: #8a2d25;
        background: #fdecea;
        border-color: #f5c2bc;
    }

    .event-status-badge--open {
        color: #1f5130;
        background: #eaf6ed;
        border-color: #c8e6d0;
    }

    .event-icon {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: var(--event-accent, var(--color-primary));
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .event-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-bottom: 12px;
    }

    .event-meta-label {
        color: var(--text-secondary);
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 4px;
    }

    .event-meta-value {
        font-size: 14px;
        font-weight: 600;
    }

    .event-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 14px;
        position: relative;
        z-index: 1;
    }

    @media (max-width: 1440px) {
        .events-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 768px) {
        .events-grid {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .event-meta {
            grid-template-columns: 1fr;
            gap: 8px;
        }
    }
</style>
@endpush

@section('content')
<div class="events-header">
    <h1 style="color: var(--color-primary);"><i class="fas fa-calendar-alt"></i> Events</h1>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Event
    </a>
</div>

@if($events->count() > 0)
    <div class="events-grid">
        @foreach($events as $event)
            @php
                $paidAttendees = (int) ($event->paid_attendees_count ?? 0);
                $pendingAttendees = (int) ($event->pending_attendees_count ?? 0);
                $reservedAttendees = $paidAttendees + $pendingAttendees;
                $remainingCapacity = max(0, (int) $event->max_capacity - $reservedAttendees);
                $isSoldOut = $remainingCapacity <= 0;
            @endphp
            <div
                class="event-card {{ $event->is_active ? 'event-card--active' : 'event-card--inactive' }}"
                data-url="{{ route('admin.events.show', $event->id) }}"
                onclick="window.location.href=this.dataset.url"
            >
                <div class="event-icon"><i class="fas fa-calendar-alt"></i></div>
                <div class="event-title-row">
                    <div class="event-title">{{ $event->name }}</div>
                    <span class="event-status-badge {{ $isSoldOut ? 'event-status-badge--soldout' : 'event-status-badge--open' }}">
                        {{ $isSoldOut ? 'Sold Out' : 'Open' }}
                    </span>
                </div>

                <div class="event-meta">
                    <div>
                        <div class="event-meta-label">Date</div>
                        <div class="event-meta-value">{{ $event->event_date->format('M d, Y') }}</div>
                    </div>
                    <div>
                        <div class="event-meta-label">Location</div>
                        <div class="event-meta-value">{{ $event->location ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="event-meta-label">Tickets Sold</div>
                        <div class="event-meta-value">{{ $event->paid_tickets_count }}</div>
                    </div>
                    <div>
                        <div class="event-meta-label">Capacity</div>
                        <div class="event-meta-value">{{ $reservedAttendees }} / {{ (int) $event->max_capacity }}</div>
                    </div>
                    <div>
                        <div class="event-meta-label">Remaining Slots</div>
                        <div class="event-meta-value">{{ $remainingCapacity }}</div>
                    </div>
                    <div>
                        <div class="event-meta-label">Scanned/Unscanned</div>
                        <div class="event-meta-value">{{ $event->scannedTicketsCount() }} / {{ $event->unscannedTicketsCount() }}</div>
                    </div>
                </div>

                <div class="event-actions">
                    <form method="POST" action="{{ route('admin.events.toggle', $event->id) }}" style="display: inline;" onclick="event.stopPropagation();">
                        @csrf
                        <button type="submit" class="btn {{ $event->is_active ? 'btn-success' : 'btn-secondary' }}" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-{{ $event->is_active ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $event->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;" onclick="event.stopPropagation();">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;" onclick="event.stopPropagation();">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="card" style="text-align: center; color: var(--text-secondary);">
        <i class="fas fa-inbox" style="font-size: 44px; margin-bottom: 10px; display: block;"></i>
        No events found. Create your first event!
    </div>
@endif
@endsection
