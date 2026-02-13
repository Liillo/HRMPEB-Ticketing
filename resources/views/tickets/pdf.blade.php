<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket - {{ $ticket->uuid }}</title>
    <style>
        @page {
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #2d2416;
            font-size: 10pt;
            background: #fdfaf5;
        }

        .page-wrap {
            width: 100%;
        }

        .ticket-card {
            border: 2px solid #7c6a46;
            border-radius: 8px;
            overflow: hidden;
            background: #ffffff;
            page-break-inside: avoid;
        }

        .ticket-header {
            padding: 12px 16px;
            background: #7c6a46;
            color: #ffffff;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 220px;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo img {
            max-width: 170px;
            width: 100%;
            height: auto;
        }

        .header-title {
            text-align: right;
            vertical-align: middle;
        }

        .header-title h2 {
            margin: 0 0 4px;
            font-size: 20pt;
            line-height: 1.1;
        }

        .header-title p {
            margin: 0;
            font-size: 10pt;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
        }

        .left-column {
            width: 64%;
            vertical-align: top;
            padding: 14px;
            background: #fcf8f1;
        }

        .right-column {
            width: 36%;
            vertical-align: top;
            text-align: center;
            padding: 14px 12px;
            border-left: 2px dashed #d4a574;
        }

        .info-row {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #efe2cc;
        }

        .info-row:last-child {
            margin-bottom: 0;
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-label {
            color: #6b5d48;
            font-size: 8pt;
            display: block;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-value {
            color: #2d2416;
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.25;
        }

        .uuid-value {
            font-family: "Courier New", monospace;
            font-size: 8pt;
            font-weight: normal;
            word-break: break-all;
        }

        .qr-title {
            color: #7c6a46;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            letter-spacing: 0.3px;
        }

        .qr-section {
            text-align: center;
        }

        .qr-wrap {
            display: inline-block;
            border: 1px solid #e8dcc8;
            border-radius: 8px;
            padding: 8px;
            background: #ffffff;
        }

        .ticket-id-box {
            margin-top: 10px;
            background: #f9f4ec;
            border-radius: 6px;
            padding: 8px;
            text-align: left;
        }

        .footer-note {
            text-align: left;
            color: #6b5d48;
            font-size: 8pt;
            margin-top: 8px;
            line-height: 1.4;
        }

        .event-meta {
            margin-top: 10px;
            background: #f9f4ec;
            border-radius: 6px;
            padding: 8px;
            font-size: 9pt;
            color: #6b5d48;
            line-height: 1.35;
        }
    </style>
</head>
<body>
    @php
        $qrSvgPath = public_path('storage/qrcodes/' . $ticket->uuid . '.svg');
        $qrPngPath = public_path('storage/qrcodes/' . $ticket->uuid . '.png');
    @endphp

    <div class="page-wrap">
        <div class="ticket-card">
            <div class="ticket-header">
                <table class="header-table">
                    <tr>
                        <td class="header-logo">
                            @if(file_exists(public_path('images/hrmpeb-logo.png')))
                                <img src="{{ public_path('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo">
                            @endif
                        </td>
                        <td class="header-title">
                            <h2>Event Ticket</h2>
                            @if($ticket->type === 'individual')
                                <p>Individual Ticket</p>
                            @else
                                <p>Corporate Ticket</p>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <table class="content-table">
                <tr>
                    <td class="left-column">
                        @if($ticket->type === 'individual')
                            <div class="info-row">
                                <span class="info-label">Attendee Name</span>
                                <span class="info-value">{{ $ticket->name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $ticket->email }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone</span>
                                <span class="info-value">{{ $ticket->phone }}</span>
                            </div>
                        @else
                            <div class="info-row">
                                <span class="info-label">Company Name</span>
                                <span class="info-value">{{ $ticket->company_name }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $ticket->company_email }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Phone</span>
                                <span class="info-value">{{ $ticket->company_phone }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Attendees</span>
                                <span class="info-value">{{ $ticket->number_of_attendees }} {{ $ticket->number_of_attendees == 1 ? 'Person' : 'People' }}</span>
                            </div>
                        @endif

                        <div class="info-row">
                            <span class="info-label">Amount Paid</span>
                            <span class="info-value">KES {{ number_format($ticket->amount, 0) }}</span>
                        </div>

                        <div class="event-meta">
                            @if(isset($ticket->event) && $ticket->event)
                                <strong>{{ $ticket->event->name }}</strong><br>
                                @if($ticket->event->event_date)
                                    Date: {{ $ticket->event->event_date->format('l, F j, Y') }}<br>
                                @endif
                                @if($ticket->event->location)
                                    Location: {{ $ticket->event->location }}<br>
                                @endif
                            @endif
                            Issued: {{ $ticket->created_at->format('F j, Y g:i A') }}
                        </div>
                    </td>
                    <td class="right-column">
                        <div class="qr-title">Scan to Validate</div>
                        <div class="qr-section">
                            <div class="qr-wrap">
                                @if(file_exists($qrSvgPath))
                                    <img src="{{ $qrSvgPath }}" alt="QR Code" width="180" height="180">
                                @elseif(file_exists($qrPngPath))
                                    <img src="{{ $qrPngPath }}" alt="QR Code" width="180" height="180">
                                @else
                                    <div style="width:180px;height:180px;background:#efefef;line-height:180px;color:#888;font-size:10pt;">QR Code</div>
                                @endif
                            </div>
                        </div>

                        <div class="ticket-id-box">
                            <span class="info-label">Ticket ID</span>
                            <span class="info-value uuid-value">{{ $ticket->uuid }}</span>
                        </div>

                        <div class="footer-note">
                            Please present this ticket at the event entrance.
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
