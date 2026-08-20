@extends('layouts.admin')

@section('title','Subscription Payments')
@section('page-title','Subscription Payments')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-lg font-bold text-slate-800">Subscription Payments</h1>
            <p class="mt-1 text-xs text-slate-500">Review and verify member monthly subscription payments.</p>
        </div>
        <button type="button" onclick="loadPayments(1)" class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-medium text-slate-400">Total Payments</p>
            <p id="statTotal" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <p class="text-[11px] font-medium text-amber-600">Pending</p>
            <p id="statPending" class="mt-1 text-xl font-bold text-amber-700">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-[11px] font-medium text-emerald-600">Verified</p>
            <p id="statVerified" class="mt-1 text-xl font-bold text-emerald-700">0</p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <p class="text-[11px] font-medium text-red-500">Rejected</p>
            <p id="statRejected" class="mt-1 text-xl font-bold text-red-600">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white">
        <div class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="md:col-span-2 xl:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold text-slate-500">Search</label>
                <div class="relative">
                    <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="filterSearch" type="text" placeholder="Payment no, member, email..." class="h-9 w-full rounded-md border border-slate-300 pl-8 pr-3 text-xs outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-slate-500">Status</label>
                <select id="filterStatus" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-xs outline-none focus:border-indigo-400">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="verified">Verified</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-slate-500">Method</label>
                <select id="filterMethod" class="h-9 w-full rounded-md border border-slate-300 bg-white px-2 text-xs outline-none focus:border-indigo-400">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="mobile_banking">Mobile Banking</option>
                    <option value="online">Online</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-slate-500">From</label>
                <input id="filterFrom" type="date" class="h-9 w-full rounded-md border border-slate-300 px-2 text-xs outline-none focus:border-indigo-400">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-semibold text-slate-500">To</label>
                <input id="filterTo" type="date" class="h-9 w-full rounded-md border border-slate-300 px-2 text-xs outline-none focus:border-indigo-400">
            </div>

            <div class="flex gap-2 md:col-span-2 xl:col-span-6">
                <button type="button" onclick="loadPayments(1)" class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">
                    <i class="bi bi-funnel"></i>
                    Apply Filter
                </button>

                <button type="button" onclick="resetFilters()" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Reset
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
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

        <div id="paginationWrap" class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between"></div>
    </div>
</div>

<div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-md bg-white shadow-xl">
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Payment Details</h3>
                <p class="text-xs text-slate-400">Subscription payment information</p>
            </div>
            <button type="button" onclick="closeModal('detailsModal')" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsContent" class="p-5">
            <p class="py-10 text-center text-xs text-slate-400">Loading...</p>
        </div>
    </div>
</div>

<div id="verifyModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-md bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Verify Payment</h3>
                <p class="text-xs text-slate-400">Confirm received subscription payment</p>
            </div>
            <button type="button" onclick="closeModal('verifyModal')" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="verifyForm">
            <div class="space-y-4 p-5">
                <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                    <p class="text-xs text-emerald-700">You are about to verify:</p>
                    <p id="verifyPaymentInfo" class="mt-1 text-sm font-bold text-emerald-800">-</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Verification Note</label>
                    <textarea id="verifyNote" rows="3" maxlength="2000" placeholder="Optional note..." class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"></textarea>
                </div>

                <div id="verifyError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeModal('verifyModal')" class="h-9 rounded-md border border-slate-300 px-4 text-xs font-semibold text-slate-600">Cancel</button>
                <button id="verifyButton" type="submit" class="h-9 rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700">
                    Verify Payment
                </button>
            </div>
        </form>
    </div>
</div>

<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-md bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Reject Payment</h3>
                <p class="text-xs text-slate-400">Provide the reason for rejection</p>
            </div>
            <button type="button" onclick="closeModal('rejectModal')" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="rejectForm">
            <div class="space-y-4 p-5">
                <div class="rounded-md border border-red-200 bg-red-50 p-3">
                    <p class="text-xs text-red-600">Payment:</p>
                    <p id="rejectPaymentInfo" class="mt-1 text-sm font-bold text-red-700">-</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">Reason <span class="text-red-500">*</span></label>
                    <textarea id="rejectReason" rows="4" maxlength="2000" required placeholder="Why is this payment being rejected?" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"></textarea>
                </div>

                <div id="rejectError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeModal('rejectModal')" class="h-9 rounded-md border border-slate-300 px-4 text-xs font-semibold text-slate-600">Cancel</button>
                <button id="rejectButton" type="submit" class="h-9 rounded-md bg-red-600 px-4 text-xs font-semibold text-white hover:bg-red-700">
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
let searchTimer=null;

document.addEventListener('DOMContentLoaded',()=>{
    loadPayments(1);

    document.getElementById('filterSearch').addEventListener('input',()=>{
        clearTimeout(searchTimer);
        searchTimer=setTimeout(()=>loadPayments(1),400);
    });

    document.getElementById('filterStatus').addEventListener('change',()=>loadPayments(1));
    document.getElementById('filterMethod').addEventListener('change',()=>loadPayments(1));
});

