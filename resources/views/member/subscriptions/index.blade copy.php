@extends('layouts.member')

@section('title','Monthly Subscription')
@section('page-title','Monthly Subscription')

@section('content')
@php
    $currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-800">
                Monthly Subscription
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                View monthly dues, fines and submit subscription payments.
            </p>
        </div>

        <button
            type="button"
            onclick="loadSubscriptions()"
            class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div id="summaryCards" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Monthly Payable</p>
            <p id="monthlyPayable" class="mt-2 text-lg font-bold text-slate-800">
                {{ $currency }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Current Due</p>
            <p id="currentDueAmount" class="mt-2 text-lg font-bold text-slate-800">
                {{ $currency }}0.00
            </p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-700">Paid</p>
            <p id="currentPaidAmount" class="mt-2 text-lg font-bold text-emerald-600">
                {{ $currency }}0.00
            </p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-xs text-amber-700">Fine</p>
            <p id="currentFineAmount" class="mt-2 text-lg font-bold text-amber-600">
                {{ $currency }}0.00
            </p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/40 p-4">
            <p class="text-xs text-red-600">Outstanding</p>
            <p id="currentOutstandingAmount" class="mt-2 text-lg font-bold text-red-600">
                {{ $currency }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Active Shares</p>
            <p id="activeShareCount" class="mt-2 text-lg font-bold text-slate-800">
                0
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Current Month
                    </h2>
                    <p id="currentPeriod" class="mt-0.5 text-[11px] text-slate-400">
                        -
                    </p>
                </div>

                <span id="currentStatus" class="rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">
                    -
                </span>
            </div>

            <div id="currentDueLoading" class="p-8 text-center text-sm text-slate-400">
                Loading current subscription...
            </div>

            <div id="currentDueContent" class="hidden">
                <div class="grid grid-cols-2 gap-px bg-slate-200 md:grid-cols-5">
                    <div class="bg-white p-4">
                        <p class="text-[11px] text-slate-400">Base Amount</p>
                        <p id="baseAmount" class="mt-1 text-sm font-bold text-slate-800">
                            {{ $currency }}0.00
                        </p>
                    </div>

                    <div class="bg-white p-4">
                        <p class="text-[11px] text-slate-400">Shares</p>
                        <p id="shareCount" class="mt-1 text-sm font-bold text-slate-800">
                            1
                        </p>
                    </div>

                    <div class="bg-white p-4">
                        <p class="text-[11px] text-slate-400">Fine</p>
                        <p id="fineAmount" class="mt-1 text-sm font-bold text-amber-600">
                            {{ $currency }}0.00
                        </p>
                    </div>

                    <div class="bg-white p-4">
                        <p class="text-[11px] text-slate-400">Paid</p>
                        <p id="paidAmount" class="mt-1 text-sm font-bold text-emerald-600">
                            {{ $currency }}0.00
                        </p>
                    </div>

                    <div class="bg-white p-4">
                        <p class="text-[11px] text-slate-400">Outstanding</p>
                        <p id="outstandingAmount" class="mt-1 text-sm font-bold text-red-600">
                            {{ $currency }}0.00
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold text-slate-700">
                            Due Date
                        </p>
                        <p id="dueDate" class="mt-1 text-[11px] text-slate-400">
                            -
                        </p>
                    </div>

                    <button
                        id="payNowButton"
                        type="button"
                        onclick="openPaymentModal()"
                        class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                        <i class="bi bi-credit-card"></i>
                        Pay Now
                    </button>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-sm font-semibold text-slate-800">
                    Subscription Plan
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    Current active plan
                </p>
            </div>

            <div class="space-y-3 p-5">
                <div>
                    <p class="text-[11px] text-slate-400">Plan</p>
                    <p id="planName" class="mt-1 text-sm font-semibold text-slate-700">
                        -
                    </p>
                </div>

                <div>
                    <p class="text-[11px] text-slate-400">Base Monthly Amount</p>
                    <p id="planAmount" class="mt-1 text-sm font-semibold text-slate-700">
                        {{ $currency }}0.00
                    </p>
                </div>

                <div>
                    <p class="text-[11px] text-slate-400">Due Day</p>
                    <p id="planDueDay" class="mt-1 text-sm font-semibold text-slate-700">
                        -
                    </p>
                </div>

                <div>
                    <p class="text-[11px] text-slate-400">Started</p>
                    <p id="subscriptionStartDate" class="mt-1 text-sm font-semibold text-slate-700">
                        -
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Subscription History
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-400">
                    Monthly dues and payment status
                </p>
            </div>

            <select
                id="historyYear"
                onchange="loadSubscriptions()"
                class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs text-slate-600 outline-none focus:border-indigo-400">
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Month
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Base
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                            Shares
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Fine
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Total
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Paid
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Outstanding
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Due Date
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                            Status
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="subscriptionHistory">
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-center text-slate-400">
                            Loading subscription history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-800">
                Payment History
            </h2>
            <p class="mt-0.5 text-[11px] text-slate-400">
                Submitted and verified subscription payments
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Payment No.
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Period
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Method
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Reference
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Submitted
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody id="paymentHistory">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                            Loading payment history...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div
    id="paymentModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur-[1px]">

    <div class="w-full max-w-lg overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Submit Payment
                </h3>
                <p id="paymentPeriodText" class="mt-0.5 text-xs text-slate-400">
                    Monthly subscription payment
                </p>
            </div>

            <button
                type="button"
                onclick="closePaymentModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="paymentForm">
            <div class="space-y-4 p-5">
                <div class="rounded-md border border-indigo-100 bg-indigo-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-indigo-700">
                            Outstanding
                        </span>

                        <span id="modalOutstanding" class="text-lg font-bold text-indigo-700">
                            {{ $currency }}0.00
                        </span>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Amount <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                            {{ $currency }}
                        </span>

                        <input
                            id="paymentAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="h-10 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Payment Method <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="paymentMethod"
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-indigo-400">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Transaction Reference
                    </label>

                    <input
                        id="transactionReference"
                        type="text"
                        maxlength="255"
                        placeholder="Txn ID / reference number"
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400">
                </div>

                <div id="paymentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>

                <div class="rounded-md border border-amber-200 bg-amber-50 p-3">
                    <div class="flex gap-2">
                        <i class="bi bi-info-circle mt-0.5 text-amber-600"></i>

                        <p class="text-[11px] leading-5 text-amber-700">
                            Submitted payment will remain pending until verified by the association.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button
                    type="button"
                    onclick="closePaymentModal()"
                    class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="submitPaymentButton"
                    type="submit"
                    class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Submit Payment
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currencySymbol=@json($currency);

let dashboardData={};
let dues=[];
let payments=[];
let selectedDue=null;

const money=value=>{
    return currencySymbol+Number(value??0).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    );
};

function initYears(){
    const select=document.getElementById('historyYear');
    const currentYear=new Date().getFullYear();

    select.innerHTML='';

    for(let year=currentYear;year>=currentYear-5;year--){
        select.insertAdjacentHTML(
            'beforeend',
            `<option value="${year}" ${year===currentYear?'selected':''}>${year}</option>`
        );
    }
}

async function loadSubscriptions(){
    const year=document.getElementById('historyYear').value;

    try{
        const response=await api(
            `/api/member/subscriptions?year=${year}`
        );

        const data=response.data??{};

        dashboardData=data.summary??{};
        dues=Array.isArray(data.dues)
            ?data.dues
            :[];
        payments=Array.isArray(data.payments)
            ?data.payments
            :[];

        renderSummary();
        renderCurrentDue();
        renderHistory();
        renderPayments();
        renderPlan(data.subscription??null);
    }catch(error){
        console.error(error);

        document.getElementById('subscriptionHistory').innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-10 text-center text-red-500">
                    ${escapeHtml(error?.data?.message??error.message??'Failed to load subscriptions.')}
                </td>
            </tr>
        `;
    }
}

function renderSummary(){
    document.getElementById('monthlyPayable').textContent=
        money(dashboardData.monthly_payable);

    document.getElementById('currentDueAmount').textContent=
        money(dashboardData.current_due);

    document.getElementById('currentPaidAmount').textContent=
        money(dashboardData.current_paid);

    document.getElementById('currentFineAmount').textContent=
        money(dashboardData.current_fine);

    document.getElementById('currentOutstandingAmount').textContent=
        money(dashboardData.current_outstanding);

    document.getElementById('activeShareCount').textContent=
        Number(dashboardData.active_shares??0);
}

function renderCurrentDue(){
    const loading=document.getElementById('currentDueLoading');
    const content=document.getElementById('currentDueContent');

    const currentYear=new Date().getFullYear();
    const currentMonth=new Date().getMonth()+1;

    const current=dues.find(due=>
        Number(due.year)===currentYear&&
        Number(due.month)===currentMonth
    );

    loading.classList.add('hidden');
    content.classList.remove('hidden');

    if(!current){
        document.getElementById('currentPeriod').textContent=
            monthName(currentMonth)+' '+currentYear;

        document.getElementById('currentStatus').textContent=
            'No Due';

        document.getElementById('currentStatus').className=
            'rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600';

        document.getElementById('baseAmount').textContent=money(0);
        document.getElementById('shareCount').textContent='0';
        document.getElementById('fineAmount').textContent=money(0);
        document.getElementById('paidAmount').textContent=money(0);
        document.getElementById('outstandingAmount').textContent=money(0);
        document.getElementById('dueDate').textContent='-';

        document.getElementById('payNowButton').disabled=true;

        return;
    }

    selectedDue=current;

    document.getElementById('currentPeriod').textContent=
        monthName(current.month)+' '+current.year;

    document.getElementById('baseAmount').textContent=
        money(current.base_amount);

    document.getElementById('shareCount').textContent=
        Number(current.share_count??1);

    document.getElementById('fineAmount').textContent=
        money(current.fine_amount);

    document.getElementById('paidAmount').textContent=
        money(current.paid_amount);

    document.getElementById('outstandingAmount').textContent=
        money(outstanding(current));

    document.getElementById('dueDate').textContent=
        formatDate(current.due_date);

    setStatusBadge(
        document.getElementById('currentStatus'),
        current.status
    );

    document.getElementById('payNowButton').disabled=
        ['paid','waived'].includes(current.status)||
        outstanding(current)<=0;
}

function renderPlan(subscription){
    const plan=subscription?.plan??{};

    document.getElementById('planName').textContent=
        plan.name??'-';

    document.getElementById('planAmount').textContent=
        money(plan.amount);

    document.getElementById('planDueDay').textContent=
        plan.due_day
            ?`Day ${plan.due_day} of every month`
            :'-';

    document.getElementById('subscriptionStartDate').textContent=
        formatDate(subscription?.start_date);
}

function renderHistory(){
    const tbody=document.getElementById('subscriptionHistory');

    if(!dues.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-10 text-center text-slate-400">
                    No subscription dues found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=dues.map(due=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="px-4 py-3">
                <p class="text-xs font-semibold text-slate-700">
                    ${monthName(due.month)} ${due.year}
                </p>
            </td>

            <td class="px-4 py-3 text-right text-xs text-slate-600">
                ${money(due.base_amount)}
            </td>

            <td class="px-4 py-3 text-center text-xs text-slate-600">
                ${Number(due.share_count??1)}
            </td>

            <td class="px-4 py-3 text-right text-xs text-amber-600">
                ${money(due.fine_amount)}
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                ${money(due.amount)}
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-emerald-600">
                ${money(due.paid_amount)}
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-red-600">
                ${money(outstanding(due))}
            </td>

            <td class="px-4 py-3 text-xs text-slate-500">
                ${formatDate(due.due_date)}
            </td>

            <td class="px-4 py-3 text-center">
                ${statusBadge(due.status)}
            </td>

            <td class="px-4 py-3 text-right">
                ${
                    !['paid','waived'].includes(due.status)&&
                    outstanding(due)>0
                        ?`
                            <button
                                type="button"
                                onclick="payDue(${due.id})"
                                class="inline-flex h-8 items-center justify-center rounded-md bg-indigo-50 px-3 text-[11px] font-semibold text-indigo-600 transition hover:bg-indigo-100">
                                Pay
                            </button>
                        `
                        :'—'
                }
            </td>
        </tr>
    `).join('');
}

function renderPayments(){
    const tbody=document.getElementById('paymentHistory');

    if(!payments.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                    No payments found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=payments.map(payment=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="px-4 py-3">
                <span class="font-mono text-xs font-semibold text-indigo-600">
                    ${escapeHtml(payment.payment_no??'-')}
                </span>
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${
                    payment.due
                        ?`${monthName(payment.due.month)} ${payment.due.year}`
                        :'-'
                }
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                ${money(payment.amount)}
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${titleCase(payment.payment_method)}
            </td>

            <td class="max-w-[160px] px-4 py-3">
                <p class="truncate text-xs text-slate-500">
                    ${escapeHtml(payment.transaction_reference??'-')}
                </p>
            </td>

            <td class="px-4 py-3 text-xs text-slate-500">
                ${formatDateTime(payment.paid_at)}
            </td>

            <td class="px-4 py-3 text-center">
                ${paymentStatusBadge(payment.status)}
            </td>
        </tr>
    `).join('');
}

window.payDue=function(id){
    const due=dues.find(
        item=>Number(item.id)===Number(id)
    );

    if(!due){
        return;
    }

    selectedDue=due;

    openPaymentModal();
};

window.openPaymentModal=function(){
    if(!selectedDue){
        return;
    }

    const amount=outstanding(selectedDue);

    if(amount<=0){
        return;
    }

    document.getElementById('paymentPeriodText').textContent=
        `${monthName(selectedDue.month)} ${selectedDue.year}`;

    document.getElementById('modalOutstanding').textContent=
        money(amount);

    document.getElementById('paymentAmount').value=
        amount.toFixed(2);

    document.getElementById('paymentMethod').value='cash';

    document.getElementById('transactionReference').value='';

    document.getElementById('paymentError').classList.add('hidden');

    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
};

window.closePaymentModal=function(){
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
};

document.getElementById('paymentForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        if(!selectedDue){
            return;
        }

        const amount=Number(
            document.getElementById('paymentAmount').value
        );

        const paymentMethod=
            document.getElementById('paymentMethod').value;

        const transactionReference=
            document.getElementById('transactionReference').value.trim();

        const errorBox=
            document.getElementById('paymentError');

        errorBox.classList.add('hidden');

        if(!Number.isFinite(amount)||amount<=0){
            errorBox.textContent='Enter a valid payment amount.';
            errorBox.classList.remove('hidden');
            return;
        }

        if(amount>outstanding(selectedDue)){
            errorBox.textContent='Payment amount exceeds outstanding balance.';
            errorBox.classList.remove('hidden');
            return;
        }

        const button=
            document.getElementById('submitPaymentButton');

        button.disabled=true;
        button.textContent='Submitting...';

        try{
            const response=await api(
                `/api/member/subscriptions/${selectedDue.id}/pay`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        amount,
                        payment_method:paymentMethod,
                        transaction_reference:
                            transactionReference||null
                    })
                }
            );

            Toast.success(
                response.message??
                'Payment submitted successfully.'
            );

            closePaymentModal();

            await loadSubscriptions();
        }catch(error){
            errorBox.textContent=
                error?.data?.message??
                error.message??
                'Failed to submit payment.';

            errorBox.classList.remove('hidden');
        }finally{
            button.disabled=false;
            button.textContent='Submit Payment';
        }
    }
);

function outstanding(due){
    return Math.max(
        Number(due.amount??0)-
        Number(due.paid_amount??0),
        0
    );
}

function monthName(month){
    return new Date(
        2000,
        Number(month)-1,
        1
    ).toLocaleString(
        'en-US',
        {
            month:'long'
        }
    );
}

function formatDate(value){
    if(!value){
        return'-';
    }

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
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

function formatDateTime(value){
    if(!value){
        return'-';
    }

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
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

function statusBadge(status){
    const classes={
        unpaid:'bg-red-50 text-red-600',
        partial:'bg-amber-50 text-amber-700',
        paid:'bg-emerald-50 text-emerald-700',
        overdue:'bg-red-100 text-red-700',
        waived:'bg-slate-100 text-slate-600'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${classes[status]??classes.unpaid}">
            ${escapeHtml(titleCase(status))}
        </span>
    `;
}

function setStatusBadge(element,status){
    element.innerHTML=titleCase(status);

    const classes={
        unpaid:'bg-red-50 text-red-600',
        partial:'bg-amber-50 text-amber-700',
        paid:'bg-emerald-50 text-emerald-700',
        overdue:'bg-red-100 text-red-700',
        waived:'bg-slate-100 text-slate-600'
    };

    element.className=
        `rounded-md px-2.5 py-1 text-[10px] font-semibold ${classes[status]??classes.unpaid}`;
}

function paymentStatusBadge(status){
    const classes={
        pending:'bg-amber-50 text-amber-700',
        verified:'bg-emerald-50 text-emerald-700',
        rejected:'bg-red-50 text-red-600'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${classes[status]??'bg-slate-100 text-slate-600'}">
            ${escapeHtml(titleCase(status))}
        </span>
    `;
}

function titleCase(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

document.addEventListener(
    'DOMContentLoaded',
    ()=>{
        initYears();
        loadSubscriptions();
    }
);
</script>
@endpush