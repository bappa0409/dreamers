@extends('layouts.admin')

@section('title','Trial Balance')
@section('page_title','Trial Balance')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bar-chart-steps"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Trial Balance</h1>
                <p class="text-sm text-slate-500">Account balances derived from posted journal entries.</p>
            </div>
        </div>
        <div id="balanceState" class="hidden rounded-md border px-3 py-2 text-xs font-semibold"></div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-4">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Code, account or sub type..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Account Type
                </label>
                <select
                    id="typeFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Types</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    As Of Date
                </label>
                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input
                        id="asOfFilter"
                        type="text"
                        class="js-date-picker h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date"
                        autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Zero Balance
                </label>
                <label class="flex h-9 cursor-pointer items-center gap-2 rounded-md border border-slate-300 bg-white px-3 transition hover:bg-slate-50">
                    <input
                        id="showZeroFilter"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-slate-600">Show Zero</span>
                </label>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Accounts</p>
            <p id="totalAccounts" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Debit</p>
            <p id="totalDebit" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Credit</p>
            <p id="totalCredit" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Difference</p>
            <p id="difference" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>
    </div>

    {{-- Trial Balance --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-700">Trial Balance</h2>
                <p id="reportPeriod" class="text-[11px] text-slate-400"></p>
            </div>
            <p class="text-[11px] text-slate-400">
                Posting accounts only
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Account</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Sub Type</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Debit</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Credit</th>
                    </tr>
                </thead>

                <tbody id="trialBalanceTable">
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                            Loading Trial Balance...
                        </td>
                    </tr>
                </tbody>

                <tfoot class="border-t border-slate-200 bg-slate-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right text-xs font-bold text-slate-700">
                            Total
                        </td>
                        <td id="footerDebit" class="px-4 py-3 text-right text-sm font-bold text-slate-800">
                            {{ setting('currency_symbol','৳') }}0.00
                        </td>
                        <td id="footerCredit" class="px-4 py-3 text-right text-sm font-bold text-slate-800">
                            {{ setting('currency_symbol','৳') }}0.00
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
let currentPage=1;

const el={
    search:document.getElementById('searchInput'),
    type:document.getElementById('typeFilter'),
    asOf:document.getElementById('asOfFilter'),
    showZero:document.getElementById('showZeroFilter'),
    table:document.getElementById('trialBalanceTable'),
    pagination:document.getElementById('paginationContainer'),
    totalAccounts:document.getElementById('totalAccounts'),
    totalDebit:document.getElementById('totalDebit'),
    totalCredit:document.getElementById('totalCredit'),
    difference:document.getElementById('difference'),
    footerDebit:document.getElementById('footerDebit'),
    footerCredit:document.getElementById('footerCredit'),
    state:document.getElementById('balanceState'),
    period:document.getElementById('reportPeriod')
};

const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>{
    const number=Number(value||0);

    return `${number<0?'-':''}${currency}${Math.abs(number).toLocaleString(undefined,{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    })}`;
};

function localDate(date=new Date()){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function setAsOfDate(value){
    if(el.asOf._flatpickr){
        el.asOf._flatpickr.setDate(
            value,
            false,
            'Y-m-d'
        );
    }else{
        el.asOf.value=value;
    }
}

async function loadTrialBalance(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState(
        'Loading Trial Balance...',
        6
    );

    const params=new URLSearchParams({
        page:String(page),
        per_page:'25',
        show_zero:el.showZero.checked?'1':'0'
    });

    const search=el.search.value.trim();

    if(search){
        params.set(
            'search',
            search
        );
    }

    if(el.type.value){
        params.set(
            'type',
            el.type.value
        );
    }

    if(el.asOf.value){
        params.set(
            'as_of',
            el.asOf.value
        );
    }

    try{
        const response=await api(
            `/api/finance/trial-balance?${params.toString()}`
        );

        const data=response.data??{};
        const summary=data.summary??{};
        const paginator=data.accounts??{};

        renderSummary(
            summary,
            data.as_of
        );

        renderAccounts(
            paginator.data??[]
        );

        AdminUI.renderPagination({
            container:el.pagination,
            currentPage:Number(
                paginator.current_page??1
            ),
            lastPage:Number(
                paginator.last_page??1
            ),
            total:Number(
                paginator.total??0
            ),
            onPageChange:loadTrialBalance
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            6
        );

        el.pagination.innerHTML='';
    }
}

function renderSummary(summary,asOf){
    el.totalAccounts.textContent=
        summary.total_accounts??0;

    el.totalDebit.textContent=
        money(
            summary.total_debit
        );

    el.totalCredit.textContent=
        money(
            summary.total_credit
        );

    const difference=Math.abs(
        Number(
            summary.difference||0
        )
    );

    el.difference.textContent=
        money(difference);

    el.footerDebit.textContent=
        money(
            summary.total_debit
        );

    el.footerCredit.textContent=
        money(
            summary.total_credit
        );

    el.period.textContent=
        asOf
            ?`As of ${AdminUI.formatDate(asOf)}`
            :'';

    el.state.classList.remove(
        'hidden',
        'border-emerald-200',
        'bg-emerald-50',
        'text-emerald-700',
        'border-red-200',
        'bg-red-50',
        'text-red-700'
    );

    if(summary.is_balanced){
        el.state.textContent=
            'Balanced';

        el.state.classList.add(
            'border-emerald-200',
            'bg-emerald-50',
            'text-emerald-700'
        );

        el.difference.className=
            'mt-1 truncate text-xl font-bold text-emerald-700';
    }else{
        el.state.textContent=
            `Out of Balance: ${money(difference)}`;

        el.state.classList.add(
            'border-red-200',
            'bg-red-50',
            'text-red-700'
        );

        el.difference.className=
            'mt-1 truncate text-xl font-bold text-red-700';
    }
}

function renderAccounts(accounts){
    if(!accounts.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No account balances found.',
            6
        );

        return;
    }

    el.table.innerHTML=accounts.map(account=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60">
            <td class="px-4 py-3">
                <span class="font-mono text-xs font-semibold text-indigo-600">
                    ${esc(account.code)}
                </span>
            </td>

            <td class="px-4 py-3">
                <div class="font-semibold text-slate-700">
                    ${esc(account.name)}
                </div>

                ${
                    !account.is_active
                        ?`
                            <div class="mt-0.5 text-[10px] font-medium text-slate-400">
                                Inactive
                            </div>
                        `
                        :''
                }
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${esc(
                    AdminUI.titleCase(
                        account.type
                    )
                )}
            </td>

            <td class="px-4 py-3 text-xs text-slate-500">
                ${
                    account.sub_type
                        ?esc(
                            AdminUI.titleCase(
                                account.sub_type
                            )
                        )
                        :'—'
                }
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                ${
                    Number(account.debit_balance)>0
                        ?money(account.debit_balance)
                        :'—'
                }
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                ${
                    Number(account.credit_balance)>0
                        ?money(account.credit_balance)
                        :'—'
                }
            </td>
        </tr>
    `).join('');
}

window.clearFilters=function(){
    el.search.value='';
    el.type.value='';
    el.showZero.checked=false;

    setAsOfDate(
        localDate()
    );

    loadTrialBalance(1);
};

function bindEvents(){
    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadTrialBalance(1)
        )
    );

    el.type.addEventListener(
        'change',
        ()=>loadTrialBalance(1)
    );

    el.showZero.addEventListener(
        'change',
        ()=>loadTrialBalance(1)
    );

    /*
     * Global js-date-picker change event
     */
    el.asOf.addEventListener(
        'change',
        ()=>loadTrialBalance(1)
    );
}

function init(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            init,
            50
        );

        return;
    }

    /*
     * js-date-picker global initializer layout/app.js থেকে run হওয়ার
     * জন্য এক tick wait করে initial date set করছি।
     */
    setTimeout(()=>{
        setAsOfDate(
            localDate()
        );

        bindEvents();
        loadTrialBalance();
    },0);
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