@extends('layouts.member')

@section('title','Dashboard')
@section('page-title','Dashboard')

@section('content')
@php
    $shareEnabled=filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN);

    $currentDue=(float)($summary['current_due']??0);
    $currentPaid=(float)($summary['current_paid']??0);
    $currentOutstanding=(float)($summary['current_outstanding']??0);

    $paymentPercentage=$currentDue>0
        ?min(100,max(0,round(($currentPaid/$currentDue)*100)))
        :0;

    $profitLoss=(float)($summary['association_profit_loss']??0);
    $isProfit=$profitLoss>=0;
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#4680b7] to-[#145da0]  text-white shadow-sm">
                <i class="bi bi-grid-1x2-fill"></i>
            </div>

            <div>
                <h1 class="text-lg font-bold tracking-tight text-slate-800">
                    Member Dashboard
                </h1>

                <p class="mt-0.5 text-xs text-slate-500">
                    Welcome back,
                    <span class="font-semibold text-slate-700">
                        {{ $member->user?->name??'Member' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 font-mono text-xs font-semibold text-emerald-700">
                <i class="bi bi-person-badge"></i>
                {{ $member->member_code }}
            </span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Deposited --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        Total Deposited
                    </p>

                    <p class="mt-2 text-xl font-bold text-slate-800">
                        ৳{{ number_format($summary['total_deposited']??0,2) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>

            <div class="mt-4 space-y-2 border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-slate-400">Subscriptions</span>

                    <span class="font-semibold text-slate-600">
                        ৳{{ number_format($summary['subscription_deposited']??0,2) }}
                    </span>
                </div>

                @if($shareEnabled)
                    <div class="flex items-center justify-between gap-3 text-[11px]">
                        <span class="text-slate-400">Shares</span>

                        <span class="font-semibold text-slate-600">
                            ৳{{ number_format($summary['share_deposited']??0,2) }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Monthly Payable --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                        Monthly Payable
                    </p>

                    <p class="mt-2 text-xl font-bold text-slate-800">
                        ৳{{ number_format($summary['monthly_payable']??0,2) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                    <i class="bi bi-calendar2-check"></i>
                </div>
            </div>

            <div class="mt-4 border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-slate-400">Current Due</span>

                    <span class="font-semibold text-slate-600">
                        ৳{{ number_format($currentDue,2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Outstanding --}}
        <div class="rounded-lg border border-red-200 bg-red-50/30 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-red-500">
                        Outstanding
                    </p>

                    <p class="mt-2 text-xl font-bold text-red-600">
                        ৳{{ number_format($summary['total_outstanding']??0,2) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>

            <div class="mt-4 border-t border-red-100 pt-3">
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-red-400">Current Outstanding</span>

                    <span class="font-semibold text-red-600">
                        ৳{{ number_format($currentOutstanding,2) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Fine --}}
        <div class="rounded-lg border border-amber-200 bg-amber-50/30 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium uppercase tracking-wide text-amber-600">
                        Total Fine
                    </p>

                    <p class="mt-2 text-xl font-bold text-amber-700">
                        ৳{{ number_format($summary['total_fine']??0,2) }}
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>

            <div class="mt-4 border-t border-amber-100 pt-3">
                <div class="flex items-center justify-between gap-3 text-[11px]">
                    <span class="text-amber-500">Current Fine</span>

                    <span class="font-semibold text-amber-700">
                        ৳{{ number_format($summary['current_fine']??0,2) }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- Current Month + Shares --}}
    <div class="grid grid-cols-1 gap-5 {{ $shareEnabled?'xl:grid-cols-3':'' }}">

        {{-- Current Month --}}
        <div class="rounded-lg border border-slate-200 bg-white {{ $shareEnabled?'xl:col-span-2':'' }}">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="bi bi-calendar-month"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            Current Month Subscription
                        </h2>

                        <p class="text-[11px] text-slate-400">
                            {{ now()->format('F Y') }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('member.subscriptions') }}" class="inline-flex h-8 w-fit items-center gap-2 rounded-md border border-indigo-200 bg-indigo-50 px-3 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    View Details
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="p-5">

                {{-- Progress --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-slate-700">
                                Payment Progress
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-400">
                                ৳{{ number_format($currentPaid,2) }}
                                paid of
                                ৳{{ number_format($currentDue,2) }}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-xl font-bold {{ $paymentPercentage>=100?'text-emerald-600':'text-indigo-600' }}">
                                {{ $paymentPercentage }}%
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full transition-all duration-500 {{ $paymentPercentage>=100?'bg-emerald-500':'bg-indigo-500' }}" style="width:{{ $paymentPercentage }}%"></div>
                    </div>
                </div>

                {{-- Values --}}
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                            Due
                        </p>

                        <p class="mt-1 text-base font-bold text-slate-700">
                            ৳{{ number_format($currentDue,2) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
                        <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                            Paid
                        </p>

                        <p class="mt-1 text-base font-bold text-emerald-700">
                            ৳{{ number_format($currentPaid,2) }}
                        </p>
                    </div>

                    <div class="rounded-lg border {{ $currentOutstanding>0?'border-red-200 bg-red-50/40':'border-emerald-200 bg-emerald-50/40' }} p-4">
                        <p class="text-[10px] font-medium uppercase tracking-wide {{ $currentOutstanding>0?'text-red-500':'text-emerald-600' }}">
                            Remaining
                        </p>

                        <p class="mt-1 text-base font-bold {{ $currentOutstanding>0?'text-red-600':'text-emerald-700' }}">
                            ৳{{ number_format($currentOutstanding,2) }}
                        </p>
                    </div>
                </div>

                @if($currentOutstanding>0)
                    <div class="mt-4 flex flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50/50 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-3">
                            <i class="bi bi-info-circle mt-0.5 text-amber-600"></i>

                            <div>
                                <p class="text-xs font-semibold text-amber-800">
                                    Payment is still due
                                </p>

                                <p class="mt-0.5 text-[11px] text-amber-600">
                                    Complete your subscription payment from the subscription page.
                                </p>
                            </div>
                        </div>

                        <a href="{{ route('member.subscriptions') }}" class="inline-flex h-8 shrink-0 items-center justify-center rounded-md bg-amber-600 px-3 text-[11px] font-semibold text-white transition hover:bg-amber-700">
                            Pay Now
                        </a>
                    </div>
                @elseif($currentDue>0)
                    <div class="mt-4 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50/50 p-4">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <i class="bi bi-check-lg"></i>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-emerald-800">
                                Current month payment completed
                            </p>

                            <p class="mt-0.5 text-[11px] text-emerald-600">
                                No outstanding subscription amount remains.
                            </p>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- Shares --}}
        @if($shareEnabled)
            <div class="rounded-lg border border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                            <i class="bi bi-layers-fill"></i>
                        </div>

                        <div>
                            <h2 class="text-sm font-semibold text-slate-800">
                                My Shares
                            </h2>

                            <p class="text-[11px] text-slate-400">
                                Share ownership summary
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('member.shares') }}" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">
                        View
                    </a>
                </div>

                <div class="space-y-3 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4 text-center">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                                Active
                            </p>

                            <p class="mt-1 text-2xl font-bold text-emerald-700">
                                {{ $summary['active_shares']??0 }}
                            </p>
                        </div>

                        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4 text-center">
                            <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">
                                Pending
                            </p>

                            <p class="mt-1 text-2xl font-bold text-amber-700">
                                {{ $summary['pending_shares']??0 }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs text-slate-500">
                                Active Share Value
                            </span>

                            <span class="text-sm font-bold text-slate-800">
                                ৳{{ number_format($summary['active_share_value']??0,2) }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs text-slate-500">
                                Monthly Payable
                            </span>

                            <span class="text-sm font-bold text-indigo-600">
                                ৳{{ number_format($summary['monthly_payable']??0,2) }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('member.shares') }}" class="flex h-9 w-full items-center justify-center gap-2 rounded-md bg-indigo-600 text-xs font-semibold text-white transition hover:bg-indigo-700">
                        <i class="bi bi-plus-circle"></i>
                        Manage Shares
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Association Finance + Quick Access --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">

        {{-- Association Finance --}}
        <div class="rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Association Finance
                    </h2>

                    <p class="text-[11px] text-slate-400">
                        Current overall financial position
                    </p>
                </div>
            </div>

            <div class="p-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-arrow-down-circle text-emerald-600"></i>

                            <span class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                                Income
                            </span>
                        </div>

                        <p class="mt-2 text-base font-bold text-emerald-700">
                            ৳{{ number_format($summary['association_income']??0,2) }}
                        </p>
                    </div>

                    <div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-arrow-up-circle text-red-500"></i>

                            <span class="text-[10px] font-medium uppercase tracking-wide text-red-500">
                                Expense
                            </span>
                        </div>

                        <p class="mt-2 text-base font-bold text-red-600">
                            ৳{{ number_format($summary['association_expense']??0,2) }}
                        </p>
                    </div>

                    <div class="rounded-lg border {{ $isProfit?'border-indigo-200 bg-indigo-50/40':'border-red-200 bg-red-50/40' }} p-4">
                        <div class="flex items-center gap-2">
                            <i class="bi {{ $isProfit?'bi-graph-up-arrow text-indigo-600':'bi-graph-down-arrow text-red-500' }}"></i>

                            <span class="text-[10px] font-medium uppercase tracking-wide {{ $isProfit?'text-indigo-600':'text-red-500' }}">
                                {{ $isProfit?'Net Profit':'Net Loss' }}
                            </span>
                        </div>

                        <p class="mt-2 text-base font-bold {{ $isProfit?'text-indigo-700':'text-red-600' }}">
                            ৳{{ number_format(abs($profitLoss),2) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Access --}}
        <div class="rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Quick Access
                    </h2>

                    <p class="text-[11px] text-slate-400">
                        Frequently used member services
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3">
                <a href="{{ route('member.profile') }}" class="group flex flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 p-4 text-center transition hover:border-indigo-200 hover:bg-indigo-50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-100">
                        <i class="bi bi-person"></i>
                    </div>

                    <span class="text-xs font-semibold text-slate-600">
                        My Profile
                    </span>
                </a>

                <a href="{{ route('member.subscriptions') }}" class="group flex flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 p-4 text-center transition hover:border-sky-200 hover:bg-sky-50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600 transition group-hover:bg-sky-100">
                        <i class="bi bi-credit-card"></i>
                    </div>

                    <span class="text-xs font-semibold text-slate-600">
                        Subscription
                    </span>
                </a>

                @if($shareEnabled)
                    <a href="{{ route('member.shares') }}" class="group flex flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 p-4 text-center transition hover:border-violet-200 hover:bg-violet-50">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 transition group-hover:bg-violet-100">
                            <i class="bi bi-layers"></i>
                        </div>

                        <span class="text-xs font-semibold text-slate-600">
                            My Shares
                        </span>
                    </a>
                @endif

                <a href="{{ route('member.polls') }}" class="group flex flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 p-4 text-center transition hover:border-amber-200 hover:bg-amber-50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition group-hover:bg-amber-100">
                        <i class="bi bi-bar-chart"></i>
                    </div>

                    <span class="text-xs font-semibold text-slate-600">
                        Polls
                    </span>
                </a>

                <a href="{{ route('member.notices') }}" class="group flex flex-col items-center justify-center gap-2 rounded-lg border border-slate-200 p-4 text-center transition hover:border-emerald-200 hover:bg-emerald-50">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-100">
                        <i class="bi bi-megaphone"></i>
                    </div>

                    <span class="text-xs font-semibold text-slate-600">
                        Notices
                    </span>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection