@extends('layouts.app')

@section('title', 'Verify Ticket')

@section('content')
<div class="container">
    <div style="min-height: 100vh; padding: 40px 0;">
        <div style="max-width: 600px; margin: 0 auto;">
            <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 40px;">Ticket Verification</h1>
            
            <div class="card">
                @if($ticket->status === 'paid')
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; margin: 0 auto 16px; background: #d4edda; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="3">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <h2 style="color: var(--color-success); margin-bottom: 8px;">Valid Ticket</h2>
                        <p style="color: var(--text-secondary);">This ticket is valid for entry</p>
                    </div>
                    
                    <div style="background: var(--color-muted); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                        @if($ticket->type === 'corporate')
                            <div style="margin-bottom: 12px;">
                                <span style="color: var(--text-secondary); font-size: 14px;">Name</span>
                                <p style="font-weight: 600; margin-top: 4px;">{{ $ticket->name }}</p>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <span style="color: var(--text-secondary); font-size: 14px;">Ticket Type</span>
                                <p style="font-weight: 600; margin-top: 4px;">Corporate</p>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <span style="color: var(--text-secondary); font-size: 14px;">Company</span>
                                <p style="font-weight: 600; margin-top: 4px;">{{ $ticket->company_name }}</p>
                            </div>
                        @else
                            <div style="margin-bottom: 12px;">
                                <span style="color: var(--text-secondary); font-size: 14px;">Name</span>
                                <p style="font-weight: 600; margin-top: 4px;">{{ $ticket->name }}</p>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <span style="color: var(--text-secondary); font-size: 14px;">Ticket Type</span>
                                <p style="font-weight: 600; margin-top: 4px;">Individual</p>
                            </div>
                        @endif
                        
                        <div style="margin-bottom: 12px;">
                            <span style="color: var(--text-secondary); font-size: 14px;">Ticket ID</span>
                            <p style="font-weight: 500; font-family: monospace; margin-top: 4px;">{{ $ticket->uuid }}</p>
                        </div>
                    </div>
                    
                    @if($ticket->canBeScanned())
                        <div style="background: #d4edda; padding: 16px; border-radius: 8px; border: 1px solid #c3e6cb;">
                            <p style="color: #155724; margin: 0; text-align: center; font-weight: 500;">
                                ✓ This ticket can be used for entry
                            </p>
                        </div>
                    @else
                        <div style="background: #fff3cd; padding: 16px; border-radius: 8px; border: 1px solid #ffeaa7;">
                            <p style="color: #856404; margin: 0; text-align: center; font-weight: 500;">
                                ⚠ This ticket has reached its scan limit
                            </p>
                        </div>
                    @endif
                    
                @elseif($ticket->status === 'pending')
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; margin: 0 auto 16px; background: #fff3cd; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#856404" stroke-width="3">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <h2 style="color: #856404; margin-bottom: 8px;">Payment Pending</h2>
                        <p style="color: var(--text-secondary);">This ticket is awaiting payment confirmation</p>
                    </div>
                    
                @else
                    <div style="text-align: center; margin-bottom: 24px;">
                        <div style="width: 80px; height: 80px; margin: 0 auto 16px; background: #f8d7da; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#721c24" stroke-width="3">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <h2 style="color: var(--color-error); margin-bottom: 8px;">Invalid Ticket</h2>
                        <p style="color: var(--text-secondary);">This ticket is not valid for entry</p>
                    </div>
                @endif
            </div>
            
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ route('home') }}" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection
