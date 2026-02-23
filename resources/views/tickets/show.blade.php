@extends('layouts.app')

@section('title', 'Ticket Details')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .ticket-shell {
        border: 2px solid #7c6a46;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .ticket-head {
        background: #7c6a46;
        color: #fff;
        padding: 14px 18px;
    }

    .ticket-head-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ticket-head-logo {
        width: 220px;
        text-align: left;
        vertical-align: middle;
    }

    .ticket-head-logo img {
        max-width: 170px;
        width: 100%;
        height: auto;
    }

    .ticket-head-title {
        text-align: right;
        vertical-align: middle;
    }

    .ticket-head-title h2 {
        margin: 0 0 4px;
        font-size: 27px;
        line-height: 1.1;
    }

    .ticket-head-title p {
        margin: 0;
        font-size: 13px;
    }

    .ticket-content-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ticket-left-column {
        width: 64%;
        vertical-align: top;
        padding: 14px;
        background: #fcf8f1;
    }

    .ticket-right-column {
        width: 36%;
        vertical-align: top;
        text-align: center;
        padding: 14px 12px;
        border-left: 2px dashed #d4a574;
    }

    .ticket-info-row {
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid #efe2cc;
    }

    .ticket-info-row:last-child {
        margin-bottom: 0;
        border-bottom: none;
        padding-bottom: 0;
    }

    .ticket-info-label {
        color: #6b5d48;
        font-size: 10px;
        display: block;
        margin-bottom: 2px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .ticket-info-value {
        color: #2d2416;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.25;
    }

    .ticket-uuid {
        font-family: monospace;
        font-size: 12px;
        word-break: break-all;
        font-weight: 500;
    }

    .ticket-qr-title {
        color: #7c6a46;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 10px;
        letter-spacing: 0.3px;
    }

    .qr-wrap {
        display: inline-block;
        border: 1px solid #e8dcc8;
        border-radius: 8px;
        padding: 8px;
        background: #fff;
    }

    .ticket-id-box {
        margin-top: 10px;
        background: #f9f4ec;
        border-radius: 6px;
        padding: 8px;
        text-align: left;
    }

    .event-meta {
        margin-top: 0;
        background: #f9f4ec;
        border-radius: 6px;
        padding: 8px;
        font-size: 13px;
        color: #6b5d48;
        line-height: 1.35;
    }

    .footer-note {
        text-align: left;
        color: #6b5d48;
        font-size: 12px;
        margin-top: 8px;
        line-height: 1.4;
    }

    .disclaimer-note {
        margin-top: 8px;
        text-align: left;
        border: 1px solid #e3b0b0;
        background: #fff3f3;
        color: #8a2f2f;
        border-radius: 8px;
        padding: 7px 8px;
        font-size: 11px;
        font-weight: 700;
        line-height: 1.3;
    }

    .ticket-section-block {
        background: #fffdf9;
        border: 1px solid #efe2cc;
        border-radius: 6px;
        padding: 8px;
        margin-bottom: 10px;
    }

    .ticket-section-block:last-child {
        margin-bottom: 0;
    }

    .left-top-grid {
        width: 100%;
        font-size: 0;
        margin-bottom: 10px;
    }

    .left-grid-item {
        display: inline-block;
        width: 49%;
        margin-right: 2%;
        vertical-align: top;
        font-size: 10pt;
    }

    .left-grid-item:last-child {
        margin-right: 0;
    }

    .attendee-table-wrap {
        overflow-x: auto;
        border: 1px solid var(--color-border);
        border-radius: 10px;
        background: #fff;
    }

    .attendee-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .attendee-table th,
    .attendee-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--color-border);
        text-align: left;
        vertical-align: middle;
    }

    .attendee-table th {
        font-weight: 700;
        color: var(--text-primary);
        background: #faf6f0;
    }

    .attendee-table tbody tr:last-child td {
        border-bottom: none;
    }

    .attendee-row-selected td {
        background: #f6efe3;
    }

    .attendee-table .col-id { width: 52px; }
    .attendee-table .col-staff { width: 14%; }
    .attendee-table .col-ihrm { width: 14%; }
    .attendee-table .col-name { width: 20%; }
    .attendee-table .col-email { width: 24%; }
    .attendee-table .col-phone { width: 14%; }
    .attendee-table .col-actions { width: 180px; }

    .attendee-table .email-cell {
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .attendee-table .actions-cell {
        white-space: nowrap;
    }

    .attendee-table .actions-row {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .paid-header-icon {
        color: #28a745;
        margin-right: 8px;
    }

    @media (max-width: 860px) {
        .ticket-left-column,
        .ticket-right-column {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .ticket-right-column {
            border-left: none;
            border-top: 2px dashed #d4a574;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .left-top-grid {
            font-size: 10pt;
        }

        .left-grid-item {
            display: block;
            width: 100%;
            margin-right: 0;
        }
    }
</style>
@endpush

@section('content')
@php
    $showSingleTicket = request()->boolean('single');
    $isCorporateGroup = $ticket->corporate_booking_ref && $bookingTickets->count() > 1 && !$showSingleTicket;
    $hasCorporateBooking = $ticket->corporate_booking_ref && $bookingTickets->count() > 1;
    $displayAmount = $transactionPayment?->amount ?? $ticket->amount;
    $isCorporateTicket = $ticket->type === 'corporate' || !empty($ticket->corporate_booking_ref);
    $paidOn = $transactionPayment?->updated_at ?? $transactionPayment?->created_at;
@endphp
<div class="container">
    <div style="min-height: 100vh; padding: 32px 0;">
        <div style="max-width: 1000px; margin: 0 auto;">
            <h1 style="color: var(--color-primary); margin-bottom: 14px;">
                <i class="fas fa-ticket-alt"></i>
                {{ $isCorporateGroup ? 'Corporate Booking Tickets' : 'Your Ticket' }}
            </h1>

            @if($paymentJustCompleted)
                <div class="alert alert-success" style="margin-bottom: 16px;">
                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                    <strong>Payment successful.</strong>
                    @if($isCorporateGroup)
                        Your corporate booking has been confirmed and attendee tickets have been generated.
                    @else
                        Your ticket has been generated.
                    @endif
                </div>
            @endif

            @if($isCorporateGroup || $transactionPayment)
                <div class="summary-grid">
                    @if($isCorporateGroup)
                        <div class="card" style="margin: 0;">
                            <h3 style="color: var(--color-primary); margin-bottom: 10px;"><i class="fas fa-building"></i> Corporate Booking Summary</h3>
                            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                                <div><strong><i class="fas fa-building"></i> Company:</strong> {{ $ticket->company_name }}</div>
                                <div><strong><i class="fas fa-envelope"></i> Contact Email:</strong> {{ $ticket->company_email }}</div>
                                <div><strong><i class="fas fa-phone"></i> Contact Phone:</strong> {{ $ticket->company_phone }}</div>
                                <div><strong><i class="fas fa-users"></i> Attendees:</strong> {{ $bookingTickets->count() }}</div>
                            </div>
                        </div>
                    @endif

                    @if($transactionPayment)
                        <div class="card" style="margin: 0;">
                            <h3 style="color: var(--color-primary); margin-bottom: 10px;"><i class="fas fa-receipt"></i> Payment Details</h3>
                            <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                                <div><strong><i class="fas fa-money-bill-wave"></i> Amount Paid:</strong> KES {{ number_format($transactionPayment->amount, 0) }}</div>
                                <div><strong><i class="fas fa-list"></i> Method:</strong> {{ strtoupper($transactionPayment->method ?? 'mpesa') }}</div>
                                <div><strong><i class="fas fa-info-circle"></i> Status:</strong> {{ ucfirst($transactionPayment->status) }}</div>
                                @if(($transactionPayment->method ?? 'mpesa') === \App\Models\Payment::METHOD_MPESA)
                                    <div><strong><i class="fas fa-receipt"></i> M-Pesa Receipt:</strong> {{ $transactionPayment->mpesa_receipt ?? 'Awaiting callback sync' }}</div>
                                    <div><strong><i class="fas fa-phone"></i> Phone:</strong> {{ $transactionPayment->phone_number ?? 'N/A' }}</div>
                                @else
                                    <div><strong><i class="fas fa-receipt"></i> Cheque Number:</strong> {{ $transactionPayment->cheque_number ?? 'N/A' }}</div>
                                    <div><strong><i class="fas fa-university"></i> Bank:</strong> {{ $transactionPayment->bank_name ?? 'N/A' }}</div>
                                    <div><strong><i class="fas fa-user"></i> Payer:</strong> {{ $transactionPayment->payer_name ?? 'N/A' }}</div>
                                @endif
                                <div><strong><i class="fas fa-clock"></i> Paid On:</strong> {{ $paidOn?->format('F j, Y g:i A') ?? 'N/A' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($isCorporateGroup)
                <div class="card" style="margin-bottom: 18px;">
                    <h3 style="color: var(--color-primary); margin-bottom: 12px;"><i class="fas fa-users"></i> Attendee Tickets</h3>
                    <p style="color: var(--text-secondary); margin-top: 0; margin-bottom: 10px;">
                        Each attendee has their own unique corporate ticket and QR code. Use the actions to view or download each one.
                    </p>
                    <div class="attendee-table-wrap">
                        <table class="attendee-table">
                            <thead>
                                <tr>
                                    <th class="col-id">#</th>
                                    <th class="col-staff">Staff No.</th>
                                    <th class="col-ihrm">IHRM No.</th>
                                    <th class="col-name">Name</th>
                                    <th class="col-email">Email</th>
                                    <th class="col-phone">Phone</th>
                                    <th class="col-actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($bookingTickets as $index => $bookingTicket)
                                <tr class="{{ $bookingTicket->id === $ticket->id ? 'attendee-row-selected' : '' }}">
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $bookingTicket->staff_no ?: '-' }}</td>
                                    <td>{{ $bookingTicket->ihrm_no ?: '-' }}</td>
                                    <td>{{ $bookingTicket->name }}</td>
                                    <td class="email-cell">{{ $bookingTicket->email }}</td>
                                    <td>{{ $bookingTicket->phone }}</td>
                                    <td class="actions-cell">
                                        <div class="actions-row">
                                            <a href="{{ route('ticket.show', ['uuid' => $bookingTicket->uuid, 'single' => 1]) }}" class="btn btn-secondary" style="padding: 6px 10px; font-size: 12px;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('ticket.download', $bookingTicket->uuid) }}" class="btn btn-primary" style="padding: 6px 10px; font-size: 12px;"><i class="fas fa-download"></i> Download</a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                @if($ticket->email && !session('hide_ticket_sent_notice'))
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        <i class="fas fa-envelope"></i> Your ticket was sent to <strong>{{ $ticket->email }}</strong>.
                    </div>
                @endif

                <div class="ticket-shell">
                    <div class="ticket-head">
                        <table class="ticket-head-table">
                            <tr>
                                <td class="ticket-head-logo">
                                    <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo">
                                </td>
                                <td class="ticket-head-title">
                                    <h2>Event Ticket</h2>
                                    @if($isCorporateTicket)
                                        <p>Corporate Ticket</p>
                                    @else
                                        <p>Individual Ticket</p>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <table class="ticket-content-table">
                        <tr>
                            <td class="ticket-left-column">
                            <div class="left-top-grid">
                                <div class="left-grid-item ticket-section-block">
                                    @if($ticket->type === 'corporate')
                                        <div class="ticket-info-row"><span class="ticket-info-label">Attendee Name</span><span class="ticket-info-value">{{ $ticket->name }}</span></div>
                                        @if($ticket->staff_no)
                                            <div class="ticket-info-row"><span class="ticket-info-label">Staff No.</span><span class="ticket-info-value">{{ $ticket->staff_no }}</span></div>
                                        @endif
                                        @if($ticket->ihrm_no)
                                            <div class="ticket-info-row"><span class="ticket-info-label">IHRM No.</span><span class="ticket-info-value">{{ $ticket->ihrm_no }}</span></div>
                                        @endif
                                        <div class="ticket-info-row"><span class="ticket-info-label">Attendee Email</span><span class="ticket-info-value">{{ $ticket->email }}</span></div>
                                        <div class="ticket-info-row"><span class="ticket-info-label">Attendee Phone</span><span class="ticket-info-value">{{ $ticket->phone }}</span></div>
                                        <div class="ticket-info-row"><span class="ticket-info-label">Company Name</span><span class="ticket-info-value">{{ $ticket->company_name }}</span></div>
                                    @else
                                        <div class="ticket-info-row"><span class="ticket-info-label">Attendee Name</span><span class="ticket-info-value">{{ $ticket->name }}</span></div>
                                        @if($ticket->staff_no)
                                            <div class="ticket-info-row"><span class="ticket-info-label">Staff No.</span><span class="ticket-info-value">{{ $ticket->staff_no }}</span></div>
                                        @endif
                                        @if($ticket->ihrm_no)
                                            <div class="ticket-info-row"><span class="ticket-info-label">IHRM No.</span><span class="ticket-info-value">{{ $ticket->ihrm_no }}</span></div>
                                        @endif
                                        <div class="ticket-info-row"><span class="ticket-info-label">Email</span><span class="ticket-info-value">{{ $ticket->email }}</span></div>
                                        <div class="ticket-info-row"><span class="ticket-info-label">Phone</span><span class="ticket-info-value">{{ $ticket->phone }}</span></div>
                                    @endif
                                </div>

                                <div class="left-grid-item ticket-section-block">
                                    <div class="ticket-info-row"><span class="ticket-info-label">Amount Paid</span><span class="ticket-info-value">KES {{ number_format($displayAmount, 0) }}</span></div>
                                    @if($transactionPayment)
                                        <div class="ticket-info-row"><span class="ticket-info-label">Payment Method</span><span class="ticket-info-value">{{ strtoupper($transactionPayment->method ?? 'mpesa') }}</span></div>
                                        @if(($transactionPayment->method ?? 'mpesa') === \App\Models\Payment::METHOD_MPESA)
                                            <div class="ticket-info-row"><span class="ticket-info-label">M-Pesa Receipt</span><span class="ticket-info-value">{{ $transactionPayment->mpesa_receipt ?? 'Awaiting callback sync' }}</span></div>
                                            <div class="ticket-info-row"><span class="ticket-info-label">Transaction Phone</span><span class="ticket-info-value">{{ $transactionPayment->phone_number ?? 'N/A' }}</span></div>
                                        @else
                                            <div class="ticket-info-row"><span class="ticket-info-label">Cheque Number</span><span class="ticket-info-value">{{ $transactionPayment->cheque_number ?? 'N/A' }}</span></div>
                                            <div class="ticket-info-row"><span class="ticket-info-label">Bank Name</span><span class="ticket-info-value">{{ $transactionPayment->bank_name ?? 'N/A' }}</span></div>
                                        @endif
                                        <div class="ticket-info-row"><span class="ticket-info-label">Paid On</span><span class="ticket-info-value">{{ $paidOn?->format('F j, Y g:i A') ?? 'N/A' }}</span></div>
                                    @endif
                                </div>
                            </div>

                            <div class="ticket-section-block event-meta">
                                <strong>{{ $ticket->event->name }}</strong><br>
                                @if($ticket->event->event_date)
                                    Date: {{ $ticket->event->event_date->format('l, F j, Y') }}<br>
                                @endif
                                @if($ticket->event->location)
                                    Location: {{ $ticket->event->location }}<br>
                                @endif
                                Issued: {{ $ticket->created_at->format('F j, Y g:i A') }}
                            </div>
                            </td>
                            <td class="ticket-right-column">
                            <div class="ticket-qr-title">Scan to Validate</div>
                            <div class="qr-wrap">
                                <img src="{{ asset('storage/qrcodes/' . $ticket->uuid . '.svg') }}" alt="QR Code" style="width: 200px; height: 200px;">
                            </div>
                            <div class="ticket-id-box">
                                <div class="ticket-info-label">Ticket ID</div>
                                <div class="ticket-uuid">{{ $ticket->uuid }}</div>
                            </div>
                            <div class="footer-note">
                                Please present this ticket at the event entrance.
                            </div>
                            <div class="disclaimer-note">
                                DISCLAIMER: This ticket can only be used once.
                            </div>
                            </td>
                        </tr>
                    </table>
                </div>
            @endif

            @if(!$isCorporateGroup)
                <div style="text-align: center; margin-top: 18px;">
                    <a href="{{ route('ticket.download', $ticket->uuid) }}" class="btn btn-primary" style="margin-right: 10px;"><i class="fas fa-download"></i> Download This Ticket</a>
                    <a href="{{ route('home') }}" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Home</a>
                </div>
            @else
                <div style="text-align: center; margin-top: 18px;">
                    <a href="{{ route('home') }}" class="btn btn-secondary"><i class="fas fa-home"></i> Back to Home</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
