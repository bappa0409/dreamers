<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance | {{ setting('organization_name', 'Dreamers Association') }}</title>

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
        @keyframes wrenchWiggle {
            0%, 100% { transform: rotate(0deg); }
            25%      { transform: rotate(-12deg); }
            75%      { transform: rotate(12deg); }
        }
        .error-card       { animation: errorFadeIn .55s cubic-bezier(.22,1,.36,1) both; }
        .error-blob-a      { animation: floatSlowA 9s ease-in-out infinite; }
        .error-blob-b      { animation: floatSlowB 11s ease-in-out infinite; }
        .error-pulse-ring  { animation: pulseRing 2.4s cubic-bezier(.4,0,.6,1) infinite; }
        .maintenance-icon  { animation: wrenchWiggle 2.6s ease-in-out infinite; }
    </style>
</head>
<body class="relative min-h-screen overflow-hidden bg-slate-50 flex items-center justify-center p-6">

    {{-- Decorative background --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="error-blob-a absolute -top-24 -left-20 h-72 w-72 rounded-full bg-orange-200 opacity-40 blur-3xl"></div>
        <div class="error-blob-b absolute -bottom-24 -right-16 h-80 w-80 rounded-full bg-amber-200 opacity-40 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(100,116,139,0.14)_1px,transparent_0)] [background-size:24px_24px]"></div>
    </div>

    <div class="error-card relative w-full max-w-md overflow-hidden rounded-2xl border border-slate-200/80 bg-white/90 p-8 text-center shadow-[0_30px_80px_rgba(15,23,42,0.14),0_10px_30px_rgba(15,23,42,0.08)] backdrop-blur-sm sm:p-10">

        <div class="relative mx-auto flex h-24 items-center justify-center">
            <span class="pointer-events-none absolute inset-x-0 top-1/2 -translate-y-1/2 select-none text-center text-6xl font-black leading-none tracking-tighter text-orange-100">
                DA
            </span>

            <div class="relative h-16 w-16">
                <span class="error-pulse-ring absolute inset-0 rounded-2xl bg-orange-200"></span>
                <div class="maintenance-icon absolute inset-0 flex items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-600 text-2xl text-white shadow-lg ring-4 ring-white">
                    <i class="bi bi-tools"></i>
                </div>
            </div>
        </div>

        <p class="relative mt-5 inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1 text-[11px] font-bold tracking-widest text-orange-600">
            UNDER MAINTENANCE
        </p>

        <h1 class="relative mt-3 text-2xl font-bold text-slate-800">
            We'll be right back
        </h1>

        <p class="relative mt-3 text-sm leading-relaxed text-slate-500">
            {{ $message ?? "We're currently performing scheduled maintenance. We'll be back online shortly — thanks for your patience." }}
        </p>

        <div class="relative mt-4 flex items-start gap-2 rounded-md border border-slate-100 bg-slate-50 px-3.5 py-2.5 text-left">
            <i class="bi bi-translate mt-0.5 text-[13px] text-slate-400"></i>
            <p class=" text-xs 2xl:text-sm leading-relaxed text-slate-500">
                সাময়িকভাবে সাইটটি রক্ষণাবেক্ষণের কাজে বন্ধ আছে। কিছুক্ষণ পর আবার চেষ্টা করুন।
            </p>
        </div>

        <p class="relative mt-6 text-[11px] font-medium text-slate-400">
            {{ setting('organization_name', 'Dreamers Association') }}
        </p>

    </div>

</body>
</html>
