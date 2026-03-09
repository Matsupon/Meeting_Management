@extends('layouts.app')

@section('title', 'Login - Meeting Management System')

@push('styles')
<style>
    .error-message {
        background: #fee2e2;
        color: #b91c1c;
        padding: 16px;
        border-radius: var(--border-radius-md);
        margin-bottom: 24px;
        font-weight: 500;
        border: 1px solid #fca5a5;
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card glass">
        <h1>Welcome Back</h1>
        <p>Sign in to manage your events and schedule.</p>

        @if ($error ?? false)
            <div class="error-message">{{ $error }}</div>
        @endif

        @if (session('error'))
            <div class="error-message">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="e.g. Adviser" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">
                Sign In
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection
