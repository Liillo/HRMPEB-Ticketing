@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div style="padding: 24px;">
    <h1 style="color: var(--color-primary); margin-bottom: 32px;">Dashboard</h1>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Tickets</h3>
            <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ $stats['total_tickets'] }}</p>
        </div>
        
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Paid Tickets</h3>
            <p style="font-size: 36px; font-weight: 600; color: var(--color-success);">{{ $stats['paid_tickets'] }}</p>
        </div>
        
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Pending Tickets</h3>
            <p style="font-size: 36px; font-weight: 600; color: #f0ad4e;">{{ $stats['pending_tickets'] }}</p>
        </div>
        
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Revenue</h3>
            <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ number_format($stats['total_revenue'], 0) }}</p>
            <small style="color: var(--text-secondary);">KES</small>
        </div>
        
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Total Scans</h3>
            <p style="font-size: 36px; font-weight: 600; color: var(--color-primary);">{{ $stats['total_scans'] }}</p>
        </div>
        
        <div class="card">
            <h3 style="color: var(--text-secondary); font-size: 14px; margin-bottom: 8px;">Failed Tickets</h3>
            <p style="font-size: 36px; font-weight: 600; color: var(--color-error);">{{ $stats['failed_tickets'] }}</p>
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
