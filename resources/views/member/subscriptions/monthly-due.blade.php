@extends('layouts.member')

@section('title','Monthly Dues')
@section('page-title','Monthly Dues')

@section('content')
@php
    $currency=setting('currency_symbol','৳');
    $shareEnabled=filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="space-y-3">
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">Monthly Dues</h1>
                <p class="mt-0.5 text-sm text-slate-500">Track monthly subscription dues, fines and outstanding balances.</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('member.subscription-payments') }}"
               class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                <i class="bi bi-receipt"></i>
                Payment History
            </a>

            <button type="button" onclick="loadSubscriptions()"
                    class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>
        </div>
    </div>

    <div id="summaryCards" class="grid grid-cols-2 gap-3 lg:grid-cols-3 {{ $shareEnabled?'2xl:grid-cols-6':'2xl:grid-cols-5' }}">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Monthly Payable</p>
                    <p id="monthlyPayable" class="text-xl font-bold text-slate-800">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-calendar2-check"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">Current Due</p>
                    <p id="currentDueAmount" class="mt-2 text-xl font-bold text-sky-700">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <i class="bi bi-receipt"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Paid</p>
                    <p id="currentPaidAmount" class="text-xl font-bold text-emerald-700">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">Fine</p>
                    <p id="currentFineAmount" class="text-xl font-bold text-amber-700">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-red-500">Outstanding</p>
                    <p id="currentOutstandingAmount" class="text-xl font-bold text-red-600">{{ $currency }}0.00</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>

        @if($shareEnabled)
            <div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">Active Shares</p>
                        <p id="activeShareCount" class="mt-2 text-xl font-bold text-violet-700">0</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                        <i class="bi bi-layers"></i>
                    </div>
                </div>
            </div>
        @else
            <span id="activeShareCount" class="hidden">0</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white xl:col-span-2">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-slate-800">Current Month</h2>
                        <p id="currentPeriod" class="text-[11px] text-slate-500">-</p>
                    </div>
                </div>
                <span id="currentStatus" class="w-fit rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600">-</span>
            </div>

            <div id="currentDueLoading" class="p-10 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
                <p class="mt-3 text-sm text-slate-500">Loading current subscription...</p>
            </div>

            <div id="currentDueContent" class="hidden">
                <div class="p-5 pb-0">
                    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Payment Progress</p>
                                <p id="progressText" class="mt-0.5 text-[11px] text-slate-500">{{ $currency }}0.00 paid</p>
                            </div>
                            <span id="progressPercentage" class="text-xl font-bold text-indigo-600">0%</span>
                        </div>

                        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-slate-200">
                            <div id="progressBar" class="h-full rounded-full bg-indigo-500 transition-all duration-500" style="width:0%"></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 p-5 md:grid-cols-5">
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-[10px] uppercase tracking-wide text-slate-500">Base Amount</p>
                        <p id="baseAmount" class="mt-1.5 text-base font-bold text-slate-800">{{ $currency }}0.00</p>
                    </div>

                    <div class="rounded-lg border border-violet-200 bg-violet-50/30 p-4">
                        <p class="text-[10px] uppercase tracking-wide text-violet-500">Shares</p>
                        <p id="shareCount" class="mt-1.5 text-base font-bold text-violet-700">1</p>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50/30 p-4">
                        <p class="text-[10px] uppercase tracking-wide text-amber-500">Fine</p>
                        <p id="fineAmount" class="mt-1.5 text-base font-bold text-amber-700">{{ $currency }}0.00</p>
                    </div>

                    <div class="rounded-lg border border-emerald-200 bg-emerald-50/30 p-4">
                        <p class="text-[10px] uppercase tracking-wide text-emerald-500">Paid</p>
                        <p id="paidAmount" class="mt-1.5 text-base font-bold text-emerald-700">{{ $currency }}0.00</p>
                    </div>

                    <div class="col-span-2 rounded-lg border border-red-200 bg-red-50/30 p-4 md:col-span-1">
                        <p class="text-[10px] uppercase tracking-wide text-red-500">Outstanding</p>
                        <p id="outstandingAmount" class="mt-1.5 text-base font-bold text-red-600">{{ $currency }}0.00</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4 border-t border-slate-200 bg-slate-50/30 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-slate-500">Due Date</p>
                            <p id="dueDate" class="mt-0.5 text-sm font-semibold text-slate-700">-</p>
                        </div>
                    </div>

                    <button id="payNowButton" type="button" onclick="openPaymentModal()" disabled
                            class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                        <i class="bi bi-credit-card"></i>
                        Pay Now
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Subscription Plan</h2>
                    <p class="text-[11px] text-slate-500">Current active plan</p>
                </div>
            </div>

            <div class="p-5">
                <div class="rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 p-5 text-white">
                    <p class="text-[10px] font-medium uppercase tracking-[0.14em] text-indigo-100">Active Plan</p>
                    <h3 id="planName" class="mt-2 text-lg font-bold">-</h3>
                    <div class="mt-4 flex items-end gap-1">
                        <span id="planAmount" class="text-2xl font-bold">{{ $currency }}0.00</span>
                        <span class="pb-1 text-sm text-indigo-100">/ base month</span>
                    </div>
                </div>

                <div class="mt-4 divide-y divide-slate-100 rounded-lg border border-slate-200">
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <span class=" text-xs 2xl:text-sm text-slate-500">Due Day</span>
                        <span id="planDueDay" class="text-sm font-semibold text-slate-700">-</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <span class=" text-xs 2xl:text-sm text-slate-500">Late Fine</span>
                        <span id="planLateFine" class="text-right text-sm font-semibold text-amber-600">-</span>
                    </div>

                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <span class=" text-xs 2xl:text-sm text-slate-500">Started</span>
                        <span id="subscriptionStartDate" class="text-sm font-semibold text-slate-700">-</span>
                    </div>

                    @if($shareEnabled)
                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                            <span class=" text-xs 2xl:text-sm text-slate-500">Billing Rule</span>
                            <span class="text-right text-[11px] font-semibold text-violet-600">Base × Active Shares</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Monthly Due History</h2>
                    <p class="text-[11px] text-slate-500">Monthly subscription, fine, paid and outstanding status</p>
                </div>
            </div>

            <select id="historyYear" onchange="loadSubscriptions()"
                    class="h-9 w-fit rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-600 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Month</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Base</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">Shares</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Fine</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Total</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Paid</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Outstanding</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">Due Date</th>
                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody id="subscriptionHistory" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="10" class="px-4 py-12 text-center text-sm text-slate-500">Loading monthly dues...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="paymentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
    <div class="w-full max-w-lg overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-credit-card"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Submit Payment</h3>
                    <p id="paymentPeriodText" class="text-xs 2xl:text-sm text-slate-500">Monthly subscription payment</p>
                </div>
            </div>

            <button type="button" onclick="closePaymentModal()"
                    class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="paymentForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <div class="rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 p-4 text-white">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[10px] uppercase tracking-wide text-indigo-100">Outstanding Amount</p>
                            <p class="mt-1 text-sm text-indigo-100">Maximum amount payable now</p>
                        </div>
                        <span id="modalOutstanding" class="text-xl font-bold">{{ $currency }}0.00</span>
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">
                        Payment Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base font-medium text-slate-500">{{ $currency }}</span>
                        <input id="paymentAmount" type="number" step="0.01" min="0.01" required
                               class="h-10 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-base text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">
                        Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select id="paymentMethod" required
                            class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="mobile_banking">Mobile Banking</option>
                        <option value="online">Online</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-semibold text-slate-600">Transaction Reference</label>
                    <input id="transactionReference" type="text" maxlength="255"
                           placeholder="Txn ID / Bank reference / Mobile banking reference"
                           class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none placeholder:text-slate-500 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <div id="paymentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

                <div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50/60 p-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <i class="bi bi-info-circle"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Verification required</p>
                        <p class="mt-0.5 text-[11px] leading-5 text-amber-700">
                            Submitted payment remains pending until verified by the association.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/40 px-5 py-4">
                <button type="button" onclick="closePaymentModal()"
                        class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button id="submitPaymentButton" type="submit"
                        class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    <i class="bi bi-send"></i>
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
let selectedDue=null;

const money=value=>currencySymbol+Number(value??0).toLocaleString('en-US',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
});

function initYears(){
    const select=document.getElementById('historyYear');
    const year=new Date().getFullYear();
    select.innerHTML='';
    for(let y=year;y>=year-5;y--){
        select.insertAdjacentHTML('beforeend',`<option value="${y}" ${y===year?'selected':''}>${y}</option>`);
    }
}

async function loadSubscriptions(){
    const year=document.getElementById('historyYear').value;

    try{
        const response=await api(`/api/member/subscriptions?year=${year}`);
        const data=response.data??{};

        dashboardData=data.summary??{};
        dues=Array.isArray(data.dues)?data.dues:[];

        renderSummary();
        renderCurrentDue();
        renderHistory();
        renderPlan(data.subscription??null);
    }catch(error){
        document.getElementById('currentDueLoading').classList.add('hidden');

        document.getElementById('subscriptionHistory').innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-12 text-center text-sm text-red-500">
                    ${escapeHtml(error?.data?.message??error?.message??'Failed to load monthly dues.')}
                </td>
            </tr>`;
    }
}

function renderSummary(){
    document.getElementById('monthlyPayable').textContent=money(dashboardData.monthly_payable);
    document.getElementById('currentDueAmount').textContent=money(dashboardData.current_due);
    document.getElementById('currentPaidAmount').textContent=money(dashboardData.current_paid);
    document.getElementById('currentFineAmount').textContent=money(dashboardData.current_fine);
    document.getElementById('currentOutstandingAmount').textContent=money(dashboardData.current_outstanding);
    document.getElementById('activeShareCount').textContent=Number(dashboardData.active_shares??0);
}

function renderCurrentDue(){
    const loading=document.getElementById('currentDueLoading');
    const content=document.getElementById('currentDueContent');
    const now=new Date();

    const current=dues.find(due=>
        Number(due.year)===now.getFullYear()&&
        Number(due.month)===now.getMonth()+1
    );

    loading.classList.add('hidden');
    content.classList.remove('hidden');

    if(!current){
        selectedDue=null;
        document.getElementById('currentPeriod').textContent=`${monthName(now.getMonth()+1)} ${now.getFullYear()}`;
        document.getElementById('currentStatus').textContent='No Due';
        document.getElementById('currentStatus').className='w-fit rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-600';
        document.getElementById('baseAmount').textContent=money(0);
        document.getElementById('shareCount').textContent='0';
        document.getElementById('fineAmount').textContent=money(0);
        document.getElementById('paidAmount').textContent=money(0);
        document.getElementById('outstandingAmount').textContent=money(0);
        document.getElementById('dueDate').textContent='-';
        updateProgress(0,0);
        document.getElementById('payNowButton').disabled=true;
        return;
    }

    selectedDue=current;
    const balance=outstanding(current);

    document.getElementById('currentPeriod').textContent=`${monthName(current.month)} ${current.year}`;
    document.getElementById('baseAmount').textContent=money(current.base_amount);
    document.getElementById('shareCount').textContent=Number(current.share_count??1);
    document.getElementById('fineAmount').textContent=money(current.fine_amount);
    document.getElementById('paidAmount').textContent=money(current.paid_amount);
    document.getElementById('outstandingAmount').textContent=money(balance);
    document.getElementById('dueDate').textContent=formatDate(current.due_date);

    setStatusBadge(document.getElementById('currentStatus'),current.status);
    updateProgress(Number(current.paid_amount??0),Number(current.amount??0));

    document.getElementById('payNowButton').disabled=
        ['paid','waived'].includes(current.status)||balance<=0;
}

function updateProgress(paid,total){
    const percentage=total>0
        ?Math.min(100,Math.max(0,Math.round((paid/total)*100)))
        :0;

    document.getElementById('progressText').textContent=`${money(paid)} paid of ${money(total)}`;
    document.getElementById('progressPercentage').textContent=`${percentage}%`;

    const bar=document.getElementById('progressBar');
    bar.style.width=`${percentage}%`;
    bar.className=`h-full rounded-full transition-all duration-500 ${percentage>=100?'bg-emerald-500':'bg-indigo-500'}`;

    document.getElementById('progressPercentage').className=
        `text-xl font-bold ${percentage>=100?'text-emerald-600':'text-indigo-600'}`;
}

function renderPlan(subscription){
    const plan=subscription?.plan??{};
    document.getElementById('planName').textContent=plan.name??'-';
    document.getElementById('planAmount').textContent=money(plan.amount);
    document.getElementById('planDueDay').textContent=plan.due_day?`Day ${plan.due_day} of every month`:'-';
    document.getElementById('planLateFine').textContent=lateFineText(plan);
    document.getElementById('subscriptionStartDate').textContent=formatDate(subscription?.start_date);
}

function lateFineText(plan){
    if(!plan.fine_type||plan.fine_type==='none'){
        return'No fine';
    }

    const value=plan.fine_type==='percentage'
        ?`${Number(plan.fine_value??0)}%`
        :money(plan.fine_value);

    const graceDays=Number(plan.grace_days??0);

    return`${value} after ${graceDays} day${graceDays===1?'':'s'}`;
}

function renderHistory(){
    const tbody=document.getElementById('subscriptionHistory');

    if(!dues.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-14 text-center">
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-600">No monthly dues found</p>
                </td>
            </tr>`;
        return;
    }

    tbody.innerHTML=dues.map(due=>`
        <tr class="transition hover:bg-slate-50">
            <td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-700">${monthName(due.month)} ${due.year}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-600">${money(due.base_amount)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-center">
                <span class="inline-flex min-w-7 justify-center rounded-md bg-violet-50 px-2 py-1 text-[10px] font-semibold text-violet-600">
                    ${Number(due.share_count??1)}
                </span>
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-amber-600">${money(due.fine_amount)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-bold text-slate-700">${money(due.amount)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-emerald-600">${money(due.paid_amount)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold ${outstanding(due)>0?'text-red-600':'text-emerald-600'}">
                ${money(outstanding(due))}
            </td>
            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">${formatDate(due.due_date)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-center">${statusBadge(due.status)}</td>
            <td class="whitespace-nowrap px-4 py-3 text-right">
                ${
                    !['paid','waived'].includes(due.status)&&outstanding(due)>0
                        ?`<button type="button" onclick="payDue(${Number(due.id)})"
                                  class="inline-flex h-8 items-center gap-1.5 rounded-md bg-indigo-50 px-3 text-[11px] font-semibold text-indigo-600 transition hover:bg-indigo-100">
                              <i class="bi bi-credit-card"></i>Pay
                           </button>`
                        :'<span class="text-sm text-slate-300">—</span>'
                }
            </td>
        </tr>
    `).join('');
}

