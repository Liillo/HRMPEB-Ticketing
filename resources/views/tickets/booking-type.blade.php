@extends('layouts.app')

@section('title', 'Select Booking Type')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="text-align: center; margin-bottom: 20px;">
                <h1 style="color: var(--color-primary); margin-bottom: 12px;">{{ $event->name }}</h1>
                <p style="color: var(--text-secondary);"><i class="fas fa-calendar"></i> {{ $event->event_date->format('F j, Y') }}</p>
            </div>
            
            <h2 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">Select Booking Type</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onclick="window.location='{{ route('booking.individual', $event->id) }}'">
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--color-muted); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user" style="font-size: 40px; color: var(--color-primary);"></i>
                    </div>
                    <h3 style="color: var(--color-primary); margin-bottom: 12px;">Individual</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">Book a single ticket</p>
                    <p style="font-size: 24px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($event->individual_price, 0) }}</p>
                </div>
                
                <div class="card" style="text-align: center; cursor: pointer; transition: transform 0.3s;" onclick="window.location='{{ route('booking.corporate', $event->id) }}'">
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--color-muted); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="font-size: 40px; color: var(--color-primary);"></i>
                    </div>
                    <h3 style="color: var(--color-primary); margin-bottom: 12px;">Corporate</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">Book for up to {{ $event->max_corporate_attendees }} people</p>
                    <p style="font-size: 24px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($event->corporate_price, 0) }}</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 40px;">
                <a href="{{ route('home') }}" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(124, 106, 70, 0.2);
    }
</style>
@endsection
