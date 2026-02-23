@extends('layouts.app')

@section('title', 'Events - Book Your Ticket')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">

<div style="text-align: center; margin-bottom: 50px;">
            <div style="margin-bottom: 18px;">
                <img
                    src="{{ asset('images/hrmpeb-logo.png') }}"
                    alt="HRMPEB Logo"
                    style="max-width: 240px; width: 100%; height: auto;"
                >
            </div>
            <h1 style="font-size: 48px; color: var(--color-primary); margin-bottom: 16px;">
                <i class="fas fa-calendar-star"></i> HRMPEB Ticketing
            </h1>
            <p style="font-size: 20px; color: var(--text-secondary);">Book your tickets for our exclusive events</p>
        </div>

        @if($events->count() > 0)
            <div class="events-grid-booking">
                @foreach($events as $event)
                @php
                    $isSoldOut = $event->isSoldOut();
                    $remainingCapacity = $event->remainingCapacity();
                    $eventCardStateClass = $isSoldOut ? 'event-card-disabled' : 'event-card-clickable';
                    $eventCardHref = $isSoldOut ? '' : route('booking.type', $event->id);
                @endphp
                <div
                    class="card event-card {{ $eventCardStateClass }}"
                    data-href="{{ $eventCardHref }}"
                    onclick="if (this.dataset.href) window.location=this.dataset.href"
                >
                    <h2 style="color: var(--color-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-calendar-alt"></i> {{ $event->name }}
                    </h2>

                    @if($event->description)
                    <p style="color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
                        {{ \Illuminate\Support\Str::limit($event->description, 120) }}
                    </p>
                    @endif

                    <div style="margin-bottom: 12px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-calendar" style="color: var(--color-accent);"></i>
                        <strong>Date:</strong> {{ $event->event_date->format('F j, Y') }}
                    </div>

                    @if($event->location)
                    <div style="margin-bottom: 20px; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-map-marker-alt" style="color: var(--color-accent);"></i>
                        <strong>Location:</strong> {{ $event->location }}
                    </div>
                    @endif

                    <div style="background: var(--color-muted); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-user"></i> Individual:
                            </span>
                            <strong style="color: var(--color-primary);">KES {{ number_format($event->individual_price, 0) }}</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-users"></i> Corporate:
                            </span>
                            <strong style="color: var(--color-primary);">KES {{ number_format($event->corporate_price, 0) }}</strong>
                        </div>
                        @if($remainingCapacity !== null)
                        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid var(--color-border); color: var(--text-secondary); font-size: 14px;">
                            <i class="fas fa-users"></i>
                            {{ $remainingCapacity }} slot{{ $remainingCapacity === 1 ? '' : 's' }} left
                        </div>
                        @endif
                    </div>

                    <button class="btn {{ $isSoldOut ? 'btn-secondary' : 'btn-primary' }}" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;" {{ $isSoldOut ? 'disabled' : '' }}>
                        <i class="fas {{ $isSoldOut ? 'fa-ban' : 'fa-ticket-alt' }}"></i> {{ $isSoldOut ? 'Sold Out' : 'Book Now' }}
                    </button>

                    @if($isSoldOut)
                    <p style="margin-top: 10px; font-size: 13px; color: var(--text-secondary); text-align: center;">
                        All available slots are currently taken. Please check with the event organizers for latest updates.
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px;">
                <i class="fas fa-calendar-times" style="font-size: 64px; color: var(--color-muted); margin-bottom: 20px;"></i>
                <h2 style="color: var(--text-secondary); margin-bottom: 12px;">No Events Available</h2>
                <p style="color: var(--text-secondary);">Check back soon for upcoming events!</p>
            </div>
        @endif

        <div class="tickets-footer">
            <p style="color: var(--text-secondary); margin: 0 0 6px; font-size: 14px;">
                Already paid and lost your ticket? Click here:
                <a href="{{ route('ticket.retrieve.form') }}" style="color: var(--color-primary); font-weight: 700; text-decoration: underline;">
                    Retrieve your ticket
                </a>
            </p>
            <p style="color: var(--text-secondary); margin: 0; font-size: 14px;">
                Need to complete a pending booking? Click here:
                <a href="{{ route('payment.pending.form') }}" style="color: var(--color-primary); font-weight: 700; text-decoration: underline;">
                    Continue payment
                </a>
            </p>
        </div>
    </div>
</div>

<style>
.event-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 16px rgba(124, 106, 70, 0.2);
}

.event-card {
    transition: transform 0.3s;
}

.event-card-clickable {
    cursor: pointer;
    opacity: 1;
}

.event-card-disabled {
    cursor: not-allowed;
    opacity: 0.75;
}

.events-grid-booking {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 350px));
    justify-content: center;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.tickets-footer {
    width: min(96vw, 1500px);
    margin: 28px 0 0;
    position: relative;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    padding: 16px 18px;
    background: linear-gradient(135deg, #f3e7d4 0%, #fff7eb 100%);
    border: 1px solid rgba(124, 106, 70, 0.22);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(124, 106, 70, 0.08);
}

@media (max-width: 768px) {
    .events-grid-booking {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .tickets-footer {
        width: calc(100vw - 24px);
    }
}
</style>
@endsection
