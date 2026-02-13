@extends('layouts.admin')

@section('title', 'All Tickets')

@push('styles')
<style>
    .admin-pagination nav {
        display: flex;
        justify-content: center;
    }

    .admin-pagination .pagination {
        list-style: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        padding: 0;
    }

    .admin-pagination .pagination li a,
    .admin-pagination .pagination li span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        border-radius: 8px;
        border: 1px solid var(--color-border);
        background: #fff;
        color: var(--text-primary);
        text-decoration: none;
        font-size: 13px;
        line-height: 1;
    }

    .admin-pagination .pagination li.active span {
        background: var(--color-primary);
        border-color: var(--color-primary);
        color: #fff;
    }

    .admin-pagination .pagination li.disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .admin-pagination .pagination li a:hover {
        background: var(--color-muted);
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 16px;">
    <h1 style="color: var(--color-primary);"><i class="fas fa-ticket-alt"></i> All Tickets</h1>
</div>

<div class="card" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.tickets') }}">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div class="form-group" style="margin-bottom: 0;">
                <input type="search" name="search" placeholder="Search..." value="{{ request('search') }}">
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="status">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <select name="type">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="corporate" {{ request('type') == 'corporate' ? 'selected' : '' }}>Corporate</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
</div>

<div class="card">
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>UUID</th>
                    <th>Type</th>
                    <th>Name/Company</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Scan Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td style="font-family: monospace; font-size: 12px;">{{ substr($ticket->uuid, 0, 13) }}...</td>
                    <td>
                        <span style="padding: 4px 8px; border-radius: 12px; font-size: 12px; background: var(--color-muted);">
                            {{ ucfirst($ticket->type) }}
                        </span>
                    </td>
                    <td>{{ $ticket->type === 'individual' ? $ticket->name : $ticket->company_name }}</td>
                    <td>{{ $ticket->type === 'individual' ? $ticket->email : $ticket->company_email }}</td>
                    <td>KES {{ number_format($ticket->amount, 0) }}</td>
                    <td>
                        <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; 
                            @if($ticket->status === 'paid') background: #d4edda; color: #155724;
                            @elseif($ticket->status === 'pending') background: #fff3cd; color: #856404;
                            @else background: #f8d7da; color: #721c24; @endif">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </td>
                    <td>
                        @if($ticket->status === 'paid')
                            @if($ticket->scan_count === 0)
                                <span class="scan-badge not-scanned">
                                    <i class="fas fa-clock"></i> Not Scanned
                                </span>
                            @elseif($ticket->type === 'corporate' && $ticket->scan_count < $ticket->max_scans)
                                <span class="scan-badge partial">
                                    <i class="fas fa-user-check"></i> {{ $ticket->scan_count }}/{{ $ticket->max_scans }} In
                                </span>
                            @elseif($ticket->scan_count >= $ticket->max_scans)
                                <span class="scan-badge scanned">
                                    <i class="fas fa-check-double"></i> All In
                                </span>
                            @else
                                <span class="scan-badge scanned">
                                    <i class="fas fa-check"></i> Scanned In
                                </span>
                            @endif
                        @else
                            <span style="color: var(--text-secondary); font-size: 12px;">—</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.ticket.detail', $ticket->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">View</a>
                        @if($ticket->status === 'paid')
                        <a href="{{ route('admin.ticket.download', $ticket->id) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">PDF</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding: 24px; text-align: center; color: var(--text-secondary);">No tickets found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="admin-pagination" style="margin-top: 24px;">
        {{ $tickets->withQueryString()->links('pagination::default') }}
    </div>
</div>
@endsection
