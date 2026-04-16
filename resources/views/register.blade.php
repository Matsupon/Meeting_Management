@extends('layouts.app')

@section('title', 'Sign Up - Schedule Management System')

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

    .login-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 50px;
        border-radius: var(--border-radius-lg);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 450px;
        width: 100%;
    }

    .login-card h1 {
        font-size: var(--font-size-xl);
        color: var(--primary-blue);
        text-align: center;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .login-card p {
        text-align: center;
        color: var(--text-muted);
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-main);
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        font-size: 15px;
        transition: border-color 0.2s;
    }

    .form-group input:focus {
        outline: none;
        border-color: var(--primary-blue);
    }

    .btn {
        width: 100%;
        padding: 14px;
        background: var(--primary-blue);
        color: white;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
    }

    .btn:hover {
        background: #1d4ed8;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
    }
</style>
@endpush

@section('content')
<div class="login-container">
    <div class="login-card glass">
        <h1>Create Account</h1>
        <p>Sign up to start managing your events and schedule.</p>

        @if ($errors->any())
            <div class="error-message">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="e.g. John Doe" value="{{ old('full_name') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="e.g. user@example.com" value="{{ old('email') }}" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">
                Sign Up
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <p style="margin-bottom: 0;">Already have an account? <a href="{{ route('login') }}" style="color: var(--primary-blue); text-decoration: none; font-weight: 600;">Sign In</a></p>
        </div>
    </div>
</div>
@endsection