async function loadPayments(page=1){
    currentPage=page;

    const body=document.getElementById('paymentTableBody');

    body.innerHTML=`
        <tr>
            <td colspan="8" class="px-4 py-12 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
                <p class="mt-2 text-xs text-slate-400">Loading payments...</p>
            </td>
        </tr>
    `;

    try{
        const params=new URLSearchParams({
            page:String(page),
            per_page:'15'
        });

        const search=document.getElementById('filterSearch').value.trim();
        const status=document.getElementById('filterStatus').value;
        const method=document.getElementById('filterMethod').value;
        const from=document.getElementById('filterFrom').value;
        const to=document.getElementById('filterTo').value;

        if(search)params.set('search',search);
        if(status)params.set('status',status);
        if(method)params.set('payment_method',method);
        if(from)params.set('from_date',from);
        if(to)params.set('to_date',to);

        const response=await api(`/api/subscription-payments?${params.toString()}`);

        const paginator=response.data??{};
        const payments=paginator.data??[];

        renderPayments(payments);
        renderPagination(paginator);
        updateStats(payments,paginator);
    }catch(error){
        body.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-12 text-center">
                    <i class="bi bi-exclamation-circle text-xl text-red-400"></i>
                    <p class="mt-2 text-xs font-semibold text-red-500">
                        ${escapeHtml(error?.data?.message??error?.message??'Failed to load payments.')}
                    </p>
                </td>
            </tr>
        `;
    }
}

function renderPayments(payments){
    const body=document.getElementById('paymentTableBody');

    if(!payments.length){
        body.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-receipt text-xl"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-600">No payments found</p>
                    <p class="mt-1 text-xs text-slate-400">Try changing your filters.</p>
                </td>
            </tr>
        `;
        return;
    }

    body.innerHTML=payments.map(payment=>{
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};

        return`
            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-4 py-3">
                    <button type="button" onclick="showPayment(${payment.id})" class="text-left">
                        <p class="font-mono text-xs font-semibold text-indigo-600 hover:underline">
                            ${escapeHtml(payment.payment_no??'-')}
                        </p>
                        <p class="mt-0.5 text-[10px] text-slate-400">
                            #${payment.id}
                        </p>
                    </button>
                </td>

                <td class="px-4 py-3">
                    <p class="max-w-[180px] truncate text-xs font-semibold text-slate-700">
                        ${escapeHtml(user.name??'-')}
                    </p>
                    <p class="mt-0.5 font-mono text-[10px] text-slate-400">
                        ${escapeHtml(member.member_code??'-')}
                    </p>
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-600">
                    ${formatPeriod(due.year,due.month)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold text-slate-800">
                    ৳${money(payment.amount)}
                </td>

                <td class="whitespace-nowrap px-4 py-3">
                    ${methodBadge(payment.payment_method)}
                </td>

                <td class="whitespace-nowrap px-4 py-3">
                    ${statusBadge(payment.status)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                    ${formatDateTime(payment.paid_at)}
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-right">
                    <div class="inline-flex items-center gap-1">
                        <button type="button" onclick="showPayment(${payment.id})" title="Details" class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 text-slate-500 hover:bg-slate-100">
                            <i class="bi bi-eye"></i>
                        </button>

                        ${payment.status==='pending'?`
                            <button type="button" onclick="openVerify(${payment.id},'${escapeJs(payment.payment_no??'')}','${escapeJs(String(payment.amount??0))}')" title="Verify" class="flex h-8 w-8 items-center justify-center rounded-md border border-emerald-200 bg-emerald-50 text-emerald-600 hover:bg-emerald-100">
                                <i class="bi bi-check-lg"></i>
                            </button>

                            <button type="button" onclick="openReject(${payment.id},'${escapeJs(payment.payment_no??'')}','${escapeJs(String(payment.amount??0))}')" title="Reject" class="flex h-8 w-8 items-center justify-center rounded-md border border-red-200 bg-red-50 text-red-500 hover:bg-red-100">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        `:''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function showPayment(id){
    openModal('detailsModal');

    const content=document.getElementById('detailsContent');

    content.innerHTML=`
        <div class="py-12 text-center">
            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
        </div>
    `;

    try{
        const response=await api(`/api/subscription-payments/${id}`);
        const payment=response.data;
        const member=payment.member??{};
        const user=member.user??{};
        const due=payment.due??{};
        const transaction=payment.finance_transaction??null;

        content.innerHTML=`
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                ${detailCard('Payment No',payment.payment_no)}
                ${detailCard('Amount',`৳${money(payment.amount)}`)}
                ${detailCard('Status',titleCase(payment.status))}
                ${detailCard('Method',titleCase(payment.payment_method))}
            </div>

            <div class="mt-5 rounded-md border border-slate-200">
                ${detailRow('Member',user.name??'-')}
                ${detailRow('Member Code',member.member_code??'-')}
                ${detailRow('Email',user.email??'-')}
                ${detailRow('Mobile',user.mobile??'-')}
                ${detailRow('Subscription Period',formatPeriod(due.year,due.month))}
                ${detailRow('Due Amount',`৳${money(due.amount)}`)}
                ${detailRow('Paid Against Due',`৳${money(due.paid_amount)}`)}
                ${detailRow('Transaction Reference',payment.transaction_reference??'-')}
                ${detailRow('Submitted At',formatDateTime(payment.paid_at))}
                ${detailRow('Verified By',payment.verifier?.name??'-')}
                ${detailRow('Verified At',formatDateTime(payment.verified_at))}
                ${detailRow('Verification Note',payment.verification_note??'-',true)}
            </div>

            ${transaction?`
                <div class="mt-5">
                    <h4 class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Accounting Transaction</h4>
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold text-emerald-700">${escapeHtml(transaction.transaction_no??`Transaction #${transaction.id}`)}</p>
                                <p class="mt-1 text-[11px] text-emerald-600">${escapeHtml(transaction.description??'')}</p>
                            </div>
                            <span class="rounded-md bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase text-emerald-700">${escapeHtml(transaction.status??'posted')}</span>
                        </div>
                    </div>
                </div>
            `:''}

            ${payment.status==='pending'?`
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" onclick="closeModal('detailsModal');openReject(${payment.id},'${escapeJs(payment.payment_no??'')}','${escapeJs(String(payment.amount??0))}')" class="h-9 rounded-md border border-red-200 bg-red-50 px-4 text-xs font-semibold text-red-600 hover:bg-red-100">
                        Reject
                    </button>
                    <button type="button" onclick="closeModal('detailsModal');openVerify(${payment.id},'${escapeJs(payment.payment_no??'')}','${escapeJs(String(payment.amount??0))}')" class="h-9 rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700">
                        Verify Payment
                    </button>
                </div>
            `:''}
        `;
    }catch(error){
        content.innerHTML=`
            <div class="py-10 text-center text-xs text-red-500">
                ${escapeHtml(error?.data?.message??error?.message??'Failed to load payment.')}
            </div>
        `;
    }
}

function openVerify(id,paymentNo,amount){
    selectedPayment=id;

    document.getElementById('verifyPaymentInfo').textContent=
        `${paymentNo} • ৳${money(amount)}`;

    document.getElementById('verifyNote').value='';
    document.getElementById('verifyError').classList.add('hidden');

    openModal('verifyModal');
}

function openReject(id,paymentNo,amount){
    selectedPayment=id;

    document.getElementById('rejectPaymentInfo').textContent=
        `${paymentNo} • ৳${money(amount)}`;

    document.getElementById('rejectReason').value='';
    document.getElementById('rejectError').classList.add('hidden');

    openModal('rejectModal');
}

document.getElementById('verifyForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!selectedPayment)return;

    const button=document.getElementById('verifyButton');
    const errorBox=document.getElementById('verifyError');

    errorBox.classList.add('hidden');
    button.disabled=true;
    button.textContent='Verifying...';

    try{
        const response=await api(`/api/subscription-payments/${selectedPayment}/verify`,{
            method:'POST',
            body:JSON.stringify({
                note:document.getElementById('verifyNote').value.trim()||null
            })
        });

        closeModal('verifyModal');

        if(window.Toast?.success){
            Toast.success(response.message??'Payment verified successfully.');
        }

        await loadPayments(currentPage);
    }catch(error){
        errorBox.textContent=getErrorMessage(error);
        errorBox.classList.remove('hidden');
    }finally{
        button.disabled=false;
        button.textContent='Verify Payment';
    }
});

document.getElementById('rejectForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!selectedPayment)return;

    const button=document.getElementById('rejectButton');
    const errorBox=document.getElementById('rejectError');
    const reason=document.getElementById('rejectReason').value.trim();

    if(!reason){
        errorBox.textContent='Rejection reason is required.';
        errorBox.classList.remove('hidden');
        return;
    }

    errorBox.classList.add('hidden');
    button.disabled=true;
    button.textContent='Rejecting...';

    try{
        const response=await api(`/api/subscription-payments/${selectedPayment}/reject`,{
            method:'POST',
            body:JSON.stringify({reason})
        });

        closeModal('rejectModal');

        if(window.Toast?.success){
            Toast.success(response.message??'Payment rejected successfully.');
        }

        await loadPayments(currentPage);
    }catch(error){
        errorBox.textContent=getErrorMessage(error);
        errorBox.classList.remove('hidden');
    }finally{
        button.disabled=false;
        button.textContent='Reject Payment';
    }
});

function renderPagination(paginator){
    const wrap=document.getElementById('paginationWrap');

    const current=Number(paginator.current_page??1);
    const last=Number(paginator.last_page??1);
    const total=Number(paginator.total??0);
    const from=paginator.from??0;
    const to=paginator.to??0;

    wrap.innerHTML=`
        <p class="text-[11px] text-slate-500">
            Showing <span class="font-semibold">${from}</span>–<span class="font-semibold">${to}</span>
            of <span class="font-semibold">${total}</span>
        </p>

        <div class="flex items-center gap-1">
            <button type="button" ${current<=1?'disabled':''} onclick="loadPayments(${current-1})" class="h-8 rounded-md border border-slate-300 px-3 text-xs font-semibold text-slate-600 disabled:cursor-not-allowed disabled:opacity-40">
                Previous
            </button>

            <span class="px-3 text-xs text-slate-500">
                ${current} / ${last}
            </span>

            <button type="button" ${current>=last?'disabled':''} onclick="loadPayments(${current+1})" class="h-8 rounded-md border border-slate-300 px-3 text-xs font-semibold text-slate-600 disabled:cursor-not-allowed disabled:opacity-40">
                Next
            </button>
        </div>
    `;
}

