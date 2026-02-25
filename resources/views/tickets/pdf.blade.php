<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket - {{ $ticket->uuid }}</title>
    <style>
        @page {
            margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            color: #444444;
            background: #f4efe7;
            font-size: 10pt;
        }

        .ticket-shell {
            border: 2px solid #1F3C88;
            border-radius: 12px;
            overflow: hidden;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .ticket-head {
            background: #1F3C88;
            color: #ffffff;
            padding: 10px 14px;
        }

        .ticket-head-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-head-logo {
            width: 240px;
            vertical-align: middle;
            text-align: left;
        }

        .ticket-head-logo img {
            max-width: 180px;
            width: 100%;
            height: auto;
        }

        .ticket-head-title {
            vertical-align: middle;
            text-align: right;
        }

        .ticket-head-title h2 {
            margin: 0 0 4px;
            font-size: 26px;
            line-height: 1;
            font-weight: 700;
        }

        .ticket-head-title p {
            margin: 0;
            font-size: 14px;
        }

        .ticket-content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .ticket-left-column {
            width: 66%;
            vertical-align: top;
            padding: 12px;
            background: #fbf8f3;
        }

        .ticket-right-column {
            width: 34%;
            vertical-align: top;
            text-align: center;
            padding: 12px;
            background: #f7f6f8;
            border-left: 2px dashed #F4B400;
        }

        .left-top-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 0;
            margin: 0 -10px 10px;
        }

        .detail-box {
            border: 1px solid #e2d2b8;
            border-radius: 8px;
            background: #ffffff;
            padding: 10px;
        }

        .ticket-info-row {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #D6E6F3;
        }

        .ticket-info-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .ticket-info-label {
            color: #556270;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .ticket-info-value {
            color: #444444;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.25;
        }

        .event-meta {
            border: 1px solid #e2d2b8;
            border-radius: 8px;
            background: #ffffff;
            padding: 10px;
            color: #556270;
            font-size: 11px;
            line-height: 1.35;
        }

        .event-meta strong {
            color: #5b4a2f;
            font-size: 13px;
        }

        .ticket-qr-title {
            color: #1F3C88;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 10px;
        }

        .qr-wrap {
            display: inline-block;
            border: 1px solid #e2d2b8;
            border-radius: 10px;
            padding: 10px;
            background: #ffffff;
        }

        .ticket-id-box {
            margin-top: 10px;
            border-radius: 8px;
            background: #f2eee8;
            padding: 8px;
            text-align: left;
        }

        .ticket-uuid {
            font-family: "Courier New", monospace;
            font-size: 10px;
            word-break: break-all;
            color: #3b3326;
        }

        .footer-note {
            margin-top: 10px;
            text-align: left;
            color: #556270;
            font-size: 10px;
            line-height: 1.35;
        }

        .disclaimer-note {
            margin-top: 8px;
            text-align: left;
            border: 1px solid #e3b0b0;
            background: #fff3f3;
            color: #8a2f2f;
            border-radius: 8px;
            padding: 7px 8px;
            font-size: 10px;
            font-weight: 700;
            line-height: 1.3;
        }
    </style>
