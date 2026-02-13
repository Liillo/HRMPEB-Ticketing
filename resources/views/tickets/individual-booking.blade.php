@extends('layouts.app')

@section('title', 'Individual Booking')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 600px; margin: 0 auto;">
            <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">
                <i class="fas fa-user"></i> Individual Booking
            </h1>
            
            <div class="card">
                <div style="background: #e8f4f8; border-left: 4px solid var(--color-primary); padding: 12px; margin-bottom: 24px; border-radius: 4px;">
                    <p style="color: var(--text-primary); font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Booking for {{ $event->name }} - {{ $event->event_date->format('F j, Y') }}
                    </p>
                </div>
                
                <form method="POST" action="{{ route('booking.individual.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="0712345678" required>
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div style="background: var(--color-muted); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-weight: 500;"><i class="fas fa-money-bill-wave"></i> Total Amount:</span>
                            <span style="font-size: 24px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($event->individual_price, 0) }}</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <i class="fas fa-arrow-right"></i> Continue to Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
