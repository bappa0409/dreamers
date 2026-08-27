<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ড্রিমার্স অ্যাসোসিয়েশন — সম্মিলিত সঞ্চয়, সমৃদ্ধ ভবিষ্যৎ</title>

    <meta name="description"
        content="ড্রিমার্স অ্যাসোসিয়েশন একটি সদস্যভিত্তিক সঞ্চয় ও বিনিয়োগ সংগঠন। সম্মিলিত সঞ্চয়ের মাধ্যমে সম্পদ ও একটি সুন্দর ভবিষ্যৎ গড়ে তোলাই আমাদের লক্ষ্য।">

    <meta name="theme-color" content="#155346">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        * {
            font-family: 'Hind Siliguri', sans-serif;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: #f7f4eb;
            color: #172c25;
        }

        ::selection {
            background: #155346;
            color: #fff;
        }

        .font-number {
            font-family: Arial, sans-serif;
        }

        .paper-pattern {
            background-image:
                linear-gradient(rgba(21, 83, 70, .035) 1px, transparent 1px),
                linear-gradient(90deg, rgba(21, 83, 70, .035) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .gold-line {
            background: linear-gradient(
                90deg,
                transparent,
                rgba(185, 141, 46, .7),
                transparent
            );
        }
    </style>
</head>

<body>

    <!-- =========================================================
         HEADER
    ========================================================== -->

    <header class="sticky top-0 z-50 bg-[#f7f4eb]/90 backdrop-blur-xl border-b border-[#155346]/10">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="h-[68px] flex items-center justify-between">

                <!-- Logo -->

                <a href="#home" class="flex items-center gap-3">

                    <div
                        class="w-10 h-10 rounded-full border-2 border-[#155346] flex items-center justify-center relative">

                        <span class="text-xl font-bold text-[#155346]">
                            ড
                        </span>

                        <span
                            class="absolute -right-1 -bottom-1 w-3 h-3 bg-[#b98d2e] rounded-full border-2 border-[#f7f4eb]">
                        </span>

                    </div>

                    <div class="leading-tight">

                        <div class="text-[17px] sm:text-[19px] font-bold text-[#155346]">
                            ড্রিমার্স অ্যাসোসিয়েশন
                        </div>

                        <div class="text-[10px] text-[#172c25]/50 tracking-wide">
                            সম্মিলিত সঞ্চয় • সম্মিলিত সমৃদ্ধি
                        </div>

                    </div>

                </a>


                <!-- Desktop Navigation -->

                <nav class="hidden lg:flex items-center gap-7 text-[14px] font-medium">

                    <a href="#home" class="text-[#155346] hover:text-[#b98d2e] transition">
                        হোম
                    </a>

                    <a href="#about" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        আমাদের সম্পর্কে
                    </a>

                    <a href="#mission" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        লক্ষ্য ও উদ্দেশ্য
                    </a>

                    <a href="#investment" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        বিনিয়োগ
                    </a>

                    <a href="#projects" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        প্রকল্প
                    </a>

                    <a href="#gallery" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        গ্যালারি
                    </a>

                    <a href="#contact" class="text-[#172c25]/70 hover:text-[#155346] transition">
                        যোগাযোগ
                    </a>

                </nav>


                <!-- Login -->

                <div class="hidden sm:block">

                    <a href="#membership"
                        class="inline-flex items-center gap-2 bg-[#155346] text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-[#0d4036] transition shadow-md shadow-[#155346]/10">

                        সদস্য হোন

                        <span>→</span>

                    </a>

                </div>


                <!-- Mobile button -->

                <button id="mobileMenuBtn"
                    class="lg:hidden w-10 h-10 rounded-full border border-[#155346]/15 flex items-center justify-center text-[#155346]"
                    type="button">

                    <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />

                    </svg>

                </button>

            </div>


            <!-- Mobile Menu -->

            <div id="mobileMenu" class="hidden lg:hidden pb-5">

                <div class="bg-white border border-[#155346]/10 rounded-2xl p-4 space-y-1 shadow-lg">

                    <a href="#home" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        হোম
                    </a>

                    <a href="#about" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        আমাদের সম্পর্কে
                    </a>

                    <a href="#mission" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        লক্ষ্য ও উদ্দেশ্য
                    </a>

                    <a href="#investment" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        বিনিয়োগ
                    </a>

                    <a href="#projects" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        প্রকল্প
                    </a>

                    <a href="#gallery" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        গ্যালারি
                    </a>

                    <a href="#contact" class="block px-4 py-2.5 rounded-xl hover:bg-[#155346]/5">
                        যোগাযোগ
                    </a>

                </div>

            </div>

        </div>

    </header>


    <main>


        <!-- =====================================================
             HERO
        ====================================================== -->

        <section id="home" class="relative overflow-hidden bg-[#f7f4eb] paper-pattern">

            <div class="absolute -top-28 -right-28 w-[420px] h-[420px] rounded-full border border-[#155346]/10">
            </div>

            <div class="absolute -top-12 -right-12 w-[300px] h-[300px] rounded-full border border-[#b98d2e]/15">
            </div>

            <div class="absolute left-0 bottom-0 w-72 h-72 bg-[#155346]/5 rounded-full blur-3xl">
            </div>


            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16 pb-10">

                <div class="grid lg:grid-cols-12 gap-8 lg:gap-12 items-center">


                    <!-- Hero Text -->

                    <div class="lg:col-span-7">

                        <div
                            class="inline-flex items-center gap-2 bg-white/70 border border-[#155346]/10 rounded-full px-4 py-1.5 mb-5">

                            <span class="w-2 h-2 rounded-full bg-[#b98d2e]"></span>

                            <span class="text-xs text-[#155346] font-medium">
                                সদস্য মালিকানাধীন সংগঠন • প্রতিষ্ঠিত ২০১৯
                            </span>

                        </div>


                        <p class="text-[#b98d2e] text-base sm:text-lg font-medium mb-2">
                            স্বপ্ন থেকে সঞ্চয়, সঞ্চয় থেকে সম্পদ
                        </p>


                        <h1
                            class="text-[#172c25] text-[43px] sm:text-[54px] lg:text-[68px] leading-[1.05] font-bold tracking-tight">

                            একসাথে সঞ্চয় করি,

                            <br>

                            <span class="text-[#155346]">
                                একসাথে এগিয়ে যাই।
                            </span>

                        </h1>


                        <p class="mt-5 text-[#172c25]/65 text-[15px] sm:text-base leading-7 max-w-2xl">

                            ড্রিমার্স অ্যাসোসিয়েশন একটি সম্মিলিত সঞ্চয় ও বিনিয়োগভিত্তিক
                            সংগঠন। নিয়মিত সঞ্চয়ের মাধ্যমে সদস্যদের জন্য দীর্ঘমেয়াদি
                            সম্পদ, বিনিয়োগ ও সমৃদ্ধ ভবিষ্যৎ তৈরি করাই আমাদের লক্ষ্য।

                        </p>


                        <div class="flex flex-wrap items-center gap-3 mt-7">

                            <a href="#membership"
                                class="inline-flex items-center gap-2 bg-[#155346] text-white px-6 py-3 rounded-full text-sm font-semibold hover:bg-[#0d4036] transition shadow-lg shadow-[#155346]/15">

                                সদস্য হতে চাই

                                <span>→</span>

                            </a>


                            <a href="#about"
                                class="inline-flex items-center gap-2 px-5 py-3 rounded-full text-[#155346] text-sm font-semibold hover:bg-[#155346]/5 transition">

                                আমাদের সম্পর্কে

                                <span class="text-[#b98d2e]">↓</span>

                            </a>

                        </div>


                        <!-- Mini Trust -->

                        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-7 text-xs text-[#172c25]/50">

                            <span class="flex items-center gap-1.5">
                                <span class="text-[#b98d2e]">✓</span>
                                নিয়মিত সঞ্চয়
                            </span>

                            <span class="flex items-center gap-1.5">
                                <span class="text-[#b98d2e]">✓</span>
                                স্বচ্ছ হিসাব
                            </span>

                            <span class="flex items-center gap-1.5">
                                <span class="text-[#b98d2e]">✓</span>
                                সম্মিলিত সিদ্ধান্ত
                            </span>

                        </div>

                    </div>


                    <!-- Hero Visual -->

                    <div class="lg:col-span-5 flex justify-center lg:justify-end">

                        <div class="relative w-[290px] sm:w-[340px] h-[370px] sm:h-[410px]">


                            <!-- Back Card -->

                            <div
                                class="absolute inset-3 bg-[#155346] rounded-[26px] rotate-[-7deg] shadow-xl shadow-[#155346]/20">
                            </div>


                            <!-- Main Card -->

                            <div
                                class="absolute inset-0 bg-[#ebe4d1] rounded-[26px] border border-[#155346]/10 shadow-xl overflow-hidden">

                                <div class="p-6 sm:p-7">

                                    <div class="flex items-start justify-between">

                                        <div>

                                            <p class="text-[10px] uppercase tracking-[.18em] text-[#155346]/50">
                                                ড্রিমার্স অ্যাসোসিয়েশন
                                            </p>

                                            <p class="text-xl font-bold text-[#155346] mt-1">
                                                সঞ্চয় খাতা
                                            </p>

                                        </div>


                                        <div
                                            class="w-10 h-10 rounded-full border-2 border-[#b98d2e] flex items-center justify-center text-[#b98d2e] font-bold">

                                            ড

                                        </div>

                                    </div>


                                    <div class="mt-12">

                                        <p class="text-sm text-[#155346]/55">
                                            মাসিক সম্মিলিত সঞ্চয়
                                        </p>

                                        <p class="font-number text-4xl sm:text-5xl font-bold text-[#172c25] mt-1">
                                            ৳১৮ লক্ষ+
                                        </p>

                                        <p class="text-xs text-[#172c25]/40 mt-1">
                                            সদস্যদের সম্মিলিত সঞ্চয় তহবিল
                                        </p>

                                    </div>


                                    <!-- Decorative lines -->

                                    <div class="mt-8 space-y-2 opacity-40">

                                        <div class="h-px bg-[#155346]"></div>
                                        <div class="h-px bg-[#155346] w-4/5"></div>
                                        <div class="h-px bg-[#155346] w-3/5"></div>

                                    </div>

                                </div>


                                <!-- Card Bottom -->

                                <div class="absolute left-0 right-0 bottom-0 bg-[#155346] p-6 sm:p-7 text-white">

                                    <div class="grid grid-cols-2 gap-5">

                                        <div>

                                            <p class="text-[10px] text-white/45">
                                                সক্রিয় সদস্য
                                            </p>

                                            <p class="font-number text-2xl font-bold mt-1">
                                                ৬১২
                                            </p>

                                        </div>

                                        <div>

                                            <p class="text-[10px] text-white/45">
                                                জমি
                                            </p>

                                            <p class="font-number text-2xl font-bold mt-1">
                                                ৭ বিঘা
                                            </p>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- Floating Badge -->

                            <div
                                class="absolute -right-4 top-8 w-20 h-20 bg-[#b98d2e] rounded-full flex items-center justify-center text-center rotate-12 shadow-lg">

                                <div
                                    class="w-[68px] h-[68px] border border-[#155346]/30 rounded-full flex items-center justify-center">

                                    <span class="text-[9px] leading-3 font-semibold text-[#155346]">
                                        বিশ্বাস<br>
                                        • ২০১৯ •<br>
                                        একসাথে
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- Hero Stats -->

            <div class="border-t border-[#155346]/10 bg-white/30">

                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5">

                    <div class="grid grid-cols-2 md:grid-cols-4">

                        <div class="px-3 sm:px-6 md:first:pl-0 border-r border-[#155346]/10">
                            <p class="font-number text-2xl font-bold text-[#155346]">
                                ৬১২
                            </p>
                            <p class="text-xs text-[#172c25]/50 mt-0.5">
                                সক্রিয় সদস্য
                            </p>
                        </div>

                        <div class="px-3 sm:px-6 md:border-r border-[#155346]/10">
                            <p class="font-number text-2xl font-bold text-[#155346]">
                                ১৮L+
                            </p>
                            <p class="text-xs text-[#172c25]/50 mt-0.5">
                                মাসিক সঞ্চয়
                            </p>
                        </div>

                        <div class="px-3 sm:px-6 border-r border-[#155346]/10 mt-4 md:mt-0">
                            <p class="font-number text-2xl font-bold text-[#155346]">
                                ৭
                            </p>
                            <p class="text-xs text-[#172c25]/50 mt-0.5">
                                বিঘা জমি
                            </p>
                        </div>

                        <div class="px-3 sm:px-6 mt-4 md:mt-0">
                            <p class="font-number text-2xl font-bold text-[#155346]">
                                ৫
                            </p>
                            <p class="text-xs text-[#172c25]/50 mt-0.5">
                                চলমান প্রকল্প
                            </p>
                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             ABOUT
        ====================================================== -->

        <section id="about" class="py-14 sm:py-16 bg-white">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid lg:grid-cols-12 gap-9 items-start">

                    <div class="lg:col-span-5">

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold tracking-wider text-[#155346]">
                                আমাদের পরিচয়
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mt-3 text-[#172c25]">

                            ছোট ছোট সঞ্চয়েই

                            <br>

                            <span class="text-[#155346]">
                                বড় স্বপ্নের শুরু।
                            </span>

                        </h2>

                    </div>


                    <div class="lg:col-span-7">

                        <p class="text-[#172c25]/65 leading-7 text-[15px] sm:text-base">

                            ড্রিমার্স অ্যাসোসিয়েশন এমন একটি সংগঠন যেখানে সদস্যদের
                            নিয়মিত সঞ্চয় একটি সম্মিলিত শক্তিতে পরিণত হয়। আমরা বিশ্বাস করি,
                            একা বড় কিছু করার চেয়ে সবাই মিলে পরিকল্পিতভাবে এগিয়ে যাওয়া
                            অনেক বেশি কার্যকর।

                        </p>

                        <p class="text-[#172c25]/60 leading-7 text-[15px] sm:text-base mt-4">

                            আমাদের মূল উদ্দেশ্য হলো সদস্যদের সঞ্চয়ের অভ্যাসকে শক্তিশালী করা,
                            সম্মিলিতভাবে সম্পদ তৈরি করা এবং ভবিষ্যৎ প্রজন্মের জন্য একটি
                            স্থায়ী আর্থিক ভিত্তি তৈরি করা।

                        </p>


                        <div class="mt-6 grid sm:grid-cols-3 gap-3">

                            <div class="border-t border-[#155346]/15 pt-4">

                                <span class="text-[#b98d2e] text-xl font-bold">
                                    ০১
                                </span>

                                <h3 class="font-bold text-[#155346] mt-2">
                                    সঞ্চয়
                                </h3>

                                <p class="text-xs text-[#172c25]/50 mt-1">
                                    নিয়মিত ও শৃঙ্খলাবদ্ধ সঞ্চয়
                                </p>

                            </div>

                            <div class="border-t border-[#155346]/15 pt-4">

                                <span class="text-[#b98d2e] text-xl font-bold">
                                    ০২
                                </span>

                                <h3 class="font-bold text-[#155346] mt-2">
                                    বিনিয়োগ
                                </h3>

                                <p class="text-xs text-[#172c25]/50 mt-1">
                                    পরিকল্পিত সম্পদ ও প্রকল্পে বিনিয়োগ
                                </p>

                            </div>

                            <div class="border-t border-[#155346]/15 pt-4">

                                <span class="text-[#b98d2e] text-xl font-bold">
                                    ০৩
                                </span>

                                <h3 class="font-bold text-[#155346] mt-2">
                                    সমৃদ্ধি
                                </h3>

                                <p class="text-xs text-[#172c25]/50 mt-1">
                                    দীর্ঘমেয়াদে সবার জন্য মূল্য তৈরি
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             MISSION / VISION
        ====================================================== -->

        <section id="mission" class="py-14 sm:py-16 bg-[#f7f4eb]">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="max-w-2xl mb-9">

                    <div class="flex items-center gap-3">

                        <span class="w-8 h-px bg-[#b98d2e]"></span>

                        <span class="text-xs font-semibold text-[#155346]">
                            আমাদের লক্ষ্য
                        </span>

                    </div>

                    <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                        যে মূল্যবোধে আমরা এগিয়ে চলি
                    </h2>

                </div>


                <div class="grid md:grid-cols-3 gap-6">


                    <div class="border-t-2 border-[#155346] pt-5">

                        <div class="flex justify-between">

                            <span class="text-[#b98d2e] font-bold">
                                ০১
                            </span>

                            <span class="text-[#155346]/30 text-xl">
                                ↗
                            </span>

                        </div>

                        <h3 class="text-2xl font-bold text-[#155346] mt-7">
                            আমাদের মিশন
                        </h3>

                        <p class="text-sm leading-6 text-[#172c25]/60 mt-3">
                            সদস্যদের নিয়মিত সঞ্চয়কে কাজে লাগিয়ে সম্মিলিতভাবে
                            সম্পদ তৈরি এবং দীর্ঘমেয়াদি আর্থিক নিরাপত্তার সুযোগ সৃষ্টি করা।
                        </p>

                    </div>


                    <div class="border-t-2 border-[#b98d2e] pt-5">

                        <div class="flex justify-between">

                            <span class="text-[#b98d2e] font-bold">
                                ০২
                            </span>

                            <span class="text-[#155346]/30 text-xl">
                                ↗
                            </span>

                        </div>

                        <h3 class="text-2xl font-bold text-[#155346] mt-7">
                            আমাদের ভিশন
                        </h3>

                        <p class="text-sm leading-6 text-[#172c25]/60 mt-3">
                            একটি শক্তিশালী, স্বচ্ছ ও বিশ্বাসযোগ্য সদস্যভিত্তিক
                            বিনিয়োগ সম্প্রদায় হিসেবে নিজেদের প্রতিষ্ঠিত করা।
                        </p>

                    </div>


                    <div class="border-t-2 border-[#155346] pt-5">

                        <div class="flex justify-between">

                            <span class="text-[#b98d2e] font-bold">
                                ০৩
                            </span>

                            <span class="text-[#155346]/30 text-xl">
                                ↗
                            </span>

                        </div>

                        <h3 class="text-2xl font-bold text-[#155346] mt-7">
                            আমাদের মূল্যবোধ
                        </h3>

                        <p class="text-sm leading-6 text-[#172c25]/60 mt-3">
                            বিশ্বাস, স্বচ্ছতা, দায়িত্বশীলতা, শৃঙ্খলা ও পারস্পরিক
                            সহযোগিতাকে আমাদের প্রতিটি সিদ্ধান্তের ভিত্তি হিসেবে রাখা।
                        </p>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             MEMBERSHIP JOURNEY
        ====================================================== -->

        <section id="membership" class="py-14 sm:py-16 bg-white">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid lg:grid-cols-12 gap-10">

                    <div class="lg:col-span-4">

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold text-[#155346]">
                                সদস্য হওয়ার প্রক্রিয়া
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                            আপনার যাত্রা শুরু হোক আজ থেকেই।
                        </h2>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-4">
                            সহজ কয়েকটি ধাপ অনুসরণ করেই ড্রিমার্স অ্যাসোসিয়েশনের
                            সদস্য হয়ে আমাদের সম্মিলিত যাত্রায় যুক্ত হতে পারেন।
                        </p>

                        <a href="#contact"
                            class="inline-flex mt-5 bg-[#155346] text-white px-5 py-2.5 rounded-full text-sm font-semibold">
                            যোগাযোগ করুন →
                        </a>

                    </div>


                    <div class="lg:col-span-8 grid sm:grid-cols-2 gap-4">


                        <div class="bg-[#f7f4eb] p-5 rounded-2xl">

                            <span class="text-[#b98d2e] font-bold">
                                ধাপ ০১
                            </span>

                            <h3 class="text-xl font-bold text-[#155346] mt-5">
                                আবেদন
                            </h3>

                            <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                                সদস্য হওয়ার জন্য আবেদনপত্র পূরণ করুন।
                            </p>

                        </div>


                        <div class="bg-[#f7f4eb] p-5 rounded-2xl">

                            <span class="text-[#b98d2e] font-bold">
                                ধাপ ০২
                            </span>

                            <h3 class="text-xl font-bold text-[#155346] mt-5">
                                যাচাই
                            </h3>

                            <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                                প্রয়োজনীয় তথ্য ও কাগজপত্র যাচাই করা হবে।
                            </p>

                        </div>


                        <div class="bg-[#f7f4eb] p-5 rounded-2xl">

                            <span class="text-[#b98d2e] font-bold">
                                ধাপ ০৩
                            </span>

                            <h3 class="text-xl font-bold text-[#155346] mt-5">
                                সঞ্চয় শুরু
                            </h3>

                            <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                                নির্ধারিত নিয়ম অনুযায়ী আপনার মাসিক সঞ্চয় শুরু করুন।
                            </p>

                        </div>


                        <div class="bg-[#155346] p-5 rounded-2xl text-white">

                            <span class="text-[#d5b76a] font-bold">
                                ধাপ ০৪
                            </span>

                            <h3 class="text-xl font-bold mt-5">
                                সম্মিলিত সমৃদ্ধি
                            </h3>

                            <p class="text-sm text-white/60 leading-6 mt-2">
                                সম্মিলিত সঞ্চয় ও বিনিয়োগের মাধ্যমে ভবিষ্যৎ গড়ে তুলুন।
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             INVESTMENT
        ====================================================== -->

        <section id="investment" class="py-14 sm:py-16 bg-[#155346] text-white relative overflow-hidden">

            <div class="absolute right-0 top-0 w-80 h-80 rounded-full border border-white/5">
            </div>

            <div class="absolute right-16 top-16 w-48 h-48 rounded-full border border-[#b98d2e]/20">
            </div>


            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">

                <div class="grid lg:grid-cols-12 gap-10">

                    <div class="lg:col-span-5">

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#d5b76a]"></span>

                            <span class="text-xs font-semibold text-[#d5b76a]">
                                আমাদের বিনিয়োগ
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-3 leading-tight">
                            সঞ্চয়কে আমরা
                            <span class="text-[#d5b76a]">সম্পদে</span>
                            রূপান্তর করি।
                        </h2>

                        <p class="text-white/60 text-sm leading-6 mt-4 max-w-md">
                            সদস্যদের সম্মিলিত অর্থকে পরিকল্পিত ও দীর্ঘমেয়াদি
                            সম্পদ তৈরির কাজে ব্যবহার করা আমাদের অন্যতম প্রধান লক্ষ্য।
                        </p>

                    </div>


                    <div class="lg:col-span-7 grid sm:grid-cols-2 gap-x-8 gap-y-7">


                        <div class="border-t border-white/15 pt-4">

                            <span class="text-[#d5b76a] text-sm font-bold">
                                ০১
                            </span>

                            <h3 class="text-xl font-bold mt-4">
                                জমি ও রিয়েল এস্টেট
                            </h3>

                            <p class="text-sm text-white/50 leading-6 mt-2">
                                সম্ভাবনাময় এলাকায় দীর্ঘমেয়াদি সম্পদ তৈরির উদ্দেশ্যে
                                জমি ও রিয়েল এস্টেটে বিনিয়োগ।
                            </p>

                        </div>


                        <div class="border-t border-white/15 pt-4">

                            <span class="text-[#d5b76a] text-sm font-bold">
                                ০২
                            </span>

                            <h3 class="text-xl font-bold mt-4">
                                ব্যবসায়িক প্রকল্প
                            </h3>

                            <p class="text-sm text-white/50 leading-6 mt-2">
                                সম্ভাবনাময় ও টেকসই ব্যবসায়িক উদ্যোগে
                                সম্মিলিত বিনিয়োগ।
                            </p>

                        </div>


                        <div class="border-t border-white/15 pt-4">

                            <span class="text-[#d5b76a] text-sm font-bold">
                                ০৩
                            </span>

                            <h3 class="text-xl font-bold mt-4">
                                দীর্ঘমেয়াদি সম্পদ
                            </h3>

                            <p class="text-sm text-white/50 leading-6 mt-2">
                                ভবিষ্যৎ প্রজন্মের জন্য স্থায়ী ও মূল্যবান
                                সম্পদ তৈরি করা।
                            </p>

                        </div>


                        <div class="border-t border-white/15 pt-4">

                            <span class="text-[#d5b76a] text-sm font-bold">
                                ০৪
                            </span>

                            <h3 class="text-xl font-bold mt-4">
                                ঝুঁকি ব্যবস্থাপনা
                            </h3>

                            <p class="text-sm text-white/50 leading-6 mt-2">
                                পরিকল্পিত সিদ্ধান্ত, যাচাই ও সম্মিলিত আলোচনার
                                মাধ্যমে ঝুঁকি কমানোর চেষ্টা।
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             LAND / PROPERTY
        ====================================================== -->

        <section class="py-14 sm:py-16 bg-[#f7f4eb]">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-7">

                    <div>

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold text-[#155346]">
                                সম্পদ
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                            আমাদের জমি ও সম্পদ
                        </h2>

                    </div>

                    <p class="text-sm text-[#172c25]/50 max-w-sm">
                        সম্মিলিত সঞ্চয়ের মাধ্যমে অর্জিত সম্পদ আমাদের
                        দীর্ঘমেয়াদি পরিকল্পনার অন্যতম ভিত্তি।
                    </p>

                </div>


                <div class="bg-white rounded-3xl overflow-hidden border border-[#155346]/10 shadow-sm">

                    <div class="grid lg:grid-cols-2">


                        <!-- Image -->

                        <div class="min-h-[280px] lg:min-h-[380px] bg-[#dfe5dc] relative overflow-hidden">

                            <img src="{{ asset('images/projects/dreamers-green-city.jpg') }}"
                                alt="ড্রিমার্স গ্রিন সিটি"
                                class="absolute inset-0 w-full h-full object-cover"
                                onerror="this.style.display='none';">


                            <div class="absolute inset-0 bg-[#155346]/10"></div>

                            <div
                                class="absolute top-5 left-5 bg-white/90 backdrop-blur px-3 py-1.5 rounded-full text-xs font-semibold text-[#155346]">

                                ✓ যাচাইকৃত সম্পদ

                            </div>


                            <div class="absolute bottom-5 left-5 right-5">

                                <div class="bg-[#155346]/90 backdrop-blur rounded-2xl p-4 text-white">

                                    <p class="text-xs text-white/50">
                                        প্রকল্প
                                    </p>

                                    <h3 class="text-2xl font-bold">
                                        ড্রিমার্স গ্রিন সিটি
                                    </h3>

                                </div>

                            </div>

                        </div>


                        <!-- Details -->

                        <div class="p-6 sm:p-8 lg:p-10">

                            <span class="text-xs text-[#b98d2e] font-semibold">
                                জমি প্রকল্প • ২০২৩
                            </span>

                            <h3 class="text-3xl font-bold text-[#155346] mt-2">
                                ড্রিমার্স গ্রিন সিটি
                            </h3>

                            <p class="text-sm leading-6 text-[#172c25]/55 mt-3">
                                সদস্যদের সম্মিলিত বিনিয়োগের মাধ্যমে গড়ে ওঠা
                                আমাদের অন্যতম গুরুত্বপূর্ণ সম্পদ প্রকল্প।
                            </p>


                            <div class="grid grid-cols-2 gap-4 mt-7">

                                <div class="border-t border-[#155346]/10 pt-4">

                                    <p class="text-xs text-[#172c25]/40">
                                        অবস্থান
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        সাভার, ঢাকা
                                    </p>

                                </div>

                                <div class="border-t border-[#155346]/10 pt-4">

                                    <p class="text-xs text-[#172c25]/40">
                                        আয়তন
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        ৩.২ বিঘা
                                    </p>

                                </div>

                                <div class="border-t border-[#155346]/10 pt-4">

                                    <p class="text-xs text-[#172c25]/40">
                                        প্রকল্পের ধরন
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        জমি
                                    </p>

                                </div>

                                <div class="border-t border-[#155346]/10 pt-4">

                                    <p class="text-xs text-[#172c25]/40">
                                        অবস্থা
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        চলমান
                                    </p>

                                </div>

                            </div>


                            <a href="#contact"
                                class="inline-flex items-center gap-2 mt-7 bg-[#155346] text-white px-5 py-2.5 rounded-full text-sm font-semibold">

                                বিস্তারিত জানুন

                                <span>→</span>

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             PROJECTS
        ====================================================== -->

        <section id="projects" class="py-14 sm:py-16 bg-white">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-8">

                    <div class="flex items-center gap-3">

                        <span class="w-8 h-px bg-[#b98d2e]"></span>

                        <span class="text-xs font-semibold text-[#155346]">
                            আমাদের উদ্যোগ
                        </span>

                    </div>

                    <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                        চলমান ও পরিকল্পিত প্রকল্প
                    </h2>

                </div>


                <div class="grid md:grid-cols-3 gap-5">


                    <div class="group border border-[#155346]/10 rounded-2xl p-5 hover:border-[#155346]/25 hover:shadow-lg transition">

                        <div class="flex justify-between">

                            <span class="text-xs bg-[#e8f0ec] text-[#155346] px-3 py-1 rounded-full">
                                চলমান
                            </span>

                            <span class="text-[#b98d2e]">
                                ০১
                            </span>

                        </div>

                        <h3 class="text-xl font-bold text-[#155346] mt-8">
                            ড্রিমার্স গ্রিন সিটি
                        </h3>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                            জমি ও আবাসনভিত্তিক দীর্ঘমেয়াদি সম্পদ প্রকল্প।
                        </p>

                        <div class="mt-6 pt-4 border-t border-[#155346]/10 flex justify-between text-xs">

                            <span class="text-[#172c25]/40">
                                সাভার
                            </span>

                            <span class="text-[#155346] font-semibold">
                                বিস্তারিত →
                            </span>

                        </div>

                    </div>


                    <div class="group border border-[#155346]/10 rounded-2xl p-5 hover:border-[#155346]/25 hover:shadow-lg transition">

                        <div class="flex justify-between">

                            <span class="text-xs bg-[#f4ead4] text-[#8a681d] px-3 py-1 rounded-full">
                                পরিকল্পিত
                            </span>

                            <span class="text-[#b98d2e]">
                                ০২
                            </span>

                        </div>

                        <h3 class="text-xl font-bold text-[#155346] mt-8">
                            ড্রিমার্স বিজনেস হাব
                        </h3>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                            সদস্যদের জন্য সম্ভাবনাময় ব্যবসায়িক বিনিয়োগ উদ্যোগ।
                        </p>

                        <div class="mt-6 pt-4 border-t border-[#155346]/10 flex justify-between text-xs">

                            <span class="text-[#172c25]/40">
                                ঢাকা
                            </span>

                            <span class="text-[#155346] font-semibold">
                                শীঘ্রই →
                            </span>

                        </div>

                    </div>


                    <div class="group border border-[#155346]/10 rounded-2xl p-5 hover:border-[#155346]/25 hover:shadow-lg transition">

                        <div class="flex justify-between">

                            <span class="text-xs bg-[#f4ead4] text-[#8a681d] px-3 py-1 rounded-full">
                                পরিকল্পিত
                            </span>

                            <span class="text-[#b98d2e]">
                                ০৩
                            </span>

                        </div>

                        <h3 class="text-xl font-bold text-[#155346] mt-8">
                            ভবিষ্যৎ আবাসন প্রকল্প
                        </h3>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-2">
                            সদস্যদের ভবিষ্যৎ আবাসন সুবিধার জন্য দীর্ঘমেয়াদি পরিকল্পনা।
                        </p>

                        <div class="mt-6 pt-4 border-t border-[#155346]/10 flex justify-between text-xs">

                            <span class="text-[#172c25]/40">
                                বাংলাদেশ
                            </span>

                            <span class="text-[#155346] font-semibold">
                                শীঘ্রই →
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             TRANSPARENCY
        ====================================================== -->

        <section class="py-14 sm:py-16 bg-[#f7f4eb]">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid lg:grid-cols-12 gap-10 items-center">

                    <div class="lg:col-span-5">

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold text-[#155346]">
                                স্বচ্ছতা
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                            বিশ্বাস তৈরি হয়
                            <span class="text-[#155346]">স্বচ্ছতার মাধ্যমে।</span>
                        </h2>

                        <p class="text-sm leading-6 text-[#172c25]/55 mt-4">
                            সদস্যদের অর্থ ও সম্পদের বিষয়ে পরিষ্কার ধারণা দেওয়া,
                            নিয়মিত হিসাব সংরক্ষণ এবং গুরুত্বপূর্ণ সিদ্ধান্তে
                            সদস্যদের সম্পৃক্ত রাখা আমাদের নীতির অংশ।
                        </p>

                    </div>


                    <div class="lg:col-span-7 grid sm:grid-cols-2 gap-4">

                        <div class="bg-white rounded-2xl p-5 border border-[#155346]/10">

                            <div class="text-2xl text-[#b98d2e]">
                                ✓
                            </div>

                            <h3 class="font-bold text-[#155346] mt-4">
                                নিয়মিত হিসাব
                            </h3>

                            <p class="text-xs text-[#172c25]/50 leading-5 mt-1">
                                সঞ্চয় ও আর্থিক কার্যক্রমের হিসাব নিয়মিত সংরক্ষণ।
                            </p>

                        </div>


                        <div class="bg-white rounded-2xl p-5 border border-[#155346]/10">

                            <div class="text-2xl text-[#b98d2e]">
                                ✓
                            </div>

                            <h3 class="font-bold text-[#155346] mt-4">
                                সদস্যদের অংশগ্রহণ
                            </h3>

                            <p class="text-xs text-[#172c25]/50 leading-5 mt-1">
                                গুরুত্বপূর্ণ সিদ্ধান্তে সদস্যদের মতামতকে গুরুত্ব দেওয়া।
                            </p>

                        </div>


                        <div class="bg-white rounded-2xl p-5 border border-[#155346]/10">

                            <div class="text-2xl text-[#b98d2e]">
                                ✓
                            </div>

                            <h3 class="font-bold text-[#155346] mt-4">
                                নথিপত্র সংরক্ষণ
                            </h3>

                            <p class="text-xs text-[#172c25]/50 leading-5 mt-1">
                                প্রকল্প ও সম্পদের প্রয়োজনীয় তথ্য সংরক্ষণ।
                            </p>

                        </div>


                        <div class="bg-white rounded-2xl p-5 border border-[#155346]/10">

                            <div class="text-2xl text-[#b98d2e]">
                                ✓
                            </div>

                            <h3 class="font-bold text-[#155346] mt-4">
                                দায়িত্বশীলতা
                            </h3>

                            <p class="text-xs text-[#172c25]/50 leading-5 mt-1">
                                প্রতিটি সিদ্ধান্তে দীর্ঘমেয়াদি স্বার্থকে অগ্রাধিকার।
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             NOTICE
        ====================================================== -->

        <section class="py-12 bg-white">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex items-center justify-between mb-6">

                    <div>

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold text-[#155346]">
                                নোটিশ ও আপডেট
                            </span>

                        </div>

                        <h2 class="text-2xl sm:text-3xl font-bold text-[#172c25] mt-2">
                            সর্বশেষ তথ্য
                        </h2>

                    </div>

                    <a href="#" class="hidden sm:block text-sm font-semibold text-[#155346]">
                        সব দেখুন →
                    </a>

                </div>


                <div class="divide-y divide-[#155346]/10 border-y border-[#155346]/10">

                    <a href="#" class="flex items-center justify-between py-4 group">

                        <div class="flex items-center gap-4">

                            <span class="text-xs text-[#b98d2e] font-bold">
                                ২৭ আগস্ট ২০২৬
                            </span>

                            <span class="text-sm text-[#172c25] group-hover:text-[#155346]">
                                মাসিক সঞ্চয় জমা দেওয়ার সময়সূচি
                            </span>

                        </div>

                        <span class="text-[#155346]">
                            →
                        </span>

                    </a>


                    <a href="#" class="flex items-center justify-between py-4 group">

                        <div class="flex items-center gap-4">

                            <span class="text-xs text-[#b98d2e] font-bold">
                                ২০ আগস্ট ২০২৬
                            </span>

                            <span class="text-sm text-[#172c25] group-hover:text-[#155346]">
                                ড্রিমার্স গ্রিন সিটি প্রকল্পের সর্বশেষ আপডেট
                            </span>

                        </div>

                        <span class="text-[#155346]">
                            →
                        </span>

                    </a>


                    <a href="#" class="flex items-center justify-between py-4 group">

                        <div class="flex items-center gap-4">

                            <span class="text-xs text-[#b98d2e] font-bold">
                                ১০ আগস্ট ২০২৬
                            </span>

                            <span class="text-sm text-[#172c25] group-hover:text-[#155346]">
                                সদস্যদের মাসিক সভা সংক্রান্ত বিজ্ঞপ্তি
                            </span>

                        </div>

                        <span class="text-[#155346]">
                            →
                        </span>

                    </a>

                </div>

            </div>

        </section>


        <!-- =====================================================
             GALLERY
        ====================================================== -->

        <section id="gallery" class="py-14 sm:py-16 bg-[#f7f4eb]">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="mb-7">

                    <div class="flex items-center gap-3">

                        <span class="w-8 h-px bg-[#b98d2e]"></span>

                        <span class="text-xs font-semibold text-[#155346]">
                            আমাদের মুহূর্ত
                        </span>

                    </div>

                    <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                        একসাথে পথচলার কিছু মুহূর্ত
                    </h2>

                </div>


                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">


                    <div class="group aspect-[4/3] rounded-xl overflow-hidden bg-[#dfe5dc] relative">

                        <img src="{{ asset('images/gallery/meeting.jpg') }}"
                            alt="সদস্যদের সভা"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            onerror="this.style.display='none';">

                        <div class="absolute inset-0 bg-[#155346]/20 flex items-end p-4">

                            <span class="text-white text-sm font-semibold">
                                সদস্য সভা
                            </span>

                        </div>

                    </div>


                    <div class="group aspect-[4/3] rounded-xl overflow-hidden bg-[#e8e0ce] relative">

                        <img src="{{ asset('images/gallery/project.jpg') }}"
                            alt="প্রকল্প পরিদর্শন"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            onerror="this.style.display='none';">

                        <div class="absolute inset-0 bg-[#155346]/20 flex items-end p-4">

                            <span class="text-white text-sm font-semibold">
                                প্রকল্প পরিদর্শন
                            </span>

                        </div>

                    </div>


                    <div class="group aspect-[4/3] rounded-xl overflow-hidden bg-[#dfe5dc] relative">

                        <img src="{{ asset('images/gallery/members.jpg') }}"
                            alt="সদস্যদের মিলন"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            onerror="this.style.display='none';">

                        <div class="absolute inset-0 bg-[#155346]/20 flex items-end p-4">

                            <span class="text-white text-sm font-semibold">
                                সদস্যদের মিলন
                            </span>

                        </div>

                    </div>


                    <div class="group aspect-[4/3] rounded-xl overflow-hidden bg-[#e8e0ce] relative">

                        <img src="{{ asset('images/gallery/event.jpg') }}"
                            alt="বিশেষ অনুষ্ঠান"
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            onerror="this.style.display='none';">

                        <div class="absolute inset-0 bg-[#155346]/20 flex items-end p-4">

                            <span class="text-white text-sm font-semibold">
                                বিশেষ অনুষ্ঠান
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CTA
        ====================================================== -->

        <section class="py-12 sm:py-14 bg-[#155346] relative overflow-hidden">

            <div class="absolute right-0 top-0 w-72 h-72 border border-white/5 rounded-full">
            </div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center relative">

                <p class="text-[#d5b76a] text-sm font-medium">
                    আপনার স্বপ্ন, আমাদের সম্মিলিত শক্তি
                </p>

                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mt-2">
                    আজকের সঞ্চয়ই হতে পারে
                    <br class="hidden sm:block">
                    আগামী দিনের সম্পদ।
                </h2>

                <p class="text-white/55 text-sm max-w-xl mx-auto mt-4 leading-6">
                    ড্রিমার্স অ্যাসোসিয়েশনের সাথে যুক্ত হয়ে
                    একটি পরিকল্পিত ও সম্মিলিত ভবিষ্যৎ গড়ার যাত্রা শুরু করুন।
                </p>

                <div class="flex flex-wrap justify-center gap-3 mt-6">

                    <a href="#contact"
                        class="bg-white text-[#155346] px-6 py-3 rounded-full text-sm font-bold hover:bg-[#f4ead4] transition">

                        সদস্য হতে যোগাযোগ করুন

                    </a>

                    <a href="#about"
                        class="border border-white/20 text-white px-6 py-3 rounded-full text-sm font-semibold hover:bg-white/10 transition">

                        আরও জানুন

                    </a>

                </div>

            </div>

        </section>


        <!-- =====================================================
             CONTACT
        ====================================================== -->

        <section id="contact" class="py-14 sm:py-16 bg-white">

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="grid lg:grid-cols-12 gap-10">


                    <div class="lg:col-span-5">

                        <div class="flex items-center gap-3">

                            <span class="w-8 h-px bg-[#b98d2e]"></span>

                            <span class="text-xs font-semibold text-[#155346]">
                                যোগাযোগ
                            </span>

                        </div>

                        <h2 class="text-3xl sm:text-4xl font-bold text-[#172c25] mt-3">
                            আমাদের সাথে কথা বলুন।
                        </h2>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-4">
                            সদস্যপদ, সঞ্চয়, প্রকল্প বা অন্যান্য যেকোনো বিষয়ে
                            জানতে আমাদের সাথে যোগাযোগ করতে পারেন।
                        </p>


                        <div class="mt-7 space-y-4">

                            <div class="flex items-start gap-4">

                                <div
                                    class="w-10 h-10 rounded-xl bg-[#f7f4eb] flex items-center justify-center text-[#155346]">
                                    ☎
                                </div>

                                <div>

                                    <p class="text-xs text-[#172c25]/40">
                                        ফোন
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        ০১৭০০-০০০০০০
                                    </p>

                                </div>

                            </div>


                            <div class="flex items-start gap-4">

                                <div
                                    class="w-10 h-10 rounded-xl bg-[#f7f4eb] flex items-center justify-center text-[#155346]">
                                    @
                                </div>

                                <div>

                                    <p class="text-xs text-[#172c25]/40">
                                        ইমেইল
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        info@dreamersassociation.com
                                    </p>

                                </div>

                            </div>


                            <div class="flex items-start gap-4">

                                <div
                                    class="w-10 h-10 rounded-xl bg-[#f7f4eb] flex items-center justify-center text-[#155346]">
                                    ⌖
                                </div>

                                <div>

                                    <p class="text-xs text-[#172c25]/40">
                                        অফিস
                                    </p>

                                    <p class="font-semibold text-[#155346] mt-1">
                                        ঢাকা, বাংলাদেশ
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- Contact Form -->

                    <div class="lg:col-span-7">

                        <form action="#" method="POST"
                            class="bg-[#f7f4eb] rounded-3xl p-5 sm:p-7 border border-[#155346]/10">

                            @csrf

                            <div class="grid sm:grid-cols-2 gap-4">

                                <div>

                                    <label class="block text-xs font-semibold text-[#172c25]/60 mb-1.5">
                                        আপনার নাম
                                    </label>

                                    <input type="text" name="name"
                                        class="w-full bg-white border border-[#155346]/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#155346]/40 transition"
                                        placeholder="নাম লিখুন">

                                </div>


                                <div>

                                    <label class="block text-xs font-semibold text-[#172c25]/60 mb-1.5">
                                        মোবাইল নম্বর
                                    </label>

                                    <input type="text" name="phone"
                                        class="w-full bg-white border border-[#155346]/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#155346]/40 transition"
                                        placeholder="০১৭xxxxxxxx">

                                </div>

                            </div>


                            <div class="mt-4">

                                <label class="block text-xs font-semibold text-[#172c25]/60 mb-1.5">
                                    ইমেইল
                                </label>

                                <input type="email" name="email"
                                    class="w-full bg-white border border-[#155346]/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#155346]/40 transition"
                                    placeholder="আপনার ইমেইল">

                            </div>


                            <div class="mt-4">

                                <label class="block text-xs font-semibold text-[#172c25]/60 mb-1.5">
                                    আপনার বার্তা
                                </label>

                                <textarea name="message" rows="4"
                                    class="w-full bg-white border border-[#155346]/10 rounded-xl px-4 py-3 text-sm outline-none focus:border-[#155346]/40 transition resize-none"
                                    placeholder="আপনার বার্তা লিখুন..."></textarea>

                            </div>


                            <button type="submit"
                                class="mt-4 bg-[#155346] text-white px-6 py-3 rounded-full text-sm font-semibold hover:bg-[#0d4036] transition">

                                বার্তা পাঠান →

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </section>


        <!-- =====================================================
             FAQ
        ====================================================== -->

        <section class="py-14 sm:py-16 bg-[#f7f4eb]">

            <div class="max-w-4xl mx-auto px-4 sm:px-6">

                <div class="text-center mb-7">

                    <div class="flex justify-center items-center gap-3">

                        <span class="w-8 h-px bg-[#b98d2e]"></span>

                        <span class="text-xs font-semibold text-[#155346]">
                            সাধারণ প্রশ্ন
                        </span>

                        <span class="w-8 h-px bg-[#b98d2e]"></span>

                    </div>

                    <h2 class="text-3xl font-bold text-[#172c25] mt-3">
                        সচরাচর জিজ্ঞাসা
                    </h2>

                </div>


                <div class="space-y-3">


                    <details class="bg-white border border-[#155346]/10 rounded-xl p-4 group">

                        <summary
                            class="cursor-pointer list-none flex items-center justify-between font-semibold text-[#155346]">

                            ড্রিমার্স অ্যাসোসিয়েশনের সদস্য হওয়া যায় কীভাবে?

                            <span class="text-[#b98d2e] group-open:rotate-45 transition">
                                +
                            </span>

                        </summary>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-3 pr-5">
                            নির্ধারিত আবেদন প্রক্রিয়া সম্পন্ন করে এবং প্রয়োজনীয়
                            তথ্য যাচাইয়ের পর সদস্য হওয়া যায়।
                        </p>

                    </details>


                    <details class="bg-white border border-[#155346]/10 rounded-xl p-4 group">

                        <summary
                            class="cursor-pointer list-none flex items-center justify-between font-semibold text-[#155346]">

                            মাসিক সঞ্চয়ের নিয়ম কী?

                            <span class="text-[#b98d2e] group-open:rotate-45 transition">
                                +
                            </span>

                        </summary>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-3 pr-5">
                            সদস্যপদের সময় নির্ধারিত নিয়ম অনুযায়ী মাসিক সঞ্চয়
                            জমা দিতে হয়।
                        </p>

                    </details>


                    <details class="bg-white border border-[#155346]/10 rounded-xl p-4 group">

                        <summary
                            class="cursor-pointer list-none flex items-center justify-between font-semibold text-[#155346]">

                            সঞ্চয়ের অর্থ কোথায় বিনিয়োগ করা হয়?

                            <span class="text-[#b98d2e] group-open:rotate-45 transition">
                                +
                            </span>

                        </summary>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-3 pr-5">
                            সংগঠনের পরিকল্পনা ও সদস্যদের সম্মিলিত সিদ্ধান্ত অনুযায়ী
                            জমি, রিয়েল এস্টেট এবং অন্যান্য অনুমোদিত প্রকল্পে
                            বিনিয়োগ করা হয়।
                        </p>

                    </details>


                    <details class="bg-white border border-[#155346]/10 rounded-xl p-4 group">

                        <summary
                            class="cursor-pointer list-none flex items-center justify-between font-semibold text-[#155346]">

                            প্রকল্প সম্পর্কে বিস্তারিত তথ্য কোথায় পাব?

                            <span class="text-[#b98d2e] group-open:rotate-45 transition">
                                +
                            </span>

                        </summary>

                        <p class="text-sm text-[#172c25]/55 leading-6 mt-3 pr-5">
                            প্রকল্প সংক্রান্ত তথ্যের জন্য আমাদের অফিসে যোগাযোগ করতে
                            অথবা যোগাযোগ ফর্মের মাধ্যমে বার্তা পাঠাতে পারেন।
                        </p>

                    </details>

                </div>

            </div>

        </section>

    </main>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <footer class="bg-[#102f28] text-white">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

            <div class="grid sm:grid-cols-2 lg:grid-cols-12 gap-8">


                <!-- Brand -->

                <div class="lg:col-span-5">

                    <div class="flex items-center gap-3">

                        <div
                            class="w-10 h-10 rounded-full border-2 border-[#d5b76a] flex items-center justify-center">

                            <span class="text-lg font-bold text-[#d5b76a]">
                                ড
                            </span>

                        </div>

                        <div>

                            <h3 class="text-lg font-bold">
                                ড্রিমার্স অ্যাসোসিয়েশন
                            </h3>

                            <p class="text-[10px] text-white/40">
                                সম্মিলিত সঞ্চয় • সম্মিলিত সমৃদ্ধি
                            </p>

                        </div>

                    </div>


                    <p class="text-sm text-white/45 leading-6 max-w-md mt-4">
                        একটি শক্তিশালী ও সমৃদ্ধ ভবিষ্যৎ গড়ার উদ্দেশ্যে
                        সদস্যদের সম্মিলিত সঞ্চয় ও বিনিয়োগের একটি নির্ভরযোগ্য
                        প্ল্যাটফর্ম।
                    </p>

                </div>


                <!-- Links -->

                <div class="lg:col-span-2">

                    <h4 class="text-sm font-bold text-[#d5b76a]">
                        দ্রুত লিংক
                    </h4>

                    <div class="mt-4 space-y-2 text-sm text-white/50">

                        <a href="#home" class="block hover:text-white transition">
                            হোম
                        </a>

                        <a href="#about" class="block hover:text-white transition">
                            আমাদের সম্পর্কে
                        </a>

                        <a href="#projects" class="block hover:text-white transition">
                            প্রকল্প
                        </a>

                        <a href="#gallery" class="block hover:text-white transition">
                            গ্যালারি
                        </a>

                    </div>

                </div>


                <!-- Information -->

                <div class="lg:col-span-2">

                    <h4 class="text-sm font-bold text-[#d5b76a]">
                        তথ্য
                    </h4>

                    <div class="mt-4 space-y-2 text-sm text-white/50">

                        <a href="#" class="block hover:text-white transition">
                            গোপনীয়তা নীতি
                        </a>

                        <a href="#" class="block hover:text-white transition">
                            শর্তাবলী
                        </a>

                        <a href="#" class="block hover:text-white transition">
                            সদস্যপদ
                        </a>

                        <a href="#contact" class="block hover:text-white transition">
                            যোগাযোগ
                        </a>

                    </div>

                </div>


                <!-- Contact -->

                <div class="lg:col-span-3">

                    <h4 class="text-sm font-bold text-[#d5b76a]">
                        যোগাযোগ
                    </h4>

                    <div class="mt-4 space-y-2 text-sm text-white/50">

                        <p>
                            ঢাকা, বাংলাদেশ
                        </p>

                        <p>
                            ০১৭০০-০০০০০০
                        </p>

                        <p>
                            info@dreamersassociation.com
                        </p>

                    </div>

                </div>

            </div>


            <div class="mt-9 pt-5 border-t border-white/10 flex flex-col sm:flex-row justify-between gap-3 text-xs text-white/30">

                <p>
                    © {{ date('Y') }} ড্রিমার্স অ্যাসোসিয়েশন। সর্বস্বত্ব সংরক্ষিত।
                </p>

                <p>
                    ভালোবাসায় তৈরি বাংলাদেশে
                </p>

            </div>

        </div>

    </footer>


    <!-- =========================================================
         MOBILE MENU SCRIPT
    ========================================================== -->

    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const button = document.getElementById('mobileMenuBtn');
            const menu = document.getElementById('mobileMenu');

            if (button && menu) {

                button.addEventListener('click', function () {

                    menu.classList.toggle('hidden');

                });

                menu.querySelectorAll('a').forEach(function (link) {

                    link.addEventListener('click', function () {
                        menu.classList.add('hidden');
                    });

                });

            }

        });

    </script>

</body>

</html>