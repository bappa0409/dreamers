@extends('layouts.admin')

@section('title','Subscription Payments')
@section('page_title','Subscription Payments')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">Subscription Payments</h1>
                <p class="text-sm text-slate-500">Review and verify member subscription payments.</p>
            </div>
        </div>

        <div class="flex w-fit shrink-0 items-center gap-2">
            <button type="button" onclick="loadPayments()" class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>

            @if(auth()->user()->hasPermission('Finance.create'))
                <button type="button" onclick="openAddPaymentModal()" class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                    <i class="bi bi-plus-lg"></i>
                    Add Payment
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total</p>
                    <p id="totalCount" class="mt-2 text-2xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-amber-700">Pending</p>
                    <p id="pendingCount" class="mt-2 text-2xl font-bold text-amber-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-700">Verified</p>
                    <p id="verifiedCount" class="mt-2 text-2xl font-bold text-emerald-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-700">Rejected</p>
                    <p id="rejectedCount" class="mt-2 text-2xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Payments</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by member, payment no or reference</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search member, ID, payment no, reference..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All Payments</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Payment</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Period</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Method</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="paymentTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">Loading payments...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Payment Details Modal --}}
<div id="paymentModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Payment Details</h2>
                    <p class="text-sm text-slate-500">Review the payment before verifying or rejecting.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('paymentModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            <div id="paymentDetails" class="grid grid-cols-1 gap-4 sm:grid-cols-2"></div>
        </div>

        <div id="paymentActions" class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end"></div>
    </div>
</div>

{{-- Reject Payment Modal --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Reject Payment</h2>
                    <p class="text-sm text-slate-500">Explain why this payment is being rejected.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('rejectModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="space-y-4 overflow-y-auto p-5">
            <div>
                <label class="form-label">Rejection Reason <span class="text-red-500">*</span></label>
                <textarea id="rejectReason" rows="4" maxlength="1000" class="app-input resize-none" placeholder="Enter rejection reason..."></textarea>
            </div>

            <div id="rejectFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" onclick="AdminUI.closeModal('rejectModal')" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </button>

            <button id="confirmRejectButton" type="button" onclick="confirmReject()" class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                Reject Payment
            </button>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<div id="addPaymentModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-plus-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Add Payment</h2>
                    <p class="text-sm text-slate-500">Record a payment received directly (e.g. cash) and verify it.</p>
                </div>
            </div>

            <button type="button" onclick="closeAddPaymentModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="addPaymentForm" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Member <span class="text-red-500">*</span></label>

                    <select id="paymentMemberSelect" class="app-input">
                        <option value="">Loading members...</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Outstanding Due <span class="text-red-500">*</span></label>

                    <select id="paymentDueSelect" class="app-input" disabled>
                        <option value="">Select a member first</option>
                    </select>

                    <p id="paymentDueHelp" class="mt-1.5 hidden text-[11px] text-amber-600">This member has no outstanding subscription dues.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>

                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">{{ setting('currency_symbol','৳') }}</span>
                            <input id="paymentAmount" type="number" step="0.01" min="0.01" class="app-input !pl-8" placeholder="0.00">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Payment Method <span class="text-red-500">*</span></label>

                        <select id="paymentMethod" class="app-input">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="mobile_banking">Mobile Banking</option>
                            <option value="online">Online</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Transaction Reference</label>
                        <input id="paymentReference" type="text" maxlength="255" class="app-input" placeholder="Optional">
                    </div>
                </div>

                <div>
                    <label class="form-label">Note</label>
                    <textarea id="paymentNote" rows="3" maxlength="2000" class="app-input resize-none" placeholder="Optional verification note..."></textarea>
                </div>

                <div id="addPaymentFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeAddPaymentModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveAddPaymentButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>
@endsection

@push('scripts')
<script>
let payments=[];
let currentPage=1;
let lastPage=1;
let total=0;
let selectedPayment=null;

const canVerifyPayment=@json(
    auth()->user()->hasPermission('Finance.update')
);

const el={
    table:document.getElementById('paymentTable'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter')
};

async function loadPayments(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState(
        'Loading payments...',
        7
    );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value,
        page
    });

    try{
        const response=await api(
            `/api/finance/subscription-payments?${query}`
        );

        const paginator=response.data??{};

        payments=Array.isArray(paginator.data)
            ?paginator.data
            :(Array.isArray(response.data)
                ?response.data
                :[]);

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??payments.length
        );

        renderPayments();

        document.getElementById(
            'totalCount'
        ).textContent=total;

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadPayments
        });

        await loadStatusCounts();
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            7
        );
    }
}

