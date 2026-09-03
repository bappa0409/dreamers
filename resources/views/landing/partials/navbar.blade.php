@php
$active = $active ?? 'home';
$navLinkClass = fn($key) => 'text-sm font-medium transition hover:text-teal-700 ' . ($active === $key ? 'text-teal-700'
: 'text-slate-700');
$mobileLinkClass = fn($key) => 'border-b border-slate-100 py-3 text-sm font-medium transition ' . ($active === $key ?
'text-teal-700' : 'text-slate-700');

$organizationName = $organizationName ?? setting('organization_name', 'Dreamers Association');
$siteLogo = $siteLogo ?? setting('site_logo');
$nameParts = preg_split('/\s+/', trim($organizationName), 2);
$primaryName = $nameParts[0] ?? $organizationName;
$secondaryName = $nameParts[1] ?? '';
@endphp

<header id="navbar"
    class="fixed top-0 left-0 right-0 z-50 border-b border-slate-200/70 bg-white/70 backdrop-blur-xl transition-all duration-300">
    <div class="mx-auto flex h-[68px] max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">

        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-md {{ $siteLogo ? '' : 'bg-teal-700 text-white shadow-lg shadow-teal-700/20' }}">

                @if($siteLogo)
                <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $organizationName }}"
                    class="h-full w-full object-contain">
                @else
                <i data-lucide="sparkles" class="h-[18px] w-[18px]"></i>
                @endif

            </div>
            <div class="leading-tight">
                <div class="text-[22px] font-bold text-slate-900">{{ $primaryName }}</div>
                @if($secondaryName)
                <div class="-mt-0.5 text-[12px] font-medium tracking-[0.15em] text-teal-700 uppercase">{{ $secondaryName
                    }}</div>
                @endif
            </div>
        </a>

        <nav class="hidden items-center gap-7 md:flex">
            <a href="{{ route('home') }}" class="{{ $navLinkClass('home') }}">হোম</a>
            <a href="{{ route('about') }}" class="{{ $navLinkClass('about') }}">আমাদের সম্পর্কে</a>
            <a href="{{ route('activities') }}" class="{{ $navLinkClass('activities') }}">কার্যক্রম</a>
            <a href="{{ route('transparency') }}" class="{{ $navLinkClass('transparency') }}">স্বচ্ছতা</a>
            <a href="{{ route('faq') }}" class="{{ $navLinkClass('faq') }}">প্রশ্নোত্তর</a>
        </nav>

        <a href="{{ route('home') }}#contact"
            class="hidden items-center gap-2 rounded-md bg-teal-700 px-4 py-2 font-semibold text-sm text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 md:flex">
            যোগাযোগ করুন
            <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
        </a>

        <button id="menuBtn" type="button"
            class="flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-700 md:hidden">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>
    </div>

    <div id="mobileMenu" class="hidden border-t border-slate-100 bg-white px-5 pb-5 pt-3 md:hidden">
        <div class="flex flex-col">
            <a href="{{ route('home') }}" class="{{ $mobileLinkClass('home') }}">হোম</a>
            <a href="{{ route('about') }}" class="{{ $mobileLinkClass('about') }}">আমাদের সম্পর্কে</a>
            <a href="{{ route('activities') }}" class="{{ $mobileLinkClass('activities') }}">কার্যক্রম</a>
            <a href="{{ route('transparency') }}" class="{{ $mobileLinkClass('transparency') }}">স্বচ্ছতা</a>
            <a href="{{ route('faq') }}" class="{{ $mobileLinkClass('faq') }}">প্রশ্নোত্তর</a>
            <a href="{{ route('home') }}#contact"
                class="mt-3 flex items-center justify-center gap-2 rounded-md bg-teal-700 py-3 text-sm font-semibold text-white">
                যোগাযোগ করুন
                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
            </a>
        </div>
    </div>
</header>