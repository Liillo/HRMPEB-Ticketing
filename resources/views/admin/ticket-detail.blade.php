@extends('layouts.admin')

@section('title', 'Ticket Details')

@push('styles')
<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 14px;
    }
    .status-badge.status-paid {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.status-pending {
        background: #fff3cd;
        color: #856404;
    }
    .status-badge.status-failed {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

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

                @if(!empty($ticket->corporate_booking_ref))
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Corporate Booking Ref</label>
                    <p style="font-family: monospace; font-size: 12px; margin-top: 4px;">{{ $ticket->corporate_booking_ref }}</p>
                </div>
                @endif

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Type</label>
                    <p style="margin-top: 4px;">{{ ucfirst($ticket->type) }}</p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Name</label>
                    <p style="margin-top: 4px;">
                        {{ $ticket->name ?? ($ticket->company_name ?? 'N/A') }}
                        @if(!$ticket->name && $ticket->company_name)
                            <span style="color: var(--text-secondary); font-size: 12px;">(Company contact)</span>
                        @endif
                    </p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Email</label>
                    <p style="margin-top: 4px;">
                        {{ $ticket->email ?? ($ticket->company_email ?? 'N/A') }}
                        @if(!$ticket->email && $ticket->company_email)
                            <span style="color: var(--text-secondary); font-size: 12px;">(Company contact)</span>
                        @endif
                    </p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Phone</label>
                    <p style="margin-top: 4px;">
                        {{ $ticket->phone ?? ($ticket->company_phone ?? 'N/A') }}
                        @if(!$ticket->phone && $ticket->company_phone)
                            <span style="color: var(--text-secondary); font-size: 12px;">(Company contact)</span>
                        @endif
                    </p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Staff No.</label>
                    <p style="margin-top: 4px;">{{ $ticket->staff_no ?? 'N/A' }}</p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">IHRM No.</label>
                    <p style="margin-top: 4px;">{{ $ticket->ihrm_no ?? 'N/A' }}</p>
                </div>

                @if(!empty($ticket->company_name))
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Company</label>
                    <p style="margin-top: 4px;">{{ $ticket->company_name }}</p>
                </div>
                @endif

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Amount</label>
                    <p style="margin-top: 4px; font-weight: 600;">KES {{ number_format($ticket->amount, 0) }}</p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Status</label>
                    <p style="margin-top: 4px;">
                        @php
                            $ticketStatusClass = $ticket->status === 'paid'
                                ? 'status-paid'
                                : ($ticket->status === 'pending' ? 'status-pending' : 'status-failed');
                        @endphp
                        <span class="status-badge {{ $ticketStatusClass }}">
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
                    <label style="color: var(--text-secondary); font-size: 14px;">Payment Method</label>
                    <p style="margin-top: 4px;">{{ strtoupper($ticket->payment->method ?? 'mpesa') }}</p>
                </div>

                @if(($ticket->payment->method ?? 'mpesa') === \App\Models\Payment::METHOD_MPESA)
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">M-Pesa Receipt</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->mpesa_receipt ?? 'N/A' }}</p>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Phone Number</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->phone_number ?? 'N/A' }}</p>
                </div>
                @else
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Cheque Number</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->cheque_number ?? 'N/A' }}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Bank Name</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->bank_name ?? 'N/A' }}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Cheque Date</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->cheque_date?->format('F j, Y') ?? 'N/A' }}</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Payer Name</label>
                    <p style="margin-top: 4px;">{{ $ticket->payment->payer_name ?? 'N/A' }}</p>
                </div>
                @endif

                <div style="margin-bottom: 16px;">
                    <label style="color: var(--text-secondary); font-size: 14px;">Payment Status</label>
                    <p style="margin-top: 4px;">
                        @php
                            $paymentStatusClass = $ticket->payment->status === 'success'
                                ? 'status-paid'
                                : ($ticket->payment->status === 'pending' ? 'status-pending' : 'status-failed');
                        @endphp
                        <span class="status-badge {{ $paymentStatusClass }}">
                            {{ ucfirst($ticket->payment->status) }}
                        </span>
                    </p>
                </div>

                @if(($ticket->payment->method ?? 'mpesa') === \App\Models\Payment::METHOD_CHEQUE && $ticket->payment->status === 'pending')
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <form method="POST" action="{{ route('admin.payments.approve-cheque', $ticket->payment->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve Cheque</button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.reject-cheque', $ticket->payment->id) }}">
                        @csrf
                        <input type="text" name="reason" placeholder="Optional rejection reason" style="padding: 8px; border: 1px solid var(--color-border); border-radius: 6px; margin-right: 8px;">
                        <button type="submit" class="btn btn-danger">Reject Cheque</button>
                    </form>
                </div>
                @endif
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
                @foreach($ticket->scans->sortBy('scanned_at') as $scan)
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



