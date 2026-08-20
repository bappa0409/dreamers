@extends('layouts.member')

@section('title','Dashboard')
@section('page-title','Member Dashboard')

@section('content')
@php
    $currency=setting('currency_symbol','৳');
    $shareEnabled=filter_var(
        setting('share_enabled',false),
        FILTER_VALIDATE_BOOLEAN
    );
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800">
                Member Dashboard
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Your membership, subscriptions and association financial summary.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="rounded-md bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-600">
                {{ $member->member_code }}
            </span>

            <span class="rounded-md bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-700">
                {{ ucfirst($member->status) }}
            </span>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500">
                        Total Deposited
                    </p>

                    <p class="mt-2 truncate text-lg font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['total_deposited'],2) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500">
                        Monthly Payable
                    </p>

                    <p class="mt-2 truncate text-lg font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['monthly_payable'],2) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-calendar2-check"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-slate-500">
                        Current Due
                    </p>

                    <p class="mt-2 truncate text-lg font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['current_outstanding'],2) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/40 p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-red-600">
                        Outstanding
                    </p>

                    <p class="mt-2 truncate text-lg font-bold text-red-600">
                        {{ $currency }}{{ number_format($summary['total_outstanding'],2) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-medium text-amber-700">
                        Total Fine
                    </p>

                    <p class="mt-2 truncate text-lg font-bold text-amber-600">
                        {{ $currency }}{{ number_format($summary['total_fine'],2) }}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        @if($shareEnabled)
            <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-emerald-700">
                            Active Shares
                        </p>

                        <p class="mt-2 text-lg font-bold text-emerald-600">
                            {{ $summary['active_shares'] }}
                        </p>
                    </div>

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                        <i class="bi bi-layers"></i>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-medium text-slate-500">
                            Current Fine
                        </p>

                        <p class="mt-2 text-lg font-bold text-slate-800">
                            {{ $currency }}{{ number_format($summary['current_fine'],2) }}
                        </p>
                    </div>

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Current Subscription + Association Summary --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-calendar-check"></i>
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

                @if($summary['current_outstanding']<=0)
                    <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                        Paid
                    </span>
                @elseif($summary['current_paid']>0)
                    <span class="rounded-md bg-amber-50 px-2.5 py-1 text-[10px] font-semibold text-amber-700">
                        Partial
                    </span>
                @else
                    <span class="rounded-md bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600">
                        Unpaid
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-px bg-slate-200 md:grid-cols-4">
                <div class="bg-white p-4">
                    <p class="text-[11px] text-slate-400">
                        Total Due
                    </p>

                    <p class="mt-1 text-base font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['current_due'],2) }}
                    </p>
                </div>

                <div class="bg-white p-4">
                    <p class="text-[11px] text-slate-400">
                        Paid
                    </p>

                    <p class="mt-1 text-base font-bold text-emerald-600">
                        {{ $currency }}{{ number_format($summary['current_paid'],2) }}
                    </p>
                </div>

                <div class="bg-white p-4">
                    <p class="text-[11px] text-slate-400">
                        Fine
                    </p>

                    <p class="mt-1 text-base font-bold text-amber-600">
                        {{ $currency }}{{ number_format($summary['current_fine'],2) }}
                    </p>
                </div>

                <div class="bg-white p-4">
                    <p class="text-[11px] text-slate-400">
                        Remaining
                    </p>

                    <p class="mt-1 text-base font-bold text-red-600">
                        {{ $currency }}{{ number_format($summary['current_outstanding'],2) }}
                    </p>
                </div>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">
                            Monthly Contribution
                        </p>

                        <p class="mt-1 text-[11px] text-slate-400">
                            @if($shareEnabled)
                                Amount is calculated based on active shares.
                            @else
                                Standard monthly membership contribution.
                            @endif
                        </p>
                    </div>

                    <a href="{{ route('member.subscriptions') }}"
                       class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700">
                        <i class="bi bi-credit-card"></i>
                        View & Pay
                    </a>
                </div>
            </div>
        </div>

        {{-- Association P/L --}}
        <div class="rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">
                            Association Summary
                        </h2>

                        <p class="text-[11px] text-slate-400">
                            Overall financial position
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-3 p-5">
                <div class="flex items-center justify-between rounded-md bg-slate-50 px-4 py-3">
                    <span class="text-xs font-medium text-slate-500">
                        Total Income
                    </span>

                    <span class="text-sm font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['association_income'],2) }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-md bg-slate-50 px-4 py-3">
                    <span class="text-xs font-medium text-slate-500">
                        Total Expense
                    </span>

                    <span class="text-sm font-bold text-slate-800">
                        {{ $currency }}{{ number_format($summary['association_expense'],2) }}
                    </span>
                </div>

                <div class="rounded-md {{ $summary['association_profit_loss']>=0?'bg-emerald-50':'bg-red-50' }} px-4 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold {{ $summary['association_profit_loss']>=0?'text-emerald-700':'text-red-700' }}">
                                {{ $summary['association_profit_loss']>=0?'Net Profit':'Net Loss' }}
                            </p>

                            <p class="mt-1 text-lg font-bold {{ $summary['association_profit_loss']>=0?'text-emerald-600':'text-red-600' }}">
                                {{ $currency }}{{ number_format(abs($summary['association_profit_loss']),2) }}
                            </p>
                        </div>

                        <i class="bi {{ $summary['association_profit_loss']>=0?'bi-graph-up-arrow text-emerald-600':'bi-graph-down-arrow text-red-600' }} text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Membership --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white lg:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        My Membership
                    </h2>

                    <p class="mt-0.5 text-[11px] text-slate-400">
                        Your association membership information.
                    </p>
                </div>

                <span class="rounded-md bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
                    {{ ucfirst($member->status) }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-px bg-slate-200 sm:grid-cols-2">
                <div class="bg-white p-5">
                    <p class="text-[11px] text-slate-400">
                        Name
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $member->user?->name??'—' }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-[11px] text-slate-400">
                        Member Code
                    </p>

                    <p class="mt-1 font-mono text-sm font-semibold text-indigo-600">
                        {{ $member->member_code }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-[11px] text-slate-400">
                        Email
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-slate-700">
                        {{ $member->user?->email??'—' }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-[11px] text-slate-400">
                        Phone
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $member->phone??$member->user?->mobile??'—' }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-[11px] text-slate-400">
                        Joining Date
                    </p>

                    <p class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $member->joining_date?->format('d M, Y')??'—' }}
                    </p>
                </div>

                @if($shareEnabled)
                    <div class="bg-white p-5">
                        <p class="text-[11px] text-slate-400">
                            Share Ownership
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $summary['active_shares'] }}
                            {{ $summary['active_shares']===1?'Share':'Shares' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Access --}}
        <div class="rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800">
                    Quick Access
                </h2>

                <p class="mt-0.5 text-[11px] text-slate-400">
                    Common member services.
                </p>
            </div>

            <div class="space-y-2 p-4">
                <a href="{{ route('member.subscriptions') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-3 py-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:text-indigo-600">
                    <span class="flex items-center gap-2.5">
                        <i class="bi bi-credit-card"></i>
                        Monthly Subscription
                    </span>

                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>

                <a href="{{ route('member.polls') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-3 py-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:text-indigo-600">
                    <span class="flex items-center gap-2.5">
                        <i class="bi bi-bar-chart"></i>
                        Polls
                    </span>

                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>

                <a href="{{ route('member.notices') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-3 py-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:text-indigo-600">
                    <span class="flex items-center gap-2.5">
                        <i class="bi bi-megaphone"></i>
                        Notices
                    </span>

                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>

                <a href="{{ route('member.profile') }}"
                   class="flex items-center justify-between rounded-md border border-slate-200 px-3 py-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50/40 hover:text-indigo-600">
                    <span class="flex items-center gap-2.5">
                        <i class="bi bi-person"></i>
                        My Profile
                    </span>

                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection