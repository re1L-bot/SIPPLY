@extends('layouts.auth')

@section('title', 'Forgot password')
@section('story-title', 'No worries, we’ll get you back in.')
@section('story-copy', 'Tell us your email and we’ll send a link to reset your password.')

@section('content')
    <p class="eyebrow">Forgot your password?</p>
    <h2>Reset password</h2>
    <p class="form-intro">Enter the email you signed up with and we’ll email you a reset link.</p>

    @if (session('status'))
        <p class="form-message success" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus
                @error('email') aria-invalid="true" @enderror>
            @error('email')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button class="submit-button" type="submit">Send reset link</button>
    </form>

    <p class="form-switch">Remembered it? <a href="{{ route('login') }}">Back to sign in</a></p>
@endsection
