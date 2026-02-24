@extends('layouts.app')

@section('title', 'Retrieve Ticket')

@section('content')
<div class="container">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="max-width: 600px; width: 100%;">
            <div class="card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo" style="max-width: 180px; width: 100%; height: auto;">
                </div>

                <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 12px;">Retrieve Your Ticket</h1>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px;">
                    Enter your payment reference (M-Pesa receipt code or cheque number) and ticket phone number to access your paid ticket.
                </p>

                <form method="POST" action="{{ route('ticket.retrieve') }}">
                    @csrf

                    <div class="form-group">
                        <label for="payment_reference">Payment Reference</label>
                        <input id="payment_reference" type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="M-Pesa receipt code or cheque number" required>
                        @error('payment_reference')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Ticket Phone Number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 10px;">
                        <input id="resend_email" type="checkbox" name="resend_email" value="1" {{ old('resend_email') ? 'checked' : '' }} style="width: auto;">
                        <label for="resend_email" style="margin: 0;">Also resend my ticket email</label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Find My Ticket</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
