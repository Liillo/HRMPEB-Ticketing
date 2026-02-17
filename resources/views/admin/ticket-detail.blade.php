@extends('layouts.admin')

@section('title', 'Ticket Details')

@section('content')
<div style="padding: 24px;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('admin.tickets') }}" style="color: var(--color-primary); text-decoration: none;">&larr; Back to All Tickets</a>
    </div>
    
    <h1 style="color: var(--color-primary); margin-bottom: 32px;">Ticket Details</h1>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
        <div>
            <div class="card" style="margin-bottom: 24px;">
                <h2 style="color: var(--color-primary); margin-bottom: 24px;">Basic Information</h2>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Ticket UUID</label>
                    <p style="font-family: monospace; font-size: 14px; margin-top: 4px;">{{ $ticket->uuid }}</p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Type</label>
                    <p style="margin-top: 4px;">{{ ucfirst($ticket->type) }}</p>
                </div>
                
                @if($ticket->type === 'individual')
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Name</label>
                        <p style="margin-top: 4px;">{{ $ticket->name }}</p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Email</label>
                        <p style="margin-top: 4px;">{{ $ticket->email }}</p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Phone</label>
                        <p style="margin-top: 4px;">{{ $ticket->phone }}</p>
                    </div>
                @else
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Company Name</label>
                        <p style="margin-top: 4px;">{{ $ticket->company_name }}</p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Company Email</label>
                        <p style="margin-top: 4px;">{{ $ticket->company_email }}</p>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="color: var(--text-secondary); font-size: 14px;">Company Phone</label>
                        <p style="margin-top: 4px;">{{ $ticket->company_phone }}</p>
                    </div>
                @endif
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Amount</label>
                    <p style="margin-top: 4px; font-weight: 600;">KES {{ number_format($ticket->amount, 0) }}</p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Status</label>
                    <p style="margin-top: 4px;">
                        <span style="padding: 4px 12px; border-radius: 12px; font-size: 14px; 
                            @if($ticket->status === 'paid') background: #d4edda; color: #155724;
                            @elseif($ticket->status === 'pending') background: #fff3cd; color: #856404;
                            @else background: #f8d7da; color: #721c24; @endif">
                            {{ ucfirst($ticket->status) }}
                        </span>
                    </p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Scans</label>
                    <p style="margin-top: 4px;">{{ $ticket->scan_count }} / {{ $ticket->max_scans }}</p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Created At</label>
                    <p style="margin-top: 4px;">{{ $ticket->created_at->format('F j, Y g:i A') }}</p>
                </div>
                
                @if($ticket->status === 'paid')
                <div style="margin-top: 24px;">
                    <a href="{{ route('admin.ticket.download', $ticket->id) }}" class="btn btn-primary">Download PDF</a>
                </div>
                @endif
            </div>
            
            @if($ticket->payment)
            <div class="card">
                <h2 style="color: var(--color-primary); margin-bottom: 24px;">Payment Information</h2>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">M-Pesa Receipt</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->mpesa_receipt ?? 'N/A' }}</p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Phone Number</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->phone_number }}</p>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Payment Status</label>
                    <p style="margin-top: 4px;">
                        <span style="padding: 4px 12px; border-radius: 12px; font-size: 14px; 
                            @if($ticket->payment->status === 'success') background: #d4edda; color: #155724;
                            @elseif($ticket->payment->status === 'pending') background: #fff3cd; color: #856404;
                            @else background: #f8d7da; color: #721c24; @endif">
                            {{ ucfirst($ticket->payment->status) }}
                        </span>
                    </p>
                </div>
            </div>
            @endif
        </div>
        
        <div>
            @if($ticket->status === 'paid')
            <div class="card" style="text-align: center; margin-bottom: 24px;">
                <h3 style="color: var(--color-primary); margin-bottom: 16px;">QR Code</h3>
                @php
                    $qrSvg = public_path('storage/qrcodes/' . $ticket->uuid . '.svg');
                    $qrPng = public_path('storage/qrcodes/' . $ticket->uuid . '.png');
                @endphp
                @if(file_exists($qrSvg))
                    <img src="{{ asset('storage/qrcodes/' . $ticket->uuid . '.svg') }}" alt="QR Code" style="width: 100%; max-width: 250px;">
                @elseif(file_exists($qrPng))
                    <img src="{{ asset('storage/qrcodes/' . $ticket->uuid . '.png') }}" alt="QR Code" style="width: 100%; max-width: 250px;">
                @endif
            </div>
            @endif
            
            @if($ticket->scans->count() > 0)
            <div class="card">
                <h3 style="color: var(--color-primary); margin-bottom: 16px;">Scan History</h3>
                @foreach($ticket->scans as $scan)
                <div style="padding: 12px; border-bottom: 1px solid var(--color-border); margin-bottom: 8px;">
                    <p style="font-size: 14px; margin-bottom: 4px;">
                        <strong>{{ $scan->scanned_at->format('M d, Y g:i A') }}</strong>
                    </p>
                    <p style="font-size: 12px; color: var(--text-secondary);">
                        By: {{ $scan->admin ? $scan->admin->name : 'System' }}
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
