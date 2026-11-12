@extends('layouts.auth')

@section('title', 'Administrator sign in')
@section('story-title', 'A smoother menu starts here.')
@section('story-copy', 'Manage your categories, drinks, prices, and what customers can order.')
@section('header-link')
    <a class="header-link" href="{{ route('login') }}">Customer <strong>sign in</strong></a>
@endsection

@section('content')
    <p class="eyebrow">SipPly team</p>
    <h2>Administrator sign in</h2>
    <p class="form-intro">Use the administrator account assigned to you.</p>

    @if (session('status'))
        <p class="form-message success" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.login.store') }}">
        @csrf

        <div class="field">
            <label for="email">Administrator email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus
                @error('email') aria-invalid="true" @enderror>
            @error('email')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required
                @error('password') aria-invalid="true" @enderror>
            @error('password')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button class="submit-button" type="submit">Sign in to admin</button>
    </form>

    <p class="form-switch">Admin accounts are created separately from customer accounts.</p>
@endsection
