@extends('layouts.app')

@section('title', 'Payment')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 600px; margin: 0 auto;">
            <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">
                <i class="fas fa-credit-card"></i> Complete Payment
            </h1>
            
            <div class="card" style="margin-bottom: 20px;">
                <h3 style="color: var(--color-primary); margin-bottom: 20px;"><i class="fas fa-file-invoice"></i> Booking Summary</h3>
                
                <div style="background: var(--color-muted); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <h4 style="color: var(--color-primary); margin-bottom: 12px;"><i class="fas fa-calendar-alt"></i> Event Details</h4>
                    <div style="margin-bottom: 8px;">
                        <strong>Event:</strong> {{ $ticket->event->name }}
                    </div>
                    <div style="margin-bottom: 8px;">
                        <strong>Date:</strong> {{ $ticket->event->event_date->format('F j, Y') }}
                    </div>
                    @if($ticket->event->location)
                    <div>
                        <strong>Location:</strong> {{ $ticket->event->location }}
                    </div>
                    @endif
                </div>
                
                <div style="background: var(--color-muted); padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                    <h4 style="color: var(--color-primary); margin-bottom: 12px;">
                        @if($ticket->type === 'individual')
                            <i class="fas fa-user"></i> Customer Details
                        @else
                            <i class="fas fa-building"></i> Company Details
                        @endif
                    </h4>
                    
                    @if($ticket->type === 'individual')
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-user"></i> Name:</strong> {{ $ticket->name }}
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-envelope"></i> Email:</strong> {{ $ticket->email }}
                        </div>
                        <div>
                            <strong><i class="fas fa-phone"></i> Phone:</strong> {{ $ticket->phone }}
                        </div>
                    @else
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-building"></i> Company:</strong> {{ $ticket->company_name }}
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-envelope"></i> Email:</strong> {{ $ticket->company_email }}
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-phone"></i> Phone:</strong> {{ $ticket->company_phone }}
                        </div>
                        <div style="margin-bottom: 8px;">
                            <strong><i class="fas fa-users"></i> Attendees:</strong> {{ $ticket->number_of_attendees }} {{ $ticket->number_of_attendees == 1 ? 'Person' : 'People' }}
                        </div>
                        <div>
                            <strong><i class="fas fa-qrcode"></i> Max Scans:</strong> {{ $ticket->max_scans }}
                        </div>
                    @endif
                </div>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid var(--color-border);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500; font-size: 18px;"><i class="fas fa-money-bill-wave"></i> Total Amount:</span>
                        <span style="font-size: 28px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($ticket->amount, 0) }}</span>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <h3 style="color: var(--color-primary); margin-bottom: 20px;"><i class="fas fa-mobile-alt"></i> M-Pesa Payment</h3>
                
                <div id="error-message" class="alert alert-error" style="display: none;"></div>
                
                <form id="payment-form">
                    @csrf
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> M-Pesa Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ $ticket->phone ?? $ticket->company_phone }}" placeholder="0712345678" required>
                        <small style="color: var(--text-secondary);">Enter the phone number to receive M-Pesa prompt</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;" id="pay-btn">
                        <i class="fas fa-money-check-alt"></i> Pay KES {{ number_format($ticket->amount, 0) }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('pay-btn');
    const errorDiv = document.getElementById('error-message');
    const phone = document.getElementById('phone').value;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    errorDiv.style.display = 'none';
    
    fetch("{{ route('payment.initiate', $ticket->uuid) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = "{{ route('payment.waiting', $ticket->uuid) }}";
        } else {
            errorDiv.textContent = data.message || 'Payment failed. Please try again.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-money-check-alt"></i> Pay KES {{ number_format($ticket->amount, 0) }}';
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-money-check-alt"></i> Pay KES {{ number_format($ticket->amount, 0) }}';
    });
});
</script>
@endpush
@endsection
