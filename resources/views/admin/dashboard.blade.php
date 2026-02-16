@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .dashboard-wrap {
        padding: 0;
    }

    .stat-card {
        position: relative;
        border: 1px solid var(--color-border);
        overflow: hidden;
    }

    .stat-card::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--stat-accent, var(--color-primary));
    }

    .stat-card--revenue {
        --stat-accent: var(--color-primary);
        background: linear-gradient(180deg, #fcf7ee 0%, #ffffff 100%);
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

    .stat-card--total {
        --stat-accent: var(--color-accent);
        background: linear-gradient(180deg, #faf4eb 0%, #ffffff 100%);
    }

    .stat-card--scans {
        --stat-accent: #4f7388;
        background: linear-gradient(180deg, #eef5f8 0%, #ffffff 100%);
    }

    .metrics-section {
        margin-bottom: 18px;
    }

    .metrics-title {
        color: var(--text-secondary);
        font-size: 12px;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 14px;
    }

    .stat-card h3 {
        color: var(--text-secondary);
        font-size: 13px;
        margin-bottom: 6px;
    }

    .stat-card p {
        font-size: 30px;
        font-weight: 600;
        line-height: 1.2;
    }

    .stat-value {
        color: var(--stat-accent, var(--color-primary));
    }

    @media (max-width: 768px) {
        .metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .stat-card h3 {
            font-size: 12px;
            margin-bottom: 4px;
        }

        .stat-card p {
            font-size: 22px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrap">
    <h1 style="color: var(--color-primary); margin-bottom: 32px;">Dashboard</h1>
    
    <div class="metrics-section">
        <div class="metrics-title">Payment</div>
        <div class="metrics-grid">
            <div class="card stat-card stat-card--revenue">
                <h3>Total Revenue</h3>
                <p class="stat-value">{{ number_format($stats['total_revenue'], 0) }}</p>
                <small style="color: var(--text-secondary);">KES</small>
            </div>
            
            <div class="card stat-card stat-card--paid">
                <h3>Paid Tickets</h3>
                <p class="stat-value">{{ $stats['paid_tickets'] }}</p>
            </div>
            
            <div class="card stat-card stat-card--pending">
                <h3>Pending Tickets</h3>
                <p class="stat-value">{{ $stats['pending_tickets'] }}</p>
            </div>
            
            <div class="card stat-card stat-card--failed">
                <h3>Failed Tickets</h3>
                <p class="stat-value">{{ $stats['failed_tickets'] }}</p>
            </div>
        </div>
    </div>

    <div class="metrics-section" style="margin-bottom: 28px;">
        <div class="metrics-title">Tickets</div>
        <div class="metrics-grid">
            <div class="card stat-card stat-card--total">
                <h3>Total Tickets</h3>
                <p class="stat-value">{{ $stats['total_tickets'] }}</p>
            </div>
            
            <div class="card stat-card stat-card--scans">
                <h3>Total Scans</h3>
                <p class="stat-value">{{ $stats['total_scans'] }}</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <h2 style="color: var(--color-primary); margin-bottom: 24px;">Recent Tickets</h2>
        
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-border);">
                        <th style="text-align: left; padding: 12px;">ID</th>
                        <th style="text-align: left; padding: 12px;">Type</th>
                        <th style="text-align: left; padding: 12px;">Name/Company</th>
                        <th style="text-align: left; padding: 12px;">Amount</th>
                        <th style="text-align: left; padding: 12px;">Status</th>
                        <th style="text-align: left; padding: 12px;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_tickets as $ticket)
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td style="padding: 12px;">{{ substr($ticket->uuid, 0, 8) }}...</td>
                        <td style="padding: 12px;">{{ ucfirst($ticket->type) }}</td>
                        <td style="padding: 12px;">{{ $ticket->type === 'individual' ? $ticket->name : $ticket->company_name }}</td>
                        <td style="padding: 12px;">KES {{ number_format($ticket->amount, 0) }}</td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; 
                                @if($ticket->status === 'paid') background: #d4edda; color: #155724;
                                @elseif($ticket->status === 'pending') background: #fff3cd; color: #856404;
                                @else background: #f8d7da; color: #721c24; @endif">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td style="padding: 12px;">{{ $ticket->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-secondary);">No tickets found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('admin.tickets') }}" class="btn btn-primary">View All Tickets</a>
        </div>
    </div>
</div>
@endsection
