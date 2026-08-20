@extends('layouts.public')

@section('title',setting('organization_name','Dreamers Association'))
@section('description','Dreamers Association — together we save, invest, build and grow.')

@section('content')
@php
$organizationName=setting('organization_name','Dreamers Association');
$organizationEmail=setting('organization_email','info@dreamersassociation.com');
$organizationPhone=setting('organization_phone','+880 1XXX-XXXXXX');
$organizationAddress=setting('organization_address','Bangladesh');
@endphp

<style>
html{scroll-behavior:smooth}
body{
    background:#ede7d3;
    background-image:radial-gradient(circle at 1px 1px,rgba(26,43,34,.055) 1px,transparent 0);
    background-size:22px 22px;
}
.landing-page{font-family:Inter,ui-sans-serif,system-ui,sans-serif;color:#1a2b22}
.landing-display{font-family:"Trebuchet MS",Inter,ui-sans-serif,sans-serif;letter-spacing:-.025em}
.landing-mono{font-family:"SFMono-Regular",Consolas,"Liberation Mono",monospace}
.ledger-rules{
    background-image:repeating-linear-gradient(to bottom,transparent 0 38px,rgba(21,83,70,.11) 38px 39px);
}
.stitch-v{
    background-image:repeating-linear-gradient(to bottom,transparent 0 8px,rgba(185,141,46,.65) 8px 9px);
}
.stitch-h{
    background-image:repeating-linear-gradient(to right,transparent 0 8px,rgba(185,141,46,.55) 8px 9px);
}
.book-binding{
    background-image:
        repeating-linear-gradient(to bottom,transparent 0 24px,rgba(11,59,52,.85) 24px 25px),
        linear-gradient(to right,#0b3b34,#0b3b34);
}
.entry-tab{writing-mode:vertical-rl;text-orientation:mixed}
.paper-shadow{box-shadow:0 1px 0 rgba(26,43,34,.05),0 18px 32px -24px rgba(11,59,52,.45)}
.notice-marquee{animation:noticeMarquee 30s linear infinite}
@keyframes noticeMarquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.reveal{opacity:0;transform:translateY(14px);transition:opacity .55s ease,transform .55s ease}
.reveal.visible{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){
    .reveal{opacity:1;transform:none;transition:none}
    .notice-marquee{animation:none}
    html{scroll-behavior:auto}
}
</style>

<div class="landing-page min-h-screen overflow-x-hidden bg-[#ede7d3]">

    {{-- Book binding --}}
    <div class="book-binding fixed bottom-0 left-0 top-0 z-40 hidden w-[9px] lg:block"></div>

    {{-- =========================================================
        HEADER
    ========================================================== --}}
    <header id="top" class="sticky top-0 z-50 border-b border-[#b98d2e]/40 bg-[#0b3b34]/95 text-[#ede7d3] shadow-sm backdrop-blur lg:pl-[9px]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <a href="#home" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 border-[#b98d2e] text-sm font-bold text-[#d9b45c]">
                        D
                    </div>

                    <div class="min-w-0">
                        <p class="landing-display truncate text-sm font-semibold tracking-wide text-[#ede7d3] sm:text-base">
                            Dreamers
                            <span class="text-[#d9b45c]">Association</span>
                        </p>
                        <p class="landing-mono text-[8px] uppercase tracking-[.18em] text-[#ede7d3]/45">
                            Together We Grow
                        </p>
                    </div>
                </a>

                <nav class="hidden items-center gap-6 lg:flex">
                    <a href="#about" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">About</a>
                    <a href="#purpose" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Purpose</a>
                    <a href="#membership" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Membership</a>
                    <a href="#finance" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Finance</a>
                    <a href="#projects" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Projects</a>
                    <a href="#transparency" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Transparency</a>
                    <a href="#contact" class="text-xs font-medium text-[#ede7d3]/75 transition hover:text-[#d9b45c]">Contact</a>
                </nav>

                <div class="flex items-center gap-2">
                    <a href="/login" class="hidden h-9 items-center justify-center rounded-full bg-[#b98d2e] px-4 text-xs font-semibold text-[#0b3b34] transition hover:bg-[#d9b45c] sm:inline-flex">
                        Member Login
                    </a>

                    <button id="menuButton" type="button" class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#ede7d3]/20 text-[#ede7d3] lg:hidden">
                        <i id="menuIcon" class="bi bi-list text-lg"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobileMenu" class="hidden border-t border-[#b98d2e]/20 bg-[#0b3b34] lg:hidden">
            <nav class="mx-auto grid max-w-7xl gap-1 px-4 py-3">
                <a href="#about" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">About</a>
                <a href="#purpose" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Purpose</a>
                <a href="#membership" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Membership</a>
                <a href="#finance" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Finance</a>
                <a href="#projects" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Projects</a>
                <a href="#transparency" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Transparency</a>
                <a href="#contact" class="mobile-link rounded-md px-3 py-2.5 text-xs font-medium text-[#ede7d3]/75 hover:bg-white/5">Contact</a>

                <a href="/login" class="mt-2 flex h-10 items-center justify-center rounded-full bg-[#b98d2e] text-xs font-semibold text-[#0b3b34] sm:hidden">
                    Member Login
                </a>
            </nav>
        </div>
    </header>

    <main class="lg:pl-[9px]">

        {{-- =========================================================
            HERO / PASSBOOK COVER
        ========================================================== --}}
        <section id="home" class="relative overflow-hidden bg-[#0b3b34] text-[#ede7d3]">

            <div class="absolute inset-0 opacity-[.07]" style="background-image:radial-gradient(circle at 2px 2px,#efdFAe 1.3px,transparent 0);background-size:27px 27px;"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="grid items-center gap-12 lg:grid-cols-5">

                    <div class="lg:col-span-3">
                        <div class="mb-4 inline-flex items-center gap-2 rounded-full border border-[#b98d2e]/40 px-3 py-1.5">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d9b45c]"></span>
                            <span class="landing-mono text-[9px] uppercase tracking-[.17em] text-[#d9b45c]">
                                Member Owned Association
                            </span>
                        </div>

                        <p class="mb-2 text-sm font-medium text-[#d9b45c]">
                            স্বপ্ন থেকে সঞ্চয়, সঞ্চয় থেকে সম্পদ
                        </p>

                        <h1 class="landing-display max-w-2xl text-3xl font-semibold leading-[1.15] text-[#ede7d3] sm:text-4xl lg:text-[42px]">
                            A shared ledger for a
                            <span class="italic text-[#d9b45c]">
                                shared future.
                            </span>
                        </h1>

                        <p class="mt-5 max-w-xl text-[13px] leading-7 text-[#ede7d3]/70 sm:text-sm">
                            Dreamers Association brings members together through regular contribution, responsible investment, transparent accounting and long-term community development.
                        </p>

                        <div class="mt-7 flex flex-wrap gap-3">
                            <a href="#about" class="inline-flex h-10 items-center gap-2 rounded-full bg-[#b98d2e] px-5 text-xs font-semibold text-[#0b3b34] transition hover:bg-[#d9b45c]">
                                Read Our Story
                                <i class="bi bi-arrow-right"></i>
                            </a>

                            <a href="/login" class="inline-flex h-10 items-center gap-2 rounded-full border border-[#ede7d3]/30 px-5 text-xs font-medium text-[#ede7d3] transition hover:border-[#d9b45c] hover:text-[#d9b45c]">
                                <i class="bi bi-person"></i>
                                Member Portal
                            </a>
                        </div>
                    </div>

                    {{-- Seal --}}
                    <div class="flex justify-center lg:col-span-2 lg:justify-end">
                        <div class="relative flex h-52 w-52 items-center justify-center rounded-full border-[3px] border-[#b98d2e] p-5 text-center sm:h-56 sm:w-56">
                            <div class="absolute inset-3 rounded-full border border-dashed border-[#d9b45c]/55"></div>

                            <div class="relative">
                                <p class="text-xs text-[#d9b45c]">
                                    ড্রিমার্স এসোসিয়েশন
                                </p>

                                <div class="mx-auto my-3 h-px w-14 bg-[#b98d2e]/60"></div>

                                <p class="landing-display text-xl font-semibold leading-6 text-[#d9b45c]">
                                    Dreamers<br>Association
                                </p>

                                <p class="landing-mono mt-3 text-[8px] uppercase tracking-[.18em] text-[#ede7d3]/50">
                                    Member Owned · Together
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Stats ledger --}}
            <div class="border-t border-[#b98d2e]/30">
                <div class="mx-auto grid max-w-7xl grid-cols-2 px-4 sm:px-6 md:grid-cols-4 lg:px-8">

                    <div class="border-b border-r border-[#b98d2e]/20 py-5 pr-4 md:border-b-0">
                        <p class="landing-mono text-xl font-semibold text-[#d9b45c] sm:text-2xl">500+</p>
                        <p class="mt-1 text-[9px] uppercase tracking-[.13em] text-[#ede7d3]/45">Members</p>
                    </div>

                    <div class="border-b border-[#b98d2e]/20 px-4 py-5 md:border-b-0 md:border-r">
                        <p class="landing-mono text-xl font-semibold text-[#d9b45c] sm:text-2xl">20+</p>
                        <p class="mt-1 text-[9px] uppercase tracking-[.13em] text-[#ede7d3]/45">Projects</p>
                    </div>

                    <div class="border-r border-[#b98d2e]/20 px-4 py-5">
                        <p class="landing-mono text-xl font-semibold text-[#d9b45c] sm:text-2xl">10+</p>
                        <p class="mt-1 text-[9px] uppercase tracking-[.13em] text-[#ede7d3]/45">Investments</p>
                    </div>

                    <div class="py-5 pl-4">
                        <p class="landing-mono text-xl font-semibold text-[#d9b45c] sm:text-2xl">100%</p>
                        <p class="mt-1 text-[9px] uppercase tracking-[.13em] text-[#ede7d3]/45">Together</p>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            01 ABOUT
        ========================================================== --}}
        <section id="about">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 01 — ABOUT
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <div class="reveal">
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                Who we are
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold text-[#1a2b22] sm:text-[28px]">
                                About Dreamers Association
                            </h2>
                        </div>

                        <div class="mt-7 grid gap-6 md:grid-cols-2">
                            <p class="reveal text-[13px] leading-7 text-[#1a2b22]/70">
                                Dreamers Association is built around disciplined participation and a shared goal: members contribute together, plan together and create assets and opportunities that are difficult to build individually.
                            </p>

                            <p class="reveal text-[13px] leading-7 text-[#1a2b22]/70">
                                Every important financial activity is recorded, participation is organized, and members remain connected through a transparent digital system covering subscriptions, shares, projects, notices and association decisions.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            02 PURPOSE
        ========================================================== --}}
        <section id="purpose" class="border-y border-[#155346]/10 bg-[#e1d9be]/55">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 02 — PURPOSE
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <div class="grid gap-4 md:grid-cols-3">

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0b3b34] text-[#d9b45c]">
                                    <i class="bi bi-bullseye text-sm"></i>
                                </div>

                                <h3 class="landing-display mt-4 text-base font-semibold">Mission</h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Build a disciplined culture of cooperation, contribution and responsible investment.
                                </p>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0b3b34] text-[#d9b45c]">
                                    <i class="bi bi-eye text-sm"></i>
                                </div>

                                <h3 class="landing-display mt-4 text-base font-semibold">Vision</h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Build a sustainable member-owned asset base that creates long-term shared value.
                                </p>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#0b3b34] text-[#d9b45c]">
                                    <i class="bi bi-flag text-sm"></i>
                                </div>

                                <h3 class="landing-display mt-4 text-base font-semibold">Objectives</h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Grow consistently, invest carefully, maintain transparent accounts and involve members in important decisions.
                                </p>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            03 MEMBERSHIP
        ========================================================== --}}
        <section id="membership" class="bg-[#0b3b34] text-[#ede7d3]">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#d9b45c]/55">
                            ENTRY 03 — MEMBERSHIP
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <div class="reveal">
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#d9b45c]">
                                Member journey
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                Membership is simple and structured
                            </h2>

                            <p class="mt-2 max-w-xl text-xs leading-6 text-[#ede7d3]/60">
                                From joining the association to participating in its long-term growth.
                            </p>
                        </div>

                        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                            <div class="reveal border-t border-[#b98d2e]/35 pt-4">
                                <p class="landing-mono text-[10px] text-[#d9b45c]">01</p>
                                <h3 class="landing-display mt-2 text-sm font-semibold">Join</h3>
                                <p class="mt-2 text-xs leading-6 text-[#ede7d3]/60">
                                    Member information is registered and reviewed.
                                </p>
                            </div>

                            <div class="reveal border-t border-[#b98d2e]/35 pt-4">
                                <p class="landing-mono text-[10px] text-[#d9b45c]">02</p>
                                <h3 class="landing-display mt-2 text-sm font-semibold">Approval</h3>
                                <p class="mt-2 text-xs leading-6 text-[#ede7d3]/60">
                                    Membership becomes active after the required approval flow.
                                </p>
                            </div>

                            <div class="reveal border-t border-[#b98d2e]/35 pt-4">
                                <p class="landing-mono text-[10px] text-[#d9b45c]">03</p>
                                <h3 class="landing-display mt-2 text-sm font-semibold">Contribute</h3>
                                <p class="mt-2 text-xs leading-6 text-[#ede7d3]/60">
                                    Members participate through monthly subscriptions and applicable shares.
                                </p>
                            </div>

                            <div class="reveal border-t border-[#b98d2e]/35 pt-4">
                                <p class="landing-mono text-[10px] text-[#d9b45c]">04</p>
                                <h3 class="landing-display mt-2 text-sm font-semibold">Participate</h3>
                                <p class="mt-2 text-xs leading-6 text-[#ede7d3]/60">
                                    Follow notices, polls, projects and association activities.
                                </p>
                            </div>

                        </div>

                        <div class="mt-8">
                            <a href="/login" class="inline-flex h-10 items-center gap-2 rounded-full bg-[#b98d2e] px-5 text-xs font-semibold text-[#0b3b34] hover:bg-[#d9b45c]">
                                Member Portal
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            04 FINANCE / CONTRIBUTIONS
        ========================================================== --}}
        <section id="finance">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 04 — FINANCE
                        </span>
                    </aside>

                    <div class="grid gap-8 lg:col-span-11 lg:grid-cols-5">

                        <div class="lg:col-span-2">
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                Monthly participation
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                Small contributions. Shared progress.
                            </h2>

                            <p class="mt-4 text-[13px] leading-7 text-[#1a2b22]/68">
                                Members contribute according to the association's configured subscription structure. Payments, dues, fines and verified collections remain recorded against each member account.
                            </p>

                            <div class="mt-5 flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#155346]/10 text-[#155346]">
                                    <i class="bi bi-check2"></i>
                                </div>

                                <p class="text-xs leading-6 text-[#1a2b22]/65">
                                    Members can track payment history from their personal portal.
                                </p>
                            </div>

                            <div class="mt-3 flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#155346]/10 text-[#155346]">
                                    <i class="bi bi-check2"></i>
                                </div>

                                <p class="text-xs leading-6 text-[#1a2b22]/65">
                                    Verified transactions automatically synchronize with association accounts.
                                </p>
                            </div>
                        </div>

                        <div class="reveal paper-shadow overflow-hidden rounded-xl border border-[#155346]/15 bg-[#ede7d3] lg:col-span-3">

                            <div class="flex items-center justify-between bg-[#0b3b34] px-5 py-3 text-[#ede7d3]">
                                <p class="landing-display text-sm font-semibold">
                                    Member Financial Ledger
                                </p>

                                <span class="landing-mono text-[9px] text-[#d9b45c]">
                                    DIGITAL RECORD
                                </span>
                            </div>

                            <div class="ledger-rules p-5">
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/45">
                                            Subscription
                                        </p>
                                        <p class="mt-2 text-sm font-semibold">Monthly</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/45">
                                            Payment
                                        </p>
                                        <p class="mt-2 text-sm font-semibold text-[#155346]">Verified</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/45">
                                            Fine
                                        </p>
                                        <p class="mt-2 text-sm font-semibold text-[#9c3b2e]">Rule Based</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/45">
                                            Records
                                        </p>
                                        <p class="mt-2 text-sm font-semibold">Transparent</p>
                                    </div>

                                </div>

                                <div class="mt-6 border-t border-[#155346]/15 pt-4">
                                    <p class="text-xs leading-6 text-[#1a2b22]/60">
                                        Every verified member payment contributes to the association's accounting ledger while maintaining a complete payment history.
                                    </p>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            05 INVESTMENT
        ========================================================== --}}
        <section class="border-y border-[#155346]/10 bg-[#e1d9be]/55">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 05 — INVESTMENT
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <div>
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                Shared resources
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                Where collective funds can create value
                            </h2>
                        </div>

                        <div class="mt-7 grid gap-4 md:grid-cols-3">

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase tracking-wider text-[#155346]">
                                    Assets
                                </span>

                                <h3 class="landing-display mt-4 text-base font-semibold">
                                    Land & Property
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Long-term property and land opportunities managed as association assets.
                                </p>

                                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-[#155346]/10">
                                    <div class="h-full w-[72%] rounded-full bg-[#155346]"></div>
                                </div>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#b98d2e]/15 px-2 py-1 text-[8px] uppercase tracking-wider text-[#8b681f]">
                                    Investment
                                </span>

                                <h3 class="landing-display mt-4 text-base font-semibold">
                                    Business Opportunities
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Carefully evaluated investments focused on sustainable association returns.
                                </p>

                                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-[#155346]/10">
                                    <div class="h-full w-[51%] rounded-full bg-[#b98d2e]"></div>
                                </div>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase tracking-wider text-[#155346]">
                                    Reserve
                                </span>

                                <h3 class="landing-display mt-4 text-base font-semibold">
                                    Financial Reserve
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Maintaining appropriate liquidity helps protect association operations.
                                </p>

                                <div class="mt-5 h-1.5 overflow-hidden rounded-full bg-[#155346]/10">
                                    <div class="h-full w-[32%] rounded-full bg-[#155346]"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            06 LAND & ASSETS
        ========================================================== --}}
        <section>
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 06 — ASSETS
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <div>
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                Association assets
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                Assets built for the long term
                            </h2>
                        </div>

                        <div class="reveal paper-shadow mt-7 overflow-hidden rounded-xl border border-[#155346]/15 bg-[#ede7d3]">
                            <div class="grid md:grid-cols-5">

                                <div class="bg-[#0b3b34] p-6 text-[#ede7d3] md:col-span-2">
                                    <span class="landing-mono inline-flex rounded-full bg-[#b98d2e] px-2 py-1 text-[8px] uppercase tracking-wider text-[#0b3b34]">
                                        Association Asset
                                    </span>

                                    <h3 class="landing-display mt-4 text-lg font-semibold">
                                        Land & Property Portfolio
                                    </h3>

                                    <p class="mt-2 text-xs leading-6 text-[#ede7d3]/60">
                                        Association-owned land and property information can be maintained with documents, investment history and sale records.
                                    </p>

                                    <p class="landing-mono mt-7 text-[8px] uppercase tracking-wider text-[#ede7d3]/35">
                                        Documents · Investment · Ownership
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-5 p-6 md:col-span-3">
                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Tracking
                                        </p>
                                        <p class="mt-1 text-sm font-semibold">Digital</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Documents
                                        </p>
                                        <p class="mt-1 text-sm font-semibold">Centralized</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Investment
                                        </p>
                                        <p class="mt-1 text-sm font-semibold text-[#155346]">Recorded</p>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Status
                                        </p>
                                        <p class="mt-1 text-sm font-semibold">Managed</p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        {{-- =========================================================
            07 PROJECTS
        ========================================================== --}}
        <section id="projects" class="border-y border-[#155346]/10 bg-[#e1d9be]/55">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 07 — PROJECTS
                        </span>
                    </aside>

                    <div class="lg:col-span-11">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                    Building forward
                                </p>

                                <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                    Association Projects
                                </h2>
                            </div>

                            <div class="flex w-fit rounded-full border border-[#155346]/15 bg-[#ede7d3] p-1">
                                <button type="button" data-project-tab="current" class="project-tab rounded-full bg-[#0b3b34] px-4 py-2 text-[10px] font-semibold text-[#ede7d3]">
                                    Current
                                </button>

                                <button type="button" data-project-tab="future" class="project-tab rounded-full px-4 py-2 text-[10px] font-semibold text-[#1a2b22]/60">
                                    Future
                                </button>
                            </div>
                        </div>

                        <div id="projectCurrent" class="mt-7 grid gap-4 md:grid-cols-3">

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase text-[#155346]">
                                    Active
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    Community Development
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Projects designed to create long-term shared value for association members.
                                </p>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase text-[#155346]">
                                    Active
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    Digital Association
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Bringing membership, finance, approvals and communication into one platform.
                                </p>
                            </div>

                            <div class="reveal paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase text-[#155346]">
                                    Active
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    Investment Planning
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Evaluating opportunities through structured financial and approval processes.
                                </p>
                            </div>

                        </div>

                        <div id="projectFuture" class="mt-7 hidden grid gap-4 md:grid-cols-3">

                            <div class="paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#b98d2e]/15 px-2 py-1 text-[8px] uppercase text-[#8b681f]">
                                    Planning
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    New Community Assets
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Future association assets can be evaluated through member participation.
                                </p>
                            </div>

                            <div class="paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#b98d2e]/15 px-2 py-1 text-[8px] uppercase text-[#8b681f]">
                                    Proposed
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    Expanded Investments
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    New investment opportunities based on sustainable association growth.
                                </p>
                            </div>

                            <div class="paper-shadow rounded-xl border border-[#155346]/10 bg-[#ede7d3] p-5">
                                <span class="landing-mono rounded-full bg-[#b98d2e]/15 px-2 py-1 text-[8px] uppercase text-[#8b681f]">
                                    Future
                                </span>

                                <h3 class="landing-display mt-4 text-sm font-semibold">
                                    Member Services
                                </h3>

                                <p class="mt-2 text-xs leading-6 text-[#1a2b22]/65">
                                    Additional services can be introduced as association operations expand.
                                </p>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            08 TRANSPARENCY
        ========================================================== --}}
        <section id="transparency">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 08 — TRANSPARENCY
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                            Open records
                        </p>

                        <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                            Transparency is part of the system
                        </h2>

                        <p class="mt-2 max-w-xl text-xs leading-6 text-[#1a2b22]/60">
                            Financial activity is structured through accounts, journal entries and ledger-based reporting.
                        </p>

                        <div class="reveal paper-shadow mt-7 overflow-hidden rounded-xl border border-[#155346]/15 bg-[#ede7d3]">

                            <div class="flex items-center justify-between bg-[#0b3b34] px-5 py-3 text-[#ede7d3]">
                                <span class="landing-display text-sm font-semibold">
                                    Financial Reporting
                                </span>

                                <span class="landing-mono text-[8px] text-[#d9b45c]">
                                    ACCOUNTING SYSTEM
                                </span>
                            </div>

                            <div class="grid sm:grid-cols-3 sm:divide-x sm:divide-[#155346]/10">
                                <div class="border-b border-[#155346]/10 p-5 sm:border-b-0">
                                    <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                        Income
                                    </p>

                                    <p class="mt-2 text-base font-semibold text-[#155346]">
                                        Recorded
                                    </p>

                                    <p class="mt-1 text-[10px] text-[#1a2b22]/45">
                                        Subscription & operational income
                                    </p>
                                </div>

                                <div class="border-b border-[#155346]/10 p-5 sm:border-b-0">
                                    <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                        Expense
                                    </p>

                                    <p class="mt-2 text-base font-semibold text-[#9c3b2e]">
                                        Tracked
                                    </p>

                                    <p class="mt-1 text-[10px] text-[#1a2b22]/45">
                                        Approved operational spending
                                    </p>
                                </div>

                                <div class="p-5">
                                    <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                        Reports
                                    </p>

                                    <p class="mt-2 text-base font-semibold">
                                        Available
                                    </p>

                                    <p class="mt-1 text-[10px] text-[#1a2b22]/45">
                                        Ledger, trial balance & balance sheet
                                    </p>
                                </div>
                            </div>

                        </div>

                        <a href="/login" class="mt-5 inline-flex items-center gap-2 border-b border-[#155346]/30 pb-1 text-xs font-medium text-[#155346] hover:border-[#0b3b34] hover:text-[#0b3b34]">
                            View member financial information
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>
                </div>
            </div>
        </section>

        {{-- =========================================================
            09 NOTICE BOARD
        ========================================================== --}}
        <section class="border-y border-[#155346]/10 bg-[#e1d9be]/45">
            <div class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 09 — NOTICE
                        </span>
                    </aside>

                    <div class="lg:col-span-11">
                        <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                            Notice board
                        </p>

                        <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                            Association Updates
                        </h2>

                        <p class="mt-2 max-w-xl text-xs leading-6 text-[#1a2b22]/60">
                            Members receive detailed notices and announcements through the member portal.
                        </p>
                    </div>

                </div>
            </div>

            <div class="mt-7 overflow-hidden border-y border-[#155346]/10 bg-[#ede7d3]/70 py-6">
                <div class="notice-marquee flex w-max gap-4 px-4">

                    @for($copy=0;$copy<2;$copy++)
                        <div class="paper-shadow w-64 shrink-0 rounded-xl border border-[#155346]/12 bg-[#ede7d3] p-4">
                            <span class="landing-mono rounded-full bg-[#b98d2e]/15 px-2 py-1 text-[8px] uppercase text-[#8b681f]">
                                Notice
                            </span>

                            <h3 class="landing-display mt-3 text-sm font-semibold">
                                Monthly Association Updates
                            </h3>

                            <p class="mt-2 text-[11px] leading-5 text-[#1a2b22]/60">
                                Members can view all current notices from their personal portal.
                            </p>
                        </div>

                        <div class="paper-shadow w-64 shrink-0 rounded-xl border border-[#155346]/12 bg-[#ede7d3] p-4">
                            <span class="landing-mono rounded-full bg-[#155346]/10 px-2 py-1 text-[8px] uppercase text-[#155346]">
                                Finance
                            </span>

                            <h3 class="landing-display mt-3 text-sm font-semibold">
                                Subscription Information
                            </h3>

                            <p class="mt-2 text-[11px] leading-5 text-[#1a2b22]/60">
                                Monthly payment status and dues remain available through the portal.
                            </p>
                        </div>

                        <div class="paper-shadow w-64 shrink-0 rounded-xl border border-[#155346]/12 bg-[#ede7d3] p-4">
                            <span class="landing-mono rounded-full bg-[#9c3b2e]/10 px-2 py-1 text-[8px] uppercase text-[#9c3b2e]">
                                Governance
                            </span>

                            <h3 class="landing-display mt-3 text-sm font-semibold">
                                Member Decisions
                            </h3>

                            <p class="mt-2 text-[11px] leading-5 text-[#1a2b22]/60">
                                Polls and association decisions are published for eligible members.
                            </p>
                        </div>
                    @endfor

                </div>
            </div>

            <div class="mx-auto max-w-7xl px-4 py-6 text-right sm:px-6 lg:px-8">
                <a href="/login" class="text-xs font-medium text-[#155346] hover:text-[#0b3b34]">
                    Open Member Portal →
                </a>
            </div>
        </section>

        {{-- =========================================================
            CONTACT
        ========================================================== --}}
        <section id="contact">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="grid gap-7 lg:grid-cols-12">

                    <aside class="hidden justify-center lg:col-span-1 lg:flex">
                        <span class="entry-tab landing-mono text-[10px] tracking-[.16em] text-[#155346]/55">
                            ENTRY 10 — CONTACT
                        </span>
                    </aside>

                    <div class="grid gap-8 lg:col-span-11 lg:grid-cols-2">

                        <div>
                            <p class="landing-mono text-[9px] uppercase tracking-[.22em] text-[#155346]">
                                Contact
                            </p>

                            <h2 class="landing-display mt-2 text-2xl font-semibold sm:text-[28px]">
                                Get in touch with us
                            </h2>

                            <p class="mt-3 max-w-md text-xs leading-6 text-[#1a2b22]/60">
                                For association information, membership or general enquiries, contact Dreamers Association.
                            </p>

                            <div class="mt-7 inline-flex items-center gap-3 rounded-xl border border-dashed border-[#155346]/25 bg-[#ede7d3] px-4 py-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#0b3b34] text-[#d9b45c]">
                                    <i class="bi bi-person-check text-sm"></i>
                                </div>

                                <div>
                                    <p class="text-xs font-semibold">Existing Member?</p>
                                    <a href="/login" class="text-[10px] font-medium text-[#155346]">
                                        Login to member portal →
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="paper-shadow rounded-xl border border-[#155346]/15 bg-[#ede7d3]">
                            <div class="divide-y divide-[#155346]/10">

                                <a href="mailto:{{ $organizationEmail }}" class="flex items-center gap-4 p-4 transition hover:bg-[#e1d9be]/50">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#155346]/10 text-[#155346]">
                                        <i class="bi bi-envelope"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Email
                                        </p>

                                        <p class="mt-1 truncate text-xs font-semibold">
                                            {{ $organizationEmail }}
                                        </p>
                                    </div>
                                </a>

                                <a href="tel:{{ preg_replace('/\s+/','',$organizationPhone) }}" class="flex items-center gap-4 p-4 transition hover:bg-[#e1d9be]/50">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#155346]/10 text-[#155346]">
                                        <i class="bi bi-telephone"></i>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Phone
                                        </p>

                                        <p class="mt-1 text-xs font-semibold">
                                            {{ $organizationPhone }}
                                        </p>
                                    </div>
                                </a>

                                <div class="flex items-start gap-4 p-4">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#155346]/10 text-[#155346]">
                                        <i class="bi bi-geo-alt"></i>
                                    </div>

                                    <div>
                                        <p class="landing-mono text-[8px] uppercase tracking-wider text-[#1a2b22]/40">
                                            Address
                                        </p>

                                        <p class="mt-1 text-xs font-semibold leading-5">
                                            {{ $organizationAddress }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </main>

    {{-- =========================================================
        FOOTER
    ========================================================== --}}
    <footer class="border-t border-[#b98d2e]/30 bg-[#0b3b34] text-[#ede7d3] lg:pl-[9px]">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

            <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full border border-[#b98d2e] text-xs font-bold text-[#d9b45c]">
                        D
                    </div>

                    <div>
                        <p class="landing-display text-sm font-semibold">
                            {{ $organizationName }}
                        </p>

                        <p class="landing-mono text-[8px] uppercase tracking-[.16em] text-[#ede7d3]/35">
                            Together We Grow
                        </p>
                    </div>
                </div>

                <nav class="flex flex-wrap gap-x-5 gap-y-2">
                    <a href="#about" class="text-[10px] text-[#ede7d3]/50 hover:text-[#d9b45c]">About</a>
                    <a href="#membership" class="text-[10px] text-[#ede7d3]/50 hover:text-[#d9b45c]">Membership</a>
                    <a href="#projects" class="text-[10px] text-[#ede7d3]/50 hover:text-[#d9b45c]">Projects</a>
                    <a href="#contact" class="text-[10px] text-[#ede7d3]/50 hover:text-[#d9b45c]">Contact</a>
                    <a href="/login" class="text-[10px] text-[#ede7d3]/50 hover:text-[#d9b45c]">Login</a>
                </nav>

            </div>

            <div class="mt-6 flex flex-col gap-2 border-t border-[#ede7d3]/10 pt-5 sm:flex-row sm:justify-between">
                <p class="text-[9px] text-[#ede7d3]/30">
                    © {{ date('Y') }} {{ $organizationName }}. All rights reserved.
                </p>

                <p class="landing-mono text-[8px] uppercase tracking-wider text-[#d9b45c]/50">
                    Member Owned · Transparent · Together
                </p>
            </div>

        </div>
    </footer>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',()=>{
    const menuButton=document.getElementById('menuButton');
    const mobileMenu=document.getElementById('mobileMenu');
    const menuIcon=document.getElementById('menuIcon');

    menuButton?.addEventListener('click',()=>{
        mobileMenu.classList.toggle('hidden');

        menuIcon.className=mobileMenu.classList.contains('hidden')
            ?'bi bi-list text-lg'
            :'bi bi-x-lg';
    });

    document.querySelectorAll('.mobile-link').forEach(link=>{
        link.addEventListener('click',()=>{
            mobileMenu.classList.add('hidden');
            menuIcon.className='bi bi-list text-lg';
        });
    });

    document.querySelectorAll('a[href^="#"]').forEach(link=>{
        link.addEventListener('click',event=>{
            const selector=link.getAttribute('href');

            if(!selector||selector==='#'){
                return;
            }

            const target=document.querySelector(selector);

            if(!target){
                return;
            }

            event.preventDefault();

            const top=
                target.getBoundingClientRect().top+
                window.scrollY-
                72;

            window.scrollTo({
                top,
                behavior:'smooth'
            });
        });
    });

    const observer=new IntersectionObserver(entries=>{
        entries.forEach(entry=>{
            if(entry.isIntersecting){
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    },{
        threshold:.08
    });

    document.querySelectorAll('.reveal').forEach(element=>{
        observer.observe(element);
    });

    const projectTabs=document.querySelectorAll('.project-tab');
    const current=document.getElementById('projectCurrent');
    const future=document.getElementById('projectFuture');

    projectTabs.forEach(button=>{
        button.addEventListener('click',()=>{
            const tab=button.dataset.projectTab;

            projectTabs.forEach(item=>{
                item.classList.remove(
                    'bg-[#0b3b34]',
                    'text-[#ede7d3]'
                );

                item.classList.add(
                    'text-[#1a2b22]/60'
                );
            });

            button.classList.add(
                'bg-[#0b3b34]',
                'text-[#ede7d3]'
            );

            button.classList.remove(
                'text-[#1a2b22]/60'
            );

            if(tab==='future'){
                current.classList.add('hidden');
                future.classList.remove('hidden');
            }else{
                future.classList.add('hidden');
                current.classList.remove('hidden');
            }
        });
    });
});
</script>
@endpush