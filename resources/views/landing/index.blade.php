@extends('layouts.public')

@section('title', setting('organization_name', 'Dreamers Association') . ' | স্বচ্ছতা, ঐক্য ও সম্মিলিত আর্থিক উন্নয়ন')
@section('description', setting('organization_name', 'Dreamers Association') . ' — সদস্যদের সম্মিলিত সঞ্চয়, বিনিয়োগ ও সম্পদ গঠনের মাধ্যমে স্বচ্ছ ও দীর্ঘমেয়াদি আর্থিক উন্নয়ন নিশ্চিত করার একটি ঐক্যবদ্ধ উদ্যোগ।')
@section('keywords', setting('organization_name', 'Dreamers Association') . ', ' . setting('organization_name', 'Dreamers Association') . ' Bangladesh, সম্মিলিত সঞ্চয়, সম্মিলিত বিনিয়োগ, আর্থিক উন্নয়ন, সদস্যভিত্তিক সঞ্চয়, সম্পদ গঠন, স্বচ্ছ আর্থিক ব্যবস্থাপনা')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');
$organizationEmail = setting('organization_email', 'info@dreamersassociation.com');
$organizationPhone = setting('organization_phone', '+880 1XXX-XXXXXX');
$organizationAddress = setting('organization_address', 'Dhaka, Bangladesh');

// Landing page content is editable from Admin > Website Page. Each helper
// below falls back to the original hard-coded copy when a section hasn't
// been saved in the database yet, so the page never breaks.
$sections = $sections ?? collect();
$lp = fn ($key) => $sections->get($key);
$lpField = fn ($key, $field, $default = null) => data_get($lp($key), $field) ?? $default;
$lpTitleParts = function ($key, $default1, $default2) use ($lp) {
    $raw = data_get($lp($key), 'title');
    if (!$raw) {
        return [$default1, $default2];
    }
    $parts = explode('|', $raw, 2);
    return [trim($parts[0]), trim($parts[1] ?? '')];
};
// A section stays visible by default; it's only hidden once an admin
// explicitly switches it off in Admin > Website Page.
$lpVisible = fn ($key) => $lp($key) === null ? true : (bool) $lp($key)->is_active;
@endphp

@include('landing.partials.navbar', ['active' => 'home'])

{{-- =========================================================
HERO
========================================================== --}}
<section id="home" class="hero-grid relative overflow-hidden bg-teal-50/50 pt-28 pb-20 sm:pt-32 sm:pb-24">
    <div class="absolute -right-28 -top-28 h-72 w-72 rounded-full bg-teal-500/20 blur-3xl"></div>
    <div class="absolute -bottom-32 -left-28 h-72 w-72 rounded-full bg-teal-600/15 blur-3xl"></div>

    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">

            <div class="reveal">
                <div
                    class="mb-5 inline-flex items-center gap-2 rounded-full border border-teal-100 bg-teal-50 px-3 py-1.5 text-[13px] font-medium text-teal-700">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-teal-600"></span>
                    {{ $lpField('hero', 'subtitle', 'একটি ঐক্যবদ্ধ ও স্বচ্ছ উদ্যোগ') }}
                </div>

                @php [$heroLine1, $heroLine2] = $lpTitleParts('hero', 'একসাথে স্বপ্ন দেখি,', 'একসাথে এগিয়ে যাই।'); @endphp
                <h1
                    class="max-w-xl text-[2rem] font-bold leading-[1.2] tracking-tight text-slate-900 sm:text-[2.6rem] lg:text-[3rem]">
                    {{ $heroLine1 }}<br>
                    <span class="text-brand-700">{{ $heroLine2 }}</span>
                </h1>

                <p class="mt-5 max-w-xl text-[14px] leading-7 text-slate-600 sm:text-[15px]">
                    {{ $organizationName }} {{ $lpField('hero', 'content', 'একটি ঐক্যবদ্ধ সংগঠন, যেখানে সদস্যদের
                    সম্মিলিত সঞ্চয়, বিনিয়োগ ও পরিকল্পনার মাধ্যমে দীর্ঘমেয়াদি
                    আর্থিক উন্নয়নের সুযোগ তৈরি করা হয়।') }}
                </p>

                <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $lpField('hero', 'button_url', route('about')) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-[13px] font-semibold text-white shadow-xl shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">
                        {{ $lpField('hero', 'button_text', 'আমাদের সম্পর্কে জানুন') }}
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                    <a href="{{ $lpField('hero', 'settings.secondary_button_url', route('activities')) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-200 bg-white px-5 py-3 text-[13px] font-semibold text-slate-700 transition hover:border-teal-200 hover:text-teal-700">
                        {{ $lpField('hero', 'settings.secondary_button_text', 'আমাদের কার্যক্রম') }}
                        <i data-lucide="move-up-right" class="h-4 w-4"></i>
                    </a>
                </div>

                <div class="mt-7 flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-teal-100 text-[10px] font-bold text-teal-700">
                            DA</div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-amber-100 text-[10px] font-bold text-amber-700">
                            25+</div>
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-100 text-[10px] font-bold text-slate-600">
                            ✓</div>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-slate-700">ঐক্য • আস্থা • স্বচ্ছতা</p>
                        <p class="text-[10px] text-slate-500">আমাদের মূল মূল্যবোধ</p>
                    </div>
                </div>
            </div>

            <div class="relative reveal">
                <div class="relative mx-auto max-w-[520px]">

                    <div
                        class="relative overflow-hidden rounded-md border border-slate-200 bg-white p-5 shadow-card sm:p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[11px] text-slate-500">Association Overview</p>
                                <h3 class="mt-1 text-[17px] font-bold text-slate-900">আমাদের অগ্রগতি</h3>
                            </div>
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                                <i data-lucide="bar-chart-3" class="h-4 w-4"></i>
                            </div>
                        </div>

                        <div class="mt-6 h-40 rounded-md bg-slate-100 p-4">
                            <div class="flex h-full items-end gap-2">
                                <div class="h-[35%] flex-1 rounded-t-md bg-teal-100"></div>
                                <div class="h-[48%] flex-1 rounded-t-md bg-teal-200"></div>
                                <div class="h-[42%] flex-1 rounded-t-md bg-teal-200"></div>
                                <div class="h-[65%] flex-1 rounded-t-md bg-teal-300"></div>
                                <div class="h-[58%] flex-1 rounded-t-md bg-teal-300"></div>
                                <div class="h-[76%] flex-1 rounded-t-md bg-teal-400"></div>
                                <div class="h-[88%] flex-1 rounded-t-md bg-teal-500"></div>
                                <div class="h-full flex-1 rounded-t-md bg-teal-600"></div>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-md bg-slate-100 p-3">
                                <p class="text-[10px] text-slate-500">সদস্য</p>
                                <p class="mt-1 text-[18px] font-bold text-slate-900">25+</p>
                            </div>
                            <div class="rounded-md bg-slate-100 p-3">
                                <p class="text-[10px] text-slate-500">পরিকল্পনা</p>
                                <p class="mt-1 text-[18px] font-bold text-slate-900">10+</p>
                            </div>
                            <div class="rounded-md bg-slate-100 p-3">
                                <p class="text-[10px] text-slate-500">লক্ষ্য</p>
                                <p class="mt-1 text-[18px] font-bold text-teal-700">দীর্ঘমেয়াদি</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="absolute -bottom-5 -left-4 hidden w-48 animate-float rounded-md border border-slate-200 bg-white p-4 shadow-xl sm:block">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                                <i data-lucide="shield-check" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500">আমাদের অঙ্গীকার</p>
                                <p class="text-[13px] font-bold text-slate-800">স্বচ্ছতা ও জবাবদিহিতা</p>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -right-4 -top-5 hidden w-44 animate-float rounded-md border border-slate-200 bg-white p-4 shadow-xl sm:block"
                        style="animation-delay: 1.2s;">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                                <i data-lucide="users" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-[10px] text-slate-500">কমিউনিটি</p>
                                <p class="text-[13px] font-bold text-slate-800">একসাথে আমরা বেড়ে উঠি</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
STATS
========================================================== --}}
@php
$statItems = $lpField('stats', 'settings.items', [
    ['value' => '25', 'suffix' => '', 'label' => 'সক্রিয় সদস্য'],
    ['value' => '10', 'suffix' => '', 'label' => 'চলমান পরিকল্পনা'],
    ['value' => '100', 'suffix' => '', 'label' => 'স্বচ্ছতার অঙ্গীকার'],
    ['value' => 'Long', 'suffix' => '', 'label' => 'দীর্ঘমেয়াদি লক্ষ্য'],
]);
@endphp
@if($lpVisible('stats'))
<section class="border-y border-slate-100 bg-white">
    <div class="mx-auto grid max-w-7xl grid-cols-2 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
        @foreach ($statItems as $i => $stat)
        <div class="reveal px-4 text-center {{ $i < 3 ? 'border-r border-slate-100' : '' }}">
            @if(is_numeric($stat['value'] ?? null))
            <p class="counter text-3xl font-bold text-slate-900" data-target="{{ $stat['value'] }}">0</p>
            @else
            <p class="text-3xl font-bold text-teal-700">{{ $stat['value'] ?? '' }}</p>
            @endif
            <p class="mt-1 text-sm text-slate-500">{{ $stat['label'] ?? '' }}</p>
        </div>
        @endforeach
    </div>
</section>
@endif


{{-- =========================================================
ABOUT
========================================================== --}}
@if($lpVisible('about'))
<section id="about" class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">{{ $lpField('about', 'subtitle', 'About Us') }}</span>

                @php [$aboutLine1, $aboutLine2] = $lpTitleParts('about', 'আমাদের স্বপ্ন,', 'আমাদের সম্মিলিত শক্তি।'); @endphp
                <h2 class="mt-3 text-[2rem] font-bold leading-tight text-slate-900 sm:text-[2.55rem]">
                    {{ $aboutLine1 }}
                    <span class="text-teal-700">{{ $aboutLine2 }}</span>
                </h2>

                <p class="mt-3 text-[14px] leading-7 text-slate-600">
                    {{ $organizationName }} {{ $lpField('about', 'content', 'এমন একটি সংগঠন যেখানে সদস্যদের
                    সম্মিলিত প্রচেষ্টা, নিয়মিত সঞ্চয় এবং পরিকল্পিত বিনিয়োগের
                    মাধ্যমে ভবিষ্যতের জন্য একটি শক্তিশালী ভিত্তি তৈরি করার
                    চেষ্টা করা হয়।') }}
                </p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="target" class="h-4 w-4"></i>
                        </div>
                        <h3 class="mt-3 text-[14px] font-bold text-slate-900">আমাদের লক্ষ্য</h3>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500">
                            পরিকল্পিত সঞ্চয় ও বিনিয়োগের মাধ্যমে দীর্ঘমেয়াদি
                            আর্থিক সম্ভাবনা তৈরি করা।
                        </p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                            <i data-lucide="eye" class="h-4 w-4"></i>
                        </div>
                        <h3 class="mt-3 text-[14px] font-bold text-slate-900">আমাদের দৃষ্টিভঙ্গি</h3>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500">
                            আস্থা, শৃঙ্খলা ও স্বচ্ছতার ভিত্তিতে একটি শক্তিশালী
                            কমিউনিটি গড়ে তোলা।
                        </p>
                    </div>
                </div>

                <a href="{{ $lpField('about', 'button_url', route('about')) }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-[13px] font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">
                    {{ $lpField('about', 'button_text', 'বিস্তারিত জানুন') }}
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>

            <div class="reveal">
                <div class="relative overflow-hidden rounded-md bg-slate-900 p-7 text-white shadow-card sm:p-9">
                    <div class="absolute -right-20 -top-20 h-48 w-48 rounded-full bg-teal-500/20 blur-3xl"></div>

                    <div class="relative">
                        <div class="flex h-11 w-11 items-center justify-center rounded-md bg-teal-500/20 text-teal-300">
                            <i data-lucide="quote" class="h-5 w-5"></i>
                        </div>

                        <h3 class="mt-6 text-[22px] font-bold">আমাদের মূল দর্শন</h3>

                        <p class="mt-4 text-[14px] leading-7 text-slate-300">
                            "একজনের শক্তি সীমিত হতে পারে, কিন্তু একটি
                            ঐক্যবদ্ধ দলের সম্ভাবনা অনেক বড়।"
                        </p>

                        <div class="my-7 h-px bg-white/10"></div>

                        <div class="space-y-3">
                            <div class="flex gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-white/10">
                                    <i data-lucide="handshake" class="h-4 w-4 text-teal-300"></i>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-semibold">পারস্পরিক সহযোগিতা</h4>
                                    <p class="mt-1 text-[13px] leading-5 text-slate-500">
                                        সদস্যদের মধ্যে সহযোগিতা ও আস্থার পরিবেশ তৈরি করা।
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-white/10">
                                    <i data-lucide="shield-check" class="h-4 w-4 text-teal-300"></i>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-semibold">স্বচ্ছতা</h4>
                                    <p class="mt-1 text-[13px] leading-5 text-slate-500">
                                        আর্থিক কার্যক্রমে স্বচ্ছতা ও জবাবদিহিতা নিশ্চিত করা।
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-white/10">
                                    <i data-lucide="trending-up" class="h-4 w-4 text-teal-300"></i>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-semibold">দীর্ঘমেয়াদি উন্নয়ন</h4>
                                    <p class="mt-1 text-[13px] leading-5 text-slate-500">
                                        তাৎক্ষণিক লাভের পাশাপাশি ভবিষ্যতের জন্য স্থায়ী
                                        সম্পদ তৈরির চেষ্টা।
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endif



{{-- =========================================================
ACTIVITIES (TEASER)
========================================================== --}}
@if($lpVisible('activities'))
<section id="activities" class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col gap-2 reveal">
            <div>
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">{{ $lpField('activities', 'subtitle', 'Our Activities') }}</span>
                <h2 class="text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">{{ $lpField('activities', 'title', 'আমাদের প্রধান কার্যক্রম') }}</h2>
                <p class="max-w-2xl text-[13px] leading-6 text-slate-500">
                    {{ $lpField('activities', 'content', 'সংগঠনের লক্ষ্য ও সদস্যদের দীর্ঘমেয়াদি কল্যাণকে সামনে রেখে
                    বিভিন্ন সম্ভাবনাময় উদ্যোগ নিয়ে কাজ করা হয়।') }}
                </p>
            </div>
        </div>

        <div class="mt-10 grid gap-5 md:grid-cols-3">

            <!-- Investment -->
            <div
                class="reveal group rounded-md border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-card">

                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="trending-up" class="h-5 w-5"></i>
                    </div>

                    <div>
                        <h3 class="text-[17px] font-bold text-slate-900">
                            বিনিয়োগ
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            সম্ভাবনাময় ও যাচাইযোগ্য ক্ষেত্রে সম্মিলিতভাবে বিনিয়োগের
                            সুযোগ তৈরি করা।
                        </p>
                    </div>
                </div>

            </div>

            <!-- Land & Assets -->
            <div
                class="reveal group rounded-md border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-card">

                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-amber-500 text-white">
                        <i data-lucide="landmark" class="h-5 w-5"></i>
                    </div>

                    <div>
                        <h3 class="text-[17px] font-bold text-slate-900">
                            জমি ও সম্পদ
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            দীর্ঘমেয়াদি সম্পদ তৈরির উদ্দেশ্যে উপযুক্ত জমি বা সম্পদ
                            ক্রয়ের পরিকল্পনা করা।
                        </p>
                    </div>
                </div>

            </div>

            <!-- Business Initiative -->
            <div
                class="reveal group rounded-md border border-slate-200 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:shadow-card">

                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-sky-600 text-white">
                        <i data-lucide="store" class="h-5 w-5"></i>
                    </div>

                    <div>
                        <h3 class="text-[17px] font-bold text-slate-900">
                            ব্যবসায়িক উদ্যোগ
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            সম্ভাবনাময় পণ্য বা ব্যবসায়িক কার্যক্রমের মাধ্যমে সংগঠনের
                            তহবিলকে উৎপাদনশীল কাজে ব্যবহার করা।
                        </p>
                    </div>
                </div>

            </div>

        </div>

        <div class="mt-10 reveal text-center">
            <a href="{{ $lpField('activities', 'button_url', route('activities')) }}"
                class="inline-flex items-center gap-2 rounded-md border border-slate-200 bg-white px-5 py-3 text-[13px] font-semibold text-slate-700 transition hover:border-teal-200 hover:text-teal-700">
                {{ $lpField('activities', 'button_text', 'সব কার্যক্রম বিস্তারিত দেখুন') }}
                <i data-lucide="arrow-up-right" class="h-4 w-4"></i>
            </a>
        </div>
    </div>
</section>
@endif

{{-- =========================================================
TRANSPARENCY (TEASER)
========================================================== --}}
@if($lpVisible('transparency'))
<section id="transparency" class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">{{ $lpField('transparency', 'subtitle', 'Transparency') }}</span>

                @php [$transLine1, $transLine2] = $lpTitleParts('transparency', 'হিসাব থাকবে পরিষ্কার,', 'সিদ্ধান্ত হবে স্বচ্ছ।'); @endphp
                <h2 class="mt-3 text-[2rem] font-bold leading-tight text-slate-900 sm:text-[2.5rem]">
                    {{ $transLine1 }}
                    <span class="text-teal-700">{{ $transLine2 }}</span>
                </h2>

                <p class="mt-5 text-[14px] leading-7 text-slate-600">
                    {{ $lpField('transparency', 'content', 'একটি সংগঠনের জন্য আর্থিক স্বচ্ছতা ও সঠিক হিসাবরক্ষণ
                    অত্যন্ত গুরুত্বপূর্ণ। তাই প্রতিটি লেনদেন, আয়-ব্যয় এবং
                    বিনিয়োগের তথ্য যথাযথভাবে সংরক্ষণ ও পর্যালোচনা করার
                    ব্যবস্থা রাখা হয়।') }}
                </p>

                <a href="{{ $lpField('transparency', 'button_url', route('transparency')) }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-[13px] font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">
                    {{ $lpField('transparency', 'button_text', 'স্বচ্ছতা সম্পর্কে বিস্তারিত জানুন') }}
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>

            <div class="reveal">
                <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-card">

                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-teal-700 text-white">
                                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-900">Financial Overview</p>
                                <p class="text-[9px] text-slate-500">{{ $organizationName }}</p>
                            </div>
                        </div>
                        <div class="rounded-md bg-emerald-50 px-2 py-1 text-[9px] font-semibold text-emerald-600">
                            Updated</div>
                    </div>

                    <div class="p-5">
                        <p class="text-[10px] text-slate-500">মোট সংগৃহীত তহবিল</p>

                        <div class="mt-1 flex items-end justify-between">
                            <p class="text-2xl font-bold text-slate-900">{{ setting('currency_symbol','৳') }} 12,50,000</p>
                            <span class="text-[10px] font-semibold text-emerald-600">+12.5%</span>
                        </div>

                        <div class="mt-5 flex h-24 items-end gap-1.5">
                            <div class="h-[35%] flex-1 rounded-t bg-teal-100"></div>
                            <div class="h-[45%] flex-1 rounded-t bg-teal-200"></div>
                            <div class="h-[40%] flex-1 rounded-t bg-teal-200"></div>
                            <div class="h-[58%] flex-1 rounded-t bg-teal-300"></div>
                            <div class="h-[52%] flex-1 rounded-t bg-teal-300"></div>
                            <div class="h-[70%] flex-1 rounded-t bg-teal-400"></div>
                            <div class="h-[80%] flex-1 rounded-t bg-teal-500"></div>
                            <div class="h-[92%] flex-1 rounded-t bg-teal-600"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endif

{{-- =========================================================
FAQ (TEASER)
========================================================== --}}

@if($lpVisible('faq'))
<section id="faq" class="bg-white py-10 sm:py-10">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span
                class="inline-flex items-center rounded-full border border-teal-100 bg-white px-3 py-1 text-[11px] font-bold uppercase tracking-[0.18em] text-teal-700 shadow-sm">
                {{ $lpField('faq', 'subtitle', 'Frequently Asked Questions') }}
            </span>
            <h2 class="mt-4 text-[1.8rem] font-bold tracking-tight text-slate-900 sm:text-[2.35rem]">{{ $lpField('faq', 'title', 'সাধারণ কিছু প্রশ্ন') }}
            </h2>
            <p class="mx-auto max-w-xl text-[13px] leading-6 text-slate-500 sm:text-[14px]">
                {{ $organizationName }} {{ $lpField('faq', 'content', 'সম্পর্কে প্রাথমিক কিছু প্রশ্নের সহজ ও
                সংক্ষিপ্ত উত্তর।') }}
            </p>
        </div>

        <div class="mx-auto mt-10 grid max-w-5xl gap-4 lg:grid-cols-2 lg:items-start">

            {{-- LEFT COLUMN --}}
            <div class="space-y-4">

                @php
                $faqLeft = [
                ['q' => $organizationName.' কী?', 'a' => $organizationName.' একটি সম্মিলিত উদ্যোগ, যেখানে সদস্যদের
                সঞ্চয় ও পরিকল্পিত কার্যক্রমের মাধ্যমে দীর্ঘমেয়াদি আর্থিক সম্ভাবনা তৈরির চেষ্টা করা হয়।'],
                ['q' => 'সংগঠনের অর্থ কীভাবে ব্যবহার করা হয়?', 'a' => 'সংগঠনের নীতিমালা ও সম্মিলিত সিদ্ধান্ত অনুযায়ী
                বিনিয়োগ, ব্যবসায়িক কার্যক্রম এবং দীর্ঘমেয়াদি সম্পদ তৈরির মতো উদ্যোগে অর্থ ব্যবহার করার পরিকল্পনা করা
                হয়।'],
                ['q' => 'আর্থিক হিসাব কীভাবে সংরক্ষণ করা হয়?', 'a' => 'সকল আয়, ব্যয়, সদস্যদের অবদান এবং অন্যান্য
                আর্থিক কার্যক্রম যথাযথভাবে নথিভুক্ত ও পর্যালোচনা করার ব্যবস্থা রাখা হয়।'],
                ['q' => 'সদস্যরা কীভাবে সংগঠনের কার্যক্রমে অংশগ্রহণ করেন?', 'a' => 'সদস্যরা নিয়মিত সঞ্চয়, সভায়
                অংশগ্রহণ, মতামত প্রদান এবং সংগঠনের বিভিন্ন পরিকল্পনা ও সিদ্ধান্ত গ্রহণের প্রক্রিয়ায় সক্রিয়ভাবে অংশ
                নিতে পারেন।'],
                ];
                @endphp

                @foreach ($faqLeft as $faq)
                <details
                    class="faq-item group reveal overflow-hidden rounded-md border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:border-teal-200 hover:shadow-md">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-5 px-5 py-4 sm:px-6 sm:py-5 [&::-webkit-details-marker]:hidden">
                        <span class="text-[13px] font-semibold leading-6 text-slate-800 sm:text-[14px]">{{ $faq['q']
                            }}</span>
                        <span
                            class="faq-plus flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500 transition-all duration-300 group-hover:border-teal-200 group-hover:bg-teal-50 group-hover:text-teal-600">
                            <svg class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </span>
                    </summary>
                    <div class="px-5 pb-5 sm:px-6">
                        <div class="mb-4 h-px bg-slate-100"></div>
                        <p class="text-[12px] leading-6 text-slate-500 sm:text-[13px]">{{ $faq['a'] }}</p>
                    </div>
                </details>
                @endforeach

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-4">

                @php
                $faqRight = [
                ['q' => 'সংগঠনের বিনিয়োগের সিদ্ধান্ত কীভাবে নেওয়া হয়?', 'a' => 'সম্ভাব্য ঝুঁকি, লাভের সম্ভাবনা,
                সংগঠনের নীতিমালা এবং সদস্যদের সম্মিলিত মতামত বিবেচনা করে বিনিয়োগের সিদ্ধান্ত নেওয়া হয়।'],
                ['q' => 'সদস্যদের অর্থের নিরাপত্তা কীভাবে নিশ্চিত করা হয়?', 'a' => 'সকল আর্থিক লেনদেনের হিসাব সংরক্ষণ,
                অনুমোদনভিত্তিক কার্যক্রম এবং নিয়মিত হিসাব পর্যালোচনার মাধ্যমে অর্থ ব্যবস্থাপনায় স্বচ্ছতা ও জবাবদিহিতা
                বজায় রাখার চেষ্টা করা হয়।'],
                ['q' => 'সদস্যদের জন্য কি নিয়মিত আপডেট দেওয়া হয়?', 'a' => 'সংগঠনের গুরুত্বপূর্ণ কার্যক্রম, আর্থিক
                বিষয়, সিদ্ধান্ত এবং ভবিষ্যৎ পরিকল্পনা সম্পর্কে সদস্যদের নিয়মিতভাবে অবহিত করার ব্যবস্থা রাখা হয়।'],
                ['q' => 'ভবিষ্যতে কী ধরনের উদ্যোগ নেওয়া হতে পারে?', 'a' => 'সংগঠনের তহবিল ও সদস্যদের সম্মিলিত
                সিদ্ধান্তের ওপর ভিত্তি করে বিনিয়োগ, ব্যবসা, জমি বা অন্যান্য উৎপাদনশীল সম্পদ তৈরির উদ্যোগ নেওয়া যেতে
                পারে।'],
                ];
                @endphp

                @foreach ($faqRight as $faq)
                <details
                    class="faq-item group reveal overflow-hidden rounded-md border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:border-teal-200 hover:shadow-md">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-5 px-5 py-4 sm:px-6 sm:py-5 [&::-webkit-details-marker]:hidden">
                        <span class="text-[13px] font-semibold leading-6 text-slate-800 sm:text-[14px]">{{ $faq['q']
                            }}</span>
                        <span
                            class="faq-plus flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500 transition-all duration-300 group-hover:border-teal-200 group-hover:bg-teal-50 group-hover:text-teal-600">
                            <svg class="h-4 w-4 transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                            </svg>
                        </span>
                    </summary>
                    <div class="px-5 pb-5 sm:px-6">
                        <div class="mb-4 h-px bg-slate-100"></div>
                        <p class="text-[12px] leading-6 text-slate-500 sm:text-[13px]">{{ $faq['a'] }}</p>
                    </div>
                </details>
                @endforeach

            </div>


        </div>

        <div class="mt-7 flex justify-center">
            <a href="{{ $lpField('faq', 'button_url', route('faq')) }}"
                class="reveal inline-flex items-center gap-2 rounded-md bg-teal-700 px-5 py-3 text-[13px] font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:-translate-y-0.5 hover:bg-teal-800">
                {{ $lpField('faq', 'button_text', 'সব প্রশ্নোত্তর দেখুন') }}
                <i data-lucide="arrow-right" class="h-4 w-4"></i>
            </a>
        </div>
    </div>
</section>

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect(array_merge($faqLeft, $faqRight))->map(fn ($faq) => [
        '@type' => 'Question',
        'name' => $faq['q'],
        'acceptedAnswer' => [
            '@type' => 'Answer',
            'text' => $faq['a'],
        ],
    ])->values()->all(),
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush
@endif

{{-- =========================================================
CTA
========================================================== --}}
@if($lpVisible('cta'))
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div
            class="relative overflow-hidden rounded-md bg-teal-700 px-6 py-12 text-center text-white shadow-card sm:px-12">
            <div class="absolute -left-20 -top-20 h-52 w-52 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-20 h-64 w-64 rounded-full bg-black/10 blur-3xl"></div>

            <div class="relative">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-white/10">
                    <i data-lucide="sparkles" class="h-5 w-5"></i>
                </div>

                <h2 class="mt-5 text-[2rem] font-bold sm:text-[2.45rem]">{{ $lpField('cta', 'title', 'আমাদের স্বপ্নের অংশ হোন') }}</h2>

                <p class="mx-auto mt-3 max-w-xl text-[13px] leading-6 text-teal-50">
                    {{ $lpField('cta', 'content', 'একটি শক্তিশালী কমিউনিটি গড়ে তুলতে ঐক্য, আস্থা ও
                    দীর্ঘমেয়াদি পরিকল্পনার বিকল্প নেই।') }}
                </p>

                <a href="{{ $lpField('cta', 'button_url', route('contact')) }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-md bg-white px-5 py-3 text-[13px] font-bold text-teal-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    {{ $lpField('cta', 'button_text', 'যোগাযোগ করুন') }}
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endif

{{-- =========================================================
CONTACT
========================================================== --}}
@if($lpVisible('contact'))
<section id="contact" class="bg-white sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">{{ $lpField('contact', 'subtitle', 'Contact') }}</span>
                <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">{{ $lpField('contact', 'title', 'যোগাযোগ করুন') }}</h2>
                <p class="max-w-lg text-[14px] leading-7 text-slate-500">
                    {{ $organizationName }} {{ $lpField('contact', 'content', 'সম্পর্কে জানতে বা কোনো প্রশ্ন
                    থাকলে আমাদের সাথে যোগাযোগ করুন।') }}
                </p>

                <div class="mt-8 space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="mail" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Email</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationEmail }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="phone" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Phone</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationPhone }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="map-pin" class="h-4.5 w-4.5"></i>
                        </div>
                        <div>
                            <p class="text-[12px] text-slate-500">Location</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $organizationAddress }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <form id="contactForm" novalidate class="rounded-md border border-slate-200 bg-white p-5 shadow-soft sm:p-6">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="contactName" class="mb-1.5 block text-[13px] font-semibold text-slate-700">আপনার নাম <span class="text-red-500">*</span></label>
                            <input type="text" id="contactName" name="name" required placeholder="আপনার নাম লিখুন"
                                class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                            <p id="contactNameError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                        </div>

                        <div>
                            <label for="contactEmail" class="mb-1.5 block text-[13px] font-semibold text-slate-700">ই-মেইল <span class="text-red-500">*</span></label>
                            <input type="email" id="contactEmail" name="email" required placeholder="আপনার ই-মেইল"
                                class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                            <p id="contactEmailError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="contactSubject" class="mb-1.5 block text-[13px] font-semibold text-slate-700">বিষয় <span class="text-red-500">*</span></label>
                        <input type="text" id="contactSubject" name="subject" required placeholder="কী বিষয়ে যোগাযোগ করতে চান?"
                            class="w-full rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10">
                        <p id="contactSubjectError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                    </div>

                    <div class="mt-4">
                        <label for="contactMessage" class="mb-1.5 block text-[13px] font-semibold text-slate-700">বার্তা <span class="text-red-500">*</span></label>
                        <textarea id="contactMessage" name="message" rows="5" required placeholder="আপনার বার্তা লিখুন..."
                            class="w-full resize-none rounded-md border border-slate-200 px-3.5 py-2.5 text-sm outline-none transition placeholder:text-slate-500 focus:border-teal-500 focus:bg-white focus:ring-2 focus:ring-teal-500/10"></textarea>
                        <p id="contactMessageError" class="mt-1 hidden text-[12px] font-medium text-rose-600"></p>
                    </div>

                    <button type="submit" id="contactSubmitBtn"
                        class="mt-4 flex w-full cursor-pointer items-center justify-center gap-2 rounded-md bg-teal-700 py-3 text-sm font-semibold text-white shadow-lg shadow-teal-700/20 transition hover:bg-teal-800 disabled:cursor-not-allowed disabled:opacity-70">
                        <span id="contactSubmitBtnText">বার্তা পাঠান</span>
                        <i data-lucide="send" id="contactSubmitBtnIcon" class="h-4 w-4"></i>
                        <i data-lucide="loader-2" id="contactSubmitSpinner" class="hidden h-4 w-4 animate-spin"></i>
                    </button>

                    <p id="formMessage" class="mt-3 hidden text-center text-[13px] font-medium text-emerald-600">
                        আপনার বার্তা সফলভাবে পাঠানো হয়েছে।
                    </p>

                    <p id="formError" class="mt-3 hidden text-center text-[13px] font-medium text-rose-600">
                        দুঃখিত, কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।
                    </p>

                </form>
            </div>

        </div>
    </div>
</section>
@endif

@include('landing.partials.footer')
@endsection

@push('scripts')
@include('landing.partials.scripts-base')
<script>
    /* Counter Animation */
    const counters = document.querySelectorAll('.counter');
    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;

            const counter = entry.target;
            const target = Number(counter.dataset.target);
            let current = 0;
            const duration = 1300;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const progress = Math.min((currentTime - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                current = Math.floor(target * eased);
                counter.textContent = current + '+';

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target + '+';
                }
            }

            requestAnimationFrame(updateCounter);
            observer.unobserve(counter);
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => counterObserver.observe(counter));

    /* Contact Form */
    const contactForm = document.getElementById('contactForm');
    const formMessage = document.getElementById('formMessage');
    const formError = document.getElementById('formError');
    const submitBtn = document.getElementById('contactSubmitBtn');
    const submitBtnText = document.getElementById('contactSubmitBtnText');
    const submitBtnIcon = document.getElementById('contactSubmitBtnIcon');
    const submitBtnSpinner = document.getElementById('contactSubmitSpinner');

    const contactFields = [
        {
            input: document.getElementById('contactName'),
            error: document.getElementById('contactNameError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার নাম লিখুন।';
                if (value.length > 150) return 'নাম সর্বোচ্চ ১৫০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactEmail'),
            error: document.getElementById('contactEmailError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার ই-মেইল লিখুন।';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) return 'সঠিক ই-মেইল ঠিকানা লিখুন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactSubject'),
            error: document.getElementById('contactSubjectError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে বিষয় লিখুন।';
                if (value.length > 200) return 'বিষয় সর্বোচ্চ ২০০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
        {
            input: document.getElementById('contactMessage'),
            error: document.getElementById('contactMessageError'),
            validate: (value) => {
                if (!value) return 'অনুগ্রহ করে আপনার বার্তা লিখুন।';
                if (value.length > 5000) return 'বার্তা সর্বোচ্চ ৫০০০ অক্ষরের মধ্যে দিন।';
                return '';
            },
        },
    ];

    function setFieldError(field, message) {
        if (message) {
            field.input.classList.add('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
            field.input.classList.remove('border-slate-200', 'focus:border-teal-500', 'focus:ring-teal-500/10');
            field.error.textContent = message;
            field.error.classList.remove('hidden');
        } else {
            field.input.classList.remove('border-rose-400', 'focus:border-rose-500', 'focus:ring-rose-100');
            field.input.classList.add('border-slate-200', 'focus:border-teal-500', 'focus:ring-teal-500/10');
            field.error.textContent = '';
            field.error.classList.add('hidden');
        }
    }

    function validateContactForm() {
        let firstInvalid = null;

        contactFields.forEach((field) => {
            const message = field.validate(field.input.value.trim());
            setFieldError(field, message);

            if (message && !firstInvalid) {
                firstInvalid = field.input;
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    contactFields.forEach((field) => {
        field.input.addEventListener('input', () => {
            if (!field.error.classList.contains('hidden')) {
                setFieldError(field, field.validate(field.input.value.trim()));
            }
        });
    });

    contactForm.addEventListener('submit', async function (event) {
        event.preventDefault();

        formMessage.classList.add('hidden');
        formError.classList.add('hidden');

        if (!validateContactForm()) {
            return;
        }

        submitBtn.disabled = true;
        submitBtnText.textContent = 'পাঠানো হচ্ছে...';
        submitBtnIcon.classList.add('hidden');
        submitBtnSpinner.classList.remove('hidden');

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content');

        const payload = {
            name: contactForm.name.value,
            email: contactForm.email.value,
            subject: contactForm.subject.value,
            message: contactForm.message.value,
        };

        try {
            const response = await fetch('/api/contact', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (data.errors) {
                    const fieldMap = { name: 0, email: 1, subject: 2, message: 3 };

                    Object.entries(data.errors).forEach(([key, messages]) => {
                        const field = contactFields[fieldMap[key]];
                        if (field) setFieldError(field, messages[0]);
                    });
                }

                formError.textContent = data.message || 'দুঃখিত, কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।';
                formError.classList.remove('hidden');
            } else {
                formMessage.classList.remove('hidden');
                contactForm.reset();
                contactFields.forEach((field) => setFieldError(field, ''));

                setTimeout(() => {
                    formMessage.classList.add('hidden');
                }, 4000);
            }
        } catch (error) {
            formError.textContent = 'নেটওয়ার্ক সমস্যা হয়েছে। আবার চেষ্টা করুন।';
            formError.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtnText.textContent = 'বার্তা পাঠান';
            submitBtnIcon.classList.remove('hidden');
            submitBtnSpinner.classList.add('hidden');
        }
    });
</script>
@endpush