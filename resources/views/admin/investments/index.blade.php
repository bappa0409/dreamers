@extends('layouts.admin')

@section('title','Investments')
@section('page_title','Investments')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Investment Management</h1>
                <p class="text-xs text-slate-500">Manage association investments, returns and accounting postings.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Investment.create'))
        <button type="button" onclick="openInvestmentModal()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Investment
        </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @php
            $stats=[
                [
                    'id'=>'totalAmount',
                    'label'=>'Total Investment',
                    'icon'=>'bi-wallet2',
                    'box'=>'border-slate-200 bg-white',
                    'text'=>'text-slate-800',
                    'iconbox'=>'bg-slate-100 text-slate-500'
                ],
                [
                    'id'=>'activeCount',
                    'label'=>'Active',
                    'icon'=>'bi-activity',
                    'box'=>'border-indigo-200 bg-indigo-50/40',
                    'text'=>'text-indigo-700',
                    'iconbox'=>'bg-indigo-100 text-indigo-600'
                ],
                [
                    'id'=>'incomeReceived',
                    'label'=>'Income Received',
                    'icon'=>'bi-cash-stack',
                    'box'=>'border-emerald-200 bg-emerald-50/40',
                    'text'=>'text-emerald-700',
                    'iconbox'=>'bg-emerald-100 text-emerald-600'
                ],
                [
                    'id'=>'principalReceived',
                    'label'=>'Principal Returned',
                    'icon'=>'bi-arrow-return-left',
                    'box'=>'border-sky-200 bg-sky-50/40',
                    'text'=>'text-sky-700',
                    'iconbox'=>'bg-sky-100 text-sky-600'
                ],
                [
                    'id'=>'expectedReturnStat',
                    'label'=>'Expected Income',
                    'icon'=>'bi-graph-up',
                    'box'=>'border-amber-200 bg-amber-50/40 col-span-2 xl:col-span-1',
                    'text'=>'text-amber-700',
                    'iconbox'=>'bg-amber-100 text-amber-600'
                ]
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="rounded-md border p-4 {{ $stat['box'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">{{ $stat['label'] }}</p>
                    <p id="{{ $stat['id'] }}" class="mt-2 truncate text-xl font-bold {{ $stat['text'] }}">
                        @if($stat['id']==='activeCount')
                            0
                        @else
                            {{ setting('currency_symbol','৳') }}0
                        @endif
                    </p>
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $stat['iconbox'] }}">
                    <i class="bi {{ $stat['icon'] }} text-base"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Investments</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by investment number, title or description.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-[1fr_150px_auto] lg:w-auto lg:grid-cols-[300px_160px_auto] lg:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search investment..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
                </div>

                <select id="statusFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button type="button" onclick="clearFilters()"
                    class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Investment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Payment Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Income</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Principal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="investmentTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">
                            Loading investments...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="investmentMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
            Loading investments...
        </div>
    </div>

    <div id="paginationWrap" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- Investment Modal --}}
