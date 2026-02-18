@extends('layouts.app')

@section('title', 'Corporate Booking')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.custom-select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%237c6a46' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 16px center;
    background-size: 20px;
    padding-right: 48px;
    background-color: #faf6f0;
    border: 2px solid var(--color-border);
    font-weight: 500;
    transition: all 0.3s ease;
}

.custom-select:hover {
    border-color: var(--color-accent);
    background-color: #fff;
}

.custom-select:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(124, 106, 70, 0.1);
}

.attendee-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-accent) 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
}

.attendee-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 14px;
}

@media (max-width: 768px) {
    .attendee-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 920px; margin: 0 auto;">
            <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">
                <i class="fas fa-building"></i> Corporate Booking
            </h1>

            @if($errors->any())
            <div class="alert alert-error" style="margin-bottom: 20px;">
                <strong>Please fix the following:</strong>
                <ul style="margin: 8px 0 0 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="card">
                <div style="background: linear-gradient(135deg, #e8f4f8 0%, #f0f8ff 100%); border-left: 4px solid var(--color-primary); padding: 16px; margin-bottom: 24px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <p style="color: var(--text-primary); font-size: 14px; display: flex; align-items: start; gap: 8px;">
                        <i class="fas fa-info-circle" style="margin-top: 2px; color: var(--color-primary);"></i>
                        <span>
                            <strong>{{ $event->name }}</strong> - Corporate ticket price is 
                            <strong>KES {{ number_format($event->corporate_price, 0) }}</strong> 
                            regardless of number of attendees (maximum 8 people).
                        </span>
                    </p>
                </div>
                
                <form method="POST" action="{{ route('booking.corporate.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">
                    
                    <div class="form-group">
                        <label for="company_name"><i class="fas fa-building"></i> Company Name</label>
                        <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                        @error('company_name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="company_email"><i class="fas fa-envelope"></i> Company Email</label>
                        <input type="email" id="company_email" name="company_email" value="{{ old('company_email') }}" required>
                        @error('company_email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="company_phone"><i class="fas fa-phone"></i> Company Phone</label>
                        <input type="tel" id="company_phone" name="company_phone" value="{{ old('company_phone') }}" placeholder="0712345678" required>
                        @error('company_phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="number_of_attendees">
                            <i class="fas fa-users"></i> Number of Attendees
                        </label>
                        <select id="number_of_attendees" name="number_of_attendees" class="custom-select" required onchange="updateSummary()">
                            @for($i = 1; $i <= 8; $i++)
                                <option value="{{ $i }}" {{ old('number_of_attendees', 1) == $i ? 'selected' : '' }}>
                                    {{ $i }} {{ $i == 1 ? 'Person' : 'People' }}
                                </option>
                            @endfor
                        </select>
                        <small style="color: var(--text-secondary); display: flex; align-items: center; gap: 6px; margin-top: 8px;">
                            <i class="fas fa-info-circle"></i>
                            <span>Select how many people will be attending from your company</span>
                        </small>
                        @error('number_of_attendees')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Attendee Details</label>
                        <small style="color: var(--text-secondary); display: block; margin-bottom: 8px;">
                            Enter each attendee's full details below. The layout adjusts automatically to fit your screen.
                        </small>
                        <div id="attendees-container"></div>
                        @error('attendee_names')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        @error('attendee_emails')
                            <span class="error">{{ $message }}</span>
                        @enderror
                        @error('attendee_phones')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div style="background: var(--color-muted); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 2px solid var(--color-border);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <span style="font-weight: 500; color: var(--text-secondary);">
                                <i class="fas fa-users"></i> Selected Attendees:
                            </span>
                            <span class="attendee-badge" id="attendees-count">
                                <i class="fas fa-user"></i> 1 Person
                            </span>
                        </div>
                        <div style="border-top: 2px solid var(--color-border); padding-top: 16px; margin-top: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-weight: 600; font-size: 18px; color: var(--text-primary);">
                                    <i class="fas fa-money-bill-wave"></i> Total Amount:
                                </span>
                                <span style="font-size: 28px; font-weight: 700; color: var(--color-primary);">
                                    KES {{ number_format($event->corporate_price, 0) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 16px; padding: 14px;">
                        <i class="fas fa-arrow-right"></i> Continue to Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const oldNames = @json(old('attendee_names', []));
const oldEmails = @json(old('attendee_emails', []));
const oldPhones = @json(old('attendee_phones', []));

function renderAttendeeInputs() {
    const attendees = parseInt(document.getElementById('number_of_attendees').value);
    const container = document.getElementById('attendees-container');
    let html = '';

    for (let i = 0; i < attendees; i++) {
        const index = i + 1;
        const nameValue = (oldNames[i] || '').replace(/"/g, '&quot;');
        const emailValue = (oldEmails[i] || '').replace(/"/g, '&quot;');
        const phoneValue = (oldPhones[i] || '').replace(/"/g, '&quot;');

        html += `
            <div style="border: 1px solid var(--color-border); border-radius: 10px; padding: 14px; margin-bottom: 12px; background: #fff;">
                <div style="font-weight: 600; margin-bottom: 10px; color: var(--color-primary);">Attendee ${index}</div>
                <div class="attendee-grid">
                    <div>
                        <label style="font-size: 13px; margin-bottom: 6px;">Full Name</label>
                        <input type="text" name="attendee_names[]" value="${nameValue}" required>
                    </div>
                    <div>
                        <label style="font-size: 13px; margin-bottom: 6px;">Email</label>
                        <input type="email" name="attendee_emails[]" value="${emailValue}" required>
                    </div>
                    <div>
                        <label style="font-size: 13px; margin-bottom: 6px;">Phone</label>
                        <input type="text" name="attendee_phones[]" value="${phoneValue}" required>
                    </div>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
}

function updateSummary() {
    const attendees = parseInt(document.getElementById('number_of_attendees').value);
    const badge = document.getElementById('attendees-count');
    
    if (attendees === 1) {
        badge.innerHTML = '<i class="fas fa-user"></i> 1 Person';
    } else {
        badge.innerHTML = '<i class="fas fa-users"></i> ' + attendees + ' People';
    }

    renderAttendeeInputs();
}

document.addEventListener('DOMContentLoaded', updateSummary);
</script>
@endsection
