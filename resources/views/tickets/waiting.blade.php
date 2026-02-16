@extends('layouts.app')

@section('title', 'Processing Payment')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .spinner {
        border: 4px solid var(--color-muted);
        border-top: 4px solid var(--color-primary);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: spin 1s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="max-width: 500px; width: 100%; text-align: center;">
            <div class="card">
                <div class="spinner"></div>
                
                <h2 style="color: var(--color-primary); margin-bottom: 16px;">
                    <i class="fas fa-mobile-alt"></i> Processing Payment
                </h2>
                
                <p style="color: var(--text-secondary); margin-bottom: 24px;" class="pulse">
                    Please complete the M-Pesa payment on your phone...
                </p>
                
                <div id="status-message" style="background: var(--color-muted); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <p style="margin: 0; color: var(--text-primary);">
                        <i class="fas fa-clock"></i> Waiting for payment confirmation
                    </p>
                    <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                        Checking status... <span id="check-count">0</span>/60
                    </small>
                </div>
                
                <div style="background: #e8f4f8; padding: 16px; border-radius: 8px; border-left: 4px solid var(--color-primary);">
                    <p style="margin: 0; font-size: 14px; color: var(--text-primary);">
                        <i class="fas fa-info-circle"></i>
                        <strong>Enter your M-Pesa PIN</strong> on your phone to complete payment
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let checkCount = 0;
const maxChecks = 60; // Check for 3 minutes (60 * 3 seconds)
const checkInterval = 3000; // 3 seconds

function checkPaymentStatus() {
    checkCount++;
    document.getElementById('check-count').textContent = checkCount;
    
    console.log('Checking payment status... Attempt:', checkCount);
    
    fetch("{{ route('payment.check', $ticket->uuid) }}", {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Payment check response:', data);
        
        if (data.status === 'paid') {
            console.log('Payment successful! Redirecting...');
            document.getElementById('status-message').innerHTML = `
                <p style="margin: 0; color: var(--color-success);">
                    <i class="fas fa-check-circle"></i> Payment successful!
                </p>
                <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                    Redirecting to your ticket...
                </small>
            `;
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 1000);
            return;
        }
        
        if (data.status === 'failed') {
            console.log('Payment failed. Redirecting...');
            document.getElementById('status-message').innerHTML = `
                <p style="margin: 0; color: var(--color-error);">
                    <i class="fas fa-times-circle"></i> Payment failed
                </p>
                <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                    Redirecting to try again...
                </small>
            `;
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 2000);
            return;
        }
        
        // Still pending
        if (checkCount < maxChecks) {
            setTimeout(checkPaymentStatus, checkInterval);
        } else {
            console.log('Max checks reached. Payment timeout.');
            document.getElementById('status-message').innerHTML = `
                <p style="margin: 0; color: var(--color-error);">
                    <i class="fas fa-clock"></i> Payment timeout
                </p>
                <small style="color: var(--text-secondary); display: block; margin-top: 8px;">
                    Please try again or contact support
                </small>
            `;
            setTimeout(() => {
                window.location.href = "{{ route('payment', $ticket->uuid) }}";
            }, 3000);
        }
    })
    .catch(error => {
        console.error('Error checking payment:', error);
        if (checkCount < maxChecks) {
            setTimeout(checkPaymentStatus, checkInterval);
        }
    });
}

// Start checking after 3 seconds (give time for callback to arrive)
setTimeout(checkPaymentStatus, 3000);
</script>
@endpush
@endsection
