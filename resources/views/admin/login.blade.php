@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="container">
    <div style="min-height: 100vh; display: flex; align-items: center; justify-content: center;">
        <div style="max-width: 400px; width: 100%;">
            <div class="card">
                <div style="text-align: center; margin-bottom: 16px;">
                    <img src="{{ asset('images/hrmpeb-logo.png') }}" alt="HRMPEB Logo" style="max-width: 170px; width: 100%; height: auto;">
                </div>
                <h1 style="text-align: center; color: var(--color-primary); margin-bottom: 32px;">Admin Login</h1>
                
                @if($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('admin.login.post') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                </form>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="{{ route('home') }}" style="color: var(--color-primary); text-decoration: none;">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
