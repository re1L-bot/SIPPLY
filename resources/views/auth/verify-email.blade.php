@extends('layouts.auth')

@section('title', 'Verify your email')
@section('story-title', 'One quick sip to confirm it’s you.')
@section('story-copy', 'We’ve sent a verification link to ensure your SipPly orders and account details stay safe and sound.')

@section('content')
    <p class="eyebrow">Almost there</p>
    <h2>Check your inbox</h2>
    <p class="form-intro">
        Thanks for creating an account! Before getting started, please verify your email address by clicking the link we just sent to
        <strong style="color:var(--espresso)">{{ auth()->user()->email ?? 'your email' }}</strong>.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="form-message success" role="status">
            A fresh verification link has just been sent to your email address!
        </div>
    @elseif (session('status'))
        <div class="form-message success" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="form-message error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div style="margin-top:28px;display:flex;flex-direction:column;gap:14px">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="submit-button" type="submit">Resend verification email</button>
        </form>

        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-top:10px;font-size:14px">
            <a href="{{ route('account') }}" style="font-weight:700;color:var(--espresso);text-decoration:underline">Go to my account &rarr;</a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" style="background:transparent;border:0;padding:0;font:inherit;font-weight:600;color:var(--muted);cursor:pointer;text-decoration:underline">
                    Wrong account? Sign out
                </button>
            </form>
        </div>
    </div>
@endsection
