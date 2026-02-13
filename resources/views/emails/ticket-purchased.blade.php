<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <div style="background: linear-gradient(135deg, #7c6a46 0%, #d4a574 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
            <div style="margin-bottom: 12px;">
                <img src="{{ $message->embed(public_path('images/hrmpeb-logo.png')) }}" alt="HRMPEB Logo" style="max-width: 160px; width: 100%; height: auto;">
            </div>
            <h1 style="margin: 0;">Ticket Confirmed!</h1>
            <p style="margin: 10px 0 0 0;">Your payment was successful</p>
        </div>
        
        <div style="background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px;">
            <h2 style="color: #7c6a46;">{{ $ticket->event->name }}</h2>
            <p><strong>Date:</strong> {{ $ticket->event->event_date->format('l, F j, Y') }}</p>
            @if($ticket->event->location)
            <p><strong>Location:</strong> {{ $ticket->event->location }}</p>
            @endif
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            
            @if($ticket->type === 'individual')
            <p><strong>Attendee:</strong> {{ $ticket->name }}</p>
            @else
            <p><strong>Company:</strong> {{ $ticket->company_name }}</p>
            <p><strong>Attendees:</strong> {{ $ticket->number_of_attendees }} {{ $ticket->number_of_attendees == 1 ? 'person' : 'people' }}</p>
            @endif
            
            <p><strong>Amount Paid:</strong> KES {{ number_format($ticket->amount, 0) }}</p>
            <p><strong>Ticket ID:</strong> {{ $ticket->uuid }}</p>
            
            <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
            
            <p><strong>Your ticket is attached to this email as a PDF.</strong></p>
            
            <p style="font-size: 12px; color: #666; margin-top: 30px;">
                Please present this ticket (printed or on your phone) at the event entrance.
            </p>
        </div>
    </div>
</body>
</html>
