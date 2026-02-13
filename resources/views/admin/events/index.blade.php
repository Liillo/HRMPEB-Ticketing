@extends('layouts.admin')

@section('title', 'Events Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
    <h1 style="color: var(--color-primary);"><i class="fas fa-calendar-alt"></i> Events</h1>
    <a href="{{ route('admin.events.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Event
    </a>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Location</th>
                    <th>Tickets Sold</th>
                    <th>Scanned/Unscanned</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td><strong>{{ $event->name }}</strong></td>
                    <td>{{ $event->event_date->format('M d, Y') }}</td>
                    <td>{{ $event->location ?? 'N/A' }}</td>
                    <td>{{ $event->paid_tickets_count }}</td>
                    <td>{{ $event->scannedTicketsCount() }} / {{ $event->unscannedTicketsCount() }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.events.toggle', $event->id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn {{ $event->is_active ? 'btn-success' : 'btn-secondary' }}" style="padding: 4px 12px; font-size: 12px;">
                                <i class="fas fa-{{ $event->is_active ? 'check-circle' : 'times-circle' }}"></i>
                                {{ $event->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.events.show', $event->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-secondary);">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 12px; display: block;"></i>
                        No events found. Create your first event!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection