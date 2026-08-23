@extends('layouts.admin')

@section('title','Subscription Payments')
@section('page-title','Subscription Payments')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-slate-800">Subscription Payments</h1>
                <p class="mt-1 text-xs text-slate-500">Review, verify and reject member monthly subscription payments.</p>
            </div>
        </div>

        <button type="button" onclick="loadPayments(currentPage)"
            class="inline-flex h-9 w-fit cursor-pointer items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium text-slate-400">Total Payments</p>
                    <p id="statTotal" class="mt-1 text-xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium text-amber-600">Pending</p>
                    <p id="statPending" class="mt-1 text-xl font-bold text-amber-700">0</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium text-emerald-600">Verified</p>
                    <p id="statVerified" class="mt-1 text-xl font-bold text-emerald-700">0</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[11px] font-medium text-red-500">Rejected</p>
                    <p id="statRejected" class="mt-1 text-xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white">
        <div class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="md:col-span-2 xl:col-span-2">
                <label class="form-label">Search</label>
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="filterSearch"
                        type="text"
                        placeholder="Payment no, member, email..."
                        class="app-input !pl-9">
                </div>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select id="filterStatus" class="app-input">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div>
                <label class="form-label">Method</label>
                <select id="filterMethod" class="app-input">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="mobile_banking">Mobile Banking</option>
                    <option value="online">Online</option>
                </select>
            </div>

            <div>
                <label class="form-label">From</label>
                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="filterFrom"
                        type="text"
                        class="app-input js-date-picker !pl-9"
                        placeholder="Select date"
                        autocomplete="off">
                </div>
            </div>

            <div>
                <label class="form-label">To</label>
                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="filterTo"
                        type="text"
                        class="app-input js-date-picker !pl-9"
                        placeholder="Select date"
                        autocomplete="off">
                </div>
            </div>

            <div class="flex gap-2 md:col-span-2 xl:col-span-6">
                <button type="button" onclick="loadPayments(1)"
                    class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700">
                    <i class="bi bi-funnel"></i>
                    Apply Filter
                </button>

                <button type="button" onclick="resetFilters()"
                    class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reset
                </button>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Payment</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Member</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Period</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Amount</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Method</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Paid At</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody id="paymentTableBody" class="divide-y divide-slate-100 bg-white">
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-xs text-slate-400">Loading payments...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div id="paymentMobileGrid" class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 lg:hidden">
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs text-slate-400">
                Loading payments...
            </div>
        </div>

        <div id="paginationWrap" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Payment Details</h3>
                    <p class="text-xs text-slate-400">Subscription payment and accounting information.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('detailsModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsContent" class="min-h-0 flex-1 overflow-y-auto p-5">
            <p class="py-10 text-center text-xs text-slate-400">Loading...</p>
        </div>
    </div>
</div>

{{-- Verify Modal --}}
<div id="verifyModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Verify Payment</h3>
                    <p class="text-xs text-slate-400">Confirm received subscription payment.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('verifyModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="verifyForm" class="flex min-h-0 flex-1 flex-col" novalidate>
            <div class="space-y-4 overflow-y-auto p-5">
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                    <p class="text-xs text-emerald-700">You are about to verify:</p>
                    <p id="verifyPaymentInfo" class="mt-1 text-sm font-bold text-emerald-800">-</p>
                </div>

                <div>
                    <label class="form-label">Verification Note</label>
                    <textarea id="verifyNote"
                        rows="3"
                        maxlength="2000"
                        placeholder="Optional note..."
                        class="app-input resize-none"></textarea>
                    <p data-field-error="verifyNote" class="mt-1 hidden text-xs text-red-600"></p>
                </div>

                <div id="verifyError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('verifyModal')"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="verifyButton" type="submit"
                    class="h-9 cursor-pointer rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">
                    Verify Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Reject Payment</h3>
                    <p class="text-xs text-slate-400">Provide the reason for rejection.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('rejectModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="rejectForm" class="flex min-h-0 flex-1 flex-col" novalidate>
            <div class="space-y-4 overflow-y-auto p-5">
                <div class="rounded-md border border-red-200 bg-red-50 p-3">
                    <p class="text-xs text-red-600">Payment:</p>
                    <p id="rejectPaymentInfo" class="mt-1 text-sm font-bold text-red-700">-</p>
                </div>

                <div>
                    <label class="form-label">
                        Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejectReason"
                        rows="4"
                        maxlength="2000"
                        placeholder="Why is this payment being rejected?"
                        class="app-input resize-none"></textarea>
                    <p data-field-error="rejectReason" class="mt-1 hidden text-xs text-red-600"></p>
                </div>

                <div id="rejectError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('rejectModal')"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="rejectButton" type="submit"
                    class="h-9 cursor-pointer rounded-md bg-red-600 px-4 text-xs font-semibold text-white hover:bg-red-700 disabled:opacity-60">
                    Reject Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage=1;
let selectedPayment=null;
let currentPayments=[];

const currency=@json(setting('currency_symbol','৳'));
const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>Number(value??0).toLocaleString('en-BD',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
});

function formatPeriod(year,month){
    if(!year||!month)return'—';

    const d=new Date(
        Number(year),
        Number(month)-1,
        1
    );

    return d.toLocaleDateString('en-US',{
        month:'short',
        year:'numeric'
    });
}

function formatDateTime(value){
    if(!value)return'—';

    const d=new Date(value);

    if(Number.isNaN(d.getTime())){
        return value;
    }

    return d.toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function methodBadge(method){
    const icons={
        cash:'bi-cash',
        bank:'bi-bank',
        mobile_banking:'bi-phone',
        online:'bi-globe'
    };

    return`
        <span class="inline-flex items-center gap-1.5 text-xs text-slate-600">
            <i class="bi ${icons[method]??'bi-credit-card'}"></i>
            ${esc(AdminUI.titleCase(method))}
        </span>
    `;
}

function actionButtons(payment){
    const buttons=[
        `
        <button type="button"
            onclick="showPayment(${payment.id})"
            title="Details"
            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md border border-slate-200 text-slate-500 transition hover:bg-slate-100">
            <i class="bi bi-eye"></i>
        </button>
        `
    ];

    if(payment.status==='pending'){
        buttons.push(`
            <button type="button"
                onclick="openVerify(${payment.id})"
                title="Verify"
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100">
                <i class="bi bi-check-lg"></i>
            </button>
        `);

        buttons.push(`
            <button type="button"
                onclick="openReject(${payment.id})"
                title="Reject"
                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-500 transition hover:bg-red-100">
                <i class="bi bi-x-lg"></i>
            </button>
        `);
    }

    return buttons.join('');
}

async function loadPayments(page=1){
    currentPage=page;

    const tbody=$('paymentTableBody');
    const grid=$('paymentMobileGrid');

    tbody.innerHTML=AdminUI.loadingState(
        'Loading payments...',
        8
    );

    grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs text-slate-400">
            <div class="flex items-center justify-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading payments...
            </div>
        </div>
    `;

    const params=new URLSearchParams({
        page:String(page),
        per_page:'15'
    });

    const search=$('filterSearch').value.trim();
    const status=$('filterStatus').value;
    const method=$('filterMethod').value;
    const from=$('filterFrom').value;
    const to=$('filterTo').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);
    if(method)params.set('payment_method',method);
    if(from)params.set('from_date',from);
    if(to)params.set('to_date',to);

    try{
        const response=await api(
            `/api/subscription-payments?${params.toString()}`
        );

        const paginator=response.data??{};
        currentPayments=paginator.data??[];

        renderPayments(currentPayments);
        updateStats(response.summary??{});

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadPayments
        );

    }catch(error){
        const message=AdminUI.extractError(error);

        tbody.innerHTML=AdminUI.emptyState(
            message,
            8
        );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-xs text-red-600">
                ${esc(message)}
            </div>
        `;
    }
}

function renderPayments(payments){
    const tbody=$('paymentTableBody');
    const grid=$('paymentMobileGrid');

    if(!payments.length){
        tbody.innerHTML=AdminUI.emptyState(
            'No payments found.',
            8
        );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-receipt"></i>
                </div>

                <p class="mt-3 text-sm font-semibold text-slate-600">
                    No payments found
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    Try changing the filters.
                </p>
            </div>
        `;

        return;
    }

    tbody.innerHTML=payments.map(payment=>{
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};

        return`
            <tr class="transition hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3">
                    <button type="button" onclick="showPayment(${payment.id})" class="cursor-pointer text-left">
                        <p class="font-mono text-xs font-semibold text-indigo-600 hover:underline">
                            ${esc(payment.payment_no??'—')}
                        </p>
                        <p class="mt-0.5 text-[10px] text-slate-400">
                            #${payment.id}
                        </p>
                    </button>
                </td>

                <td class="px-4 py-3">
                    <p class="max-w-[180px] truncate text-xs font-semibold text-slate-700">
                        ${esc(user.name??'—')}
                    </p>
                    <p class="mt-0.5 font-mono text-[10px] text-slate-400">
                        ${esc(member.member_code??'—')}
                    </p>
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">
                    ${formatPeriod(due.year,due.month)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold text-slate-800">
                    ${currency}${money(payment.amount)}
                </td>

                <td class="whitespace-nowrap px-4 py-3">
                    ${methodBadge(payment.payment_method)}
                </td>

                <td class="whitespace-nowrap px-4 py-3">
                    ${AdminUI.statusBadge(payment.status)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                    ${formatDateTime(payment.paid_at)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-right">
                    <div class="inline-flex items-center gap-1">
                        ${actionButtons(payment)}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    grid.innerHTML=payments.map(payment=>{
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};

        return`
            <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-mono text-xs font-bold text-indigo-600">
                                ${esc(payment.payment_no??'—')}
                            </p>

                            <p class="mt-1 truncate text-sm font-semibold text-slate-700">
                                ${esc(user.name??'—')}
                            </p>

                            <p class="mt-0.5 font-mono text-[10px] text-slate-400">
                                ${esc(member.member_code??'—')}
                            </p>
                        </div>

                        ${AdminUI.statusBadge(payment.status)}
                    </div>
                </div>

                <div class="space-y-3 p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Amount
                            </p>
                            <p class="mt-1 text-sm font-bold text-slate-700">
                                ${currency}${money(payment.amount)}
                            </p>
                        </div>

                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Period
                            </p>
                            <p class="mt-1 text-sm font-bold text-slate-700">
                                ${formatPeriod(due.year,due.month)}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <p class="text-[10px] text-slate-400">Method</p>
                            <div class="mt-1">
                                ${methodBadge(payment.payment_method)}
                            </div>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Paid At</p>
                            <p class="mt-1 font-medium text-slate-600">
                                ${formatDateTime(payment.paid_at)}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-1 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${actionButtons(payment)}
                </div>
            </article>
        `;
    }).join('');
}

function updateStats(summary){
    $('statTotal').textContent=summary.total??0;
    $('statPending').textContent=summary.pending??0;
    $('statVerified').textContent=summary.verified??0;
    $('statRejected').textContent=summary.rejected??0;
}

async function showPayment(id){
    AdminUI.openModal('detailsModal');

    $('detailsContent').innerHTML=`
        <div class="py-12 text-center">
            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
        </div>
    `;

    try{
        const response=await api(
            `/api/subscription-payments/${id}`
        );

        const payment=response.data;
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};
        const transaction=payment.finance_transaction??null;

        $('detailsContent').innerHTML=`
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                ${detailCard('Payment No',payment.payment_no)}
                ${detailCard('Amount',`${currency}${money(payment.amount)}`)}
                ${detailCard('Status',AdminUI.titleCase(payment.status))}
                ${detailCard('Method',AdminUI.titleCase(payment.payment_method))}
            </div>

            <div class="mt-5 overflow-hidden rounded-md border border-slate-200">
                ${detailRow('Member',user.name??'—')}
                ${detailRow('Member Code',member.member_code??'—')}
                ${detailRow('Email',user.email??'—')}
                ${detailRow('Mobile',user.mobile??'—')}
                ${detailRow('Subscription Period',formatPeriod(due.year,due.month))}
                ${detailRow('Due Amount',`${currency}${money(due.amount)}`)}
                ${detailRow('Paid Against Due',`${currency}${money(due.paid_amount)}`)}
                ${detailRow('Transaction Reference',payment.transaction_reference??'—')}
                ${detailRow('Submitted At',formatDateTime(payment.paid_at))}
                ${detailRow('Verified By',payment.verifier?.name??'—')}
                ${detailRow('Verified At',formatDateTime(payment.verified_at))}
                ${detailRow('Verification Note',payment.verification_note??'—',true)}
            </div>

            ${transaction?journalHtml(transaction):''}

            ${payment.status==='pending'?`
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button"
                        onclick="AdminUI.closeModal('detailsModal');openReject(${payment.id})"
                        class="h-9 cursor-pointer rounded-md border border-red-200 bg-red-50 px-4 text-xs font-semibold text-red-600 hover:bg-red-100">
                        Reject
                    </button>

                    <button type="button"
                        onclick="AdminUI.closeModal('detailsModal');openVerify(${payment.id})"
                        class="h-9 cursor-pointer rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700">
                        Verify Payment
                    </button>
                </div>
            `:''}
        `;

    }catch(error){
        $('detailsContent').innerHTML=`
            <div class="py-10 text-center text-xs text-red-500">
                ${esc(AdminUI.extractError(error))}
            </div>
        `;
    }
}

function detailCard(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] uppercase tracking-wide text-slate-400">
                ${esc(label)}
            </p>
            <p class="mt-1 break-words text-xs font-bold text-slate-700">
                ${esc(value??'—')}
            </p>
        </div>
    `;
}

function detailRow(label,value,multiline=false){
    return`
        <div class="grid gap-1 border-b border-slate-100 px-4 py-3 last:border-b-0 sm:grid-cols-[160px_1fr]">
            <span class="text-xs text-slate-400">
                ${esc(label)}
            </span>

            <span class="text-xs font-semibold text-slate-700 ${multiline?'whitespace-pre-wrap':''}">
                ${esc(value??'—')}
            </span>
        </div>
    `;
}

function journalHtml(transaction){
    return`
        <div class="mt-5 overflow-hidden rounded-md border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Accounting Transaction
                        </h4>
                        <p class="mt-1 text-xs font-semibold text-emerald-700">
                            ${esc(transaction.transaction_no??`Transaction #${transaction.id}`)}
                        </p>
                    </div>

                    ${AdminUI.statusBadge(transaction.status??'posted')}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-xs">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-2 text-left text-slate-500">Account</th>
                            <th class="px-3 py-2 text-right text-slate-500">Debit</th>
                            <th class="px-3 py-2 text-right text-slate-500">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(transaction.entries||[]).map(entry=>`
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-2 text-slate-600">
                                    ${esc(entry.account?.code||'')} -
                                    ${esc(entry.account?.name||'')}
                                </td>

                                <td class="px-3 py-2 text-right font-medium text-slate-700">
                                    ${currency}${money(entry.debit)}
                                </td>

                                <td class="px-3 py-2 text-right font-medium text-slate-700">
                                    ${currency}${money(entry.credit)}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function findPayment(id){
    return currentPayments.find(
        payment=>
            Number(payment.id)===
            Number(id)
    );
}

window.openVerify=function(id){
    const payment=findPayment(id);

    if(!payment){
        Toast.error('Payment not found.');
        return;
    }

    selectedPayment=id;

    $('verifyPaymentInfo').textContent=
        `${payment.payment_no} • ${currency}${money(payment.amount)}`;

    $('verifyNote').value='';

    AdminUI.clearError('verifyError');
    AdminUI.clearFieldErrors('verifyForm');

    AdminUI.openModal('verifyModal');
};

window.openReject=function(id){
    const payment=findPayment(id);

    if(!payment){
        Toast.error('Payment not found.');
        return;
    }

    selectedPayment=id;

    $('rejectPaymentInfo').textContent=
        `${payment.payment_no} • ${currency}${money(payment.amount)}`;

    $('rejectReason').value='';

    AdminUI.clearError('rejectError');
    AdminUI.clearFieldErrors('rejectForm');

    AdminUI.openModal('rejectModal');
};

$('verifyForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!selectedPayment)return;

    AdminUI.clearError('verifyError');
    AdminUI.clearFieldErrors('verifyForm');

    const button=$('verifyButton');

    AdminUI.setLoading(
        button,
        'Verifying...'
    );

    try{
        const response=await api(
            `/api/subscription-payments/${selectedPayment}/verify`,
            {
                method:'POST',
                body:JSON.stringify({
                    note:$('verifyNote').value.trim()||null
                })
            }
        );

        AdminUI.closeModal('verifyModal');

        Toast.success(
            response.message??
            'Payment verified successfully.'
        );

        await loadPayments(currentPage);

    }catch(error){
        if(!AdminUI.showValidationErrors(
            'verifyForm',
            error,
            {
                note:'verifyNote'
            }
        )){
            AdminUI.showError(
                'verifyError',
                AdminUI.extractError(error)
            );
        }

    }finally{
        AdminUI.resetLoading(button);
    }
});

$('rejectForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!selectedPayment)return;

    AdminUI.clearError('rejectError');
    AdminUI.clearFieldErrors('rejectForm');

    if(!AdminUI.validateRequired(
        'rejectForm',
        {
            rejectReason:'Rejection reason is required.'
        }
    )){
        return;
    }

    const button=$('rejectButton');

    AdminUI.setLoading(
        button,
        'Rejecting...'
    );

    try{
        const response=await api(
            `/api/subscription-payments/${selectedPayment}/reject`,
            {
                method:'POST',
                body:JSON.stringify({
                    reason:$('rejectReason').value.trim()
                })
            }
        );

        AdminUI.closeModal('rejectModal');

        Toast.success(
            response.message??
            'Payment rejected successfully.'
        );

        await loadPayments(currentPage);

    }catch(error){
        if(!AdminUI.showValidationErrors(
            'rejectForm',
            error,
            {
                reason:'rejectReason'
            }
        )){
            AdminUI.showError(
                'rejectError',
                AdminUI.extractError(error)
            );
        }

    }finally{
        AdminUI.resetLoading(button);
    }
});

window.resetFilters=function(){
    $('filterSearch').value='';
    $('filterStatus').value='';
    $('filterMethod').value='';

    if($('filterFrom')._flatpickr){
        $('filterFrom')._flatpickr.clear();
    }else{
        $('filterFrom').value='';
    }

    if($('filterTo')._flatpickr){
        $('filterTo')._flatpickr.clear();
    }else{
        $('filterTo').value='';
    }

    loadPayments(1);
};

function init(){
    if(
        typeof AdminUI==='undefined'||
        typeof api==='undefined'
    ){
        setTimeout(init,50);
        return;
    }

    window.initDatePickers?.();

    AdminUI.bindFieldValidation('verifyForm');
    AdminUI.bindFieldValidation('rejectForm');

    $('filterSearch').addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadPayments(1)
        )
    );

    $('filterStatus').addEventListener(
        'change',
        ()=>loadPayments(1)
    );

    $('filterMethod').addEventListener(
        'change',
        ()=>loadPayments(1)
    );

    loadPayments(1);
}

document.readyState==='loading'
    ?document.addEventListener('DOMContentLoaded',init)
    :init();
</script>
@endpush