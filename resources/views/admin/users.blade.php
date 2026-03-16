@extends('layouts.admin')

@section('title', 'Admin Users')

@push('styles')
<style>
    .users-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 20px;
    }

    .users-table {
        overflow-x: auto;
    }

    .role-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        background: #fff3cd;
        color: #7b5a13;
        border: 1px solid #f0d999;
    }

    .role-muted {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .role-form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .role-form select {
        max-width: 220px;
    }

    .role-form input {
        max-width: 240px;
    }

    .role-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
</style>
@endpush

@section('content')
@php
    $canManageAdmins = auth()->user()?->isIct();
@endphp

<div class="users-header">
    <div>
        <h1 style="color: var(--color-primary); margin-bottom: 4px;">Admin Users</h1>
        <div class="role-muted">Assign departments to control access and actions.</div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <h2 style="color: var(--color-primary); margin-bottom: 16px;">Add Admin</h2>
    <form method="POST" action="{{ route('admin.users.store') }}" class="role-form">
        @csrf
        @if(!$canManageAdmins)
            <div class="role-muted" style="width: 100%; margin-bottom: 8px;">Only ICT admins can add or update admin roles.</div>
        @endif
        <fieldset @if(!$canManageAdmins) disabled @endif style="border: 0; padding: 0; margin: 0; display: contents;">
            <input type="text" name="name" placeholder="Full name" required>
            <input type="email" name="email" placeholder="Email address" required>
            <input type="text" name="password" placeholder="Temporary password" required>
            <select name="role" required>
                <option value="" disabled selected>Select department</option>
                <option value="{{ \App\Models\User::ROLE_FINANCE }}">Finance</option>
                <option value="{{ \App\Models\User::ROLE_HR }}">HR</option>
                <option value="{{ \App\Models\User::ROLE_ICT }}">ICT</option>
            </select>
            <button type="submit" class="btn btn-primary" @if(!$canManageAdmins) disabled @endif>Create Admin</button>
        </fieldset>
    </form>
</div>

<div class="card users-table">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Admin</th>
                <th>Role</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                <td>
                    @if($user->role)
                        <span class="role-pill">{{ ucfirst($user->role) }}</span>
                    @else
                        <span class="role-muted">None</span>
                    @endif
                </td>
                <td>
                    @if($user->is_admin)
                        <form class="role-form" method="POST" action="{{ route('admin.users.role', $user->id) }}">
                            @csrf
                            <fieldset @if(!$canManageAdmins) disabled @endif style="border: 0; padding: 0; margin: 0; display: contents;">
                                <select name="role" required>
                                    <option value="{{ \App\Models\User::ROLE_FINANCE }}" @selected($user->role === \App\Models\User::ROLE_FINANCE)>Finance</option>
                                    <option value="{{ \App\Models\User::ROLE_HR }}" @selected($user->role === \App\Models\User::ROLE_HR)>HR</option>
                                    <option value="{{ \App\Models\User::ROLE_ICT }}" @selected($user->role === \App\Models\User::ROLE_ICT)>ICT</option>
                                </select>
                                <div class="role-actions">
                                    <button type="submit" class="btn btn-primary" @if(!$canManageAdmins) disabled @endif>Save</button>
                                </div>
                            </fieldset>
                        </form>
                    @else
                        <span class="role-muted">Roles available for admins only.</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="role-muted">No users found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
