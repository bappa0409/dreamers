@extends('layouts.admin')

@section('title','Profit & Loss')
@section('page_title','Profit & Loss')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-graph-up"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Profit & Loss</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Income, expenses and net operating result from posted journals.</p>
            </div>
        </div>
        <div id="resultBadge" class="hidden rounded-md border px-3 py-2 text-xs 2xl:text-sm font-semibold"></div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-5">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Date Range
                </label>
                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="setCurrentMonth()"
                    class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-100">
                    <i class="bi bi-calendar-month me-1"></i>
                    Current Month
                </button>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="setCurrentYear()"
                    class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-100">
                    <i class="bi bi-calendar3 me-1"></i>
                    Current Year
                </button>
            </div>

            <div class="lg:col-span-3">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3  text-xs 2xl:text-sm font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-md border border-emerald-200 bg-emerald-50/30 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs 2xl:text-sm text-emerald-600">Total Income</p>
                    <p id="totalIncome" class="mt-2 truncate text-sm 2xl:text-xl font-bold text-emerald-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-arrow-down-left"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/30 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs 2xl:text-sm text-red-600">Total Expense</p>
                    <p id="totalExpense" class="mt-2 truncate text-sm 2xl:text-xl font-bold text-red-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-arrow-up-right"></i>
                </div>
            </div>
        </div>

        <div id="netCard" class="rounded-md border border-indigo-200 bg-indigo-50/30 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p id="netLabel" class="text-xs 2xl:text-sm text-indigo-600">Net Surplus</p>
                    <p id="netResult" class="mt-2 truncate text-sm 2xl:text-xl font-bold text-indigo-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
                <div id="netIcon" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Income & Expense --}}
    <div class="grid gap-4 xl:grid-cols-2">
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-emerald-200 bg-emerald-50/40 px-5 py-3">
                <div>
                    <h2 class="text-base font-bold text-emerald-700">Income</h2>
                    <p class="mt-0.5 text-[10px] text-emerald-600/70">Credit-normal accounts</p>
                </div>
                <i class="bi bi-arrow-down-circle text-emerald-600"></i>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-base">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs 2xl:text-sm font-semibold text-slate-500">Account</th>
                            <th class="px-4 py-2.5 text-right text-xs 2xl:text-sm font-semibold text-slate-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="incomeTable">
                        <tr>
                            <td colspan="2" class="px-4 py-10  text-xs 2xl:text-sm text-center text-slate-400">Loading...</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td class="px-4 py-3 text-right text-xs 2xl:text-sm font-bold text-slate-700">Total Income</td>
                            <td id="incomeFooter" class="px-4 py-3 text-right font-bold text-emerald-700">
                                {{ setting('currency_symbol','৳') }}0.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-red-200 bg-red-50/40 px-5 py-3">
                <div>
                    <h2 class="text-base font-bold text-red-700">Expenses</h2>
                    <p class="mt-0.5 text-[10px] text-red-600/70">Debit-normal accounts</p>
                </div>
                <i class="bi bi-arrow-up-circle text-red-600"></i>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px] text-base">
                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs 2xl:text-sm font-semibold text-slate-500">Account</th>
                            <th class="px-4 py-2.5 text-right text-xs 2xl:text-sm font-semibold text-slate-500">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="expenseTable">
                        <tr>
                            <td colspan="2" class="px-4 py-10  text-xs 2xl:text-sm text-center text-slate-400">Loading...</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td class="px-4 py-3 text-right text-xs 2xl:text-sm font-bold text-slate-700">Total Expense</td>
                            <td id="expenseFooter" class="px-4 py-3 text-right font-bold text-red-700">
                                {{ setting('currency_symbol','৳') }}0.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Operating Result --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-700">Operating Result</h2>
                <p id="periodLabel" class="mt-0.5 text-[11px] text-slate-400"></p>
            </div>
            <span class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                Posted journals only
            </span>
        </div>

        <div class="space-y-3 p-5">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <span class=" text-xs 2xl:text-sm text-slate-500">Total Income</span>
                <span id="resultIncome" class="font-bold text-emerald-700">
                    {{ setting('currency_symbol','৳') }}0.00
                </span>
            </div>

            <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <span class=" text-xs 2xl:text-sm text-slate-500">Less: Total Expense</span>
                <span id="resultExpense" class="font-bold text-red-700">
                    {{ setting('currency_symbol','৳') }}0.00
                </span>
            </div>

            <div id="resultBox" class="flex items-center justify-between gap-3 rounded-md border border-indigo-100 bg-indigo-50/40 p-4">
                <span id="resultLabel" class="text-base 2xl:text-xl font-bold text-slate-700">Net Surplus</span>
                <span id="resultNet" class="text-base 2xl:text-xl font-bold text-indigo-700">
                    {{ setting('currency_symbol','৳') }}0.00
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

