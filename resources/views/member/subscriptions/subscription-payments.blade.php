@extends('layouts.member')

@section('title','Payment History')
@section('page-title','Payment History')

@section('content')
@php
    $currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">Subscription Payments</h1>
                <p class="mt-0.5 text-sm text-slate-500">View submitted, verified and rejected subscription payments.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('member.subscriptions') }}"
               class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i class="bi bi-calendar2-check"></i>
                Monthly Dues
            </a>

            <button id="paymentRefreshButton" type="button" onclick="loadPayments(currentPage)"
                    class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Total</p>
                    <p id="summaryTotal" class="mt-2 text-xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">Pending</p>
                    <p id="summaryPending" class="mt-2 text-xl font-bold text-amber-700">0</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Verified</p>
                    <p id="summaryVerified" class="mt-2 text-xl font-bold text-emerald-700">0</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-red-500">Rejected</p>
                    <p id="summaryRejected" class="mt-2 text-xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-span-2 rounded-lg border border-sky-200 bg-sky-50/40 p-4 xl:col-span-1">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">Verified Amount</p>
                    <p id="summaryVerifiedAmount" class="mt-2 text-xl font-bold text-sky-700">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-5">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="paymentSearch" type="text"
                           placeholder="Payment no. or transaction reference..."
                           class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Year</label>
                <select id="paymentYear"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select id="paymentStatus"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Method</label>
                <select id="paymentMethodFilter"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="mobile_banking">Mobile Banking</option>
                    <option value="online">Online</option>
                </select>
            </div>

            <div class="lg:col-span-1">
                <button type="button" onclick="clearPaymentFilters()"
                        class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-slate-800">Payment History</h2>
                <p class="text-[11px] text-slate-400">Subscription payment submissions and verification status</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Payment No.</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Period</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Method</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Reference</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Submitted</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody id="paymentHistory" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="8" class="px-4 py-14 text-center text-sm text-slate-400">
                            Loading payment history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paymentPagination"></div>
    </div>
</div>

{{-- Details Modal --}}
<div id="paymentDetailsModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
    <div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Payment Details</h3>
                    <p class="text-sm text-slate-400">Subscription payment information</p>
                </div>
            </div>

            <button type="button" onclick="closePaymentDetails()"
                    class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="paymentDetailsBody" class="max-h-[70vh] overflow-y-auto p-5"></div>

        <div class="flex justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
            <button type="button" onclick="closePaymentDetails()"
                    class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currencySymbol=@json($currency);
let payments=[];
let currentPage=1;
let lastPage=1;
let totalPayments=0;
let perPage=20;
let searchTimer=null;
let requestController=null;

const money=value=>currencySymbol+Number(value??0).toLocaleString('en-US',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
});

function initPaymentYears(){
    const select=document.getElementById('paymentYear');
    const current=new Date().getFullYear();

    select.innerHTML='';

    for(let year=current;year>=current-5;year--){
        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${year}" ${year===current?'selected':''}>${year}</option>`
        );
    }
}

