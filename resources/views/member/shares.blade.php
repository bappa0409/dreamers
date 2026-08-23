@extends('layouts.member')

@section('title','My Shares')
@section('page-title','My Shares')

@section('content')

@php
    $currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-layers"></i>
            </div>

            <div>
                <h1 class="text-lg font-bold tracking-tight text-slate-800">
                    My Shares
                </h1>

                <p class="mt-0.5 text-xs text-slate-500">
                    View your association shares, purchase history and verification status.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                onclick="loadShares()"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>

            <button
                id="buyShareButton"
                type="button"
                onclick="openPurchaseModal()"
                class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-plus-circle"></i>
                Buy Share
            </button>
        </div>
    </div>

    {{-- Information --}}
    <div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
            <i class="bi bi-info-circle"></i>
        </div>

        <div>
            <p class="text-xs font-semibold text-indigo-800">
                Association Share Ownership
            </p>

            <p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
                Each purchase represents one association share. Additional shares may be purchased separately and every purchase remains pending until verified by the association.
            </p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Total Shares
                    </p>

                    <p id="totalShares" class="mt-2 text-xl font-bold text-slate-800">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-layers"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                        Active Shares
                    </p>

                    <p id="activeShares" class="mt-2 text-xl font-bold text-emerald-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">
                        Pending Purchases
                    </p>

                    <p id="pendingShares" class="mt-2 text-xl font-bold text-amber-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">
                        Active Share Value
                    </p>

                    <p id="activeShareValue" class="mt-2 text-xl font-bold text-violet-700">
                        {{ $currency }}0.00
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-7">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="shareSearch"
                        type="text"
                        placeholder="Search share no, method or transaction reference..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    id="shareStatusFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="rejected">Rejected</option>
                    <option value="transferred">Transferred</option>
                    <option value="retired">Retired</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="clearShareFilters()"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    Clear
                </button>
            </div>

        </div>
    </div>

    {{-- Share Table --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="bi bi-layers"></i>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Share Portfolio
                </h2>

                <p class="text-[11px] text-slate-400">
                    Your association share purchase and ownership history
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px]">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Share
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Payment Method
                        </th>

                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Reference
                        </th>

                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Acquired
                        </th>

                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="sharesTable" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="7" class="px-4 py-14 text-center">
                            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                            <p class="mt-3 text-xs text-slate-400">
                                Loading shares...
                            </p>
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

        <div id="sharePagination"></div>
    </div>
</div>

{{-- Share Details Modal --}}
<div
    id="shareDetailsModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

    <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">

            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-layers"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Share Details
                    </h2>

                    <p id="shareDetailNumber" class="font-mono text-[10px] text-slate-400">
                        -
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeShareDetails()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="shareDetailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
            <button
                type="button"
                onclick="closeShareDetails()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Purchase Modal --}}
<div
    id="purchaseModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

    <div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">

            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-plus-circle"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Buy New Share
                    </h2>

                    <p class="text-[11px] text-slate-400">
                        Purchase will remain pending until verified.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closePurchaseModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="purchaseForm" class="flex min-h-0 flex-1 flex-col">

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

                <div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-500">
                                Share Value
                            </p>

                            <p class="mt-1 text-[11px] text-indigo-600">
                                Fixed purchase value for one share
                            </p>
                        </div>

                        <p
                            id="defaultShareValue"
                            class="text-xl font-bold text-indigo-700">
                            {{ $currency }}0.00
                        </p>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Purchase Amount
                    </label>

                    <div class="relative">
                        <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                            {{ $currency }}
                        </span>

                        <input
                            id="purchaseAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            readonly
                            required
                            class="app-input !pl-8 bg-slate-50">
                    </div>

                    <p class="mt-1 text-[10px] text-slate-400">
                        One purchase creates one share. To purchase more shares, submit another purchase.
                    </p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Payment Method
                    </label>

                    <select
                        id="sharePaymentMethod"
                        required
                        class="app-input">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Transaction Reference
                    </label>

                    <input
                        id="shareReference"
                        type="text"
                        maxlength="255"
                        placeholder="Txn ID / bank reference"
                        class="app-input">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">
                        Notes
                    </label>

                    <textarea
                        id="shareNotes"
                        rows="3"
                        maxlength="3000"
                        placeholder="Optional note"
                        class="app-input resize-none"></textarea>
                </div>

                <div
                    id="shareError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                </div>

            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

                <button
                    type="button"
                    onclick="closePurchaseModal()"
                    class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="purchaseButton"
                    type="submit"
                    class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
                    Submit Purchase
                </button>

            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')

<script>
const shareCurrency=@json($currency);

let shareData=[];
let filteredShares=[];
let shareSettings={};

let currentSharePage=1;
const sharesPerPage=15;

let shareSearchTimer=null;

const shareSearch=document.getElementById('shareSearch');
const shareStatusFilter=document.getElementById('shareStatusFilter');

function shareMoney(value){
    return shareCurrency+
        Number(value??0).toLocaleString(
            'en-US',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        );
}

async function loadShares(){
    const table=document.getElementById(
        'sharesTable'
    );

    table.innerHTML=`
        <tr>
            <td colspan="7" class="px-4 py-14 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                <p class="mt-3 text-xs text-slate-400">
                    Loading shares...
                </p>
            </td>
        </tr>
    `;

    try{
        const response=await api(
            '/api/member/shares'
        );

        const data=response.data??{};
        const summary=data.summary??{};

        shareData=Array.isArray(data.shares)
            ?data.shares
            :[];

        shareSettings=data.settings??{};

        document.getElementById(
            'totalShares'
        ).textContent=Number(
            summary.total_shares??0
        );

        document.getElementById(
            'activeShares'
        ).textContent=Number(
            summary.active_shares??0
        );

        document.getElementById(
            'pendingShares'
        ).textContent=Number(
            summary.pending_shares??0
        );

        document.getElementById(
            'activeShareValue'
        ).textContent=shareMoney(
            summary.active_share_value
        );

        document.getElementById(
            'defaultShareValue'
        ).textContent=shareMoney(
            shareSettings.default_share_value
        );

        updateBuyShareButton();

        currentSharePage=1;

        applyShareFilters();

    }catch(error){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="px-4 py-14 text-center">

                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                        <i class="bi bi-exclamation-circle text-lg"></i>
                    </div>

                    <p class="mt-3 text-sm font-semibold text-red-600">
                        Failed to load shares
                    </p>

                    <p class="mt-1 text-xs text-red-400">
                        ${escapeShareHtml(
                            extractShareError(error)
                        )}
                    </p>

                    <button
                        type="button"
                        onclick="loadShares()"
                        class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                        <i class="bi bi-arrow-clockwise"></i>
                        Try Again
                    </button>

                </td>
            </tr>
        `;

        document.getElementById(
            'sharePagination'
        ).innerHTML='';
    }
}

function updateBuyShareButton(){
    const button=document.getElementById(
        'buyShareButton'
    );

    const enabled=
        shareSettings.share_enabled===true||
        shareSettings.share_enabled===1||
        shareSettings.share_enabled==='1';

    if(enabled){
        button.classList.remove('hidden');
    }else{
        button.classList.add('hidden');
    }
}

function applyShareFilters(){
    const search=shareSearch
        .value
        .trim()
        .toLowerCase();

    const status=
        shareStatusFilter.value;

    filteredShares=shareData.filter(
        share=>{
            if(
                status&&
                share.status!==status
            ){
                return false;
            }

            if(search){
                const haystack=[
                    share.share_no,
                    share.payment_method,
                    share.transaction_reference,
                    share.verification_note,
                    share.notes,
                    share.status
                ]
                .map(
                    value=>
                        String(value??'')
                            .toLowerCase()
                )
                .join(' ');

                if(
                    !haystack.includes(search)
                ){
                    return false;
                }
            }

            return true;
        }
    );

    const lastPage=Math.max(
        Math.ceil(
            filteredShares.length/
            sharesPerPage
        ),
        1
    );

    if(currentSharePage>lastPage){
        currentSharePage=lastPage;
    }

    renderShares();
    renderSharePagination();
}

function renderShares(){
    const table=document.getElementById(
        'sharesTable'
    );

    if(!filteredShares.length){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="px-4 py-16 text-center">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                        <i class="bi bi-layers text-xl"></i>
                    </div>

                    <p class="mt-4 text-sm font-semibold text-slate-700">
                        No shares found
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        No share records match the current filters.
                    </p>

                </td>
            </tr>
        `;

        return;
    }

    const start=
        (currentSharePage-1)*
        sharesPerPage;

    const shares=
        filteredShares.slice(
            start,
            start+sharesPerPage
        );

    table.innerHTML=shares
        .map(
            share=>`
                <tr class="transition hover:bg-slate-50/70">

                    <td class="px-4 py-3">
                        <div>
                            <p class="font-mono text-[10px] font-semibold text-indigo-600">
                                ${escapeShareHtml(
                                    share.share_no??'-'
                                )}
                            </p>

                            <p class="mt-1 text-[10px] text-slate-400">
                                Share #${Number(share.id)}
                            </p>
                        </div>
                    </td>

                    <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                        ${shareMoney(
                            share.purchase_amount
                        )}
                    </td>

                    <td class="px-4 py-3 text-xs text-slate-600">
                        ${escapeShareHtml(
                            titleShare(
                                share.payment_method
                            )
                        )}
                    </td>

                    <td class="px-4 py-3">
                        <p class="max-w-[200px] truncate font-mono text-[10px] text-slate-500">
                            ${escapeShareHtml(
                                share.transaction_reference??
                                '-'
                            )}
                        </p>
                    </td>

                    <td class="px-4 py-3 text-xs text-slate-500">
                        ${
                            share.acquired_date
                                ?formatShareDate(
                                    share.acquired_date
                                )
                                :'Pending'
                        }
                    </td>

                    <td class="px-4 py-3 text-center">
                        ${shareStatusBadge(
                            share.status
                        )}
                    </td>

                    <td class="px-4 py-3 text-right">
                        <button
                            type="button"
                            onclick="openShareDetails(${Number(share.id)})"
                            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                            <i class="bi bi-eye"></i>
                            View
                        </button>
                    </td>

                </tr>
            `
        )
        .join('');
}

function renderSharePagination(){
    const container=document.getElementById(
        'sharePagination'
    );

    const total=
        filteredShares.length;

    const lastPage=Math.max(
        Math.ceil(
            total/sharesPerPage
        ),
        1
    );

    if(total<=sharesPerPage){
        container.innerHTML='';
        return;
    }

    const from=
        ((currentSharePage-1)*
        sharesPerPage)+1;

    const to=Math.min(
        currentSharePage*
        sharesPerPage,
        total
    );

    container.innerHTML=`
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-[11px] text-slate-500">
                Showing
                <span class="font-semibold text-slate-700">
                    ${from}
                </span>
                –
                <span class="font-semibold text-slate-700">
                    ${to}
                </span>
                of
                <span class="font-semibold text-slate-700">
                    ${total}
                </span>
                shares
            </p>

            <div class="flex items-center gap-1">

                <button
                    type="button"
                    ${currentSharePage<=1?'disabled':''}
                    onclick="changeSharePage(${currentSharePage-1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    <i class="bi bi-chevron-left"></i>
                    Previous
                </button>

                <span class="px-3 text-[11px] font-medium text-slate-500">
                    ${currentSharePage} / ${lastPage}
                </span>

                <button
                    type="button"
                    ${currentSharePage>=lastPage?'disabled':''}
                    onclick="changeSharePage(${currentSharePage+1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    Next
                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>
        </div>
    `;
}

window.changeSharePage=function(page){
    const lastPage=Math.max(
        Math.ceil(
            filteredShares.length/
            sharesPerPage
        ),
        1
    );

    if(
        page<1||
        page>lastPage
    ){
        return;
    }

    currentSharePage=page;

    renderShares();
    renderSharePagination();
};

window.clearShareFilters=function(){
    shareSearch.value='';
    shareStatusFilter.value='';

    currentSharePage=1;

    applyShareFilters();
};

window.openShareDetails=function(id){
    const share=shareData.find(
        item=>
            Number(item.id)===
            Number(id)
    );

    if(!share){
        Toast.error(
            'Share record not found.'
        );

        return;
    }

    document.getElementById(
        'shareDetailNumber'
    ).textContent=
        share.share_no??'-';

    document.getElementById(
        'shareDetailsBody'
    ).innerHTML=`
        <div class="space-y-5">

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">

                ${shareSummaryCard(
                    'Purchase Amount',
                    shareMoney(
                        share.purchase_amount
                    ),
                    'bi-cash-stack',
                    'indigo'
                )}

                ${shareSummaryCard(
                    'Payment Method',
                    titleShare(
                        share.payment_method
                    ),
                    'bi-credit-card',
                    'sky'
                )}

                ${shareSummaryCard(
                    'Status',
                    titleShare(
                        share.status
                    ),
                    'bi-check2-circle',
                    statusTone(
                        share.status
                    )
                )}

            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">
                        Share Information
                    </h3>

                    <p class="text-[11px] text-slate-400">
                        Purchase, verification and ownership details
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2">

                    ${shareInformationItem(
                        'Share No.',
                        share.share_no
                    )}

                    ${shareInformationItem(
                        'Status',
                        shareStatusBadge(
                            share.status
                        ),
                        true
                    )}

                    ${shareInformationItem(
                        'Purchase Amount',
                        shareMoney(
                            share.purchase_amount
                        )
                    )}

                    ${shareInformationItem(
                        'Payment Method',
                        titleShare(
                            share.payment_method
                        )
                    )}

                    ${shareInformationItem(
                        'Transaction Reference',
                        share.transaction_reference??
                        '-'
                    )}

                    ${shareInformationItem(
                        'Acquired Date',
                        share.acquired_date
                            ?formatShareDate(
                                share.acquired_date
                            )
                            :'Pending'
                    )}

                    ${shareInformationItem(
                        'Verified At',
                        formatShareDateTime(
                            share.verified_at
                        )
                    )}

                    ${shareInformationItem(
                        'Verified By',
                        share.verifier?.name??
                        '-'
                    )}

                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">
                            Purchase Notes
                        </h3>
                    </div>

                    <div class="p-5">
                        <p class="whitespace-pre-line break-words text-xs leading-6 text-slate-600">
                            ${
                                share.notes
                                    ?escapeShareHtml(
                                        share.notes
                                    )
                                    :'No notes provided.'
                            }
                        </p>
                    </div>

                </div>

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">
                            Verification Note
                        </h3>
                    </div>

                    <div class="p-5">
                        <p class="whitespace-pre-line break-words text-xs leading-6 text-slate-600">
                            ${
                                share.verification_note
                                    ?escapeShareHtml(
                                        share.verification_note
                                    )
                                    :'No verification note.'
                            }
                        </p>
                    </div>

                </div>

            </div>

        </div>
    `;

    const modal=document.getElementById(
        'shareDetailsModal'
    );

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add(
        'overflow-hidden'
    );
};

window.closeShareDetails=function(){
    const modal=document.getElementById(
        'shareDetailsModal'
    );

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.body.classList.remove(
        'overflow-hidden'
    );
};

function shareSummaryCard(
    label,
    value,
    icon,
    tone='indigo'
){
    const styles={
        indigo:
            'bg-indigo-50 text-indigo-600',

        sky:
            'bg-sky-50 text-sky-600',

        emerald:
            'bg-emerald-50 text-emerald-600',

        amber:
            'bg-amber-50 text-amber-600',

        red:
            'bg-red-50 text-red-600',

        slate:
            'bg-slate-100 text-slate-600'
    };

    return`
        <div class="rounded-lg border border-slate-200 bg-white p-4">

            <div class="flex items-start justify-between gap-3">

                <div class="min-w-0">
                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                        ${escapeShareHtml(label)}
                    </p>

                    <p class="mt-2 truncate text-sm font-bold text-slate-700">
                        ${escapeShareHtml(value)}
                    </p>
                </div>

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${
                    styles[tone]??
                    styles.indigo
                }">
                    <i class="bi ${icon}"></i>
                </div>

            </div>
        </div>
    `;
}

function shareInformationItem(
    label,
    value,
    raw=false
){
    return`
        <div class="border-b border-slate-100 px-5 py-4 sm:border-r">

            <p class="text-[10px] uppercase tracking-wide text-slate-400">
                ${escapeShareHtml(label)}
            </p>

            <div class="mt-1.5 break-words text-xs font-semibold text-slate-700">
                ${
                    raw
                        ?value
                        :escapeShareHtml(
                            value??'-'
                        )
                }
            </div>

        </div>
    `;
}

window.openPurchaseModal=function(){
    const enabled=
        shareSettings.share_enabled===true||
        shareSettings.share_enabled===1||
        shareSettings.share_enabled==='1';

    if(!enabled){
        Toast.error(
            'Share purchasing is currently disabled.'
        );

        return;
    }

    const amount=Number(
        shareSettings.default_share_value??0
    );

    if(amount<=0){
        Toast.error(
            'Share value has not been configured.'
        );

        return;
    }

    document.getElementById(
        'purchaseAmount'
    ).value=
        amount.toFixed(2);

    document.getElementById(
        'defaultShareValue'
    ).textContent=
        shareMoney(amount);

    document.getElementById(
        'sharePaymentMethod'
    ).value='cash';

    document.getElementById(
        'shareReference'
    ).value='';

    document.getElementById(
        'shareNotes'
    ).value='';

    document.getElementById(
        'shareError'
    ).classList.add(
        'hidden'
    );

    const modal=document.getElementById(
        'purchaseModal'
    );

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add(
        'overflow-hidden'
    );
};

window.closePurchaseModal=function(){
    const modal=document.getElementById(
        'purchaseModal'
    );

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.body.classList.remove(
        'overflow-hidden'
    );
};

document.getElementById(
    'purchaseForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const amount=Number(
            shareSettings.default_share_value??0
        );

        const paymentMethod=
            document.getElementById(
                'sharePaymentMethod'
            ).value;

        const reference=
            document.getElementById(
                'shareReference'
            ).value.trim();

        const notes=
            document.getElementById(
                'shareNotes'
            ).value.trim();

        const errorBox=
            document.getElementById(
                'shareError'
            );

        const button=
            document.getElementById(
                'purchaseButton'
            );

        errorBox.classList.add(
            'hidden'
        );

        if(
            !Number.isFinite(amount)||
            amount<=0
        ){
            showShareError(
                'Configured share value is invalid.'
            );

            return;
        }

        AdminUI.setLoading(
            button,
            'Submitting...'
        );

        try{
            const response=await api(
                '/api/member/shares',
                {
                    method:'POST',
                    body:JSON.stringify({
                        purchase_amount:
                            amount,

                        payment_method:
                            paymentMethod,

                        transaction_reference:
                            reference||null,

                        notes:
                            notes||null
                    })
                }
            );

            Toast.success(
                response.message??
                'Share purchase submitted successfully.'
            );

            closePurchaseModal();

            await loadShares();

        }catch(error){
            showShareError(
                extractShareError(
                    error
                )
            );

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

function showShareError(message){
    const errorBox=
        document.getElementById(
            'shareError'
        );

    errorBox.textContent=message;

    errorBox.classList.remove(
        'hidden'
    );
}

function shareStatusBadge(status){
    const classes={
        active:
            'border-emerald-200 bg-emerald-50 text-emerald-700',

        pending:
            'border-amber-200 bg-amber-50 text-amber-700',

        rejected:
            'border-red-200 bg-red-50 text-red-600',

        cancelled:
            'border-slate-200 bg-slate-50 text-slate-600',

        transferred:
            'border-indigo-200 bg-indigo-50 text-indigo-700',

        retired:
            'border-slate-200 bg-slate-50 text-slate-600'
    };

    return`
        <span class="inline-flex rounded-md border px-2.5 py-1 text-[10px] font-semibold ${
            classes[status]??
            'border-slate-200 bg-slate-50 text-slate-600'
        }">

            ${escapeShareHtml(
                titleShare(status)
            )}

        </span>
    `;
}

function statusTone(status){
    return{
        active:'emerald',
        pending:'amber',
        rejected:'red',
        cancelled:'slate',
        transferred:'indigo',
        retired:'slate'
    }[status]??'indigo';
}

function titleShare(value){
    if(!value){
        return'-';
    }

    return String(value)
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}

function formatShareDate(value){
    if(!value){
        return'-';
    }

    const date=new Date(
        String(value).length===10
            ?`${value}T00:00:00`
            :value
    );

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return value;
    }

    return date.toLocaleDateString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric'
        }
    );
}

