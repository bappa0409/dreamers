@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('brand-eyebrow', 'ACCOUNT RECOVERY')
@section('brand-features-cols', 'grid-cols-1')

@section('brand-heading')
    Locked out?
    <br>
    <span class="text-emerald-200">Let's get you back in.</span>
@endsection

@section('phrases')
    'Check your inbox after submitting.',
    'The reset link stays valid for 60 minutes.',
    'Didn\'t get it? You can request again.',
    'Your account stays safe throughout.'
@endsection

@section('brand-features')
    <div class="rounded-md border border-white/10 bg-white/5 backdrop-blur-sm p-4">
        <div class="w-9 h-9 rounded-md bg-emerald-400/20 flex items-center justify-center mb-3">
            🔒
        </div>

        <p class="text-sm font-semibold">
            Secure Reset Link
        </p>

        <p class="text-xs text-emerald-100/70 mt-1">
            We'll email you a one-time link that expires in 60 minutes.
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

        <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
            Forgot password?
        </h2>

        <p class="mt-3 text-sm sm:text-base text-slate-500 leading-relaxed">
            No worries — enter your account email and we'll send you a link to reset it.
        </p>
    </div>

    @if(session('success'))
        <div class="message-box mt-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="message-box mt-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5" novalidate data-js-validation="1">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                Email Address
            </label>

            <input type="email" id="email" name="email" autocomplete="username" autofocus
                placeholder="you@example.com" required value="{{ old('email') }}"
                class="login-input w-full h-12 rounded-md border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10">
        </div>

        <button type="submit"
            class="login-button w-full h-12 rounded-md bg-gradient-to-r from-emerald-600 to-green-800 hover:from-emerald-700 hover:to-green-900 text-white font-semibold text-sm shadow-lg shadow-emerald-600/20 transition-all duration-200 flex items-center cursor-pointer justify-center gap-2">
            <span>Send Reset Link →</span>
        </button>
    </form>

    <div class="mt-8">
        <div class="flex items-center gap-3">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class="text-xs text-slate-400 uppercase tracking-wider">Secure Access</span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>
        <p class="mt-5 text-center text-xs text-slate-400 leading-relaxed">
            Remembered your password?
            <a href="{{ route('login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
                Sign in instead
            </a>
        </p>
    </div>

@endsection