<div id="investmentModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <h3 id="investmentModalTitle" class="text-sm font-semibold text-slate-800">
                        Add Investment
                    </h3>
                    <p class="text-xs text-slate-500">
                        Record an association investment and automatically post the accounting journal.
                    </p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('investmentModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="investmentForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="investmentError"
                    class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700">
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">
                            Payment Account <span class="text-red-500">*</span>
                        </label>
                        <select id="paymentAccountId" class="app-input w-full">
                            <option value="">Select Cash/Bank</option>
                        </select>
                        <p data-field-error="paymentAccountId"
                            class="mt-1 hidden text-sm text-red-600"></p>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Journal: Dr Investment Asset, Cr selected Cash/Bank.
                        </p>
                    </div>

                    <div>
                        <label class="form-label">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="investmentStatus" class="app-input w-full">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                        </select>
                        <p data-field-error="investmentStatus"
                            class="mt-1 hidden text-sm text-red-600"></p>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Completed status is set automatically after full principal recovery.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">
                            Investment Title <span class="text-red-500">*</span>
                        </label>
                        <input id="investmentTitle" type="text"
                            maxlength="150"
                            class="app-input w-full">
                        <p data-field-error="investmentTitle"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <input id="investmentAmount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="app-input w-full">
                        <p data-field-error="investmentAmount"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Expected Income</label>
                        <input id="investmentExpectedReturn"
                            type="number"
                            min="0"
                            step="0.01"
                            class="app-input w-full">
                        <p data-field-error="investmentExpectedReturn"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Investment Date <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="investmentDate"
                                type="text"
                                class="app-input js-date-picker !pl-9"
                                placeholder="Select date"
                                autocomplete="off">
                        </div>
                        <p data-field-error="investmentDate"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Maturity Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="maturityDate"
                                type="text"
                                class="app-input js-date-picker !pl-9"
                                placeholder="Select date"
                                autocomplete="off">
                        </div>
                        <p data-field-error="maturityDate"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="investmentDescription"
                            rows="3"
                            maxlength="5000"
                            class="app-input w-full resize-none"></textarea>
                        <p data-field-error="investmentDescription"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button"
                    onclick="AdminUI.closeModal('investmentModal')"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Close
                </button>

                <button id="saveInvestmentButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Save Investment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Return Modal --}}
<div id="returnModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h3 id="returnModalTitle" class="text-sm font-semibold text-slate-800">
                        Add Return
                    </h3>
                    <p id="returnInvestmentInfo" class="text-xs text-slate-500"></p>
                </div>
            </div>

            <button type="button"
                onclick="AdminUI.closeModal('returnModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="returnForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="returnInvestmentId" type="hidden">
            <input id="returnId" type="hidden">

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="returnError"
                    class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">
                            Return Type <span class="text-red-500">*</span>
                        </label>
                        <select id="returnType" class="app-input w-full">
                            <option value="income">Investment Income</option>
                            <option value="principal">Principal Return</option>
                        </select>
                        <p data-field-error="returnType"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="returnStatus" class="app-input w-full">
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <p data-field-error="returnStatus"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <input id="returnAmount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="app-input w-full">
                        <p data-field-error="returnAmount"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Return Date <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="returnDate"
                                type="text"
                                class="app-input js-date-picker !pl-9"
                                placeholder="Select date"
                                autocomplete="off">
                        </div>

                        <p data-field-error="returnDate"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div id="receiveAccountWrap" class="md:col-span-2">
                        <label class="form-label">
                            Receive Account
                        </label>

                        <select id="receiveAccountId" class="app-input w-full">
                            <option value="">Select Cash/Bank</option>
                        </select>

                        <p data-field-error="receiveAccountId"
                            class="mt-1 hidden text-sm text-red-600"></p>

                        <p class="mt-1 text-[11px] text-slate-400">
                            Income: Dr Cash/Bank, Cr Investment Income. Principal: Dr Cash/Bank, Cr Investment Asset.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="returnDescription"
                            rows="3"
                            maxlength="3000"
                            class="app-input w-full resize-none"></textarea>
                        <p data-field-error="returnDescription"
                            class="mt-1 hidden text-sm text-red-600"></p>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button"
                    onclick="AdminUI.closeModal('returnModal')"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Close
                </button>

                <button id="saveReturnButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60">
                    Save Return
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Details Modal --}}
<div id="detailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-card-list"></i>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Investment Details</h3>
                    <p class="text-xs text-slate-500">
                        Investment, returns and accounting journals.
                    </p>
                </div>
            </div>

            <button type="button"
                onclick="AdminUI.closeModal('detailsModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsContent"
            class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5"></div>
    </div>
</div>
@endsection

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Investment.update'));
const canDelete=@json(auth()->user()->hasPermission('Investment.delete'));

