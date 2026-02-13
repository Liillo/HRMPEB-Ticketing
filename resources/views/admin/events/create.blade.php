@extends('layouts.admin')
@section('title', 'Create Event')
@section('content')
<h1 style="color: var(--color-primary); margin-bottom: 32px;"><i class="fas fa-plus-circle"></i> Create New Event</h1>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.events.store') }}">
        @csrf
        
        <div class="form-group">
            <label for="name"><i class="fas fa-tag"></i> Event Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Description</label>
            <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
            @error('description')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="event_date"><i class="fas fa-calendar"></i> Event Date</label>
            <input type="date" id="event_date" name="event_date" value="{{ old('event_date') }}" required>
            @error('event_date')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
            <input type="text" id="location" name="location" value="{{ old('location') }}">
            @error('location')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="individual_price"><i class="fas fa-user"></i> Individual Price (KES)</label>
                <input type="number" id="individual_price" name="individual_price" value="{{ old('individual_price', 1000) }}" required>
                @error('individual_price')<span class="error">{{ $message }}</span>@enderror
            </div>
            
            <div class="form-group">
                <label for="corporate_price"><i class="fas fa-users"></i> Corporate Price (KES)</label>
                <input type="number" id="corporate_price" name="corporate_price" value="{{ old('corporate_price', 40000) }}" required>
                @error('corporate_price')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>
        
        <div class="form-group">
            <label for="max_corporate_attendees"><i class="fas fa-users-cog"></i> Max Corporate Attendees</label>
            <input type="number" id="max_corporate_attendees" name="max_corporate_attendees" value="{{ old('max_corporate_attendees', 8) }}" min="1" max="50" required>
            @error('max_corporate_attendees')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Event</button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
        </div>
    </form>
</div>
@endsection