function updateStats(payments,paginator){
    document.getElementById('statTotal').textContent=paginator.total??payments.length;

    let pending=0;
    let verified=0;
    let rejected=0;

    payments.forEach(payment=>{
        if(payment.status==='pending')pending++;
        if(payment.status==='verified')verified++;
        if(payment.status==='rejected')rejected++;
    });

    document.getElementById('statPending').textContent=pending;
    document.getElementById('statVerified').textContent=verified;
    document.getElementById('statRejected').textContent=rejected;
}

function resetFilters(){
    document.getElementById('filterSearch').value='';
    document.getElementById('filterStatus').value='';
    document.getElementById('filterMethod').value='';
    document.getElementById('filterFrom').value='';
    document.getElementById('filterTo').value='';

    loadPayments(1);
}

function openModal(id){
    const modal=document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeModal(id){
    const modal=document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    if(!document.querySelector('.fixed.flex[id$="Modal"]')){
        document.body.classList.remove('overflow-hidden');
    }

    if(id==='verifyModal'||id==='rejectModal'){
        selectedPayment=null;
    }
}

function statusBadge(status){
    const styles={
        pending:'bg-amber-50 text-amber-700 border-amber-200',
        verified:'bg-emerald-50 text-emerald-700 border-emerald-200',
        rejected:'bg-red-50 text-red-600 border-red-200'
    };

    return`
        <span class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold ${styles[status]??'bg-slate-50 text-slate-600 border-slate-200'}">
            ${escapeHtml(titleCase(status))}
        </span>
    `;
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
            ${escapeHtml(titleCase(method))}
        </span>
    `;
}

function detailCard(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
            <p class="text-[10px] uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
            <p class="mt-1 break-words text-xs font-bold text-slate-700">${escapeHtml(value??'-')}</p>
        </div>
    `;
}

function detailRow(label,value,multiline=false){
    return`
        <div class="grid gap-1 border-b border-slate-100 px-4 py-3 last:border-b-0 sm:grid-cols-[160px_1fr]">
            <span class="text-xs text-slate-400">${escapeHtml(label)}</span>
            <span class="text-xs font-semibold text-slate-700 ${multiline?'whitespace-pre-wrap':''}">${escapeHtml(value??'-')}</span>
        </div>
    `;
}

function formatPeriod(year,month){
    if(!year||!month)return'-';

    const date=new Date(Number(year),Number(month)-1,1);

    return date.toLocaleDateString('en-US',{
        month:'short',
        year:'numeric'
    });
}

function formatDateTime(value){
    if(!value)return'-';

    const date=new Date(value);

    if(Number.isNaN(date.getTime()))return value;

    return date.toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function money(value){
    return Number(value??0).toLocaleString('en-BD',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function titleCase(value){
    if(!value)return'-';

    return String(value)
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function getErrorMessage(error){
    if(error?.data?.errors){
        const errors=Object.values(error.data.errors).flat();

        if(errors.length)return errors[0];
    }

    return error?.data?.message??error?.message??'Something went wrong.';
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

function escapeJs(value){
    return String(value??'')
        .replace(/\\/g,'\\\\')
        .replace(/'/g,"\\'")
        .replace(/\r/g,'')
        .replace(/\n/g,'\\n');
}

['detailsModal','verifyModal','rejectModal'].forEach(id=>{
    document.getElementById(id).addEventListener('click',event=>{
        if(event.target===event.currentTarget){
            closeModal(id);
        }
    });
});

document.addEventListener('keydown',event=>{
    if(event.key!=='Escape')return;

    ['detailsModal','verifyModal','rejectModal'].forEach(id=>{
        const modal=document.getElementById(id);

        if(!modal.classList.contains('hidden')){
            closeModal(id);
        }
    });
});
</script>
@endpush