async function loadStatusCounts(){
    try{
        const [pending,verified,rejected]=await Promise.all([
            api('/api/finance/subscription-payments?status=pending&per_page=5'),
            api('/api/finance/subscription-payments?status=verified&per_page=5'),
            api('/api/finance/subscription-payments?status=rejected&per_page=5')
        ]);

        document.getElementById('pendingCount').textContent=
            pending.data.total??0;

        document.getElementById('verifiedCount').textContent=
            verified.data.total??0;

        document.getElementById('rejectedCount').textContent=
            rejected.data.total??0;
    }catch(error){
        console.error(error);
    }
}

function renderPayments(){
    if(!payments.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No subscription payments found.',
            7
        );
        return;
    }

    el.table.innerHTML=payments.map(payment=>{
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="px-3 py-3">
                    <p class="truncate text-xs font-semibold text-slate-800">
                        ${AdminUI.escapeHtml(payment.payment_no??'—')}
                    </p>
                    <p class="mt-0.5 text-[10px] text-slate-400">
                        ${AdminUI.formatDate(payment.paid_at)}
                    </p>
                </td>

                <td class="px-3 py-3">
                    <p class="truncate text-xs font-semibold text-slate-800">
                        ${AdminUI.escapeHtml(user.name??'N/A')}
                    </p>
                    <p class="mt-0.5 truncate text-[10px] font-mono text-indigo-600">
                        ${AdminUI.escapeHtml(member.member_code??'')}
                    </p>
                </td>

                <td class="px-3 py-3 text-xs text-slate-600">
                    ${monthName(due.month)} ${due.year??''}
                </td>

                <td class="px-3 py-3 text-right text-xs font-semibold text-slate-700">
                    ৳${money(payment.amount)}
                </td>

                <td class="px-3 py-3 text-xs text-slate-600">
                    ${titleCase(payment.payment_method)}
                </td>

                <td class="px-3 py-3">
                    ${AdminUI.statusBadge(payment.status)}
                </td>

                <td class="px-3 py-3">
                    <div class="flex items-center justify-end">
                        <button
                            type="button"
                            onclick="showPayment(${payment.id})"
                            class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                            title="View">
                            <i class="bi bi-eye text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function showPayment(id){
    try{
        const response=await api(
            `/api/finance/subscription-payments/${id}`
        );

        selectedPayment=response.data;

        renderPaymentDetails(selectedPayment);

        AdminUI.openModal('paymentModal');
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
}

function renderPaymentDetails(payment){
    const member=payment.member??{};
    const user=member.user??{};
    const due=payment.due??{};

    document.getElementById('paymentDetails').innerHTML=`
        ${detail('Member',`${AdminUI.escapeHtml(user.name??'-')} (${AdminUI.escapeHtml(member.member_code??'-')})`,false)}
        ${detail('Payment No',payment.payment_no??'-')}
        ${detail('Subscription Period',`${monthName(due.month)} ${due.year??''}`,false)}
        ${detail('Payment Amount',`৳${money(payment.amount)}`,false)}
        ${detail('Payment Method',titleCase(payment.payment_method))}
        ${detail('Status',AdminUI.statusBadge(payment.status),false)}
        ${detail('Transaction Reference',payment.transaction_reference??'-')}
        ${detail('Submitted',AdminUI.formatDate(payment.paid_at,true))}

        <div class="sm:col-span-2 border-t border-slate-100 pt-4">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                ${detail('Base Amount',`৳${money(due.base_amount)}`,false)}
                ${detail('Shares',due.share_count??1,false)}
                ${detail('Fine',`৳${money(due.fine_amount)}`,false)}
                ${detail('Total Due',`৳${money(due.amount)}`,false)}
            </div>
        </div>

        ${
            payment.verifier
                ?`
                    ${detail('Processed By',payment.verifier.name??'-')}
                    ${detail('Processed At',AdminUI.formatDate(payment.verified_at,true))}
                `
                :''
        }

        ${
            payment.verification_note
                ?`
                    <div class="sm:col-span-2">
                        ${detail(
                            payment.status==='rejected'
                                ?'Rejection Reason'
                                :'Verification Note',
                            payment.verification_note
                        )}
                    </div>
                `
                :''
        }
    `;

    const actions=document.getElementById('paymentActions');

    if(payment.status==='pending'&&canVerifyPayment){
        actions.innerHTML=`
            <button
                type="button"
                onclick="openReject()"
                class="cursor-pointer rounded-md border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">
                Reject
            </button>

            <button
                id="verifyButton"
                type="button"
                onclick="verifyPayment()"
                class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60">
                Verify Payment
            </button>
        `;
    }else{
        actions.innerHTML=`
            <button
                type="button"
                onclick="AdminUI.closeModal('paymentModal')"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        `;
    }
}

async function verifyPayment(){
    if(!selectedPayment){
        return;
    }

    if(!confirm(
        `Verify payment ${selectedPayment.payment_no}?`
    )){
        return;
    }

    const button=document.getElementById('verifyButton');

    AdminUI.setLoading(button,'Verifying...');

    try{
        await api(
            `/api/finance/subscription-payments/${selectedPayment.id}/verify`,
            {
                method:'POST',
                body:JSON.stringify({note:null})
            }
        );

        AdminUI.closeModal('paymentModal');

        Toast.success('Payment verified successfully.');

        await loadPayments(currentPage);
    }catch(error){
        Toast.error(AdminUI.extractError(error));

        AdminUI.resetLoading(button);
    }
}

function openReject(){
    if(!selectedPayment){
        return;
    }

    document.getElementById('rejectReason').value='';

    AdminUI.clearError('rejectFormError');

    AdminUI.closeModal('paymentModal');

    setTimeout(()=>{
        AdminUI.openModal('rejectModal');
    },200);
}

async function confirmReject(){
    if(!selectedPayment){
        return;
    }

    AdminUI.clearError('rejectFormError');

    const reason=document
        .getElementById('rejectReason')
        .value
        .trim();

    if(!reason){
        AdminUI.showError(
            'rejectFormError',
            'Rejection reason is required.'
        );

        return;
    }

    const button=document.getElementById(
        'confirmRejectButton'
    );

    AdminUI.setLoading(button,'Rejecting...');

    try{
        await api(
            `/api/finance/subscription-payments/${selectedPayment.id}/reject`,
            {
                method:'POST',
                body:JSON.stringify({reason})
            }
        );

        AdminUI.closeModal('rejectModal');

        Toast.success('Payment rejected successfully.');

        await loadPayments(currentPage);
    }catch(error){
        AdminUI.showError(
            'rejectFormError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
}

function detail(label,value,escape=true){
    return`
        <div>
            <p class="text-[11px] font-medium text-slate-400">${label}</p>
            <p class="mt-1 text-xs font-semibold text-slate-700">
                ${escape?AdminUI.escapeHtml(String(value??'-')):value}
            </p>
        </div>
    `;
}

function money(value){
    return Number(value??0).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    );
}

function titleCase(value){
    if(!value){
        return'—';
    }

    return String(value)
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}

function monthName(month){
    const months=[
        '','January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    return months[Number(month)]??'-';
}

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';

    loadPayments(1);
};

/*
|--------------------------------------------------------------------------
| Add Payment
|--------------------------------------------------------------------------
*/

let selectedPaymentMember=null;
let outstandingDues=[];
let allActiveMembers=[];

const paymentEl={
    form:document.getElementById('addPaymentForm'),
    memberSelect:document.getElementById('paymentMemberSelect'),
    dueSelect:document.getElementById('paymentDueSelect'),
    dueHelp:document.getElementById('paymentDueHelp'),
    amount:document.getElementById('paymentAmount'),
    method:document.getElementById('paymentMethod'),
    reference:document.getElementById('paymentReference'),
    note:document.getElementById('paymentNote'),
    saveButton:document.getElementById('saveAddPaymentButton')
};

window.openAddPaymentModal=function(){
    resetAddPaymentForm();

    AdminUI.openModal('addPaymentModal');

    loadActiveMembers();
};

window.closeAddPaymentModal=function(){
    AdminUI.closeModal('addPaymentModal');

    resetAddPaymentForm();
};

function resetAddPaymentForm(){
    paymentEl.form.reset();

    AdminUI.clearError('addPaymentFormError');

    selectedPaymentMember=null;
    outstandingDues=[];

    paymentEl.dueSelect.innerHTML=
        '<option value="">Select a member first</option>';

    paymentEl.dueSelect.disabled=true;

    paymentEl.dueHelp.classList.add('hidden');

    paymentEl.method.value='cash';
}

async function loadActiveMembers(){
    paymentEl.memberSelect.innerHTML=
        '<option value="">Loading members...</option>';

    paymentEl.memberSelect.disabled=true;

    try{
        const response=await api(
            '/api/finance/subscriptions/members'
        );

        allActiveMembers=Array.isArray(response.data)
            ?response.data
            :[];

        renderMemberOptions();
    }catch(error){
        paymentEl.memberSelect.innerHTML=
            '<option value="">Failed to load members</option>';

        Toast.error(AdminUI.extractError(error));
    }
}

function renderMemberOptions(){
    if(!allActiveMembers.length){
        paymentEl.memberSelect.innerHTML=
            '<option value="">No active members found</option>';

        paymentEl.memberSelect.disabled=true;

        return;
    }

    paymentEl.memberSelect.disabled=false;

    paymentEl.memberSelect.innerHTML=
        '<option value="">Select a member</option>'+
        allActiveMembers.map(member=>{
            const user=member.user??{};

            const label=
                `${user.name??'N/A'} — ${member.member_code??''}`;

            return`
                <option value="${member.id}">
                    ${AdminUI.escapeHtml(label)}
                </option>
            `;
        }).join('');
}

paymentEl.memberSelect.addEventListener(
    'change',
    async function(){
        const memberId=this.value;

        outstandingDues=[];

        paymentEl.dueSelect.disabled=true;

        paymentEl.dueHelp.classList.add('hidden');

        paymentEl.amount.value='';

        if(!memberId){
            selectedPaymentMember=null;

            paymentEl.dueSelect.innerHTML=
                '<option value="">Select a member first</option>';

            return;
        }

        selectedPaymentMember=allActiveMembers.find(
            member=>Number(member.id)===Number(memberId)
        )??null;

        paymentEl.dueSelect.innerHTML=
            '<option value="">Loading dues...</option>';

        try{
            const response=await api(
                `/api/finance/subscription-payments/outstanding-dues?member_id=${memberId}`
            );

            outstandingDues=Array.isArray(response.data)
                ?response.data
                :[];

            renderDueOptions();
        }catch(error){
            paymentEl.dueSelect.innerHTML=
                '<option value="">Failed to load dues</option>';

            Toast.error(AdminUI.extractError(error));
        }
    }
);


function renderDueOptions(){
    if(!outstandingDues.length){
        paymentEl.dueSelect.innerHTML=
            '<option value="">No outstanding dues</option>';

        paymentEl.dueSelect.disabled=true;

        paymentEl.dueHelp.classList.remove('hidden');

        paymentEl.amount.value='';

        return;
    }

    paymentEl.dueHelp.classList.add('hidden');

    paymentEl.dueSelect.disabled=false;

    paymentEl.dueSelect.innerHTML=
        '<option value="">Select a due</option>'+
        outstandingDues.map(due=>{
            const outstanding=Number(
                due.outstanding??
                (due.amount-due.paid_amount)
            ).toFixed(2);

            return`
                <option value="${due.id}" data-outstanding="${outstanding}">
                    ${monthName(due.month)} ${due.year} — Due ${outstanding}
                </option>
            `;
        }).join('');
}

paymentEl.dueSelect.addEventListener('change',function(){
    const option=this.selectedOptions[0];

    const outstanding=option
        ?Number(option.dataset.outstanding??0)
        :0;

    paymentEl.amount.value=
        outstanding>0
            ?outstanding.toFixed(2)
            :'';
});

paymentEl.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError('addPaymentFormError');

        if(!selectedPaymentMember){
            AdminUI.showError(
                'addPaymentFormError',
                'Please select a member.'
            );
            return;
        }

        const dueId=paymentEl.dueSelect.value;

        if(!dueId){
            AdminUI.showError(
                'addPaymentFormError',
                'Please select an outstanding due.'
            );
            return;
        }

        const amount=Number(paymentEl.amount.value);

        if(!Number.isFinite(amount)||amount<=0){
            AdminUI.showError(
                'addPaymentFormError',
                'Amount must be greater than zero.'
            );
            return;
        }

        AdminUI.setLoading(
            paymentEl.saveButton,
            'Recording...'
        );

        try{
            await api(
                '/api/finance/subscription-payments',
                {
                    method:'POST',
                    body:JSON.stringify({
                        member_id:selectedPaymentMember.id,
                        subscription_due_id:Number(dueId),
                        amount,
                        payment_method:paymentEl.method.value,
                        transaction_reference:
                            paymentEl.reference.value.trim()||null,
                        note:
                            paymentEl.note.value.trim()||null
                    })
                }
            );

            closeAddPaymentModal();

            Toast.success('Payment recorded and verified successfully.');

            await loadPayments(1);
        }catch(error){
            AdminUI.showError(
                'addPaymentFormError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(paymentEl.saveButton);
        }
    }
);

async function initSubscriptionPaymentsPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initSubscriptionPaymentsPage,50);
        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(()=>loadPayments(1))
    );

    el.status.addEventListener(
        'change',
        ()=>loadPayments(1)
    );

    await loadPayments();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initSubscriptionPaymentsPage
    );
}else{
    initSubscriptionPaymentsPage();
}
</script>
@endpush