@extends('layouts.auth')

@section('title', 'Create account')
@section('story-title', 'Your next favorite is just a sip away.')
@section('story-copy', 'Create an account and make every SipPly visit a little more yours.')

@section('content')
    <p class="eyebrow">Join the good stuff</p>
    <h2>Create account</h2>
    <p class="form-intro">It only takes a moment to get started.</p>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="form-grid">
            <div class="field">
                <label for="first_name">First name</label>
                <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" autocomplete="given-name" required
                    @error('first_name') aria-invalid="true" @enderror>
                @error('first_name')
                    <p class="field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="last_name">Last name</label>
                <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" autocomplete="family-name" required
                    @error('last_name') aria-invalid="true" @enderror>
                @error('last_name')
                    <p class="field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required
                @error('email') aria-invalid="true" @enderror>
            @error('email')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="phone_number">Phone number</label>
            <input id="phone_number" name="phone_number" type="tel" value="{{ old('phone_number') }}" autocomplete="tel" inputmode="tel" required
                @error('phone_number') aria-invalid="true" @enderror>
            @error('phone_number')
                <p class="field-error" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-grid">
            <div class="field">
                <label for="password">Password</label>
                <div class="password-wrap">
                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        @error('password') aria-invalid="true" @enderror>
                    <button type="button" class="password-toggle" data-target="password" aria-label="Show password" aria-pressed="false">
                        <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="field-error" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <div class="password-wrap">
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                    <button type="button" class="password-toggle" data-target="password_confirmation" aria-label="Show password" aria-pressed="false">
                        <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C5 20 1 12 1 12a18.5 18.5 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A10.9 10.9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <path d="M14.12 14.12A3 3 0 1 1 9.88 9.88"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <button class="submit-button" type="submit">Create my account</button>
    </form>

    <p class="form-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>

    {{-- Inline styles + script so both password toggles work --}}
    <style>
        .password-wrap {
            position: relative;
            display: block;
        }
        .password-wrap input {
            width: 100%;
            padding-right: 3rem;
            box-sizing: border-box;
        }
        .password-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            margin: 0;
            border: 0;
            border-radius: 0.5rem;
            background: transparent;
            color: #8a6553;
            cursor: pointer;
            line-height: 0;
            z-index: 2;
        }
        .password-toggle:hover {
            color: #542812;
            background: rgba(84, 40, 18, 0.08);
        }
        .password-toggle:focus-visible {
            outline: 2px solid #D99A69;
            outline-offset: 2px;
        }
        .password-toggle svg {
            width: 1.25rem;
            height: 1.25rem;
            pointer-events: none;
        }
        .password-toggle .icon-show { display: block !important; }
        .password-toggle .icon-hide  { display: none  !important; }
        .password-toggle.is-visible .icon-show { display: none  !important; }
        .password-toggle.is-visible .icon-hide  { display: block !important; }
    </style>

    <script>
    (function () {
        function setupToggle(btn) {
            if (btn.getAttribute('data-bound') === '1') return;
            btn.setAttribute('data-bound', '1');

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var targetId = btn.getAttribute('data-target');
                var input = targetId ? document.getElementById(targetId) : null;

                if (!input) {
                    var wrap = btn.parentElement;
                    if (wrap) input = wrap.querySelector('input');
                }
                if (!input) return;

                var showing = input.type === 'password';
                input.type = showing ? 'text' : 'password';

                if (showing) {
                    btn.classList.add('is-visible');
                    btn.setAttribute('aria-pressed', 'true');
                    btn.setAttribute('aria-label', 'Hide password');
                } else {
                    btn.classList.remove('is-visible');
                    btn.setAttribute('aria-pressed', 'false');
                    btn.setAttribute('aria-label', 'Show password');
                }
            });
        }

        function init() {
            var buttons = document.querySelectorAll('.password-toggle');
            for (var i = 0; i < buttons.length; i++) {
                setupToggle(buttons[i]);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
@endsection
