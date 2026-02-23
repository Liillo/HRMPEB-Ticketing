@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('admin.events.index') }}" style="color: var(--color-primary); text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Events
    </a>
</div>

<h1 style="color: var(--color-primary); margin-bottom: 32px;">
    <i class="fas fa-edit"></i> Edit Event
</h1>

<div class="card" style="max-width: 800px;">
    <form method="POST" action="{{ route('admin.events.update', $event->id) }}">
        @csrf
        @method('PUT')
        
        <div class="form-group">
            <label for="name"><i class="fas fa-tag"></i> Event Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $event->name) }}" required>
            @error('name')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="description"><i class="fas fa-align-left"></i> Description</label>
            <textarea id="description" name="description" rows="4">{{ old('description', $event->description) }}</textarea>
            @error('description')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="event_date"><i class="fas fa-calendar"></i> Event Date</label>
            <input type="date" id="event_date" name="event_date" value="{{ old('event_date', $event->event_date->format('Y-m-d')) }}" required>
            @error('event_date')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
            <input type="text" id="location" name="location" value="{{ old('location', $event->location) }}">
            @error('location')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="individual_price"><i class="fas fa-user"></i> Individual Price (KES)</label>
                <input type="number" id="individual_price" name="individual_price" value="{{ old('individual_price', $event->individual_price) }}" required>
                @error('individual_price')<span class="error">{{ $message }}</span>@enderror
            </div>
            
            <div class="form-group">
                <label for="corporate_price"><i class="fas fa-users"></i> Corporate Price (KES)</label>
                <input type="number" id="corporate_price" name="corporate_price" value="{{ old('corporate_price', $event->corporate_price) }}" required>
                @error('corporate_price')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="max_capacity"><i class="fas fa-users"></i> Max Event Capacity</label>
            <input type="number" id="max_capacity" name="max_capacity" value="{{ old('max_capacity', $event->max_capacity) }}" min="1" required>
            <small style="color: var(--text-secondary);">Set the maximum number of attendees allowed for this event.</small>
            @error('max_capacity')<span class="error">{{ $message }}</span>@enderror
        </div>

        <div class="form-group">
            <label for="max_corporate_tables"><i class="fas fa-table"></i> Max Corporate Tables</label>
            <input type="number" id="max_corporate_tables" name="max_corporate_tables" value="{{ old('max_corporate_tables', $event->max_corporate_tables) }}" min="1" required>
            <small style="color: var(--text-secondary);">Each paid corporate booking uses exactly one table (up to 10 attendees).</small>
            @error('max_corporate_tables')<span class="error">{{ $message }}</span>@enderror
        </div>
        
        <div class="form-group">
            <label><i class="fas fa-users-cog"></i> Max Corporate Attendees</label>
            <div style="background: var(--color-muted); border: 1px solid var(--color-border); border-radius: 8px; padding: 12px;">
                Fixed at <strong>10 attendees</strong> for all events.
            </div>
        </div>
        
        <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }}>
                <span><i class="fas fa-toggle-on"></i> Event is Active</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Event
            </button>
            <a href="{{ route('admin.events.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<div class="card" style="max-width: 800px; margin-top: 24px; border: 2px solid var(--color-error);">
    <h3 style="color: var(--color-error); margin-bottom: 16px;">
        <i class="fas fa-exclamation-triangle"></i> ALERT
    </h3>
    <p style="color: var(--text-secondary); margin-bottom: 16px;">
        Deleting this event will also delete all associated tickets and payments. This action cannot be undone.
    </p>
    <form method="POST" action="{{ route('admin.events.destroy', $event->id) }}" onsubmit="return confirm('Are you sure you want to delete this event? This will delete all tickets and payments associated with it.');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> Delete Event
        </button>
    </form>
</div>
@endsection