let investments=[];
let cashBankAccounts=[];
let editingInvestment=null;
let currentPage=1;
let currentDetailReturns={};

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>`${currency}${Number(value||0).toLocaleString(undefined,{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

const date=value=>value
    ?AdminUI.formatDate(value)
    :'—';

const today=()=>{
    const now=new Date();
    const offset=now.getTimezoneOffset();

    return new Date(
        now.getTime()-offset*60000
    ).toISOString().slice(0,10);
};

const investmentFieldMap={
    payment_account_id:'paymentAccountId',
    title:'investmentTitle',
    amount:'investmentAmount',
    expected_return:'investmentExpectedReturn',
    investment_date:'investmentDate',
    maturity_date:'maturityDate',
    status:'investmentStatus',
    description:'investmentDescription'
};

const returnFieldMap={
    return_type:'returnType',
    receive_account_id:'receiveAccountId',
    amount:'returnAmount',
    return_date:'returnDate',
    status:'returnStatus',
    description:'returnDescription'
};

function paymentAccountName(investment){
    if(!investment?.payment_account){
        return '—';
    }

    return[
        investment.payment_account.code,
        investment.payment_account.name
    ].filter(Boolean).join(' - ');
}

function principalProgress(investment){
    const amount=Number(
        investment?.amount||0
    );

    const returned=Number(
        investment?.principal_return_total||0
    );

    if(amount<=0){
        return 0;
    }

    return Math.min(
        100,
        Math.max(
            0,
            returned/amount*100
        )
    );
}

function remainingPrincipal(investment){
    return Math.max(
        0,
        Number(investment?.amount||0)-
        Number(investment?.principal_return_total||0)
    );
}

function setDate(id,value){
    const element=$(id);

    if(!element){
        return;
    }

    const dateValue=value
        ?String(value).substring(0,10)
        :'';

    element.value=dateValue;

    if(element._flatpickr){
        dateValue
            ?element._flatpickr.setDate(
                dateValue,
                false,
                'Y-m-d'
            )
            :element._flatpickr.clear();
    }
}

async function loadOptions(){
    try{
        const response=await api(
            '/api/investments/options'
        );

        cashBankAccounts=
            response.data?.cash_bank_accounts||[];

        const options=
            `<option value="">Select Cash/Bank</option>`+
            cashBankAccounts.map(account=>`
                <option value="${account.id}">
                    ${esc(account.code)} - ${esc(account.name)}
                </option>
            `).join('');

        $('paymentAccountId').innerHTML=options;
        $('receiveAccountId').innerHTML=options;

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadStatistics(){
    try{
        const response=await api(
            '/api/investments/statistics'
        );

        const data=response.data||{};

        $('totalAmount').textContent=
            money(data.total_amount);

        $('activeCount').textContent=
            data.active||0;

        $('incomeReceived').textContent=
            money(data.income_received);

        $('principalReceived').textContent=
            money(data.principal_received);

        $('expectedReturnStat').textContent=
            money(data.expected_return);

    }catch(error){
        console.error(error);
    }
}

async function loadInvestments(page=1){
    currentPage=page;

    const tbody=$('investmentTableBody');
    const grid=$('investmentMobileGrid');

    if(tbody){
        tbody.innerHTML=
            AdminUI.loadingState(
                'Loading investments...',
                8
            );
    }

    if(grid){
        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                <div class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                    Loading investments...
                </div>
            </div>
        `;
    }

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;

    if(search){
        params.set(
            'search',
            search
        );
    }

    if(status){
        params.set(
            'status',
            status
        );
    }

    try{
        const response=await api(
            `/api/investments?${params.toString()}`
        );

        const paginator=response.data||{};

        investments=paginator.data||[];

        renderInvestments();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadInvestments
        );

    }catch(error){
        const message=
            AdminUI.extractError(error);

        if(tbody){
            tbody.innerHTML=
                AdminUI.emptyState(
                    message,
                    8
                );
        }

        if(grid){
            grid.innerHTML=`
                <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                    ${esc(message)}
                </div>
            `;
        }
    }
}

function investmentActions(investment){
    const actions=[
        `
        <button type="button"
            onclick="viewInvestment(${investment.id})"
            class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-eye"></i>
            View
        </button>
        `
    ];

    if(
        canUpdate&&
        !['cancelled'].includes(
            investment.status
        )
    ){
        actions.push(`
            <button type="button"
                onclick="editInvestment(${investment.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <i class="bi bi-pencil"></i>
                Edit
            </button>
        `);

        if(
            ['active','completed'].includes(
                investment.status
            )
        ){
            actions.push(`
                <button type="button"
                    onclick="openReturnModal(${investment.id})"
                    class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                    <i class="bi bi-cash-coin"></i>
                    Return
                </button>
            `);
        }

        if(
            investment.status!=='completed'
        ){
            actions.push(`
                <button type="button"
                    onclick="cancelInvestment(${investment.id})"
                    class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-700 transition hover:bg-red-100">
                    <i class="bi bi-x-circle"></i>
                    Cancel
                </button>
            `);
        }
    }

    if(
        canDelete&&
        !investment.finance_transaction_id
    ){
        actions.push(`
            <button type="button"
                onclick="deleteInvestment(${investment.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50">
                <i class="bi bi-trash3"></i>
                Delete
            </button>
        `);
    }

    return actions.join('');
}

function renderInvestments(){
    const tbody=$('investmentTableBody');
    const grid=$('investmentMobileGrid');

    if(!investments.length){
        if(tbody){
            tbody.innerHTML=
                AdminUI.emptyState(
                    'No investments found.',
                    8
                );
        }

        if(grid){
            grid.innerHTML=`
                <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-inbox"></i>
                    </div>

                    <p class="mt-3 text-base font-semibold text-slate-600">
                        No investments found
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        Try changing the search or status filter.
                    </p>
                </div>
            `;
        }

        return;
    }

    if(tbody){
        tbody.innerHTML=
            investments.map(investment=>`
                <tr class="transition hover:bg-slate-50/70">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-graph-up-arrow text-base"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="font-semibold text-xs text-slate-700">
                                    ${esc(investment.investment_no)}
                                </div>

                                <div class="mt-0.5 max-w-[220px] truncate text-sm text-slate-400"
                                    title="${esc(investment.title)}">
                                    ${esc(investment.title)}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-3">
                        <div class="max-w-[190px] truncate text-base text-slate-500">
                            ${esc(paymentAccountName(investment))}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-right">
                        <div class="font-semibold text-xs text-slate-700">
                            ${money(investment.amount)}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-right">
                        <div class="font-semibold text-emerald-700">
                            ${money(investment.paid_return_total)}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-right">
                        <div class="font-medium text-sky-700">
                            ${money(investment.principal_return_total)}
                        </div>

                        <div class="mt-0.5 text-[10px] text-slate-400">
                            ${principalProgress(investment).toFixed(0)}% recovered
                        </div>
                    </td>

                    <td class="px-4 py-3 text-slate-500">
                        ${date(investment.investment_date)}
                    </td>

                    <td class="px-4 py-3">
                        ${AdminUI.statusBadge(investment.status)}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex flex-wrap justify-end gap-1">
                            ${investmentActions(investment)}
                        </div>
                    </td>
                </tr>
            `).join('');
    }

    if(grid){
        grid.innerHTML=
            investments.map(investment=>{
                const progress=
                    principalProgress(investment);

                return`
                    <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                        <i class="bi bi-graph-up-arrow"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-base font-bold text-slate-700">
                                            ${esc(investment.title)}
                                        </p>

                                        <p class="mt-0.5 text-[11px] font-medium text-indigo-600">
                                            ${esc(investment.investment_no)}
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    ${AdminUI.statusBadge(investment.status)}
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4 p-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-md bg-slate-50 p-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                        Invested
                                    </p>

                                    <p class="mt-1 truncate text-base font-bold text-slate-700">
                                        ${money(investment.amount)}
                                    </p>
                                </div>

                                <div class="rounded-md bg-emerald-50 p-3">
                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">
                                        Income
                                    </p>

                                    <p class="mt-1 truncate text-base font-bold text-emerald-700">
                                        ${money(investment.paid_return_total)}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <p class="text-[11px] font-medium text-slate-500">
                                        Principal recovery
                                    </p>

                                    <p class="text-[11px] font-semibold text-slate-600">
                                        ${progress.toFixed(0)}%
                                    </p>
                                </div>

                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="h-full rounded-full bg-indigo-500 transition-all"
                                        style="width:${progress}%"></div>
                                </div>

                                <div class="mt-1.5 flex items-center justify-between gap-2 text-[10px] text-slate-400">
                                    <span>
                                        Returned ${money(investment.principal_return_total)}
                                    </span>

                                    <span>
                                        Due ${money(remainingPrincipal(investment))}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-x-3 gap-y-2 border-t border-slate-100 pt-3 text-sm">
                                <div>
                                    <p class="text-[10px] text-slate-400">
                                        Investment Date
                                    </p>
                                    <p class="mt-0.5 font-medium text-slate-600">
                                        ${date(investment.investment_date)}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-[10px] text-slate-400">
                                        Maturity
                                    </p>
                                    <p class="mt-0.5 font-medium text-slate-600">
                                        ${date(investment.maturity_date)}
                                    </p>
                                </div>

                                <div class="col-span-2">
                                    <p class="text-[10px] text-slate-400">
                                        Payment Account
                                    </p>
                                    <p class="mt-0.5 truncate font-medium text-slate-600">
                                        ${esc(paymentAccountName(investment))}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                            ${investmentActions(investment)}
                        </div>
                    </article>
                `;
            }).join('');
    }
}

window.openInvestmentModal=function(){
    editingInvestment=null;

    $('investmentForm').reset();

    AdminUI.clearError(
        'investmentError'
    );

    AdminUI.clearFieldErrors(
        'investmentForm'
    );

    $('investmentModalTitle')
        .textContent='Add Investment';

    $('investmentStatus').disabled=false;
    $('investmentStatus').value='active';

    [
        'paymentAccountId',
        'investmentAmount',
        'investmentDate'
    ].forEach(id=>{
        $(id).disabled=false;
    });

    AdminUI.openModal(
        'investmentModal'
    );

    window.initDatePickers?.();

    setDate(
        'investmentDate',
        today()
    );

    setDate(
        'maturityDate',
        ''
    );
};

