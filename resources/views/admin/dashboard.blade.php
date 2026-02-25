@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .dashboard-wrap { padding: 4px 2px; }
    .dash-header {
        margin-bottom: 22px;
        padding: 16px 18px;
        border-radius: 14px;
        background:
            radial-gradient(120% 140% at 0% 0%, rgba(244, 180, 0, 0.2) 0%, rgba(244, 180, 0, 0) 42%),
            linear-gradient(180deg, #ffffff 0%, #ffffff 100%);
        border: 1px solid rgba(31, 60, 136, 0.14);
    }
    .dash-title { color: var(--color-primary); margin-bottom: 6px; letter-spacing: 0.01em; }
    .dash-subtitle { color: var(--text-secondary); font-size: 14px; }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 24px;
        align-items: stretch;
    }

    .stat-link {
        text-decoration: none;
        color: inherit;
        display: block;
        height: 100%;
    }

    .stat-card {
        position: relative;
        border: 1px solid var(--color-border);
        border-radius: 16px;
        padding: 16px 16px 14px;
        background: #fff;
        min-height: 122px;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        box-shadow: 0 6px 20px rgba(45, 36, 22, 0.06);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        border-radius: 16px 0 0 16px;
        background: var(--stat-accent, var(--color-primary));
    }

    .stat-card::after {
        content: "";
        position: absolute;
        right: -40px;
        top: -45px;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: color-mix(in srgb, var(--stat-accent, var(--color-primary)) 16%, transparent);
        pointer-events: none;
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

    .stat-card--cheque {
        --stat-accent: #8f5f2a;
        background: linear-gradient(180deg, #fff2e6 0%, #ffffff 100%);
    }

    .stat-link:hover .stat-card {
        transform: translateY(-4px);
        border-color: color-mix(in srgb, var(--stat-accent, var(--color-primary)) 38%, var(--color-border));
        box-shadow: 0 14px 28px rgba(45, 36, 22, 0.14);
    }

    .stat-label {
        color: var(--text-secondary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 10px;
    }

    .stat-icon {
        position: absolute;
        top: 12px;
        right: 12px;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: var(--stat-accent, var(--color-primary));
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.85), 0 4px 10px rgba(0, 0, 0, 0.08);
    }

    .stat-value {
        font-size: 31px;
        font-weight: 700;
        line-height: 1.05;
        color: var(--color-primary);
        letter-spacing: -0.02em;
    }

    .stat-note {
        margin-top: 7px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--text-secondary);
    }

    .lists-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        align-items: stretch;
        grid-auto-rows: 1fr;
    }

    .list-card {
        border: 1px solid var(--color-border);
        border-radius: 16px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(45, 36, 22, 0.08);
        display: flex;
        flex-direction: column;
    }

    .list-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px 13px;
        border-bottom: 1px solid var(--color-border);
        background: linear-gradient(180deg, #fffefb 0%, #fff 100%);
    }

    .list-head h2 {
        color: var(--color-primary);
        margin: 0;
        font-size: 17px;
    }

    .list-body {
        padding: 2px 12px 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
    }

    .list-item {
        position: relative;
        padding: 12px 10px;
        border: 1px solid #eee4d2;
        border-radius: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #fffdfa 100%);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .list-item:hover {
        transform: translateY(-1px);
        border-color: #e3d3ba;
        box-shadow: 0 10px 18px rgba(45, 36, 22, 0.08);
    }

    .list-item::before {
        content: "";
        position: absolute;
        left: 0;
        top: 10px;
        bottom: 10px;
        width: 3px;
        border-radius: 999px;
        background: rgba(31, 60, 136, 0.18);
    }

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
        background: #f7f2ea;
        border: 1px solid #e9decb;
        border-radius: 999px;
        padding: 2px 8px;
    }

    .list-name { font-size: 14px; font-weight: 700; color: #2f2416; }
    .list-meta { color: var(--text-secondary); font-size: 12px; margin-top: 2px; line-height: 1.35; }

    .list-actions {
        margin-top: 8px;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    @media (max-width: 1360px) {
        .metrics-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .lists-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

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
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-label">Total Tickets</div>
                <div class="stat-value">{{ $stats['total_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'paid']) }}">
            <div class="stat-card stat-card--paid">
                <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
                <div class="stat-label">Paid Tickets</div>
                <div class="stat-value">{{ $stats['paid_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'pending']) }}">
            <div class="stat-card stat-card--pending">
                <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-label">Pending Tickets</div>
                <div class="stat-value">{{ $stats['pending_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'failed']) }}">
            <div class="stat-card stat-card--failed">
                <div class="stat-icon"><i class="fas fa-circle-xmark"></i></div>
                <div class="stat-label">Failed Tickets</div>
                <div class="stat-value">{{ $stats['failed_tickets'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['scan' => 'scanned']) }}">
            <div class="stat-card stat-card--scans">
                <div class="stat-icon"><i class="fas fa-qrcode"></i></div>
                <div class="stat-label">Total Scans</div>
                <div class="stat-value">{{ $stats['total_scans'] }}</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.tickets', ['status' => 'paid']) }}">
            <div class="stat-card stat-card--revenue">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">{{ number_format($stats['total_revenue'], 0) }}</div>
                <div class="stat-note">KES</div>
            </div>
        </a>
        <a class="stat-link" href="{{ route('admin.dashboard') }}#pending-cheque-approvals">
            <div class="stat-card stat-card--cheque">
                <div class="stat-icon"><i class="fas fa-file-signature"></i></div>
                <div class="stat-label">Pending Cheques</div>
                <div class="stat-value">{{ $stats['pending_cheque_payments'] }}</div>
                <div class="stat-note">Need approval</div>
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
                @forelse($recent_scans as $scan)
                    @php
                        $ticket = $scan->ticket;
                        $displayName = 'Unknown Ticket';

                        if ($ticket) {
                            $displayName = $ticket->name ?: $ticket->company_name;
                        }
                    @endphp
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-name">{{ $displayName }}</div>
                            <div class="list-id">{{ $ticket ? substr($ticket->uuid, 0, 10) . '...' : '-' }}</div>
                        </div>
                        <div class="list-meta">
                            {{ $scan->admin ? $scan->admin->name : 'System' }}
                            &middot; {{ $scan->scanned_at ? \Carbon\Carbon::parse($scan->scanned_at)->format('M d, g:i A') : '-' }}
                        </div>
                        @if($ticket && ($ticket->staff_no || $ticket->ihrm_no))
                            <div class="list-meta">
                                {{ $ticket->staff_no ? 'Staff No.: ' . $ticket->staff_no : '' }}
                                @if($ticket->staff_no && $ticket->ihrm_no) &middot; @endif
                                {{ $ticket->ihrm_no ? 'IHRM No.: ' . $ticket->ihrm_no : '' }}
                            </div>
                        @endif
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
                            <div class="list-name">{{ $ticket->name ?: $ticket->company_name }}</div>
                            <div class="list-id">{{ substr($ticket->uuid, 0, 10) }}...</div>
                        </div>
                        <div class="list-meta">KES {{ number_format($ticket->amount, 0) }} &middot; {{ $ticket->payment_max_updated_at ? \Carbon\Carbon::parse($ticket->payment_max_updated_at)->format('M d, g:i A') : $ticket->updated_at->format('M d, g:i A') }}</div>
                        @if($ticket->staff_no || $ticket->ihrm_no)
                            <div class="list-meta">
                                {{ $ticket->staff_no ? 'Staff No.: ' . $ticket->staff_no : '' }}
                                @if($ticket->staff_no && $ticket->ihrm_no) &middot; @endif
                                {{ $ticket->ihrm_no ? 'IHRM No.: ' . $ticket->ihrm_no : '' }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="list-item"><div class="list-meta">No paid tickets yet.</div></div>
                @endforelse
            </div>
        </div>

        <div class="list-card" id="pending-cheque-approvals">
            <div class="list-head">
                <h2>Pending Cheque Approvals</h2>
                <a href="{{ route('admin.tickets', ['status' => 'pending']) }}" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">All pending tickets</a>
            </div>
            <div class="list-body">
                @forelse($pending_cheque_payments as $payment)
                    @php
                        $ticket = $payment->ticket;
                        $displayName = $ticket?->name ?: $ticket?->company_name ?: 'Unknown';
                    @endphp
                    <div class="list-item">
                        <div class="list-main">
                            <div class="list-name">{{ $displayName }}</div>
                            <div class="list-id">{{ $ticket ? substr($ticket->uuid, 0, 10) . '...' : '-' }}</div>
                        </div>
                        <div class="list-meta">
                            KES {{ number_format($payment->amount, 0) }}
                            &middot; Cheque {{ $payment->cheque_number ?? 'N/A' }}
                            &middot; {{ $payment->bank_name ?? 'N/A' }}
                        </div>
                        <div class="list-meta">
                            Submitted {{ $payment->updated_at ? \Carbon\Carbon::parse($payment->updated_at)->format('M d, g:i A') : '-' }}
                        </div>
                        <div class="list-actions">
                            @if($ticket)
                                <a href="{{ route('admin.ticket.detail', $ticket->id) }}" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">Open ticket</a>
                            @endif
                            <form method="POST" action="{{ route('admin.payments.approve-cheque', $payment->id) }}" onsubmit="return confirm('Are you sure you want to approve this cheque payment? This will mark the ticket as paid and send ticket email(s).');">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 6px 10px; font-size: 12px;">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject-cheque', $payment->id) }}" onsubmit="return confirm('Are you sure you want to reject this cheque payment? This will mark the ticket as failed.');">
                                @csrf
                                <input type="hidden" name="reason" value="Cheque payment rejected from dashboard.">
                                <button type="submit" class="btn btn-danger" style="padding: 6px 10px; font-size: 12px;">Reject</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="list-item"><div class="list-meta">No cheque payments are waiting for approval.</div></div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

