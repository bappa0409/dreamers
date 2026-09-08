@extends('layouts.auth')

@section('title', 'Login')

@section('brand-heading')
    Build dreams.
    <br>
    <span class="text-emerald-200">Build the future.</span>
@endsection

@section('phrases')
    'Manage your membership and contributions with ease.',
    'Track investments, projects and financial activities.',
    'Stay connected with the Dreamers community.',
    'Together we build a better tomorrow.'
@endsection

@section('brand-features')
    <div class="rounded-md border border-white/10 bg-white/5 backdrop-blur-sm p-4">
        <div class="w-9 h-9 rounded-md bg-emerald-400/20 flex items-center justify-center mb-3">
            ✓
        </div>

        <p class="text-sm font-semibold">
            Member Management
        </p>

        <p class=" text-xs 2xl:text-sm text-emerald-100/70 mt-1">
            Manage members and accounts easily.
        </p>
    </div>

    <div class="rounded-md border border-white/10 bg-white/5 backdrop-blur-sm p-4">
        <div class="w-9 h-9 rounded-md bg-teal-400/20 flex items-center justify-center mb-3">
            ৳
        </div>

        <p class="text-sm font-semibold">
            Financial Tracking
        </p>

        <p class=" text-xs 2xl:text-sm text-emerald-100/70 mt-1">
            Track contributions and investments.
        </p>
    </div>
@endsection

@section('content')

    <a href="/"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors">
        <span>←</span>
        <span>Back to home</span>
    </a>

    <div class="mt-8">
        <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
            Welcome back
        </h2>

        <p class="text-sm sm:text-base text-slate-500 leading-relaxed">
            Sign in to manage your {{ setting('organization_name', 'Dreamers Association') }} account.
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

    <form id="loginForm" method="POST" action="{{ route('login.submit') }}" class="mt-8 space-y-5" novalidate data-js-validation="1">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                Email or Mobile Number
            </label>

            <input type="text" id="email" name="login" autocomplete="username" placeholder="Enter your email" required class="login-input w-full h-12 rounded-md border border-slate-200 bg-slate-50 px-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/10" autocomplete="off">
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label for="password" class="text-sm font-semibold text-slate-700">
                    Password
                </label>

                <a href="{{ route('password.request') }}"
                    class=" text-xs 2xl:text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                    Forgot password?
                </a>
            </div>

            <div class="relative">
                <input type="password" id="password" name="password" autocomplete="current-password"
                    placeholder="Enter your password" required
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
        </div>

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" id="remember"
                    class="w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600/20">
                <span class="text-sm text-slate-600">Remember me</span>
            </label>
        </div>

        <button type="submit"
            class="login-button w-full h-12 rounded-md bg-gradient-to-r from-emerald-600 to-green-800 hover:from-emerald-700 hover:to-green-900 text-white font-semibold text-sm shadow-lg shadow-emerald-600/20 transition-all duration-200 flex items-center cursor-pointer justify-center gap-2">
            <span>Sign In →</span>
        </button>
    </form>

    <div class="mt-8">
        <div class="flex items-center gap-3">
            <div class="h-px bg-slate-200 flex-1"></div>
            <span class=" text-xs 2xl:text-sm text-slate-400 uppercase tracking-wider">Secure Access</span>
            <div class="h-px bg-slate-200 flex-1"></div>
        </div>
        <p class="mt-5 text-center  text-xs 2xl:text-sm text-slate-400 leading-relaxed">
            Your account information is protected and securely handled by the {{ setting('organization_name', 'Dreamers Association') }} member portal.
        </p>
    </div>

@endsection