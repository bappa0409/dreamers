@extends('layouts.public')

@section('title', 'আমাদের সম্পর্কে | ' . setting('organization_name', 'Dreamers Association'))
@section('description', setting('organization_name', 'Dreamers Association') . '-এর লক্ষ্য, দৃষ্টিভঙ্গি, দর্শন, যাত্রা, পরিচালনা কাঠামো ও সদস্যপদ সম্পর্কে বিস্তারিত জানুন।')

@section('content')
@php
$organizationName = setting('organization_name', 'Dreamers Association');
@endphp

@include('landing.partials.navbar', ['active' => 'about'])

{{-- PAGE HEADER --}}
@include('landing.partials.page-header', [
'eyebrow' => 'About Us',
'title' => 'আমাদের সম্পর্কে',
'description' => $organizationName.' একটি ঐক্যবদ্ধ সংগঠন, যেখানে সদস্যদের সম্মিলিত সঞ্চয়, বিনিয়োগ ও
পরিকল্পনার মাধ্যমে দীর্ঘমেয়াদি আর্থিক উন্নয়নের সুযোগ তৈরি করা হয়।',
])

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'হোম', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'আমাদের সম্পর্কে', 'item' => route('about')],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

{{-- =========================================================
STORY / MISSION
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Our Story</span>

                <h2 class="mt-3 text-[2rem] font-bold leading-tight text-slate-900 sm:text-[2.55rem]">
                    আমাদের স্বপ্ন,
                    <span class="text-teal-700">আমাদের সম্মিলিত শক্তি।</span>
                </h2>

                <p class="mt-5 text-[14px] leading-7 text-slate-600">
                    {{ $organizationName }} এমন একটি সংগঠন যেখানে সদস্যদের
                    সম্মিলিত প্রচেষ্টা, নিয়মিত সঞ্চয় এবং পরিকল্পিত বিনিয়োগের
                    মাধ্যমে ভবিষ্যতের জন্য একটি শক্তিশালী ভিত্তি তৈরি করার
                    চেষ্টা করা হয়। একক প্রচেষ্টায় যা সম্ভব নয়, সম্মিলিত
                    প্রচেষ্টায় তা বাস্তবায়নযোগ্য হয়ে ওঠে—এই বিশ্বাস থেকেই
                    সংগঠনের যাত্রা শুরু।
                </p>

                <p class="mt-4 text-[14px] leading-7 text-slate-600">
                    আমাদের লক্ষ্য শুধু অর্থ সঞ্চয় করা নয়; বরং সেই অর্থকে
                    সঠিক পরিকল্পনার মাধ্যমে উৎপাদনশীল কাজে ব্যবহার করা এবং
                    সদস্যদের জন্য দীর্ঘমেয়াদি সম্ভাবনা তৈরি করা।
                </p>

                <p class="mt-4 text-[14px] leading-7 text-slate-600">
                    প্রতিটি সিদ্ধান্ত সদস্যদের মতামত ও সম্মিলিত আলোচনার
                    ভিত্তিতে নেওয়া হয়, যাতে সংগঠনের প্রতিটি উদ্যোগে সকলের
                    আস্থা ও মালিকানাবোধ বজায় থাকে। সময়ের সাথে সাথে সংগঠনের
                    কার্যক্রমের পরিধি বাড়ানো এবং নতুন সদস্যদের সম্পৃক্ত করার
                    মধ্য দিয়ে একটি টেকসই কমিউনিটি গড়ে তোলাই আমাদের দীর্ঘমেয়াদি
                    পরিকল্পনা।
                </p>

                <div class="mt-7 grid grid-cols-2 gap-3">
                    <div class="rounded-md border border-slate-200 bg-white p-4">
                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="target" class="h-4 w-4"></i>
                        </div>
                        <h3 class="mt-3 text-[14px] font-bold text-slate-900">আমাদের লক্ষ্য</h3>
                        <p class="mt-1 text-[13px] leading-5 text-slate-500">
                            পরিকল্পিত সঞ্চয় ও বিনিয়োগের মাধ্যমে দীর্ঘমেয়াদি
                            আর্থিক সম্ভাবনা তৈরি করা।
                        </p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white p-4">
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

                        <div class="space-y-5">
                            <div class="flex gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-white/10">
                                    <i data-lucide="handshake" class="h-4 w-4 text-teal-300"></i>
                                </div>
                                <div>
                                    <h4 class="text-[13px] font-semibold">পারস্পরিক সহযোগিতা</h4>
                                    <p class="mt-1 text-[13px] leading-5 text-slate-400">
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
                                    <p class="mt-1 text-[13px] leading-5 text-slate-400">
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
                                    <p class="mt-1 text-[13px] leading-5 text-slate-400">
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

{{-- =========================================================
OUR JOURNEY
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Our Journey</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">আমাদের পথচলা</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                একটি ছোট উদ্যোগ থেকে ধাপে ধাপে সংগঠিত হয়ে ওঠার গল্প।
            </p>
        </div>

        <div class="relative mt-14">
            <div class="absolute left-1/2 top-0 hidden h-full w-px -translate-x-1/2 bg-slate-200 lg:block"></div>

            <div class="space-y-10 lg:space-y-14">

                <div class="reveal grid items-center gap-5 lg:grid-cols-2">
                    <div class="lg:text-right lg:pr-10">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-[11px] font-bold text-teal-700">ধাপ
                            ০১</span>
                        <h3 class="mt-3 text-[16px] font-bold text-slate-900">ধারণা ও পরিকল্পনা</h3>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">
                            সমমনা কিছু মানুষ একসাথে সঞ্চয় ও বিনিয়োগের মাধ্যমে
                            দীর্ঘমেয়াদি আর্থিক সম্ভাবনা তৈরির পরিকল্পনা নিয়ে
                            আলোচনা শুরু করেন।
                        </p>
                    </div>
                    <div class="hidden lg:block"></div>
                </div>

                <div class="reveal grid items-center gap-5 lg:grid-cols-2">
                    <div class="hidden lg:block"></div>
                    <div class="lg:pl-10">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-600">ধাপ
                            ০২</span>
                        <h3 class="mt-3 text-[16px] font-bold text-slate-900">সংগঠন গঠন ও নীতিমালা প্রণয়ন</h3>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">
                            সদস্যপদ, সঞ্চয়, বিনিয়োগ ও হিসাবরক্ষণ সংক্রান্ত
                            স্পষ্ট নীতিমালা তৈরি করে সংগঠনের কাঠামো দাঁড় করানো
                            হয়।
                        </p>
                    </div>
                </div>

                <div class="reveal grid items-center gap-5 lg:grid-cols-2">
                    <div class="lg:text-right lg:pr-10">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1 text-[11px] font-bold text-sky-600">ধাপ
                            ০৩</span>
                        <h3 class="mt-3 text-[16px] font-bold text-slate-900">সদস্য সংগ্রহ ও নিয়মিত সঞ্চয়</h3>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">
                            আগ্রহী সদস্যরা যুক্ত হন এবং নির্ধারিত নিয়ম অনুযায়ী
                            নিয়মিত সঞ্চয় কার্যক্রম শুরু হয়।
                        </p>
                    </div>
                    <div class="hidden lg:block"></div>
                </div>

                <div class="reveal grid items-center gap-5 lg:grid-cols-2">
                    <div class="hidden lg:block"></div>
                    <div class="lg:pl-10">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-teal-50 px-3 py-1 text-[11px] font-bold text-teal-700">ধাপ
                            ০৪</span>
                        <h3 class="mt-3 text-[16px] font-bold text-slate-900">প্রথম উদ্যোগ বাস্তবায়ন</h3>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">
                            যাচাই-বাছাই শেষে প্রথম বিনিয়োগ বা সম্পদ ক্রয়ের
                            উদ্যোগ নেওয়া হয় এবং হিসাব সংরক্ষণের ব্যবস্থা চালু
                            হয়।
                        </p>
                    </div>
                </div>

                <div class="reveal grid items-center gap-5 lg:grid-cols-2">
                    <div class="lg:text-right lg:pr-10">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-600">ধাপ
                            ০৫</span>
                        <h3 class="mt-3 text-[16px] font-bold text-slate-900">সম্প্রসারণ ও প্রাতিষ্ঠানিকীকরণ</h3>
                        <p class="mt-1 text-[13px] leading-6 text-slate-500">
                            কার্যক্রমের পরিধি বাড়ানো, ডিজিটাল হিসাবরক্ষণ চালু
                            এবং সংগঠনকে আরও কাঠামোবদ্ধভাবে পরিচালনার দিকে
                            এগিয়ে যাওয়া—এখন আমরা এই পর্যায়ে আছি।
                        </p>
                    </div>
                    <div class="hidden lg:block"></div>
                </div>

            </div>
        </div>
    </div>
</section>

{{-- =========================================================
GOVERNANCE / STRUCTURE
========================================================== --}}
<section class="bg-slate-100 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Governance</span>
            <h2 class="mt-3 text-[2rem] font-bold text-slate-900 sm:text-[2.5rem]">পরিচালনা কাঠামো</h2>
            <p class="text-[14px] leading-7 text-slate-500">
                সংগঠনের প্রতিটি সিদ্ধান্ত একটি নির্দিষ্ট কাঠামো ও প্রক্রিয়ার
                মধ্য দিয়ে নেওয়া হয়, যাতে দায়বদ্ধতা ও অংশগ্রহণ নিশ্চিত থাকে।
            </p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                    <i data-lucide="users" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">সাধারণ সদস্য পরিষদ</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    সংগঠনের সকল সদস্য নিয়ে গঠিত সর্বোচ্চ ফোরাম, যেখানে বড়
                    সিদ্ধান্ত, নীতিমালা পরিবর্তন ও কমিটি নির্বাচন অনুমোদিত হয়।
                    বার্ষিক সাধারণ সভায় সব সদস্য অংশগ্রহণের সুযোগ পান।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i data-lucide="clipboard-check" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">পরিচালনা কমিটি</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    দৈনন্দিন কার্যক্রম, হিসাব ব্যবস্থাপনা ও পরিকল্পনা
                    বাস্তবায়নের দায়িত্বে থাকে এই কমিটি। নির্দিষ্ট মেয়াদের
                    জন্য সাধারণ সদস্যদের ভোটে এই কমিটি নির্বাচিত হয়।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-6 shadow-soft">
                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i data-lucide="scale" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-slate-900">নিরীক্ষা ও পর্যালোচনা</h3>
                <p class="mt-2 text-sm leading-6 text-slate-500">
                    নিয়মিত অন্তর্বর্তী পর্যালোচনার মাধ্যমে আর্থিক হিসাব ও
                    সিদ্ধান্তের যথার্থতা যাচাই করা হয়, এবং প্রয়োজনে
                    সাধারণ সদস্যদের কাছে ব্যাখ্যা উপস্থাপন করা হয়।
                </p>
            </div>

        </div>

        {{-- Individual responsibilities --}}
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

            <div class="reveal rounded-md border border-slate-200 bg-white p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-700 text-white">
                    <i data-lucide="user-check" class="h-4 w-4"></i>
                </div>
                <h4 class="mt-4 text-[13px] font-bold text-slate-900">সভাপতি</h4>
                <p class="mt-1 text-[12px] leading-5 text-slate-500">
                    সংগঠনের সার্বিক দিকনির্দেশনা ও সভা পরিচালনার দায়িত্বে
                    থাকেন।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-700 text-white">
                    <i data-lucide="file-signature" class="h-4 w-4"></i>
                </div>
                <h4 class="mt-4 text-[13px] font-bold text-slate-900">সাধারণ সম্পাদক</h4>
                <p class="mt-1 text-[12px] leading-5 text-slate-500">
                    সভার কার্যবিবরণী, যোগাযোগ ও প্রশাসনিক কার্যক্রম দেখাশোনা
                    করেন।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-700 text-white">
                    <i data-lucide="banknote" class="h-4 w-4"></i>
                </div>
                <h4 class="mt-4 text-[13px] font-bold text-slate-900">কোষাধ্যক্ষ</h4>
                <p class="mt-1 text-[12px] leading-5 text-slate-500">
                    তহবিল সংগ্রহ, সংরক্ষণ ও হিসাবরক্ষণের সরাসরি দায়িত্বে
                    থাকেন।
                </p>
            </div>

            <div class="reveal rounded-md border border-slate-200 bg-white p-5">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-teal-700 text-white">
                    <i data-lucide="users-round" class="h-4 w-4"></i>
                </div>
                <h4 class="mt-4 text-[13px] font-bold text-slate-900">কার্যনির্বাহী সদস্যবৃন্দ</h4>
                <p class="mt-1 text-[12px] leading-5 text-slate-500">
                    নির্দিষ্ট কার্যক্রম বাস্তবায়ন ও পরিকল্পনায় সহায়তা করেন।
                </p>
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
MEMBERSHIP
========================================================== --}}
<section class="bg-white py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">

            <div class="reveal">
                <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-700">Membership</span>
                <h2 class="mt-3 text-[1.8rem] font-bold text-slate-900 sm:text-[2.2rem]">কারা সদস্য হতে পারেন</h2>
                <p class="mt-3 text-[14px] leading-7 text-slate-500">
                    সংগঠনের নীতিমালা মেনে চলতে আগ্রহী যেকোনো ব্যক্তি নির্ধারিত
                    প্রক্রিয়ার মাধ্যমে সদস্য হতে পারেন।
                </p>

                <div class="mt-6 space-y-4">
                    <div class="flex gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </div>
                        <p class="text-[13px] leading-6 text-slate-600">সংগঠনের গঠনতন্ত্র ও নীতিমালা মেনে চলতে সম্মত
                            থাকা।</p>
                    </div>
                    <div class="flex gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </div>
                        <p class="text-[13px] leading-6 text-slate-600">নির্ধারিত আবেদন ফরম পূরণ ও পরিচালনা কমিটির
                            অনুমোদন প্রয়োজন।</p>
                    </div>
                    <div class="flex gap-3">
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-teal-50 text-teal-700">
                            <i data-lucide="check" class="h-4 w-4"></i>
                        </div>
                        <p class="text-[13px] leading-6 text-slate-600">নিয়মিত সঞ্চয় ও নির্ধারিত চাঁদা প্রদানে সম্মত
                            থাকতে হয়।</p>
                    </div>
                </div>
            </div>

            <div class="reveal">
                <h2 class="text-[1.8rem] font-bold text-slate-900 sm:text-[2.2rem]">সদস্যদের দায়িত্ব ও সুবিধা</h2>
                <p class="mt-3 text-[14px] leading-7 text-slate-500">
                    সদস্যপদ শুধু আর্থিক সম্পৃক্ততা নয়, বরং সংগঠনের সিদ্ধান্ত
                    গ্রহণ প্রক্রিয়ায় সরাসরি অংশগ্রহণের সুযোগও বটে।
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <i data-lucide="vote" class="h-4 w-4 text-teal-700"></i>
                        <p class="mt-2 text-[13px] font-semibold text-slate-800">ভোটাধিকার</p>
                        <p class="mt-1 text-[12px] leading-5 text-slate-500">সভায় মতামত ও সিদ্ধান্তে ভোট প্রদানের
                            অধিকার।</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <i data-lucide="file-text" class="h-4 w-4 text-teal-700"></i>
                        <p class="mt-2 text-[13px] font-semibold text-slate-800">হিসাব দেখার অধিকার</p>
                        <p class="mt-1 text-[12px] leading-5 text-slate-500">নিজের সঞ্চয় ও সংগঠনের হিসাব-বিবরণী দেখার
                            সুযোগ।</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <i data-lucide="hand-heart" class="h-4 w-4 text-teal-700"></i>
                        <p class="mt-2 text-[13px] font-semibold text-slate-800">কল্যাণ সুবিধা</p>
                        <p class="mt-1 text-[12px] leading-5 text-slate-500">প্রয়োজনে সদস্য কল্যাণ তহবিলের আওতায়
                            সহায়তা পাওয়ার সুযোগ।</p>
                    </div>
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
                        <i data-lucide="megaphone" class="h-4 w-4 text-teal-700"></i>
                        <p class="mt-2 text-[13px] font-semibold text-slate-800">মতামত প্রদান</p>
                        <p class="mt-1 text-[12px] leading-5 text-slate-500">নতুন উদ্যোগ ও পরিকল্পনায় প্রস্তাব দেওয়ার
                            সুযোগ।</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- =========================================================
VALUES
========================================================== --}}
<section class="bg-slate-900 py-14 sm:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <div class="mx-auto max-w-2xl text-center reveal">
            <span class="text-[13px] font-bold uppercase tracking-[0.18em] text-teal-400">Our Values</span>
            <h2 class="mt-3 text-[2rem] font-bold text-white sm:text-[2.5rem]">যে মূল্যবোধ আমাদের এগিয়ে নিয়ে যায়</h2>
        </div>

        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <div class="reveal rounded-md border border-white/10 bg-white/5 p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-500/10 text-teal-300">
                    <i data-lucide="shield-check" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-white">স্বচ্ছতা</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-400">
                    প্রতিটি কার্যক্রমে পরিষ্কার হিসাব ও তথ্যের গুরুত্ব।
                </p>
            </div>

            <div class="reveal rounded-md border border-white/10 bg-white/5 p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-500/10 text-teal-300">
                    <i data-lucide="users" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-white">ঐক্য</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-400">
                    সম্মিলিত সিদ্ধান্ত ও পারস্পরিক সহযোগিতার মাধ্যমে এগিয়ে চলা।
                </p>
            </div>

            <div class="reveal rounded-md border border-white/10 bg-white/5 p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-500/10 text-teal-300">
                    <i data-lucide="scale" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-white">দায়বদ্ধতা</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-400">
                    সিদ্ধান্ত ও সম্পদের ব্যবহারে দায়িত্বশীল থাকা।
                </p>
            </div>

            <div class="reveal rounded-md border border-white/10 bg-white/5 p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-teal-500/10 text-teal-300">
                    <i data-lucide="rocket" class="h-5 w-5"></i>
                </div>
                <h3 class="mt-5 text-[15px] font-bold text-white">উন্নয়ন</h3>
                <p class="mt-2 text-[13px] leading-5 text-slate-400">
                    বর্তমানের পাশাপাশি ভবিষ্যতের জন্য পরিকল্পনা করা।
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

                <h2 class="mt-5 text-[2rem] font-bold sm:text-[2.45rem]">আমাদের কার্যক্রম সম্পর্কে জানুন</h2>

                <p class="mx-auto mt-3 max-w-xl text-[13px] leading-6 text-teal-50">
                    সংগঠনের বিনিয়োগ, জমি ও ব্যবসায়িক উদ্যোগসহ প্রতিটি
                    কার্যক্রম সম্পর্কে বিস্তারিত জানতে পাশের পাতাটি দেখুন।
                </p>

                <a href="{{ route('activities') }}"
                    class="mt-7 inline-flex items-center gap-2 rounded-md bg-white px-5 py-3 text-[13px] font-bold text-teal-700 shadow-lg transition hover:-translate-y-0.5 hover:bg-teal-50">
                    আমাদের কার্যক্রম দেখুন
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