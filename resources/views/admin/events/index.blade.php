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
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .event-card .event-title {
        font-size: 17px;
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 10px;
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
        margin-top: 12px;
    }

    @media (max-width: 768px) {
        .events-grid { gap: 10px; }

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
            <div class="card event-card">
                <div class="event-title">{{ $event->name }}</div>

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
                        <div class="event-meta-label">Scanned/Unscanned</div>
                        <div class="event-meta-value">{{ $event->scannedTicketsCount() }} / {{ $event->unscannedTicketsCount() }}</div>
                    </div>
                </div>

                <div class="event-actions">
                    <form method="POST" action="{{ route('admin.events.toggle', $event->id) }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn {{ $event->is_active ? 'btn-success' : 'btn-secondary' }}" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-{{ $event->is_active ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $event->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                        <i class="fas fa-eye"></i> View
                    </a>
                    <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
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
