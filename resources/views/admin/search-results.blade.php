@extends('layouts.admin')

@section('title', 'Search Results')

@section('content')
<div style="padding: 24px;">
    <h1 style="color: var(--color-primary); margin-bottom: 32px;">Search Results</h1>
    
    <div class="card" style="margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.search') }}">
            <div style="display: flex; gap: 12px;">
                <input type="search" name="query" placeholder="Search by name, email, phone, UUID..." value="{{ $query ?? '' }}" style="flex: 1;">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
    
    @if(isset($query))
    <div class="card">
        <h2 style="color: var(--color-primary); margin-bottom: 24px;">
            Found {{ $tickets->count() }} result(s) for "{{ $query }}"
        </h2>
        
        @if($tickets->count() > 0)
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--color-border);">
                        <th style="text-align: left; padding: 12px;">UUID</th>
                        <th style="text-align: left; padding: 12px;">Type</th>
                        <th style="text-align: left; padding: 12px;">Name/Company</th>
                        <th style="text-align: left; padding: 12px;">Email</th>
                        <th style="text-align: left; padding: 12px;">Phone</th>
                        <th style="text-align: left; padding: 12px;">Status</th>
                        <th style="text-align: left; padding: 12px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr style="border-bottom: 1px solid var(--color-border);">
                        <td style="padding: 12px; font-family: monospace; font-size: 12px;">{{ substr($ticket->uuid, 0, 13) }}...</td>
                        <td style="padding: 12px;">{{ ucfirst($ticket->type) }}</td>
                        <td style="padding: 12px;">{{ $ticket->name ?: $ticket->company_name }}</td>
                        <td style="padding: 12px;">{{ $ticket->email ?: $ticket->company_email }}</td>
                        <td style="padding: 12px;">{{ $ticket->phone ?: $ticket->company_phone }}</td>
                        <td style="padding: 12px;">
                            <span style="padding: 4px 12px; border-radius: 12px; font-size: 12px; 
                                @if($ticket->status === 'paid') background: #d4edda; color: #155724;
                                @elseif($ticket->status === 'pending') background: #fff3cd; color: #856404;
                                @else background: #f8d7da; color: #721c24; @endif">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <a href="{{ route('admin.ticket.detail', $ticket->id) }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p style="text-align: center; color: var(--text-secondary); padding: 24px;">No tickets found matching your search.</p>
        @endif
    </div>
    @endif
</div>
@endsection
