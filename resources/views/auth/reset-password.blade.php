@extends('layouts.auth')

@section('title', 'Reset Password | Dreamers Association')

@section('brand-eyebrow', 'ACCOUNT RECOVERY')
@section('brand-features-cols', 'grid-cols-1')

@section('brand-heading')
    Almost there.
    <br>
    <span class="text-emerald-200">Choose a new password.</span>
@endsection

@section('phrases')
    'Choose something you haven\'t used before.',
    'Avoid using your name or birthdate.',
    'A password manager can help you stay safe.'
@endsection

@section('brand-features')
    <div class="rounded-md border border-white/10 bg-white/5 backdrop-blur-sm p-4">
        <div class="w-9 h-9 rounded-md bg-emerald-400/20 flex items-center justify-center mb-3">
            🛡️
        </div>

        <p class="text-sm font-semibold">
            Stay Protected
        </p>

        <p class="text-xs text-emerald-100/70 mt-1">
            Use at least 8 characters with a mix of letters and numbers.
        </p>
    </div>
@endsection

@section('content')

    <a href="{{ route('login') }}"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors">
        <span>←</span>
        <span>Back to login</span>
    </a>

    <div class="mt-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
            Reset password
        </h2>

        <p class="mt-3 text-sm sm:text-base text-slate-500 leading-relaxed">
            Set a new password for
            <span class="font-semibold text-slate-700">{{ $email }}</span>
        </p>
    </div>

    @if($errors->any())
        <div class="message-box mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                New Password
            </label>

            <div class="relative">
                <input type="password" id="password" name="password" minlength="8" autocomplete="new-password"
                    placeholder="Enter new password" required
                    class="login-input w-full h-12 rounded-md border border-slate-200 bg-slate-50 px-4 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">

                <button type="button" onclick="togglePassword('password',this)"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-md flex items-center justify-center cursor-pointer text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                    aria-label="Show password">

                    <svg class="eye-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178a1.012 1.012 0 0 1 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>

            <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                <div id="strengthBar" class="strength-bar h-full w-0 bg-red-400"></div>
            </div>
            <p id="strengthLabel" class="mt-1 text-xs text-slate-400">
                Minimum 8 characters
            </p>
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                Confirm New Password
            </label>

            <div class="relative">
                <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                    autocomplete="new-password" placeholder="Re-enter new password" required
                    class="login-input w-full h-12 rounded-md border border-slate-200 bg-slate-50 px-4 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">

                <button type="button" onclick="togglePassword('password_confirmation',this)"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 rounded-md flex items-center justify-center cursor-pointer text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors"
                    aria-label="Show password">

                    <svg class="eye-icon w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.64 0 8.577 3.01 9.964 7.178a1.012 1.012 0 0 1 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.64 0-8.577-3.01-9.964-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit"
            class="login-button w-full h-12 rounded-md bg-gradient-to-r from-emerald-600 to-green-800 hover:from-emerald-700 hover:to-green-900 text-white font-semibold text-sm shadow-lg shadow-emerald-600/20 transition-all duration-200 flex items-center cursor-pointer justify-center gap-2">
            <span>Reset Password →</span>
        </button>
    </form>

    <div class="mt-8">
        <div class="flex items-center gap-3">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-xs text-slate-400 uppercase tracking-wider">Secure Access</span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>
        <p class="mt-5 text-center text-xs text-slate-400 leading-relaxed">
            This link expires 60 minutes after it was requested.
        </p>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const strengthBar = document.getElementById('strengthBar');
            const strengthLabel = document.getElementById('strengthLabel');

            if (!passwordInput) return;

            passwordInput.addEventListener('input', function () {
                const value = passwordInput.value;
                let score = 0;

                if (value.length >= 8) score++;
                if (value.length >= 12) score++;
                if (/[A-Z]/.test(value) && /[a-z]/.test(value)) score++;
                if (/[0-9]/.test(value)) score++;
                if (/[^A-Za-z0-9]/.test(value)) score++;

                const levels = [
                    { width: '0%', color: 'bg-red-400', label: 'Minimum 8 characters' },
                    { width: '20%', color: 'bg-red-400', label: 'Too weak' },
                    { width: '40%', color: 'bg-orange-400', label: 'Weak' },
                    { width: '60%', color: 'bg-yellow-400', label: 'Fair' },
                    { width: '80%', color: 'bg-emerald-400', label: 'Good' },
                    { width: '100%', color: 'bg-emerald-600', label: 'Strong' },
                ];

                const level = levels[Math.min(score, levels.length - 1)];

                strengthBar.style.width = value.length ? level.width : '0%';
                strengthBar.className = 'strength-bar h-full ' + level.color;
                strengthLabel.textContent = value.length ? level.label : 'Minimum 8 characters';
            });
        });
    </script>
@endpush