</head>
<body>
    @php
        $qrSvgPath = public_path('storage/qrcodes/' . $ticket->uuid . '.svg');
        $qrPngPath = public_path('storage/qrcodes/' . $ticket->uuid . '.png');
        $displayAmount = $payment?->amount ?? $ticket->amount;
        $paidOn = $payment?->updated_at ?? $payment?->created_at;
        $isCorporateTicket = $ticket->type === 'corporate' || !empty($ticket->corporate_booking_ref);
    @endphp

    <div class="ticket-shell">
        <div class="ticket-head">
            <table class="ticket-head-table">
                <tr>
                    <td class="ticket-head-logo">
                        @if(file_exists(public_path('images/hrmpeb-logo.png')))
                            <img src="{{ public_path('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo">
                        @endif
                    </td>
                    <td class="ticket-head-title">
                        <h2>Event Ticket</h2>
                        <p>{{ $isCorporateTicket ? 'Corporate Ticket' : 'Individual Ticket' }}</p>
                    </td>
                </tr>
            </table>
        </div>

        <table class="ticket-content-table">
            <tr>
                <td class="ticket-left-column">
                    <table class="left-top-grid">
                        <tr>
                            <td style="width: 50%; vertical-align: top;">
                                <div class="detail-box">
                                    @if($ticket->type === 'corporate')
                                        <div class="ticket-info-row"><div class="ticket-info-label">Attendee Name</div><div class="ticket-info-value">{{ $ticket->name }}</div></div>
                                        @if($ticket->staff_no)
                                            <div class="ticket-info-row"><div class="ticket-info-label">Staff No.</div><div class="ticket-info-value">{{ $ticket->staff_no }}</div></div>
                                        @endif
                                        @if($ticket->ihrm_no)
                                            <div class="ticket-info-row"><div class="ticket-info-label">IHRM No.</div><div class="ticket-info-value">{{ $ticket->ihrm_no }}</div></div>
                                        @endif
                                        <div class="ticket-info-row"><div class="ticket-info-label">Attendee Email</div><div class="ticket-info-value">{{ $ticket->email }}</div></div>
                                        <div class="ticket-info-row"><div class="ticket-info-label">Attendee Phone</div><div class="ticket-info-value">{{ $ticket->phone }}</div></div>
                                        <div class="ticket-info-row"><div class="ticket-info-label">Company Name</div><div class="ticket-info-value">{{ $ticket->company_name }}</div></div>
                                    @else
                                        <div class="ticket-info-row"><div class="ticket-info-label">Attendee Name</div><div class="ticket-info-value">{{ $ticket->name }}</div></div>
                                        @if($ticket->staff_no)
                                            <div class="ticket-info-row"><div class="ticket-info-label">Staff No.</div><div class="ticket-info-value">{{ $ticket->staff_no }}</div></div>
                                        @endif
                                        @if($ticket->ihrm_no)
                                            <div class="ticket-info-row"><div class="ticket-info-label">IHRM No.</div><div class="ticket-info-value">{{ $ticket->ihrm_no }}</div></div>
                                        @endif
                                        <div class="ticket-info-row"><div class="ticket-info-label">Email</div><div class="ticket-info-value">{{ $ticket->email }}</div></div>
                                        <div class="ticket-info-row"><div class="ticket-info-label">Phone</div><div class="ticket-info-value">{{ $ticket->phone }}</div></div>
                                    @endif
                                </div>
                            </td>
                            <td style="width: 50%; vertical-align: top;">
                                <div class="detail-box">
                                    <div class="ticket-info-row"><div class="ticket-info-label">Amount Paid</div><div class="ticket-info-value">KES {{ number_format($displayAmount, 0) }}</div></div>
                                    @if($payment)
                                        <div class="ticket-info-row"><div class="ticket-info-label">Payment Method</div><div class="ticket-info-value">{{ strtoupper($payment->method ?? 'mpesa') }}</div></div>
                                        @if(($payment->method ?? 'mpesa') === \App\Models\Payment::METHOD_MPESA)
                                            <div class="ticket-info-row"><div class="ticket-info-label">M-Pesa Receipt</div><div class="ticket-info-value">{{ $payment->mpesa_receipt ?? 'Awaiting callback sync' }}</div></div>
                                            <div class="ticket-info-row"><div class="ticket-info-label">Transaction Phone</div><div class="ticket-info-value">{{ $payment->phone_number ?? 'N/A' }}</div></div>
                                        @else
                                            <div class="ticket-info-row"><div class="ticket-info-label">Cheque Number</div><div class="ticket-info-value">{{ $payment->cheque_number ?? 'N/A' }}</div></div>
                                            <div class="ticket-info-row"><div class="ticket-info-label">Bank Name</div><div class="ticket-info-value">{{ $payment->bank_name ?? 'N/A' }}</div></div>
                                        @endif
                                        <div class="ticket-info-row"><div class="ticket-info-label">Paid On</div><div class="ticket-info-value">{{ $paidOn?->format('F j, Y g:i A') ?? 'N/A' }}</div></div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="event-meta">
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
                        @if(file_exists($qrSvgPath))
                            <img src="{{ $qrSvgPath }}" alt="QR Code" width="200" height="200">
                        @elseif(file_exists($qrPngPath))
                            <img src="{{ $qrPngPath }}" alt="QR Code" width="200" height="200">
                        @else
                            <div style="width:200px;height:200px;background:#efefef;line-height:200px;color:#888;font-size:10pt;">QR Code</div>
                        @endif
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
</body>
</html>

