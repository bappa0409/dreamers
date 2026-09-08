@extends('layouts.public')

@section('title', 'স্বচ্ছতা ও জবাবদিহিতা | ' . setting('organization_name', 'Dreamers Association'))
@section('description', setting('organization_name', 'Dreamers Association') . '-এর আর্থিক স্বচ্ছতা, হিসাবরক্ষণ, প্রতিবেদন প্রকাশ ও পর্যালোচনা প্রক্রিয়া সম্পর্কে বিস্তারিত জানুন।')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');
@endphp

@include('landing.partials.navbar', ['active' => 'transparency'])

{{-- PAGE HEADER --}}
@include('landing.partials.page-header', [
'eyebrow' => 'Transparency',
'title' => 'হিসাব থাকবে পরিষ্কার, সিদ্ধান্ত হবে স্বচ্ছ',
'description' => 'একটি সংগঠনের জন্য আর্থিক স্বচ্ছতা ও সঠিক হিসাবরক্ষণ অত্যন্ত গুরুত্বপূর্ণ। তাই প্রতিটি লেনদেন,
আয়-ব্যয় এবং বিনিয়োগের তথ্য যথাযথভাবে সংরক্ষণ ও পর্যালোচনা করার ব্যবস্থা রাখা হয়।',
])

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'হিসাব থাকবে পরিষ্কার, সিদ্ধান্ত হবে স্বচ্ছ', 'item' => route('transparency')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

{{-- =========================================================
COMMITMENTS + DASHBOARD PREVIEW
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-12 lg:grid-cols-2">

            <div class="reveal space-y-4">
                <div class="flex gap-3 rounded-md border border-slate-200 bg-white p-4">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="check" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-slate-900">আয় ও ব্যয়ের হিসাব</h4>
                        <p class="mt-0.5 text-[13px] leading-5 text-slate-500">
                            প্রতিটি আর্থিক লেনদেন নথিভুক্ত করার ব্যবস্থা।
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-md border border-slate-200 bg-white p-4">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="check" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-slate-900">নিয়মিত পর্যালোচনা</h4>
                        <p class="mt-0.5 text-[13px] leading-5 text-slate-500">
                            সংগঠনের আর্থিক কার্যক্রম নিয়মিত যাচাই ও পর্যালোচনা করা।
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-md border border-slate-200 bg-white p-4">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="check" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-slate-900">জবাবদিহিতা</h4>
                        <p class="mt-0.5 text-[13px] leading-5 text-slate-500">
                            সিদ্ধান্ত ও অর্থ ব্যবহারের ক্ষেত্রে দায়বদ্ধতা নিশ্চিত করা।
                        </p>
                    </div>
                </div>

                <div class="flex gap-3 rounded-md border border-slate-200 bg-white p-4">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="check" class="h-4 w-4"></i>
                    </div>
                    <div>
                        <h4 class="text-[13px] font-bold text-slate-900">সদস্যদের অংশগ্রহণ</h4>
                        <p class="mt-0.5 text-[13px] leading-5 text-slate-500">
                            গুরুত্বপূর্ণ আর্থিক সিদ্ধান্তে সদস্যদের মতামত নেওয়ার ব্যবস্থা।
                        </p>
                    </div>
                </div>
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
                                <p class="text-[9px] text-slate-400">{{ $organizationName }}</p>
                            </div>
                        </div>
                        <div class="rounded-md bg-emerald-50 px-2 py-1 text-[9px] font-semibold text-emerald-600">
                            Updated</div>
                    </div>

                    <div class="p-5">
                        <p class="text-[10px] text-slate-400">মোট সংগৃহীত তহবিল</p>

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

                        <div class="mt-6">
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-[13px] font-bold text-slate-800">সাম্প্রতিক কার্যক্রম</p>
                                <span class="text-[9px] text-slate-400">View all</span>
                            </div>

                            <div class="space-y-2">
                                <div class="dashboard-line flex items-center justify-between rounded-md px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                                            <i data-lucide="arrow-down-left" class="h-3.5 w-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-700">মাসিক সঞ্চয়</p>
                                            <p class="text-[8px] text-slate-400">Member Contribution</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] font-bold text-emerald-600">+৳ 50,000</p>
                                </div>

                                <div class="dashboard-line flex items-center justify-between rounded-md px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                                            <i data-lucide="arrow-up-right" class="h-3.5 w-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-700">বিনিয়োগ</p>
                                            <p class="text-[8px] text-slate-400">Investment</p>
                                        </div>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-700">-{{ setting('currency_symbol','৳') }}30,000</p>
                                </div>

                                <div class="dashboard-line flex items-center justify-between rounded-md px-3 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex h-7 w-7 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                                            <i data-lucide="file-text" class="h-3.5 w-3.5"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] font-semibold text-slate-700">হিসাব আপডেট</p>
                                            <p class="text-[8px] text-slate-400">Record Updated</p>
                                        </div>
                                    </div>
                                    <span class="text-[9px] font-semibold text-sky-600">Completed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
REPORT TYPES
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Reports</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">যেসব প্রতিবেদন প্রকাশ করা হয়</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                সময়ের ব্যবধানে ভিন্ন ভিন্ন পরিসরে হিসাব-বিবরণী সদস্যদের কাছে
                উপস্থাপন করা হয়।
            </p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                    <i data-lucide="calendar-days" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">মাসিক প্রতিবেদন</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    প্রতি মাসের আয়, ব্যয় ও সঞ্চয়ের সংক্ষিপ্ত সারাংশ সদস্যদের
                    কাছে জানানো হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i data-lucide="calendar-range" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">ত্রৈমাসিক পর্যালোচনা</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    প্রতি তিন মাস অন্তর বিনিয়োগ ও কার্যক্রমের অগ্রগতি
                    বিস্তারিতভাবে পর্যালোচনা করা হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i data-lucide="bar-chart-3" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">বার্ষিক প্রতিবেদন</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    বছরের সামগ্রিক আর্থিক চিত্র নিয়ে একটি পূর্ণাঙ্গ প্রতিবেদন
                    বার্ষিক সাধারণ সভায় উপস্থাপন করা হয়।
                </p>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
REPORTING CYCLE
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Reporting Cycle</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">হিসাব যেভাবে পর্যালোচনা করা হয়</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                প্রতিটি লেনদেন থেকে প্রতিবেদন পর্যন্ত—একটি ধারাবাহিক প্রক্রিয়া
                অনুসরণ করা হয়।
            </p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-4">

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">01</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="file-plus-2" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">এন্ট্রি ও নথিভুক্তকরণ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    প্রতিটি আয়-ব্যয় সংঘটিত হওয়ার সাথে সাথে হিসাবে নথিভুক্ত
                    করা হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">02</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="badge-check" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">অনুমোদন প্রক্রিয়া</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    নির্দিষ্ট অঙ্কের লেনদেন সংশ্লিষ্ট পর্যায়ের অনুমোদনের
                    মাধ্যমে সম্পন্ন হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">03</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="search-check" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">অভ্যন্তরীণ পর্যালোচনা</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    নির্দিষ্ট বিরতিতে হিসাবের সামঞ্জস্যতা ও নির্ভুলতা যাচাই
                    করা হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">04</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="send" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">প্রতিবেদন প্রকাশ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সদস্যদের কাছে নির্ধারিত সময় অন্তর হিসাব সারসংক্ষেপ
                    পৌঁছে দেওয়া হয়।
                </p>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
ACCESS TO STATEMENTS
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Access</span>
                <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.4rem]">সদস্যরা কীভাবে হিসাব দেখতে
                    পারেন</h2>
                <p class="mt-4 text-[14px] leading-7 text-slate-500">
                    স্বচ্ছতা নিশ্চিত করতে সদস্যদের জন্য নিজের ও সংগঠনের
                    সার্বিক হিসাব দেখার একাধিক উপায় রাখা হয়েছে।
                </p>
            </div>

            <div class="reveal grid gap-4 sm:grid-cols-2">
                <div class="rounded-md border border-slate-200 bg-slate-50 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="monitor" class="h-4.5 w-4.5"></i>
                    </div>
                    <h4 class="mt-4 text-[13px] font-bold text-slate-900">সদস্য ড্যাশবোর্ড</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">
                        লগইন করে নিজের সঞ্চয়, লেনদেন ও সংগঠনের সার্বিক হিসাব
                        সারাংশ দেখা যায়।
                    </p>
                </div>
                <div class="rounded-md border border-slate-200 bg-slate-50 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="file-text" class="h-4.5 w-4.5"></i>
                    </div>
                    <h4 class="mt-4 text-[13px] font-bold text-slate-900">লিখিত অনুরোধ</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">
                        প্রয়োজনে পরিচালনা কমিটির কাছে বিস্তারিত হিসাব-বিবরণী
                        চেয়ে আবেদন করা যায়।
                    </p>
                </div>
                <div class="rounded-md border border-slate-200 bg-slate-50 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="users" class="h-4.5 w-4.5"></i>
                    </div>
                    <h4 class="mt-4 text-[13px] font-bold text-slate-900">সাধারণ সভা</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">
                        বার্ষিক সাধারণ সভায় সরাসরি প্রশ্ন করে হিসাব সম্পর্কে
                        জানার সুযোগ থাকে।
                    </p>
                </div>
                <div class="rounded-md border border-slate-200 bg-slate-50 p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="bell" class="h-4.5 w-4.5"></i>
                    </div>
                    <h4 class="mt-4 text-[13px] font-bold text-slate-900">নোটিফিকেশন</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">
                        গুরুত্বপূর্ণ আর্থিক আপডেট এসএমএস বা নোটিশের মাধ্যমে
                        জানিয়ে দেওয়া হয়।
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
CTA
========================================================== --}}
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

                <h2 class="mt-5 text-[2rem] font-bold sm:text-[2.45rem]">আরও প্রশ্ন আছে?</h2>

                <p class="mx-auto mt-3 max-w-xl text-[13px] leading-6 text-teal-50">
                    স্বচ্ছতা ও হিসাব সংক্রান্ত সাধারণ কিছু প্রশ্নের উত্তর
                    প্রশ্নোত্তর পাতায় পাবেন।
                </p>

                <a href="{{ route('faq') }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-md bg-white px-5 py-3 text-[13px] font-bold text-teal-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    প্রশ্নোত্তর দেখুন
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>
        </div>
    </div>
</section>

@include('landing.partials.footer')
@endsection

@push('scripts')
@include('landing.partials.scripts-base')
@endpush