window.payDue=function(id){
    selectedDue=dues.find(item=>Number(item.id)===Number(id))??null;
    if(selectedDue)openPaymentModal();
};

window.openPaymentModal=function(){
    if(!selectedDue)return;

    const amount=outstanding(selectedDue);
    if(amount<=0)return;

    document.getElementById('paymentPeriodText').textContent=`${monthName(selectedDue.month)} ${selectedDue.year}`;
    document.getElementById('modalOutstanding').textContent=money(amount);
    document.getElementById('paymentAmount').value=amount.toFixed(2);
    document.getElementById('paymentAmount').max=amount.toFixed(2);
    document.getElementById('paymentMethod').value='cash';
    document.getElementById('transactionReference').value='';
    document.getElementById('paymentError').classList.add('hidden');

    const modal=document.getElementById('paymentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
};

window.closePaymentModal=function(){
    const modal=document.getElementById('paymentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
};

document.getElementById('paymentForm').addEventListener('submit',async event=>{
    event.preventDefault();
    if(!selectedDue)return;

    const amount=Number(document.getElementById('paymentAmount').value);
    const paymentMethod=document.getElementById('paymentMethod').value;
    const transactionReference=document.getElementById('transactionReference').value.trim();
    const errorBox=document.getElementById('paymentError');

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

    const button=document.getElementById('submitPaymentButton');
    const original=button.innerHTML;

    button.disabled=true;
    button.innerHTML='<span class="h-3 w-3 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>Submitting...';

    try{
        const response=await api(
            `/api/member/subscriptions/dues/${selectedDue.id}/pay`,
            {
                method:'POST',
                body:JSON.stringify({
                    amount,
                    payment_method:paymentMethod,
                    transaction_reference:transactionReference||null
                })
            }
        );

        Toast?.success?.(response.message??'Payment submitted successfully.');
        closePaymentModal();
        await loadSubscriptions();
    }catch(error){
        let message=error?.data?.message??error?.message??'Failed to submit payment.';

        if(error?.data?.errors){
            const errors=Object.values(error.data.errors).flat();
            if(errors.length)message=errors[0];
        }

        errorBox.textContent=message;
        errorBox.classList.remove('hidden');
    }finally{
        button.disabled=false;
        button.innerHTML=original;
    }
});

function outstanding(due){
    return Math.max(
        Math.round((Number(due.amount??0)-Number(due.paid_amount??0))*100)/100,
        0
    );
}

function monthName(month){
    return new Date(2000,Number(month)-1,1).toLocaleString('en-US',{month:'long'});
}

function formatDate(value){
    if(!value)return'-';

    const raw=String(value);
    const date=new Date(raw.length===10?`${raw}T00:00:00`:raw);

    if(Number.isNaN(date.getTime()))return raw;

    return date.toLocaleDateString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric'
    });
}

