@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .dashboard-wrap { padding: 0; }
    .dash-header { margin-bottom: 24px; }
    .dash-title { color: var(--color-primary); margin-bottom: 6px; }
    .dash-subtitle { color: var(--text-secondary); font-size: 14px; }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 26px;
    }

    .stat-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .stat-card {
        position: relative;
        border: 1px solid var(--color-border);
        border-radius: 12px;
        padding: 16px 18px;
        background: #fff;
        min-height: 108px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 12px 0 0 12px;
        background: var(--stat-accent, var(--color-primary));
    }

    .stat-card--total {
        --stat-accent: var(--color-accent);
        background: linear-gradient(180deg, #faf4eb 0%, #ffffff 100%);
    }

    .stat-card--paid {
        --stat-accent: var(--color-success);
        background: linear-gradient(180deg, #eef8f1 0%, #ffffff 100%);
    }

    .stat-card--pending {
        --stat-accent: #b07a2f;
        background: linear-gradient(180deg, #fff7ea 0%, #ffffff 100%);
    }

    .stat-card--failed {
        --stat-accent: var(--color-error);
        background: linear-gradient(180deg, #fdf1ef 0%, #ffffff 100%);
    }

    .stat-card--scans {
        --stat-accent: #4f7388;
        background: linear-gradient(180deg, #eef5f8 0%, #ffffff 100%);
    }

    .stat-card--revenue {
        --stat-accent: var(--color-primary);
        background: linear-gradient(180deg, #fcf7ee 0%, #ffffff 100%);
    }

    .stat-link:hover .stat-card {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(45, 36, 22, 0.08);
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
        color: var(--color-primary);
    }

    .stat-note {
        margin-top: 6px;
        font-size: 12px;
        color: var(--text-secondary);
    }

    .lists-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .list-card {
        border: 1px solid var(--color-border);
        border-radius: 12px;
        background: #fff;
        overflow: hidden;
    }

    .list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        border-bottom: 1px solid var(--color-border);
    }

    .list-head h2 {
        color: var(--color-primary);
        margin: 0;
        font-size: 17px;
    }

    .list-body { padding: 0 12px 12px; }

    .list-item {
        padding: 12px 6px;
        border-bottom: 1px solid var(--color-border);
    }

    .list-item:last-child { border-bottom: none; }

    .list-main {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .list-id {
        font-family: monospace;
        color: var(--color-primary);
        font-size: 12px;
    }

    .list-name { font-size: 14px; font-weight: 600; }
    .list-meta { color: var(--text-secondary); font-size: 12px; }

    @media (max-width: 1024px) {
        .metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .lists-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .metrics-grid { grid-template-columns: 1fr; }
        .stat-card { min-height: 92px; }
        .stat-value { font-size: 24px; }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrap">
    <div class="dash-header">
        <h1 class="dash-title">Dashboard</h1>
    </div>

    <div class="metrics-grid">
        <a class="stat-link" href="{{ route('admin.tickets') }}">
            <div class="stat-card stat-card--total">
                <div class="stat-label">Total Tickets</div>
                <div class="stat-value">{{ $stats['total_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'paid']) }}">
            <div class="stat-card stat-card--paid">
                <div class="stat-label">Paid Tickets</div>
                <div class="stat-value">{{ $stats['paid_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'pending']) }}">
            <div class="stat-card stat-card--pending">
                <div class="stat-label">Pending Tickets</div>
                <div class="stat-value">{{ $stats['pending_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'failed']) }}">
            <div class="stat-card stat-card--failed">
                <div class="stat-label">Failed Tickets</div>
                <div class="stat-value">{{ $stats['failed_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['scan' => 'scanned']) }}">
            <div class="stat-card stat-card--scans">
                <div class="stat-label">Total Scans</div>
                <div class="stat-value">{{ $stats['total_scans'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'paid']) }}">
            <div class="stat-card stat-card--revenue">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">{{ number_format($stats['total_revenue'], 0) }}</div>
                <div class="stat-note">KES</div>
            </div>
        </a>
    </div>

    <div class="lists-grid">
        <div class="list-card">
            <div class="list-head">
                <h2>Recently Scanned</h2>
                <a href="{{ route('admin.tickets', ['scan' => 'scanned']) }}" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">View all</a>
            </div>
            <div class="list-body">
                @forelse($recent_scanned_tickets as $ticket)
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-name">{{ $ticket->type === 'individual' ? $ticket->name : $ticket->company_name }}</div>
                            <div class="list-id">{{ substr($ticket->uuid, 0, 10) }}...</div>
                        </div>
                        <div class="list-meta">
                            {{ $ticket->latestScan && $ticket->latestScan->admin ? $ticket->latestScan->admin->name : 'System' }}
                            · {{ $ticket->scans_max_scanned_at ? \Carbon\Carbon::parse($ticket->scans_max_scanned_at)->format('M d, g:i A') : '-' }}
                        </div>
                    </div>
                @empty
                    <div class="list-item"><div class="list-meta">No scanned tickets yet.</div></div>
                @endforelse
            </div>
        </div>

        <div class="list-card">
            <div class="list-head">
                <h2>Recently Paid</h2>
                <a href="{{ route('admin.tickets', ['status' => 'paid']) }}" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">View all</a>
            </div>
            <div class="list-body">
                @forelse($recent_paid_tickets as $ticket)
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-name">{{ $ticket->type === 'individual' ? $ticket->name : $ticket->company_name }}</div>
                            <div class="list-id">{{ substr($ticket->uuid, 0, 10) }}...</div>
                        </div>
                        <div class="list-meta">KES {{ number_format($ticket->amount, 0) }} · {{ $ticket->payment_max_updated_at ? \Carbon\Carbon::parse($ticket->payment_max_updated_at)->format('M d, g:i A') : $ticket->updated_at->format('M d, g:i A') }}</div>
                    </div>
                @empty
                    <div class="list-item"><div class="list-meta">No paid tickets yet.</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
