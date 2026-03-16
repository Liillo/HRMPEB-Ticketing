@extends('layouts.app')

@section('title', 'Continue Payment')

@section('content')
<div class="container">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="max-width: 600px; width: 100%;">
            <div class="card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo" style="max-width: 180px; width: 100%; height: auto;">
                </div>

                <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 12px;">Continue Payment</h1>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 24px;">
                    Enter your email and phone number to resume a pending payment.
                </p>


                <form method="POST" action="{{ route('payment.pending') }}">
                    @csrf

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required>
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Find Pending Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
