@extends('layouts.admin')

@section('title','Finance')
@section('page_title','Finance')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-wallet2"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">Finance & Accounting</h1>
                <p class="mt-1 text-sm text-slate-500">Financial transactions, cash flow and accounting summary.</p>
            </div>
        </div>

        <button type="button" onclick="refreshFinance()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">

        <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Cash / Bank</p>
            <p id="cashBalance" class="mt-2 text-lg font-bold text-slate-800">৳0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-600">Income</p>
            <p id="totalIncome" class="mt-2 text-lg font-bold text-emerald-700">৳0</p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/40 p-4">
            <p class="text-xs text-red-600">Expense</p>
            <p id="totalExpense" class="mt-2 text-lg font-bold text-red-700">৳0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-xs text-indigo-600">Net</p>
            <p id="netIncome" class="mt-2 text-lg font-bold text-indigo-700">৳0</p>
        </div>

        <div class="col-span-2 rounded-md border border-slate-200 bg-white p-4 shadow-sm xl:col-span-1">
            <p class="text-xs text-slate-500">Transactions</p>
            <p id="transactionCount" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">Search Transactions</p>
                <p class="hidden text-[11px] text-slate-400 sm:block">
                    Search by transaction no, description, account or creator
                </p>
            </div>
        </div>

        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">
            <div class="relative w-full sm:min-w-[240px] lg:w-72">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Search transactions..."
                    class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none"
                >
            </div>

            <select
                id="typeFilter"
                class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0"
            >
                <option value="">All Types</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="transfer">Transfer</option>
                <option value="adjustment">Adjustment</option>
            </select>

            <select
                id="statusFilter"
                class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0"
            >
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="posted">Posted</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <input
                id="dateRangeFilter"
                type="text"
                placeholder="Date range"
                autocomplete="off"
                class="js-date-range h-9 min-w-0 rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:w-[190px] lg:rounded-none lg:border-l-0"
            >

            <button
                type="button"
                onclick="clearFilters()"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0"
            >
                <i class="bi bi-x-lg text-[10px]"></i>
                Clear
            </button>
        </div>
    </div>
</div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Transaction</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Accounts</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Credit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="transactionTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">
                            Loading transactions...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Transaction Details Modal --}}
<div id="transactionModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-5">
            <div>
                <h2 id="transactionModalTitle" class="text-lg font-bold text-slate-800">Transaction</h2>
                <p id="transactionModalSubtitle" class="mt-1 text-xs text-slate-500"></p>
            </div>

            <button type="button" onclick="AdminUI.closeModal('transactionModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="transactionDetails" class="overflow-y-auto p-5"></div>
    </div>
</div>

<script>
let transactions=[];
let currentPage=1;
let lastPage=1;
let total=0;

const currency=@json(setting('currency_symbol','৳'));

const el={
    table:document.getElementById('transactionTable'),
    search:document.getElementById('searchInput'),
    typeFilter:document.getElementById('typeFilter'),
    statusFilter:document.getElementById('statusFilter'),
    dateRange:document.getElementById('dateRangeFilter')
};