function statusBadge(status){
    const classes={
        unpaid:'border-red-200 bg-red-50 text-red-600',
        partial:'border-amber-200 bg-amber-50 text-amber-700',
        paid:'border-emerald-200 bg-emerald-50 text-emerald-700',
        overdue:'border-red-300 bg-red-100 text-red-700',
        waived:'border-slate-200 bg-slate-100 text-slate-600'
    };

    return `<span class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold ${classes[status]??classes.unpaid}">
        ${escapeHtml(titleCase(status))}
    </span>`;
}

function setStatusBadge(element,status){
    const classes={
        unpaid:'border border-red-200 bg-red-50 text-red-600',
        partial:'border border-amber-200 bg-amber-50 text-amber-700',
        paid:'border border-emerald-200 bg-emerald-50 text-emerald-700',
        overdue:'border border-red-300 bg-red-100 text-red-700',
        waived:'border border-slate-200 bg-slate-100 text-slate-600'
    };

    element.textContent=titleCase(status);
    element.className=`w-fit rounded-md px-2.5 py-1 text-[10px] font-semibold ${classes[status]??classes.unpaid}`;
}

function titleCase(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function escapeHtml(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

document.getElementById('paymentModal').addEventListener('click',event=>{
    if(event.target===event.currentTarget)closePaymentModal();
});

document.addEventListener('keydown',event=>{
    if(event.key==='Escape')closePaymentModal();
});

document.addEventListener('DOMContentLoaded',()=>{
    initYears();
    loadSubscriptions();
});
</script>
@endpush