function buildPaymentParams(page=1){
    const params=new URLSearchParams({
        year:document.getElementById('paymentYear').value,
        page:String(page),
        per_page:String(perPage)
    });

    const search=document.getElementById('paymentSearch').value.trim();
    const status=document.getElementById('paymentStatus').value;
    const method=document.getElementById('paymentMethodFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);
    if(method)params.set('payment_method',method);

    return params;
}

async function loadPayments(page=1){
    currentPage=page;

    const tbody=document.getElementById('paymentHistory');

    tbody.innerHTML=`
        <tr>
            <td colspan="8" class="px-4 py-14 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-emerald-600"></div>
                <p class="mt-3 text-sm text-slate-400">Loading payment history...</p>
            </td>
        </tr>`;

    document.getElementById('paymentPagination').innerHTML='';

    try{
        const params=buildPaymentParams(page);

        const response=await api(
            `/api/member/subscriptions/payments?${params.toString()}`
        );

        const paginator=response.data??{};

        payments=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        totalPayments=Number(paginator.total??payments.length);
        perPage=Number(paginator.per_page??20);

        renderPaymentSummary(response.summary??{});
        renderPayments();
        renderPagination();

    }catch(error){
        tbody.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-14 text-center">
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-50 text-red-500">
                        <i class="bi bi-exclamation-circle"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-red-600">
                        Failed to load payment history
                    </p>
                    <p class="mt-1 text-[10px] text-red-400">
                        ${escapeHtml(extractError(error))}
                    </p>
                    <button type="button" onclick="loadPayments(${currentPage})"
                            class="mt-3 text-[10px] font-semibold text-emerald-600">
                        Try Again
                    </button>
                </td>
            </tr>`;
    }
}

function renderPaymentSummary(summary){
    document.getElementById('summaryTotal').textContent=Number(summary.total??0);
    document.getElementById('summaryPending').textContent=Number(summary.pending??0);
    document.getElementById('summaryVerified').textContent=Number(summary.verified??0);
    document.getElementById('summaryRejected').textContent=Number(summary.rejected??0);
    document.getElementById('summaryVerifiedAmount').textContent=money(summary.verified_amount);
}

function renderPayments(){
    const tbody=document.getElementById('paymentHistory');

    if(!payments.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-receipt"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-600">No payments found</p>
                    <p class="mt-1 text-[10px] text-slate-400">No payment matches the current filters.</p>
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML=payments.map(payment=>`
        <tr class="transition hover:bg-slate-50">
            <td class="whitespace-nowrap px-4 py-3">
                <span class="font-mono text-sm font-semibold text-indigo-600">
                    ${escapeHtml(payment.payment_no??'-')}
                </span>
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                ${payment.due?`${monthName(payment.due.month)} ${payment.due.year}`:'-'}
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-700">
                ${money(payment.amount)}
            </td>

            <td class="whitespace-nowrap px-4 py-3">
                ${paymentMethodBadge(payment.payment_method)}
            </td>

            <td class="max-w-[180px] px-4 py-3">
                <p class="truncate text-sm text-slate-500"
                   title="${escapeAttribute(payment.transaction_reference??'')}">
                    ${escapeHtml(payment.transaction_reference??'-')}
                </p>
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">
                ${formatDateTime(payment.paid_at)}
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-center">
                ${paymentStatusBadge(payment.status)}
            </td>

            <td class="whitespace-nowrap px-4 py-3 text-right">
                <button type="button" onclick="viewPayment(${Number(payment.id)})"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="bi bi-eye"></i>
                    View
                </button>
            </td>
        </tr>
    `).join('');
}

function renderPagination(){
    const container=document.getElementById('paymentPagination');

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    const from=totalPayments===0
        ?0
        :((currentPage-1)*perPage)+1;

    const to=Math.min(
        currentPage*perPage,
        totalPayments
    );

    container.innerHTML=`
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-[11px] text-slate-500">
                Showing
                <span class="font-semibold text-slate-700">${from}</span>
                –
                <span class="font-semibold text-slate-700">${to}</span>
                of
                <span class="font-semibold text-slate-700">${totalPayments}</span>
                payments
            </p>

            <div class="flex items-center gap-1">
                <button type="button"
                        ${currentPage<=1?'disabled':''}
                        onclick="loadPayments(${currentPage-1})"
                        class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <i class="bi bi-chevron-left"></i>
                    Previous
                </button>

                <span class="px-3 text-[11px] font-medium text-slate-500">
                    ${currentPage} / ${lastPage}
                </span>

                <button type="button"
                        ${currentPage>=lastPage?'disabled':''}
                        onclick="loadPayments(${currentPage+1})"
                        class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    Next
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>`;
}

window.viewPayment=function(id){
    const payment=payments.find(
        item=>Number(item.id)===Number(id)
    );

    if(!payment)return;

    const period=payment.due
        ?`${monthName(payment.due.month)} ${payment.due.year}`
        :'-';

    document.getElementById('paymentDetailsBody').innerHTML=`
        <div class="space-y-4">
            <div class="rounded-lg bg-gradient-to-br from-emerald-600 to-teal-600 p-5 text-white">
                <p class="text-[10px] uppercase tracking-wide text-emerald-100">
                    Payment Amount
                </p>
                <p class="mt-2 text-2xl font-bold">
                    ${money(payment.amount)}
                </p>
                <div class="mt-3">
                    ${paymentStatusBadge(payment.status)}
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200">
                ${detailRow('Payment No.',payment.payment_no??'-')}
                ${detailRow('Period',period)}
                ${detailRow('Payment Method',titleCase(payment.payment_method))}
                ${detailRow('Transaction Reference',payment.transaction_reference??'-')}
                ${detailRow('Submitted At',formatDateTime(payment.paid_at))}
                ${detailRow('Verified At',formatDateTime(payment.verified_at))}
                ${detailRow('Status',titleCase(payment.status))}
            </div>

            ${
                payment.verification_note
                    ?`
                        <div class="rounded-lg border ${
                            payment.status==='rejected'
                                ?'border-red-200 bg-red-50'
                                :'border-slate-200 bg-slate-50'
                        } p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                Verification Note
                            </p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-5 ${
                                payment.status==='rejected'
                                    ?'text-red-700'
                                    :'text-slate-600'
                            }">
                                ${escapeHtml(payment.verification_note)}
                            </p>
                        </div>
                    `
                    :''
            }
        </div>`;

    const modal=document.getElementById('paymentDetailsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
};

window.closePaymentDetails=function(){
    const modal=document.getElementById('paymentDetailsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
};

window.clearPaymentFilters=function(){
    document.getElementById('paymentSearch').value='';
    document.getElementById('paymentStatus').value='';
    document.getElementById('paymentMethodFilter').value='';
    loadPayments(1);
};

function detailRow(label,value){
    return`
        <div class="flex flex-col gap-1 border-b border-slate-100 px-4 py-3 last:border-0 sm:flex-row sm:items-center sm:justify-between">
            <span class="text-[11px] text-slate-500">${escapeHtml(label)}</span>
            <span class="text-sm font-semibold text-slate-700">${escapeHtml(value??'-')}</span>
        </div>`;
}

function paymentStatusBadge(status){
    const classes={
        pending:'border-amber-200 bg-amber-50 text-amber-700',
        verified:'border-emerald-200 bg-emerald-50 text-emerald-700',
        rejected:'border-red-200 bg-red-50 text-red-600'
    };

    return`
        <span class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold ${
            classes[status]??'border-slate-200 bg-slate-100 text-slate-600'
        }">
            ${escapeHtml(titleCase(status))}
        </span>`;
}

function paymentMethodBadge(method){
    const icons={
        cash:'bi-cash-stack',
        bank:'bi-bank',
        mobile_banking:'bi-phone',
        online:'bi-globe'
    };

    return`
        <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
            <i class="bi ${icons[method]??'bi-credit-card'} text-slate-400"></i>
            ${escapeHtml(titleCase(method))}
        </span>`;
}

function monthName(month){
    return new Date(
        2000,
        Number(month)-1,
        1
    ).toLocaleString('en-US',{
        month:'long'
    });
}

function formatDateTime(value){
    if(!value)return'-';

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
        return String(value);
    }

    return date.toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function titleCase(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function extractError(error){
    if(error?.data?.errors){
        const errors=Object.values(error.data.errors).flat();
        if(errors.length)return errors[0];
    }

    return error?.data?.message??
        error?.message??
        'Something went wrong.';
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

function escapeAttribute(value){
    return String(value??'')
        .replaceAll('&','&amp;')
        .replaceAll('"','&quot;')
        .replaceAll("'",'&#039;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;');
}

document.getElementById('paymentSearch').addEventListener('input',()=>{
    clearTimeout(searchTimer);
    searchTimer=setTimeout(
        ()=>loadPayments(1),
        350
    );
});

document.getElementById('paymentStatus').addEventListener(
    'change',
    ()=>loadPayments(1)
);

document.getElementById('paymentMethodFilter').addEventListener(
    'change',
    ()=>loadPayments(1)
);

document.getElementById('paymentYear').addEventListener(
    'change',
    ()=>loadPayments(1)
);

document.getElementById('paymentDetailsModal').addEventListener(
    'click',
    event=>{
        if(event.target===event.currentTarget){
            closePaymentDetails();
        }
    }
);

document.addEventListener('keydown',event=>{
    if(event.key==='Escape'){
        closePaymentDetails();
    }
});

document.addEventListener('DOMContentLoaded',()=>{
    initPaymentYears();
    loadPayments(1);
});
</script>
@endpush