window.editInvestment=async function(id){
    try{
        const response=await api(
            `/api/investments/${id}`
        );

        const investment=response.data;

        editingInvestment=investment;

        $('investmentForm').reset();

        AdminUI.clearError(
            'investmentError'
        );

        AdminUI.clearFieldErrors(
            'investmentForm'
        );

        $('investmentModalTitle')
            .textContent='Edit Investment';

        $('paymentAccountId').value=
            investment.payment_account_id||'';

        $('investmentTitle').value=
            investment.title||'';

        $('investmentAmount').value=
            investment.amount||'';

        $('investmentExpectedReturn').value=
            investment.expected_return||0;

        $('investmentDescription').value=
            investment.description||'';

        /*
        | Completed is system-managed.
        | Do not expose it as an editable option.
        */
        const systemCompleted=
            investment.status==='completed';

        $('investmentStatus').value=
            investment.status==='pending'
                ?'pending'
                :'active';

        $('investmentStatus').disabled=
            systemCompleted;

        const posted=Boolean(
            investment.finance_transaction_id
        );

        [
            'paymentAccountId',
            'investmentAmount',
            'investmentDate'
        ].forEach(field=>{
            $(field).disabled=posted;
        });

        AdminUI.openModal(
            'investmentModal'
        );

        window.initDatePickers?.();

        setDate(
            'investmentDate',
            investment.investment_date
        );

        setDate(
            'maturityDate',
            investment.maturity_date
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

$('investmentForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'investmentError'
        );

        AdminUI.clearFieldErrors(
            'investmentForm'
        );

        const required={
            paymentAccountId:'Please select a payment account.',
            investmentTitle:'Investment title is required.',
            investmentAmount:'Investment amount is required.',
            investmentDate:'Investment date is required.',
            investmentStatus:'Investment status is required.'
        };

        /*
        | Posted record's disabled fields are intentionally skipped
        | from client-side required validation during edit.
        */
        if(
            editingInvestment?.finance_transaction_id
        ){
            delete required.paymentAccountId;
            delete required.investmentAmount;
            delete required.investmentDate;
        }

        if(
            editingInvestment?.status==='completed'
        ){
            delete required.investmentStatus;
        }

        if(
            !AdminUI.validateForm(
                'investmentForm',
                required
            )
        ){
            return;
        }

        const amount=Number(
            $('investmentAmount').value
        );

        if(
            !editingInvestment?.finance_transaction_id&&
            (!Number.isFinite(amount)||amount<=0)
        ){
            AdminUI.showFieldError(
                'investmentAmount',
                'Investment amount must be greater than zero.'
            );

            return;
        }

        const investmentDate=
            $('investmentDate').value;

        const maturityDate=
            $('maturityDate').value;

        if(
            investmentDate&&
            maturityDate&&
            maturityDate<investmentDate
        ){
            AdminUI.showFieldError(
                'maturityDate',
                'Maturity date must be on or after investment date.'
            );

            return;
        }

        const data={
            payment_account_id:Number(
                $('paymentAccountId').value
            ),

            title:
                $('investmentTitle')
                    .value.trim(),

            amount,

            expected_return:Number(
                $('investmentExpectedReturn').value||0
            ),

            investment_date:
                investmentDate,

            maturity_date:
                maturityDate||null,

            status:
                $('investmentStatus').value,

            description:
                $('investmentDescription')
                    .value.trim()||null
        };

        if(
            editingInvestment?.finance_transaction_id
        ){
            delete data.payment_account_id;
            delete data.amount;
            delete data.investment_date;
        }

        if(
            editingInvestment?.status==='completed'
        ){
            delete data.status;
        }

        const button=
            $('saveInvestmentButton');

        AdminUI.setLoading(
            button,
            editingInvestment
                ?'Updating...'
                :'Saving...'
        );

        try{
            await api(
                editingInvestment
                    ?`/api/investments/${editingInvestment.id}`
                    :'/api/investments',
                {
                    method:
                        editingInvestment
                            ?'PUT'
                            :'POST',

                    body:JSON.stringify(
                        data
                    )
                }
            );

            AdminUI.closeModal(
                'investmentModal'
            );

            Toast.success(
                editingInvestment
                    ?'Investment updated successfully.'
                    :data.status==='active'
                        ?'Investment created and accounting entry posted successfully.'
                        :'Investment saved as pending.'
            );

            await Promise.all([
                loadInvestments(
                    editingInvestment
                        ?currentPage
                        :1
                ),
                loadStatistics()
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    'investmentForm',
                    error,
                    investmentFieldMap
                )
            ){
                AdminUI.showError(
                    'investmentError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.openReturnModal=function(
    id,
    returnData=null
){
    const investment=
        investments.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!investment){
        Toast.error(
            'Investment not found.'
        );

        return;
    }

    if(
        !['active','completed'].includes(
            investment.status
        )
    ){
        Toast.error(
            'Returns can only be added to active or completed investments.'
        );

        return;
    }

    $('returnForm').reset();

    AdminUI.clearError(
        'returnError'
    );

    AdminUI.clearFieldErrors(
        'returnForm'
    );

    $('returnInvestmentId').value=id;
    $('returnId').value=
        returnData?.id||'';

    $('returnModalTitle').textContent=
        returnData
            ?'Edit Return'
            :'Add Return';

    $('returnInvestmentInfo').textContent=
        `${investment.investment_no} • ${investment.title}`;

    $('returnType').value=
        returnData?.return_type||
        'income';

    $('returnStatus').value=
        returnData?.status||
        'paid';

    $('returnAmount').value=
        returnData?.amount||
        '';

    $('receiveAccountId').value=
        returnData?.receive_account_id||
        '';

    $('returnDescription').value=
        returnData?.description||
        '';

    syncReturnAccount();

    AdminUI.openModal(
        'returnModal'
    );

    window.initDatePickers?.();

    setDate(
        'returnDate',
        returnData?.return_date||
        today()
    );
};

function syncReturnAccount(){
    const paid=
        $('returnStatus').value===
        'paid';

    $('receiveAccountWrap')
        .classList.remove(
            'opacity-60'
        );

    $('receiveAccountId')
        .disabled=false;

    const label=$('receiveAccountWrap')
        .querySelector('label');

    if(label){
        label.innerHTML=paid
            ?'Receive Account <span class="text-red-500">*</span>'
            :'Receive Account <span class="text-slate-400">(Optional)</span>';
    }

    if(!paid){
        AdminUI.clearFieldError(
            'receiveAccountId'
        );
    }
}

$('returnStatus').addEventListener(
    'change',
    syncReturnAccount
);

$('returnForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'returnError'
        );

        AdminUI.clearFieldErrors(
            'returnForm'
        );

        const required={
            returnType:'Return type is required.',
            returnStatus:'Status is required.',
            returnAmount:'Return amount is required.',
            returnDate:'Return date is required.'
        };

        if(
            $('returnStatus').value===
            'paid'
        ){
            required.receiveAccountId=
                'Receive account is required.';
        }

        if(
            !AdminUI.validateForm(
                'returnForm',
                required
            )
        ){
            return;
        }

        const amount=
            Number(
                $('returnAmount').value
            );

        if(
            !Number.isFinite(amount)||
            amount<=0
        ){
            AdminUI.showFieldError(
                'returnAmount',
                'Return amount must be greater than zero.'
            );

            return;
        }

        const investmentId=
            $('returnInvestmentId').value;

        const returnId=
            $('returnId').value;

        const status=
            $('returnStatus').value;

        const receiveAccountValue=
            $('receiveAccountId').value;

        const data={
            return_type:
                $('returnType').value,

            receive_account_id:
                receiveAccountValue
                    ?Number(receiveAccountValue)
                    :null,

            amount,

            return_date:
                $('returnDate').value,

            status,

            description:
                $('returnDescription')
                    .value.trim()||null
        };

        const button=
            $('saveReturnButton');

        AdminUI.setLoading(
            button,
            returnId
                ?'Updating...'
                :'Saving...'
        );

        try{
            await api(
                returnId
                    ?`/api/investments/returns/${returnId}`
                    :`/api/investments/${investmentId}/returns`,
                {
                    method:
                        returnId
                            ?'PUT'
                            :'POST',

                    body:JSON.stringify(
                        data
                    )
                }
            );

            AdminUI.closeModal(
                'returnModal'
            );

            Toast.success(
                returnId
                    ?'Return updated successfully.'
                    :status==='paid'
                        ?'Return saved and accounting entry posted successfully.'
                        :'Return saved successfully.'
            );

            await Promise.all([
                loadInvestments(
                    currentPage
                ),
                loadStatistics()
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    'returnForm',
                    error,
                    returnFieldMap
                )
            ){
                AdminUI.showError(
                    'returnError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

function journalHtml(
    title,
    journal
){
    return`
        <div class="mt-4 overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 bg-slate-50 px-3 py-2">
                <span class="text-sm font-semibold text-slate-600">
                    ${esc(title)}
                </span>

                <span class="rounded bg-white px-2 py-1 text-[10px] font-medium text-slate-500">
                    ${esc(journal.transaction_no||'')}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-2 text-left text-slate-500">
                                Account
                            </th>
                            <th class="px-3 py-2 text-right text-slate-500">
                                Debit
                            </th>
                            <th class="px-3 py-2 text-right text-slate-500">
                                Credit
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        ${(journal.entries||[])
                            .map(entry=>`
                                <tr class="border-b border-slate-50">
                                    <td class="px-3 py-2 text-xs text-slate-600">
                                        ${esc(entry.account?.code||'')} -
                                        ${esc(entry.account?.name||'')}
                                    </td>

                                    <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                        ${money(entry.debit)}
                                    </td>

                                    <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                        ${money(entry.credit)}
                                    </td>
                                </tr>
                            `)
                            .join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

window.viewInvestment=async function(id){
    try{
        const response=await api(
            `/api/investments/${id}`
        );

        const investment=
            response.data;

        const returns=
            investment.returns||[];

        const progress=
            principalProgress(
                investment
            );

        currentDetailReturns=
            Object.fromEntries(
                returns.map(item=>[
                    Number(item.id),
                    item
                ])
            );

        $('detailsContent').innerHTML=`
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-md border border-slate-200 bg-slate-50/50 p-3">
                    <p class="text-[11px] text-slate-400">
                        Investment No
                    </p>
                    <p class="mt-1 truncate text-base font-bold text-slate-700">
                        ${esc(investment.investment_no)}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-3">
                    <p class="text-[11px] text-slate-400">
                        Amount
                    </p>
                    <p class="mt-1 truncate text-base font-bold text-slate-700">
                        ${money(investment.amount)}
                    </p>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-3">
                    <p class="text-[11px] text-emerald-600">
                        Income
                    </p>
                    <p class="mt-1 truncate text-base font-bold text-emerald-700">
                        ${money(investment.paid_return_total)}
                    </p>
                </div>

                <div class="rounded-md border border-sky-200 bg-sky-50/50 p-3">
                    <p class="text-[11px] text-sky-600">
                        Principal Returned
                    </p>
                    <p class="mt-1 truncate text-base font-bold text-sky-700">
                        ${money(investment.principal_return_total)}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-500">
                            Principal Recovery
                        </span>

                        <span class="text-sm font-bold text-indigo-600">
                            ${progress.toFixed(0)}%
                        </span>
                    </div>

                    <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-indigo-500"
                            style="width:${progress}%"></div>
                    </div>
                </div>

                <div class="grid gap-3 text-base md:grid-cols-2">
                    <div>
                        <span class="text-slate-400">Title:</span>
                        <span class="font-medium text-slate-700">
                            ${esc(investment.title)}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400">Payment:</span>
                        <span class="font-medium text-slate-700">
                            ${esc(paymentAccountName(investment))}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400">Status:</span>
                        ${AdminUI.statusBadge(investment.status)}
                    </div>

                    <div>
                        <span class="text-slate-400">Investment Date:</span>
                        ${date(investment.investment_date)}
                    </div>

                    <div>
                        <span class="text-slate-400">Maturity:</span>
                        ${date(investment.maturity_date)}
                    </div>

                    <div>
                        <span class="text-slate-400">Expected Income:</span>
                        <span class="font-medium text-slate-700">
                            ${money(investment.expected_return)}
                        </span>
                    </div>

                    <div>
                        <span class="text-slate-400">Principal Due:</span>
                        <span class="font-medium text-slate-700">
                            ${money(remainingPrincipal(investment))}
                        </span>
                    </div>

                    <div class="md:col-span-2">
                        <span class="text-slate-400">Description:</span>
                        <span class="text-slate-700">
                            ${esc(investment.description||'—')}
                        </span>
                    </div>
                </div>
            </div>

            ${
                investment.finance_transaction
                    ?journalHtml(
                        'Purchase Journal',
                        investment.finance_transaction
                    )
                    :''
            }

            <div class="mt-5 overflow-hidden rounded-md border border-slate-200">
                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <h4 class="text-base font-bold text-slate-700">
                        Returns
                    </h4>

                    <p class="text-[11px] text-slate-400">
                        Investment income and principal return history.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-base">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="px-3 py-2 text-left text-sm text-slate-500">Date</th>
                                <th class="px-3 py-2 text-left text-sm text-slate-500">Type</th>
                                <th class="px-3 py-2 text-right text-sm text-slate-500">Amount</th>
                                <th class="px-3 py-2 text-left text-sm text-slate-500">Account</th>
                                <th class="px-3 py-2 text-left text-sm text-slate-500">Status</th>
                                <th class="px-3 py-2 text-right text-sm text-slate-500">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${
                                returns.length
                                    ?returns.map(item=>`
                                        <tr class="border-t border-slate-100">
                                            <td class="px-3 py-2">
                                                ${date(item.return_date)}
                                            </td>

                                            <td class="px-3 py-2">
                                                ${esc(
                                                    AdminUI.titleCase(
                                                        item.return_type
                                                    )
                                                )}
                                            </td>

                                            <td class="px-3 py-2 text-right font-medium">
                                                ${money(item.amount)}
                                            </td>

                                            <td class="px-3 py-2">
                                                ${
                                                    item.receive_account
                                                        ?`${esc(item.receive_account.code)} - ${esc(item.receive_account.name)}`
                                                        :'—'
                                                }
                                            </td>

                                            <td class="px-3 py-2">
                                                ${AdminUI.statusBadge(item.status)}
                                            </td>

                                            <td class="px-3 py-2 text-right">
                                                ${
                                                    canUpdate&&
                                                    !item.finance_transaction_id
                                                        ?`
                                                            <button type="button"
                                                                onclick="editReturnFromDetails(${item.id},${investment.id})"
                                                                class="mr-1 rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">
                                                                Edit
                                                            </button>

                                                            <button type="button"
                                                                onclick="deleteReturn(${item.id},${investment.id})"
                                                                class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-sm font-semibold text-red-700 hover:bg-red-100">
                                                                Delete
                                                            </button>
                                                        `
                                                        :''
                                                }
                                            </td>
                                        </tr>

                                        ${
                                            item.finance_transaction
                                                ?`
                                                    <tr>
                                                        <td colspan="6" class="px-3 pb-3">
                                                            ${journalHtml(
                                                                'Return Journal',
                                                                item.finance_transaction
                                                            )}
                                                        </td>
                                                    </tr>
                                                `
                                                :''
                                        }
                                    `).join('')
                                    :`
                                        <tr>
                                            <td colspan="6"
                                                class="px-3 py-8 text-center text-slate-400">
                                                No returns recorded.
                                            </td>
                                        </tr>
                                    `
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        AdminUI.openModal(
            'detailsModal'
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.editReturnFromDetails=function(
    returnId,
    investmentId
){
    const item=
        currentDetailReturns[
            Number(returnId)
        ];

    if(!item){
        Toast.error(
            'Return not found.'
        );

        return;
    }

    AdminUI.closeModal(
        'detailsModal'
    );

    openReturnModal(
        investmentId,
        item
    );
};

window.deleteReturn=function(
    id,
    investmentId
){
    AdminUI.deleteRequest(
        `/api/investments/returns/${id}`,
        {
            message:
                'Delete this pending investment return?',

            successMessage:
                'Return deleted successfully.',

            onSuccess:async()=>{
                AdminUI.closeModal(
                    'detailsModal'
                );

                await Promise.all([
                    loadInvestments(
                        currentPage
                    ),
                    loadStatistics()
                ]);
            }
        }
    );
};

window.cancelInvestment=function(id){
    const investment=
        investments.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!investment){
        Toast.error(
            'Investment not found.'
        );

        return;
    }

    AdminUI.request(
        `/api/investments/${id}/cancel`,
        {
            method:'POST',
            data:{},

            confirmation:{
                title:'Cancel Investment?',

                message:
                    investment.finance_transaction_id
                        ?`Cancel ${investment.investment_no}? The purchase journal will be reversed.`
                        :`Cancel ${investment.investment_no}?`,

                confirmText:
                    'Cancel Investment',

                type:'danger'
            },

            successMessage:
                investment.finance_transaction_id
                    ?'Investment cancelled and journal reversed.'
                    :'Investment cancelled successfully.',

            onSuccess:async()=>
                Promise.all([
                    loadInvestments(
                        currentPage
                    ),
                    loadStatistics()
                ])
        }
    );
};

window.deleteInvestment=function(id){
    AdminUI.deleteRequest(
        `/api/investments/${id}`,
        {
            message:
                'Delete this unposted investment permanently?',

            successMessage:
                'Investment deleted successfully.',

            onSuccess:async()=>
                Promise.all([
                    loadInvestments(
                        currentPage
                    ),
                    loadStatistics()
                ])
        }
    );
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('statusFilter').value='';

    loadInvestments(1);
};

async function init(){
    if(
        typeof AdminUI==='undefined'||
        typeof api==='undefined'
    ){
        setTimeout(
            init,
            50
        );

        return;
    }

    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadInvestments(1)
        )
    );

    $('statusFilter').addEventListener(
        'change',
        ()=>loadInvestments(1)
    );
window.initDatePickers?.();

    await loadOptions();

    await Promise.all([
        loadInvestments(),
        loadStatistics()
    ]);
}

document.readyState==='loading'
    ?document.addEventListener(
        'DOMContentLoaded',
        init
    )
    :init();
</script>
@endpush