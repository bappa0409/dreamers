@extends('layouts.public')

@section('title', 'আমাদের কার্যক্রম | ' . setting('organization_name', 'Dreamers Association'))
@section('description', setting('organization_name', 'Dreamers Association') . '-এর বিনিয়োগ, জমি ও সম্পদ, ব্যবসায়িক উদ্যোগ, পণ্য ক্রয়-বিক্রয় ও সদস্য কল্যাণসহ প্রতিটি কার্যক্রম সম্পর্কে বিস্তারিত জানুন।')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');
@endphp

@include('landing.partials.navbar', ['active' => 'activities'])

{{-- PAGE HEADER --}}
@include('landing.partials.page-header', [
'eyebrow' => 'Our Activities',
'title' => 'আমাদের প্রধান কার্যক্রম',
'description' => 'সংগঠনের লক্ষ্য ও সদস্যদের দীর্ঘমেয়াদি কল্যাণকে সামনে রেখে বিভিন্ন সম্ভাবনাময় উদ্যোগ নিয়ে কাজ করা হয়।',
])

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'আমাদের প্রধান কার্যক্রম', 'item' => route('activities')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

{{-- =========================================================
ACTIVITIES — DETAILED
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="grid gap-6 lg:grid-cols-2">

    <div class="reveal rounded-md border border-slate-200 bg-white p-7 shadow-soft">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-teal-700 text-white">
                <i data-lucide="trending-up" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-[18px] font-bold text-slate-900">বিনিয়োগ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সম্ভাবনাময় ও যাচাইযোগ্য ক্ষেত্রে সদস্যদের সম্মিলিত তহবিল
                    বিনিয়োগের সুযোগ তৈরি করা।
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div class="flex gap-3">
                <i data-lucide="search" class="mt-0.5 h-4 w-4 shrink-0 text-teal-700"></i>
                <p class="text-[13px] leading-5 text-slate-500">সম্ভাব্য খাত ও ঝুঁকি যাচাই-বাছাই করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="vote" class="mt-0.5 h-4 w-4 shrink-0 text-teal-700"></i>
                <p class="text-[13px] leading-5 text-slate-500">সদস্যদের মতামত নিয়ে সম্মিলিত সিদ্ধান্ত নেওয়া
                    হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="line-chart" class="mt-0.5 h-4 w-4 shrink-0 text-teal-700"></i>
                <p class="text-[13px] leading-5 text-slate-500">বিনিয়োগের অগ্রগতি নিয়মিত পর্যালোচনা করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="rotate-cw" class="mt-0.5 h-4 w-4 shrink-0 text-teal-700"></i>
                <p class="text-[13px] leading-5 text-slate-500">রিটার্ন পুনরায় সংগঠনের তহবিলে যুক্ত করে
                    পুনর্বিনিয়োগের সুযোগ রাখা হয়।</p>
            </div>
        </div>
    </div>

    <div class="reveal rounded-md border border-slate-200 bg-white p-7 shadow-soft">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-amber-500 text-white">
                <i data-lucide="landmark" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-[18px] font-bold text-slate-900">জমি ও সম্পদ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    দীর্ঘমেয়াদি সম্পদ তৈরির উদ্দেশ্যে উপযুক্ত জমি বা সম্পদ
                    ক্রয়ের পরিকল্পনা করা।
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div class="flex gap-3">
                <i data-lucide="map" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">উপযুক্ত অবস্থান ও মূল্য যাচাই করে জমি নির্বাচন
                    করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="file-check-2" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">দলিলপত্র ও আইনগত দিক যাচাই-বাছাই করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="archive" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">সম্পদ সংগঠনের নামে নথিভুক্ত ও সংরক্ষিত রাখা হয়।
                </p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="calendar-clock" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">সম্পদের মূল্যায়ন নিয়মিত বিরতিতে পুনঃযাচাই করা
                    হয়।</p>
            </div>
        </div>
    </div>

    <div class="reveal rounded-md border border-slate-200 bg-white p-7 shadow-soft">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-sky-600 text-white">
                <i data-lucide="store" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-[18px] font-bold text-slate-900">ব্যবসায়িক উদ্যোগ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সম্ভাবনাময় পণ্য বা ব্যবসায়িক কার্যক্রমের মাধ্যমে
                    সংগঠনের তহবিলকে উৎপাদনশীল কাজে ব্যবহার করা।
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div class="flex gap-3">
                <i data-lucide="lightbulb" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">সম্ভাবনাময় ব্যবসায়িক ধারণা যাচাই করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="handshake" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">প্রয়োজনে নির্ভরযোগ্য অংশীদারদের সাথে কাজ করা
                    হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="receipt" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">লাভ-ক্ষতির হিসাব নিয়মিত নথিভুক্ত করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="repeat" class="mt-0.5 h-4 w-4 shrink-0 text-sky-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">সফল উদ্যোগের ক্ষেত্র ভবিষ্যতে সম্প্রসারণের
                    সুযোগ রাখা হয়।</p>
            </div>
        </div>
    </div>

    <div class="reveal rounded-md border border-slate-200 bg-white p-7 shadow-soft">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-rose-600 text-white">
                <i data-lucide="shopping-bag" class="h-5 w-5"></i>
            </div>
            <div>
                <h3 class="text-[18px] font-bold text-slate-900">পণ্য ক্রয়-বিক্রয়</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    দৈনন্দিন প্রয়োজনীয় বা মৌসুমি পণ্য সম্মিলিতভাবে ক্রয়
                    করে সংগঠনের মাধ্যমে ন্যায্য মূল্যে বিক্রির উদ্যোগ নেওয়া
                    হয়।
                </p>
            </div>
        </div>

        <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
            <div class="flex gap-3">
                <i data-lucide="package-search" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">চাহিদাসম্পন্ন পণ্য ও সরবরাহকারী যাচাই করা হয়।
                </p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="truck" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">সংগ্রহ, সংরক্ষণ ও বিতরণ প্রক্রিয়া পরিকল্পিতভাবে
                    সম্পন্ন করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="tags" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">যৌক্তিক মুনাফায় সদস্য ও সাধারণ ক্রেতাদের কাছে
                    বিক্রি করা হয়।</p>
            </div>
            <div class="flex gap-3">
                <i data-lucide="receipt" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"></i>
                <p class="text-[13px] leading-5 text-slate-500">প্রতিটি লেনদেনের হিসাব পৃথকভাবে সংরক্ষণ করা
                    হয়।</p>
            </div>
        </div>
    </div>

</div>

        {{-- Welfare & member support --}}
        <div class="reveal mt-6 rounded-md border border-slate-200 bg-white p-7 shadow-soft">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md bg-teal-700 text-white">
                    <i data-lucide="hand-heart" class="h-6 w-6"></i>
                </div>
                <div>
                    <h3 class="text-[18px] font-bold text-slate-900">সদস্য কল্যাণ</h3>
                    <p class="mt-1 text-sm leading-6 text-slate-500">
                        সংগঠনের কার্যক্রম শুধু আর্থিক প্রবৃদ্ধির মধ্যেই
                        সীমাবদ্ধ নয়; সদস্যদের প্রয়োজনে পাশে দাঁড়ানোর জন্য
                        একটি পৃথক কল্যাণ তহবিল পরিচালনা করা হয়।
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-md bg-slate-50 p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-teal-700 shadow-sm">
                        <i data-lucide="stethoscope" class="h-4 w-4"></i>
                    </div>
                    <h4 class="mt-3 text-[13px] font-bold text-slate-900">চিকিৎসা সহায়তা</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">অসুস্থতাজনিত জরুরি প্রয়োজনে নীতিমালা
                        অনুযায়ী সহায়তা প্রদান।</p>
                </div>
                <div class="rounded-md bg-slate-50 p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-teal-700 shadow-sm">
                        <i data-lucide="graduation-cap" class="h-4 w-4"></i>
                    </div>
                    <h4 class="mt-3 text-[13px] font-bold text-slate-900">শিক্ষা সহায়তা</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">সদস্য পরিবারের শিক্ষা কার্যক্রমে সহযোগিতার
                        উদ্যোগ।</p>
                </div>
                <div class="rounded-md bg-slate-50 p-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-white text-teal-700 shadow-sm">
                        <i data-lucide="siren" class="h-4 w-4"></i>
                    </div>
                    <h4 class="mt-3 text-[13px] font-bold text-slate-900">জরুরি সহায়তা</h4>
                    <p class="mt-1 text-[12px] leading-5 text-slate-500">অপ্রত্যাশিত সংকটে দ্রুত সিদ্ধান্তে সহায়তার
                        ব্যবস্থা।</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
FUND ALLOCATION OVERVIEW
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Fund Allocation</span>
                <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.4rem]">তহবিল যেভাবে বণ্টিত হয়</h2>
                <p class="mt-4 text-[14px] leading-7 text-slate-500">
                    সংগঠনের সংগৃহীত তহবিল একক কোনো খাতে সীমাবদ্ধ না রেখে
                    ঝুঁকি বিবেচনা করে একাধিক কার্যক্রমে ভাগ করে বরাদ্দ করার
                    নীতি অনুসরণ করা হয়, যাতে দীর্ঘমেয়াদে স্থিতিশীলতা বজায়
                    থাকে।
                </p>
                <p class="mt-3 text-[12px] leading-5 text-slate-400">
                    * নিচের চিত্রটি একটি সাধারণ ধারণা দেওয়ার জন্য; প্রকৃত
                    বরাদ্দ সময়ে সময়ে সদস্যদের সিদ্ধান্ত অনুযায়ী পরিবর্তিত
                    হতে পারে।
                </p>
            </div>

            <div class="reveal space-y-4 rounded-md border border-slate-200 bg-slate-50 p-6">
                <div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">বিনিয়োগ</span>
                        <span class="text-slate-500">৩৫%</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-teal-600" style="width:35%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">জমি ও সম্পদ</span>
                        <span class="text-slate-500">৩০%</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-amber-500" style="width:30%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">ব্যবসায়িক উদ্যোগ ও পণ্য</span>
                        <span class="text-slate-500">২০%</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-sky-500" style="width:20%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">সদস্য কল্যাণ তহবিল</span>
                        <span class="text-slate-500">১৫%</span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-rose-500" style="width:15%"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
HOW IT WORKS
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">How It Works</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">আমাদের কার্যক্রম কীভাবে চলে?</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                পরিকল্পনা থেকে বাস্তবায়ন—প্রতিটি ধাপ একটি নির্দিষ্ট প্রক্রিয়ার
                মাধ্যমে পরিচালিত হয়।
            </p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-4">

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">01</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="wallet" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">নিয়মিত সঞ্চয়</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সদস্যরা নির্ধারিত নিয়ম অনুযায়ী নিয়মিত অর্থ প্রদান করেন।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">02</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="clipboard-list" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">পরিকল্পনা ও মূল্যায়ন</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সম্ভাব্য উদ্যোগ ও বিনিয়োগের সুযোগ যাচাই করে সিদ্ধান্ত নেওয়া হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">03</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="briefcase-business" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">বাস্তবায়ন</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    অনুমোদিত পরিকল্পনা অনুযায়ী ব্যবসা, বিনিয়োগ বা সম্পদ ক্রয়ের
                    উদ্যোগ নেওয়া হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-bold text-slate-200">04</span>
                    <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                        <i data-lucide="file-check-2" class="h-4.5 w-4.5"></i>
                    </div>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">হিসাব ও প্রতিবেদন</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সকল আর্থিক কার্যক্রম নথিভুক্ত ও পর্যালোচনার মাধ্যমে পরিচালিত হয়।
                </p>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
FUTURE PLANS
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">What's Next</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">ভবিষ্যৎ পরিকল্পনা</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                বর্তমান কার্যক্রমের ভিত্তিতে ভবিষ্যতে যেসব সম্ভাবনাময় উদ্যোগ
                নিয়ে ভাবা হচ্ছে।
            </p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

            <div class="reveal rounded-md border border-slate-200 bg-slate-50 p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                    <i data-lucide="building-2" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 text-[14px] font-bold text-slate-900">স্থায়ী স্থাপনা নির্মাণ</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-500">
                    ক্রয়কৃত জমিতে দীর্ঘমেয়াদি ব্যবহারযোগ্য স্থাপনা তৈরির
                    পরিকল্পনা।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-slate-50 p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i data-lucide="sprout" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 text-[14px] font-bold text-slate-900">কৃষি-ভিত্তিক উদ্যোগ</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-500">
                    উপযুক্ত জমিতে কৃষিভিত্তিক আয়ের সম্ভাবনা যাচাই করা।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-slate-50 p-6">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i data-lucide="graduation-cap" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-4 text-[14px] font-bold text-slate-900">শিক্ষাবৃত্তি কর্মসূচি</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-500">
                    সদস্য পরিবারের মেধাবী শিক্ষার্থীদের জন্য বৃত্তি চালুর
                    পরিকল্পনা।
                </p>
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

                <h2 class="mt-5 text-[2rem] font-bold sm:text-[2.45rem]">হিসাবে স্বচ্ছতা দেখুন</h2>

                <p class="mx-auto mt-3 max-w-xl text-[13px] leading-6 text-teal-50">
                    প্রতিটি কার্যক্রমের আর্থিক হিসাব কীভাবে সংরক্ষণ ও পর্যালোচনা
                    করা হয়, তা জানতে স্বচ্ছতা পাতাটি দেখুন।
                </p>

                <a href="{{ route('transparency') }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-md bg-white px-5 py-3 text-[13px] font-bold text-teal-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    স্বচ্ছতা সম্পর্কে জানুন
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