@php
    $__code = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('code', '000'))));

    $__themes = [
        'indigo' => [
            'icon'      => 'from-indigo-500 to-indigo-600',
            'ring'      => 'bg-indigo-200',
            'watermark' => 'text-indigo-100',
            'blob1'     => 'bg-indigo-200',
            'blob2'     => 'bg-violet-200',
            'badgeBg'   => 'bg-indigo-50',
            'badgeText' => 'text-indigo-600',
            'btn'       => 'from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800',
        ],
        'rose' => [
            'icon'      => 'from-rose-500 to-rose-600',
            'ring'      => 'bg-rose-200',
            'watermark' => 'text-rose-100',
            'blob1'     => 'bg-rose-200',
            'blob2'     => 'bg-orange-200',
            'badgeBg'   => 'bg-rose-50',
            'badgeText' => 'text-rose-600',
            'btn'       => 'from-rose-600 to-rose-700 hover:from-rose-700 hover:to-rose-800',
        ],
        'amber' => [
            'icon'      => 'from-amber-500 to-amber-600',
            'ring'      => 'bg-amber-200',
            'watermark' => 'text-amber-100',
            'blob1'     => 'bg-amber-200',
            'blob2'     => 'bg-yellow-200',
            'badgeBg'   => 'bg-amber-50',
            'badgeText' => 'text-amber-600',
            'btn'       => 'from-amber-600 to-amber-700 hover:from-amber-700 hover:to-amber-800',
        ],
        'red' => [
            'icon'      => 'from-red-500 to-red-600',
            'ring'      => 'bg-red-200',
            'watermark' => 'text-red-100',
            'blob1'     => 'bg-red-200',
            'blob2'     => 'bg-rose-200',
            'badgeBg'   => 'bg-red-50',
            'badgeText' => 'text-red-600',
            'btn'       => 'from-red-600 to-red-700 hover:from-red-700 hover:to-red-800',
        ],
        'orange' => [
            'icon'      => 'from-orange-500 to-orange-600',
            'ring'      => 'bg-orange-200',
            'watermark' => 'text-orange-100',
            'blob1'     => 'bg-orange-200',
            'blob2'     => 'bg-amber-200',
            'badgeBg'   => 'bg-orange-50',
            'badgeText' => 'text-orange-600',
            'btn'       => 'from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800',
        ],
    ];

    $__theme = trim($__env->yieldContent('theme', 'indigo'));
    $t = $__themes[$__theme] ?? $__themes['indigo'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | Dreamers Association</title>

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        @keyframes errorFadeIn {
            from { opacity: 0; transform: translateY(18px) scale(.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes floatSlowA {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(16px, -18px) scale(1.08); }
        }
        @keyframes floatSlowB {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50%      { transform: translate(-18px, 16px) scale(1.06); }
        }
        @keyframes pulseRing {
            0%   { transform: scale(1); opacity: .55; }
            70%  { transform: scale(1.55); opacity: 0; }
            100% { transform: scale(1.55); opacity: 0; }
        }
        .error-card      { animation: errorFadeIn .55s cubic-bezier(.22,1,.36,1) both; }
        .error-blob-a     { animation: floatSlowA 9s ease-in-out infinite; }
        .error-blob-b     { animation: floatSlowB 11s ease-in-out infinite; }
        .error-pulse-ring { animation: pulseRing 2.4s cubic-bezier(.4,0,.6,1) infinite; }
    </style>
</head>
<body class="relative min-h-screen overflow-hidden bg-slate-50 flex items-center justify-center p-6">

    {{-- Decorative background --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="error-blob-a absolute -top-24 -left-20 h-72 w-72 rounded-full {{ $t['blob1'] }} opacity-40 blur-3xl"></div>
        <div class="error-blob-b absolute -bottom-24 -right-16 h-80 w-80 rounded-full {{ $t['blob2'] }} opacity-40 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(100,116,139,0.14)_1px,transparent_0)] [background-size:24px_24px]"></div>
    </div>

    <div class="error-card relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-8 text-center shadow-[0_30px_80px_rgba(15,23,42,0.14),0_10px_30px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:p-10">

        {{-- Icon + watermark --}}
        <div class="relative mx-auto flex h-24 items-center justify-center">
            <span class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 select-none text-center text-7xl font-black leading-none tracking-tighter {{ $t['watermark'] }}">
                {{ $__code }}
            </span>

            <div class="relative h-16 w-16">
                <span class="error-pulse-ring absolute inset-0 rounded-2xl {{ $t['ring'] }}"></span>
                <div class="absolute inset-0 flex items-center justify-center rounded-2xl bg-gradient-to-br {{ $t['icon'] }} text-2xl text-white shadow-lg ring-4 ring-white">
                    <i class="bi @yield('icon', 'bi-exclamation-triangle')"></i>
                </div>
            </div>
        </div>

        <p class="relative mt-5 inline-flex items-center gap-1.5 rounded-full {{ $t['badgeBg'] }} px-3 py-1 text-[11px] font-bold tracking-widest {{ $t['badgeText'] }}">
            ERROR {{ $__code }}
        </p>

        <h1 class="relative mt-3 text-2xl font-bold text-slate-800">
            @yield('title')
        </h1>

        <p class="relative mt-3 text-sm leading-relaxed text-slate-500">
            @yield('message')
        </p>

        @hasSection('bn_message')
            <div class="relative mt-4 flex items-start gap-2 rounded-md border border-slate-100 bg-slate-50 px-3.5 py-2.5 text-left">
                <i class="bi bi-translate mt-0.5 text-[13px] text-slate-400"></i>
                <p class="text-xs leading-relaxed text-slate-500">
                    @yield('bn_message')
                </p>
            </div>
        @endif

        <div class="relative mt-7 flex flex-col items-center justify-center gap-2 sm:flex-row">
            <a
                href="{{ url('/') }}"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-gradient-to-r {{ $t['btn'] }} px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:shadow-md sm:w-auto"
            >
                <i class="bi bi-house-door text-[12px]"></i>
                Go to Home
            </a>

            <button
                type="button"
                onclick="window.history.back()"
                class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 sm:w-auto"
            >
                <i class="bi bi-arrow-left text-[12px]"></i>
                Go Back
            </button>
        </div>

        <p class="relative mt-6 text-[11px] font-medium text-slate-400">
            Dreamers Association
        </p>

    </div>

</body>
</html>
