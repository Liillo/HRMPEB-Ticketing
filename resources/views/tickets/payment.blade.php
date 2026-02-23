@extends('layouts.app')

@section('title', 'Payment')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 920px; margin: 0 auto;">
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
                        @if($ticket->staff_no)
                            <div style="margin-bottom: 8px;">
                                <strong><i class="fas fa-id-badge"></i> Staff No.:</strong> {{ $ticket->staff_no }}
                            </div>
                        @endif
                        @if($ticket->ihrm_no)
                            <div style="margin-bottom: 8px;">
                                <strong><i class="fas fa-hashtag"></i> IHRM No.:</strong> {{ $ticket->ihrm_no }}
                            </div>
                        @endif
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

                        @if(is_array($ticket->attendee_details) && count($ticket->attendee_details) > 0)
                            <div style="margin-top: 12px; padding: 12px; background: #fff; border: 1px solid var(--color-border); border-radius: 8px;">
                                <div style="font-weight: 600; margin-bottom: 8px;">
                                    <i class="fas fa-id-card"></i> Attendee Details
                                </div>
                                @foreach($ticket->attendee_details as $index => $attendee)
                                    <div class="attendee-row {{ $index === 0 ? 'attendee-row-first' : '' }}" style="padding: 8px 0;">
                                        <div style="display: flex; align-items: center; gap: 10px; white-space: nowrap; overflow-x: auto; font-size: 13px; margin-bottom: 4px;">
                                            <span style="font-weight: 600;">{{ $index + 1 }}. {{ $attendee['name'] ?? 'N/A' }}</span>
                                            <span style="color: var(--text-secondary);">{{ $attendee['email'] ?? 'N/A' }}</span>
                                            <span style="color: var(--text-secondary);">&middot;</span>
                                            <span style="color: var(--text-secondary);">{{ $attendee['phone'] ?? 'N/A' }}</span>
                                        </div>
                                        @if(!empty($attendee['staff_no']) || !empty($attendee['ihrm_no']))
                                            <div style="font-size: 12px; color: var(--text-secondary);">
                                                @if(!empty($attendee['staff_no']))
                                                    <span><strong>Staff No.:</strong> {{ $attendee['staff_no'] }}</span>
                                                @endif
                                                @if(!empty($attendee['staff_no']) && !empty($attendee['ihrm_no']))
                                                    <span> &middot; </span>
                                                @endif
                                                @if(!empty($attendee['ihrm_no']))
                                                    <span><strong>IHRM No.:</strong> {{ $attendee['ihrm_no'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
                <h3 style="color: var(--color-primary); margin-bottom: 20px;"><i class="fas fa-wallet"></i> Payment Method</h3>
                @php
                    $notificationEmail = $ticket->email ?? $ticket->company_email;
                @endphp

                @if($ticket->payment && $ticket->payment->method === \App\Models\Payment::METHOD_CHEQUE && $ticket->payment->status === 'pending')
                    <div class="alert alert-success" style="margin-bottom: 16px;">
                        Cheque details already submitted. Awaiting admin verification.
                        @if($notificationEmail)
                            <div style="margin-top: 8px;">
                                You can close this page and check your emails. Your ticket will be sent once approval is done.
                            </div>
                        @endif
                    </div>
                @endif
                
                <div id="error-message" class="alert alert-error" style="display: none;"></div>
                <div id="success-message" class="alert alert-success" style="display: none;"></div>
                
                <form id="payment-form">
                    @csrf
                    <div class="form-group">
                        <label for="method"><i class="fas fa-list"></i> Choose Method</label>
                        <select id="method" name="method" required>
                            <option value="mpesa">M-Pesa</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div id="mpesa-fields">
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone"></i> M-Pesa Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ $ticket->phone ?? $ticket->company_phone }}" placeholder="0712345678">
                            <small style="color: var(--text-secondary);">Enter the phone number to receive M-Pesa prompt</small>
                        </div>
                    </div>

                    <div id="cheque-fields" style="display: none;">
                        <div class="form-group">
                            <label for="cheque_number"><i class="fas fa-receipt"></i> Cheque Number</label>
                            <input type="text" id="cheque_number" name="cheque_number" placeholder="Cheque number">
                        </div>
                        <div class="form-group">
                            <label for="bank_name"><i class="fas fa-university"></i> Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" placeholder="Bank name">
                        </div>
                        <div class="form-group">
                            <label for="cheque_date"><i class="fas fa-calendar-alt"></i> Cheque Date</label>
                            <input type="date" id="cheque_date" name="cheque_date">
                        </div>
                        <div class="form-group">
                            <label for="payer_name"><i class="fas fa-user"></i> Payer Name</label>
                            <input type="text" id="payer_name" name="payer_name" placeholder="Name on cheque">
                        </div>
                        <small style="color: var(--text-secondary); display: block; margin-top: -8px; margin-bottom: 12px;">
                            Cheque payments are manually verified by an admin.
                            @if($notificationEmail)
                                Once approved, your ticket will be sent to the respective emails.
                            @endif
                        </small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;" id="pay-btn">
                        <i class="fas fa-money-check-alt"></i> Submit Payment for KES {{ number_format($ticket->amount, 0) }}
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
    
    const method = document.getElementById('method').value;
    const btn = document.getElementById('pay-btn');
    const errorDiv = document.getElementById('error-message');
    const successDiv = document.getElementById('success-message');
    const phone = document.getElementById('phone').value;
    const chequeNumber = document.getElementById('cheque_number').value;
    const bankName = document.getElementById('bank_name').value;
    const chequeDate = document.getElementById('cheque_date').value;
    const payerName = document.getElementById('payer_name').value;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';
    
    fetch("{{ route('payment.initiate', $ticket->uuid) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            method: method,
            phone: phone,
            cheque_number: chequeNumber,
            bank_name: bankName,
            cheque_date: chequeDate,
            payer_name: payerName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            if (method === 'cheque') {
                successDiv.textContent = 'Cheque submitted. Approval is manual. Once approved, your ticket will be emailed to {{ $notificationEmail ?? "your email" }}.';
            } else {
                successDiv.textContent = data.message || 'Payment submitted successfully.';
            }
            successDiv.style.display = 'block';
        } else {
            errorDiv.textContent = data.message || 'Payment failed. Please try again.';
            errorDiv.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-money-check-alt"></i> Submit Payment for KES {{ number_format($ticket->amount, 0) }}';
        }
    })
    .catch(error => {
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-money-check-alt"></i> Submit Payment for KES {{ number_format($ticket->amount, 0) }}';
    });
});

function setMethodFields() {
    const method = document.getElementById('method').value;
    const mpesaFields = document.getElementById('mpesa-fields');
    const chequeFields = document.getElementById('cheque-fields');
    const phone = document.getElementById('phone');
    const chequeNumber = document.getElementById('cheque_number');
    const bankName = document.getElementById('bank_name');
    const chequeDate = document.getElementById('cheque_date');
    const payerName = document.getElementById('payer_name');

    const isMpesa = method === 'mpesa';

    mpesaFields.style.display = isMpesa ? 'block' : 'none';
    chequeFields.style.display = isMpesa ? 'none' : 'block';

    phone.required = isMpesa;
    chequeNumber.required = !isMpesa;
    bankName.required = !isMpesa;
    chequeDate.required = !isMpesa;
    payerName.required = !isMpesa;
}

document.getElementById('method').addEventListener('change', setMethodFields);
setMethodFields();
</script>
@endpush

@push('styles')
<style>
    .attendee-row {
        border-top: 1px solid var(--color-border);
    }

    .attendee-row-first {
        border-top: none;
    }
</style>
@endpush
@endsection