const el={
    dateRange:document.getElementById('dateRangeFilter'),
    income:document.getElementById('incomeTable'),
    expense:document.getElementById('expenseTable'),
    totalIncome:document.getElementById('totalIncome'),
    totalExpense:document.getElementById('totalExpense'),
    net:document.getElementById('netResult'),
    netLabel:document.getElementById('netLabel'),
    netCard:document.getElementById('netCard'),
    netIcon:document.getElementById('netIcon'),
    incomeFooter:document.getElementById('incomeFooter'),
    expenseFooter:document.getElementById('expenseFooter'),
    period:document.getElementById('periodLabel'),
    badge:document.getElementById('resultBadge'),
    resultIncome:document.getElementById('resultIncome'),
    resultExpense:document.getElementById('resultExpense'),
    resultLabel:document.getElementById('resultLabel'),
    resultNet:document.getElementById('resultNet'),
    resultBox:document.getElementById('resultBox')
};

let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

const esc=value=>AdminUI.escapeHtml(value??'');

function money(value){
    const number=Number(value||0);

    return `${number<0?'-':''}${currency}${Math.abs(number).toLocaleString(undefined,{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    })}`;
}

function localDate(date){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function monthRange(){
    const now=new Date();

    return{
        from:new Date(
            now.getFullYear(),
            now.getMonth(),
            1
        ),
        to:new Date(
            now.getFullYear(),
            now.getMonth()+1,
            0
        )
    };
}

function yearRange(){
    const now=new Date();

    return{
        from:new Date(
            now.getFullYear(),
            0,
            1
        ),
        to:new Date(
            now.getFullYear(),
            11,
            31
        )
    };
}

function initDateRangePicker(){
    if(typeof flatpickr==='undefined'){
        console.error('Flatpickr is not loaded.');
        return;
    }

    if(el.dateRange._flatpickr){
        el.dateRange._flatpickr.destroy();
    }

    dateRangePicker=flatpickr(
        el.dateRange,
        {
            mode:'range',
            dateFormat:'Y-m-d',
            allowInput:false,
            disableMobile:true,
            onChange(selectedDates,dateStr,instance){
                if(selectedDates.length===2){
                    selectedFrom=instance.formatDate(
                        selectedDates[0],
                        'Y-m-d'
                    );

                    selectedTo=instance.formatDate(
                        selectedDates[1],
                        'Y-m-d'
                    );

                    loadProfitLoss();
                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    loadProfitLoss();
                }
            }
        }
    );
}

function setRange(from,to,load=true){
    selectedFrom=localDate(from);
    selectedTo=localDate(to);

    if(dateRangePicker){
        dateRangePicker.setDate(
            [from,to],
            false
        );
    }else{
        el.dateRange.value=
            `${selectedFrom} to ${selectedTo}`;
    }

    if(load){
        loadProfitLoss();
    }
}

window.setCurrentMonth=function(){
    const range=monthRange();

    setRange(
        range.from,
        range.to
    );
};

window.setCurrentYear=function(){
    const range=yearRange();

    setRange(
        range.from,
        range.to
    );
};

window.clearFilters=function(){
    selectedFrom='';
    selectedTo='';

    if(dateRangePicker){
        dateRangePicker.clear(
            false
        );
    }else{
        el.dateRange.value='';
    }

    loadProfitLoss();
};

async function loadProfitLoss(){
    el.income.innerHTML=AdminUI.loadingState(
        'Loading income...',
        2
    );

    el.expense.innerHTML=AdminUI.loadingState(
        'Loading expenses...',
        2
    );

    const params=new URLSearchParams();

    if(selectedFrom){
        params.set(
            'from',
            selectedFrom
        );
    }

    if(selectedTo){
        params.set(
            'to',
            selectedTo
        );
    }

    try{
        const response=await api(
            `/api/finance/profit-loss?${params.toString()}`
        );

        renderReport(
            response.data||{}
        );
    }catch(error){
        const message=AdminUI.extractError(error);

        el.income.innerHTML=AdminUI.emptyState(
            message,
            2
        );

        el.expense.innerHTML=AdminUI.emptyState(
            message,
            2
        );

        Toast.error(message);
    }
}

function renderReport(data){
    const summary=data.summary||{};
    const net=Number(
        summary.net_surplus||0
    );
    const surplus=net>=0;
    const label=surplus
        ?'Net Surplus'
        :'Net Deficit';

    renderAccounts(
        el.income,
        data.income||[],
        'income'
    );

    renderAccounts(
        el.expense,
        data.expenses||[],
        'expense'
    );

    el.totalIncome.textContent=
        money(
            summary.total_income
        );

    el.totalExpense.textContent=
        money(
            summary.total_expense
        );

    el.net.textContent=
        money(net);

    el.incomeFooter.textContent=
        money(
            summary.total_income
        );

    el.expenseFooter.textContent=
        money(
            summary.total_expense
        );

    el.resultIncome.textContent=
        money(
            summary.total_income
        );

    el.resultExpense.textContent=
        money(
            summary.total_expense
        );

    el.resultNet.textContent=
        money(net);

    el.netLabel.textContent=label;
    el.resultLabel.textContent=label;

    el.period.textContent=
        data.from&&data.to
            ?`${AdminUI.formatDate(data.from)} - ${AdminUI.formatDate(data.to)}`
            :'All available periods';

    renderNetState(
        surplus,
        net,
        label
    );
}

function renderNetState(surplus,net,label){
    el.badge.classList.remove(
        'hidden',
        'border-emerald-200',
        'bg-emerald-50',
        'text-emerald-700',
        'border-red-200',
        'bg-red-50',
        'text-red-700'
    );

    if(surplus){
        el.badge.textContent=
            `Surplus ${money(net)}`;

        el.badge.classList.add(
            'border-emerald-200',
            'bg-emerald-50',
            'text-emerald-700'
        );

        el.netCard.className=
            'rounded-md border border-emerald-200 bg-emerald-50/30 p-4';

        el.netLabel.className=
            'text-sm text-emerald-600';

        el.net.className=
            'mt-2 truncate text-xl font-bold text-emerald-700';

        el.netIcon.className=
            'flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600';

        el.netIcon.innerHTML=
            '<i class="bi bi-graph-up-arrow"></i>';

        el.resultNet.className=
            'text-xl font-bold text-emerald-700';

        el.resultBox.className=
            'flex items-center justify-between gap-3 rounded-md border border-emerald-100 bg-emerald-50/40 p-4';
    }else{
        el.badge.textContent=
            `Deficit ${money(net)}`;

        el.badge.classList.add(
            'border-red-200',
            'bg-red-50',
            'text-red-700'
        );

        el.netCard.className=
            'rounded-md border border-red-200 bg-red-50/30 p-4';

        el.netLabel.className=
            'text-sm text-red-600';

        el.net.className=
            'mt-2 truncate text-xl font-bold text-red-700';

        el.netIcon.className=
            'flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-100 text-red-600';

        el.netIcon.innerHTML=
            '<i class="bi bi-graph-down-arrow"></i>';

        el.resultNet.className=
            'text-xl font-bold text-red-700';

        el.resultBox.className=
            'flex items-center justify-between gap-3 rounded-md border border-red-100 bg-red-50/40 p-4';
    }

    el.resultLabel.textContent=label;
}

function renderAccounts(target,accounts,type){
    if(!accounts.length){
        target.innerHTML=AdminUI.emptyState(
            type==='income'
                ?'No income recorded for this period.'
                :'No expenses recorded for this period.',
            2
        );

        return;
    }

    target.innerHTML=accounts.map(account=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-semibold text-indigo-600">
                        ${esc(account.code)}
                    </span>

                    <span class="font-medium text-xs 2xl:text-sm text-slate-700">
                        ${esc(account.name)}
                    </span>
                </div>

                ${
                    account.sub_type
                        ?`
                            <div class="mt-0.5 text-[10px] text-slate-400">
                                ${esc(
                                    AdminUI.titleCase(
                                        account.sub_type
                                    )
                                )}
                            </div>
                        `
                        :''
                }

                ${
                    !account.is_active
                        ?`
                            <div class="mt-0.5 text-[10px] font-medium text-slate-400">
                                Inactive account
                            </div>
                        `
                        :''
                }
            </td>

            <td class="px-4 py-3 text-right font-semibold ${
                Number(account.balance)<0
                    ?'text-red-700'
                    :type==='income'
                        ?'text-emerald-700'
                        :'text-red-700'
            }">
                ${money(account.balance)}
            </td>
        </tr>
    `).join('');
}

function init(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'||
        typeof window.flatpickr==='undefined'
    ){
        setTimeout(
            init,
            50
        );

        return;
    }

    initDateRangePicker();

    const range=monthRange();

    setRange(
        range.from,
        range.to,
        false
    );

    loadProfitLoss();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        init
    );
}else{
    init();
}
</script>
@endpush