function formatShareDateTime(value){
    if(!value){
        return'-';
    }

    const date=new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return value;
    }

    return date.toLocaleString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric',
            hour:'2-digit',
            minute:'2-digit'
        }
    );
}

function extractShareError(error){
    if(error?.data?.errors){
        const errors=
            Object.values(
                error.data.errors
            ).flat();

        if(errors.length){
            return errors[0];
        }
    }

    return error?.data?.message??
        error?.message??
        'Something went wrong.';
}

function escapeShareHtml(value){
    const div=document.createElement(
        'div'
    );

    div.textContent=String(
        value??''
    );

    return div.innerHTML;
}

shareSearch.addEventListener(
    'input',
    ()=>{
        clearTimeout(
            shareSearchTimer
        );

        shareSearchTimer=setTimeout(
            ()=>{
                currentSharePage=1;
                applyShareFilters();
            },
            350
        );
    }
);

shareStatusFilter.addEventListener(
    'change',
    ()=>{
        currentSharePage=1;
        applyShareFilters();
    }
);

document.getElementById(
    'shareDetailsModal'
).addEventListener(
    'click',
    event=>{
        if(
            event.target===
            event.currentTarget
        ){
            closeShareDetails();
        }
    }
);

document.getElementById(
    'purchaseModal'
).addEventListener(
    'click',
    event=>{
        if(
            event.target===
            event.currentTarget
        ){
            closePurchaseModal();
        }
    }
);

document.addEventListener(
    'keydown',
    event=>{
        if(event.key==='Escape'){
            closeShareDetails();
            closePurchaseModal();
        }
    }
);

document.addEventListener(
    'DOMContentLoaded',
    loadShares
);
</script>

@endpush