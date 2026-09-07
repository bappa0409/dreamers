@extends('layouts.public')

@section('title', 'সাধারণ জিজ্ঞাসা (FAQ) | ' . setting('organization_name', 'Dreamers Association'))
@section('description', 'Dreamers Association সম্পর্কে সচরাচর জিজ্ঞাসিত প্রশ্ন ও তার উত্তর — সাধারণ তথ্য, সদস্যপদ,
আর্থিক বিষয় ও কার্যক্রম অনুযায়ী সাজানো।')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');

$faqGroups = [
[
'key' => 'general',
'title' => 'সাধারণ তথ্য',
'icon' => 'info',
'items' => [
['q' => $organizationName.' কী?', 'a' => $organizationName.' একটি সম্মিলিত উদ্যোগ, যেখানে সদস্যদের সঞ্চয় ও
পরিকল্পিত কার্যক্রমের মাধ্যমে দীর্ঘমেয়াদি আর্থিক সম্ভাবনা তৈরির চেষ্টা করা হয়।'],
['q' => 'সংগঠনের প্রধান লক্ষ্য কী?', 'a' => 'সদস্যদের সম্মিলিত সঞ্চয় ও পরিকল্পিত বিনিয়োগের মাধ্যমে দীর্ঘমেয়াদি
আর্থিক স্থিতিশীলতা ও সম্পদ তৈরি করা।'],
['q' => 'সংগঠন কীভাবে পরিচালিত হয়?', 'a' => 'সাধারণ সদস্য পরিষদ, পরিচালনা কমিটি এবং নিয়মিত নিরীক্ষা ও পর্যালোচনার
মাধ্যমে সংগঠনের কার্যক্রম পরিচালিত হয়।'],
['q' => 'সংগঠনের নীতিমালা পরিবর্তন করা যায় কি?', 'a' => 'হ্যাঁ, সাধারণ সদস্য পরিষদের সংখ্যাগরিষ্ঠ সিদ্ধান্তের মাধ্যমে
নীতিমালা প্রয়োজনে সংশোধন করা যায়।'],
],
],
[
'key' => 'membership',
'title' => 'সদস্যপদ',
'icon' => 'users',
'items' => [
['q' => 'নতুন সদস্য হিসেবে যোগ দেওয়ার প্রক্রিয়া কী?', 'a' => 'আগ্রহী ব্যক্তিরা নির্ধারিত আবেদন প্রক্রিয়া অনুসরণ
করে এবং পরিচালনা কমিটির অনুমোদনের মাধ্যমে সংগঠনের সদস্য হতে পারেন।'],
['q' => 'সদস্যরা কীভাবে সংগঠনের কার্যক্রমে অংশগ্রহণ করেন?', 'a' => 'সদস্যরা নিয়মিত সঞ্চয়, সভায় অংশগ্রহণ, মতামত
প্রদান এবং সংগঠনের বিভিন্ন পরিকল্পনা ও সিদ্ধান্ত গ্রহণের প্রক্রিয়ায় সক্রিয়ভাবে অংশ নিতে পারেন।'],
['q' => 'কোনো সদস্য সংগঠন থেকে বের হতে চাইলে কী হয়?', 'a' => 'সংগঠনের নীতিমালা অনুযায়ী নির্ধারিত প্রক্রিয়া অনুসরণ
করে সদস্যপদ ত্যাগ ও সংশ্লিষ্ট হিসাব নিষ্পত্তি করা হয়।'],
['q' => 'সদস্যপদ কি হস্তান্তরযোগ্য?', 'a' => 'সাধারণত সদস্যপদ ব্যক্তিগত এবং সরাসরি হস্তান্তরযোগ্য নয়; বিশেষ
পরিস্থিতিতে পরিচালনা কমিটির অনুমোদন সাপেক্ষে প্রক্রিয়া অনুসরণ করা হয়।'],
],
],
[
'key' => 'financial',
'title' => 'আর্থিক ও বিনিয়োগ',
'icon' => 'banknote',
'items' => [
['q' => 'সংগঠনের অর্থ কীভাবে ব্যবহার করা হয়?', 'a' => 'সংগঠনের নীতিমালা ও সম্মিলিত সিদ্ধান্ত অনুযায়ী বিনিয়োগ,
ব্যবসায়িক কার্যক্রম এবং দীর্ঘমেয়াদি সম্পদ তৈরির মতো উদ্যোগে অর্থ ব্যবহার করার পরিকল্পনা করা হয়।'],
['q' => 'সংগঠনের বিনিয়োগের সিদ্ধান্ত কীভাবে নেওয়া হয়?', 'a' => 'সম্ভাব্য ঝুঁকি, লাভের সম্ভাবনা, সংগঠনের নীতিমালা
এবং সদস্যদের সম্মিলিত মতামত বিবেচনা করে বিনিয়োগের সিদ্ধান্ত নেওয়া হয়।'],
['q' => 'সদস্যদের অর্থের নিরাপত্তা কীভাবে নিশ্চিত করা হয়?', 'a' => 'সকল আর্থিক লেনদেনের হিসাব সংরক্ষণ,
অনুমোদনভিত্তিক কার্যক্রম এবং নিয়মিত হিসাব পর্যালোচনার মাধ্যমে অর্থ ব্যবস্থাপনায় স্বচ্ছতা ও জবাবদিহিতা বজায়
রাখার চেষ্টা করা হয়।'],
['q' => 'আর্থিক হিসাব কীভাবে সংরক্ষণ করা হয়?', 'a' => 'সকল আয়, ব্যয়, সদস্যদের অবদান এবং অন্যান্য আর্থিক কার্যক্রম
যথাযথভাবে নথিভুক্ত ও পর্যালোচনা করার ব্যবস্থা রাখা হয়।'],
['q' => 'বিনিয়োগে লোকসান হলে কী হয়?', 'a' => 'ঝুঁকি যাচাই করেই সিদ্ধান্ত নেওয়া হয়, তবে কোনো উদ্যোগে লোকসান হলে তা
সদস্যদের কাছে স্বচ্ছভাবে জানানো হয় এবং পরবর্তী সিদ্ধান্তে সেই অভিজ্ঞতা বিবেচনায় নেওয়া হয়।'],
],
],
[
'key' => 'activities',
'title' => 'কার্যক্রম ও স্বচ্ছতা',
'icon' => 'layout-grid',
'items' => [
['q' => 'সদস্যদের জন্য কি নিয়মিত আপডেট দেওয়া হয়?', 'a' => 'সংগঠনের গুরুত্বপূর্ণ কার্যক্রম, আর্থিক বিষয়, সিদ্ধান্ত
এবং ভবিষ্যৎ পরিকল্পনা সম্পর্কে সদস্যদের নিয়মিতভাবে অবহিত করার ব্যবস্থা রাখা হয়।'],
['q' => 'ভবিষ্যতে কী ধরনের উদ্যোগ নেওয়া হতে পারে?', 'a' => 'সংগঠনের তহবিল ও সদস্যদের সম্মিলিত সিদ্ধান্তের ওপর
ভিত্তি করে বিনিয়োগ, ব্যবসা, জমি বা অন্যান্য উৎপাদনশীল সম্পদ তৈরির উদ্যোগ নেওয়া যেতে পারে।'],
['q' => 'সদস্য কল্যাণ তহবিল থেকে সহায়তা কীভাবে পাওয়া যায়?', 'a' => 'নির্ধারিত আবেদন প্রক্রিয়ায় প্রয়োজন উল্লেখ করে
আবেদন করলে পরিচালনা কমিটি নীতিমালা অনুযায়ী তা পর্যালোচনা করে সিদ্ধান্ত নেয়।'],
],
],
];
@endphp

@include('landing.partials.navbar', ['active' => 'faq'])

{{-- PAGE HEADER --}}
@include('landing.partials.page-header', [
'eyebrow' => 'Frequently Asked Questions',
'title' => 'সাধারণ কিছু প্রশ্ন',
'description' => $organizationName.' সম্পর্কে প্রাথমিক কিছু প্রশ্নের সহজ ও সংক্ষিপ্ত উত্তর, বিষয়ভিত্তিকভাবে
সাজানো।',
])

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'সাধারণ কিছু প্রশ্ন', 'item' => route('faq')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => collect($faqGroups)->flatMap(fn ($group) => $group['items'])->map(fn ($faq) => [
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

{{-- =========================================================
CATEGORY QUICK NAV
========================================================== --}}
<section class="bg-slate-50/70 pt-4">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="reveal flex flex-wrap justify-center gap-2">
            @foreach ($faqGroups as $group)
            <a href="#{{ $group['key'] }}"
                class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-[12px] font-semibold text-slate-600 transition hover:border-teal-200 hover:text-teal-700">
                <i data-lucide="{{ $group['icon'] }}" class="h-3.5 w-3.5"></i>
                {{ $group['title'] }}
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
FAQ — BY CATEGORY
========================================================== --}}
<section class="bg-slate-50/70 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">

            @foreach ($faqGroups as $group)

            <div id="{{ $group['key'] }}" class="scroll-mt-24">

                {{-- CATEGORY HEADER --}}
                <div class="reveal mb-5 flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-teal-700 text-white">
                        <i data-lucide="{{ $group['icon'] }}" class="h-4 w-4"></i>
                    </div>

                    <h2 class="text-[18px] font-bold text-slate-900 sm:text-[20px]">
                        {{ $group['title'] }}
                    </h2>
                </div>

                {{-- FAQ ITEMS --}}
                <div class="space-y-4">

                    @foreach ($group['items'] as $faq)

                    <details
                        class="faq-item group reveal overflow-hidden rounded-md border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:border-teal-200 hover:shadow-md">

                        <summary
                            class="flex cursor-pointer list-none items-center justify-between gap-5 px-5 py-4 sm:px-6 sm:py-5 [&::-webkit-details-marker]:hidden">

                            <span class="text-[13px] font-semibold leading-6 text-slate-800 sm:text-[14px]">
                                {{ $faq['q'] }}
                            </span>

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

                            <p class="text-[12px] leading-6 text-slate-500 sm:text-[13px]">
                                {{ $faq['a'] }}
                            </p>

                        </div>

                    </details>

                    @endforeach

                </div>

            </div>

            @endforeach

        </div>

    </div>
</section>

{{-- =========================================================
CONTACT CTA
========================================================== --}}
<section class="bg-slate-100 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div
            class="relative overflow-hidden rounded-md bg-teal-700 px-6 py-12 text-center text-white shadow-card sm:px-12">
            <div class="absolute -left-20 -top-20 h-52 w-52 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-24 -right-20 h-64 w-64 rounded-full bg-black/10 blur-3xl"></div>

            <div class="relative">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-white/10">
                    <i data-lucide="message-circle-question" class="h-5 w-5"></i>
                </div>

                <h2 class="mt-5 text-[2rem] font-bold sm:text-[2.45rem]">আপনার প্রশ্নের উত্তর পাননি?</h2>

                <p class="mx-auto mt-3 max-w-xl text-[13px] leading-6 text-teal-50">
                    সরাসরি আমাদের সাথে যোগাযোগ করুন, আমরা যত দ্রুত সম্ভব উত্তর
                    দেওয়ার চেষ্টা করব।
                </p>

                <a href="{{ route('contact') }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-md bg-white px-5 py-3 text-[13px] font-bold text-teal-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    যোগাযোগ করুন
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