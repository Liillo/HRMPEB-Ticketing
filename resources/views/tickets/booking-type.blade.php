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
            @php
                $isEventSoldOut = $event->isSoldOut();
                $isCorporateSoldOut = $event->isCorporateSoldOut();
                $corporateDisabled = $isEventSoldOut || $isCorporateSoldOut;
            @endphp

            @if($isEventSoldOut)
                <div class="alert alert-error" style="text-align: center;">
                    <strong>All slots for this event are currently filled.</strong>
                    Please check with the event organizers for availability updates.
                </div>
            @endif

            @if(!$isEventSoldOut && $isCorporateSoldOut)
                <div class="alert alert-error" style="text-align: center;">
                    <strong>Corporate booking is currently sold out.</strong>
                    Individual booking is still available while attendee slots remain.
                </div>
            @endif
            
            <h2 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">Select Booking Type</h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="card booking-card {{ $isEventSoldOut ? 'booking-card-disabled' : 'booking-card-clickable' }}" style="text-align: center;" @if(!$isEventSoldOut) data-href="{{ route('booking.individual', $event->id) }}" onclick="window.location=this.dataset.href" @endif>
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--color-muted); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user" style="font-size: 40px; color: var(--color-primary);"></i>
                    </div>
                    <h3 style="color: var(--color-primary); margin-bottom: 12px;">Individual</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">Book a single ticket</p>
                    <p style="font-size: 24px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($event->individual_price, 0) }}</p>
                </div>
                
                <div class="card booking-card {{ $corporateDisabled ? 'booking-card-disabled' : 'booking-card-clickable' }}" style="text-align: center;" @if(!$corporateDisabled) data-href="{{ route('booking.corporate', $event->id) }}" onclick="window.location=this.dataset.href" @endif>
                    <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: var(--color-muted); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="font-size: 40px; color: var(--color-primary);"></i>
                    </div>
                    <h3 style="color: var(--color-primary); margin-bottom: 12px;">Corporate</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">Book a table for up to 10 people</p>
                    <p style="font-size: 24px; font-weight: 600; color: var(--color-primary);">KES {{ number_format($event->corporate_price, 0) }}</p>
                    @if($isCorporateSoldOut && !$isEventSoldOut)
                        <p style="margin-top: 8px; color: #8a2d25; font-size: 13px; font-weight: 600;">Corporate sold out</p>
                    @endif
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
    .booking-card {
        transition: transform 0.3s;
    }
    .booking-card-clickable {
        cursor: pointer;
        opacity: 1;
    }
    .booking-card-disabled {
        cursor: not-allowed;
        opacity: 0.7;
    }
    .booking-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(124, 106, 70, 0.2);
    }
</style>
@endsection