function money(value){
    return currency+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function rawDate(date){
    if(!date)return '';

    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function selectedRange(){
    if(!el.dateRange._flatpickr){
        return {
            from:'',
            to:''
        };
    }

    const dates=el.dateRange._flatpickr.selectedDates;

    return {
        from:dates[0]?rawDate(dates[0]):'',
        to:dates[1]?rawDate(dates[1]):''
    };
}

async function loadTransactions(page=1){
    currentPage=page;

    const range=selectedRange();

    const query=AdminUI.query({
        search:el.search.value.trim(),
        type:el.typeFilter.value,
        status:el.statusFilter.value,
        from:range.from,
        to:range.to,
        page
    });

    try{
        const response=await api(
            `/api/finance/transactions?${query}`
        );

        const paginator=response.data??{};

        transactions=paginator.data??[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;
        total=paginator.total??0;

        document.getElementById('transactionCount').innerText=
            total;

        renderTransactions();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadTransactions
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            7
        );
    }
}

function renderTransactions(){
    if(!transactions.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No transactions found.',
            7
        );

        return;
    }

    el.table.innerHTML=transactions.map(item=>`
        <tr class="border-b border-slate-100 transition hover:bg-slate-50">

            <td class="px-4 py-4">
                <p class="text-xs font-bold text-indigo-600">
                    ${AdminUI.escapeHtml(item.transaction_no)}
                </p>

                <p class="mt-1 max-w-[260px] truncate text-xs text-slate-600">
                    ${AdminUI.escapeHtml(item.description??'No description')}
                </p>

                <p class="mt-1 text-[10px] text-slate-400">
                    ${AdminUI.formatDate(item.transaction_date)}
                </p>
            </td>

            <td class="px-4 py-4">
                ${transactionTypeBadge(item.type)}
            </td>

            <td class="px-4 py-4">
                <div class="max-w-[230px] space-y-1">
                    ${(item.entries??[]).slice(0,2).map(entry=>`
                        <p class="truncate text-[11px] text-slate-600">
                            <span class="font-semibold text-slate-700">
                                ${AdminUI.escapeHtml(entry.account?.code??'')}
                            </span>
                            ${AdminUI.escapeHtml(entry.account?.name??'')}
                        </p>
                    `).join('')}
                </div>
            </td>

            <td class="px-4 py-4 text-right text-xs font-semibold text-slate-700">
                ${money(item.total_debit)}
            </td>

            <td class="px-4 py-4 text-right text-xs font-semibold text-slate-700">
                ${money(item.total_credit)}
            </td>

            <td class="px-4 py-4">
                ${AdminUI.statusBadge(item.status)}
            </td>

            <td class="px-4 py-4">
                <div class="flex justify-end">
                    <button type="button" onclick="showTransaction(${item.id})" title="View Transaction" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                        <i class="bi bi-eye text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function transactionTypeBadge(type){
    const map={
        income:'bg-emerald-50 text-emerald-700',
        expense:'bg-red-50 text-red-700',
        deposit:'bg-sky-50 text-sky-700',
        withdrawal:'bg-amber-50 text-amber-700',
        transfer:'bg-indigo-50 text-indigo-700',
        adjustment:'bg-slate-100 text-slate-600'
    };

    return `
        <span class="rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[type]??map.adjustment}">
            ${AdminUI.escapeHtml(type)}
        </span>
    `;
}

async function showTransaction(id){
    try{
        const response=await api(
            `/api/finance/transactions/${id}`
        );

        const item=response.data??{};

        document.getElementById('transactionModalTitle')
            .innerText=item.transaction_no??'Transaction';

        document.getElementById('transactionModalSubtitle')
            .innerText=`${AdminUI.formatDate(item.transaction_date)} • ${item.type??''}`;

        const details=document.getElementById('transactionDetails');

        details.innerHTML=`
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] text-slate-400">Type</p>
                    <div class="mt-1">${transactionTypeBadge(item.type)}</div>
                </div>

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] text-slate-400">Status</p>
                    <div class="mt-1">${AdminUI.statusBadge(item.status)}</div>
                </div>

                <div class="rounded-md bg-emerald-50 p-3">
                    <p class="text-[10px] text-emerald-500">Debit</p>
                    <p class="mt-1 text-sm font-bold text-emerald-700">
                        ${money(item.total_debit)}
                    </p>
                </div>

                <div class="rounded-md bg-indigo-50 p-3">
                    <p class="text-[10px] text-indigo-500">Credit</p>
                    <p class="mt-1 text-sm font-bold text-indigo-700">
                        ${money(item.total_credit)}
                    </p>
                </div>
            </div>

            ${item.description?`
                <div class="mt-4 rounded-md border border-slate-200 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                        Description
                    </p>

                    <p class="mt-2 text-xs leading-5 text-slate-600">
                        ${AdminUI.escapeHtml(item.description)}
                    </p>
                </div>
            `:''}

            <div class="mt-4 overflow-hidden rounded-md border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">
                                Account
                            </th>

                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600">
                                Debit
                            </th>

                            <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600">
                                Credit
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        ${(item.entries??[]).map(entry=>`
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-700">
                                        ${AdminUI.escapeHtml(entry.account?.name??'Account')}
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-slate-400">
                                        ${AdminUI.escapeHtml(entry.account?.code??'')}
                                    </p>
                                </td>

                                <td class="px-4 py-3 text-right text-xs font-medium text-slate-600">
                                    ${money(entry.debit)}
                                </td>

                                <td class="px-4 py-3 text-right text-xs font-medium text-slate-600">
                                    ${money(entry.credit)}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-[10px] text-slate-400">
                <span>
                    Created by:
                    ${AdminUI.escapeHtml(item.creator?.name??'System')}
                </span>

                ${item.reference_type?`
                    <span>
                        Reference:
                        ${AdminUI.escapeHtml(item.reference_type)}
                        #${item.reference_id??''}
                    </span>
                `:''}
            </div>
        `;

        AdminUI.openModal('transactionModal');
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadFinanceSummary(){
    try{
        const [
            incomeExpenseResponse,
            cashBankResponse
        ]=await Promise.all([
            api('/api/finance/income-expense'),
            api('/api/finance/cash-bank')
        ]);

        const incomeExpense=
            incomeExpenseResponse.data??{};

        const cashBank=
            cashBankResponse.data??{};

        const income=Number(
            incomeExpense.total_income
            ??incomeExpense.income
            ??0
        );

        const expense=Number(
            incomeExpense.total_expense
            ??incomeExpense.expense
            ??0
        );

        const net=Number(
            incomeExpense.net_income
            ??incomeExpense.profit
           ??(income-expense)
        );

        document.getElementById('totalIncome')
            .innerText=money(income);

        document.getElementById('totalExpense')
            .innerText=money(expense);

        document.getElementById('netIncome')
            .innerText=money(net);

        document.getElementById('cashBalance')
            .innerText=money(
                extractCashBalance(cashBank)
            );
    }catch(error){
        console.error(error);
    }
}

function extractCashBalance(data){
    if(typeof data==='number'){
        return data;
    }

    if(Array.isArray(data)){
        return data.reduce(
            (sum,item)=>
                sum+Number(
                    item.balance
                    ??item.current_balance
                    ??0
                ),
            0
        );
    }

    return Number(
        data.balance
        ??data.total_balance
        ??data.cash_balance
        ??data.total
        ??0
    );
}

function clearFilters(){
    el.search.value='';
    el.typeFilter.value='';
    el.statusFilter.value='';

    if(el.dateRange._flatpickr){
        el.dateRange._flatpickr.clear();
    }else{
        el.dateRange.value='';
    }

    loadTransactions(1);
}

async function refreshFinance(){
    await Promise.all([
        loadTransactions(currentPage),
        loadFinanceSummary()
    ]);

    Toast.success('Finance data refreshed.');
}

async function initFinancePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initFinancePage,50);
        return;
    }

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadTransactions(1)
        )
    );

    el.typeFilter.addEventListener(
        'change',
        ()=>loadTransactions(1)
    );

    el.statusFilter.addEventListener(
        'change',
        ()=>loadTransactions(1)
    );

    if(el.dateRange._flatpickr){
        el.dateRange._flatpickr.config.onClose.push(
            selectedDates=>{
                if(
                    selectedDates.length===0||
                    selectedDates.length===2
                ){
                    loadTransactions(1);
                }
            }
        );
    }

    await Promise.all([
        loadTransactions(),
        loadFinanceSummary()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initFinancePage
    );
}else{
    initFinancePage();
}
</script>

@endsection