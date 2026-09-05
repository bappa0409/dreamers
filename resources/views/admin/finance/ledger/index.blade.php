@extends('layouts.admin')

@section('title','General Ledger')
@section('page_title','General Ledger')

@section('content')
<div class="space-y-4">
    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-journal-bookmark"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">General Ledger</h1>
                <p class="text-xs text-slate-500">
                    View account-wise posted journal movements and running balance.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-5">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Account
                </label>
                <select
                    id="accountFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">Select Account</option>
                </select>
            </div>

            <div class="lg:col-span-4">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Date Range
                </label>
                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-3">
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

    {{-- Account Info --}}
    <div
        id="ledgerInfo"
        class="hidden rounded-md border border-slate-200 bg-white px-5 py-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 id="accountTitle" class="font-bold text-slate-800"></h2>
                    <span id="accountStatus"></span>
                </div>
                <p id="accountMeta" class="mt-1 text-sm text-slate-500"></p>
            </div>

            <div class="text-xs text-slate-500">
                <i class="bi bi-calendar3 me-1"></i>
                <span id="periodLabel">All posted transactions</span>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Opening Balance</p>
            <p id="openingBalance" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/30 p-4">
            <p class="text-sm text-emerald-600">Period Debit</p>
            <p id="periodDebit" class="mt-1 truncate text-xl font-bold text-emerald-700">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/30 p-4">
            <p class="text-sm text-red-600">Period Credit</p>
            <p id="periodCredit" class="mt-1 truncate text-xl font-bold text-red-700">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/30 p-4">
            <p class="text-sm text-indigo-600">Closing Balance</p>
            <p id="closingBalance" class="mt-1 truncate text-xl font-bold text-indigo-700">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>
    </div>

    {{-- Ledger Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-700">Ledger Entries</h2>
                <p class="text-[11px] text-slate-400">
                    Posted accounting transactions only
                </p>
            </div>
            <span class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                Running Balance
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1150px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Journal
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Type / Source
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Description
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Debit
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Credit
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Balance
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Posted By
                        </th>
                    </tr>
                </thead>

                <tbody id="ledgerTable">
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-base text-slate-400">
                            Select an account to view the General Ledger.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            id="paginationContainer"
            class="border-t border-slate-200 px-4 py-3">
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

let currentPage=1;
let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

const el={
    account:document.getElementById('accountFilter'),
    dateRange:document.getElementById('dateRangeFilter'),
    table:document.getElementById('ledgerTable'),
    pagination:document.getElementById('paginationContainer'),
    info:document.getElementById('ledgerInfo'),
    accountTitle:document.getElementById('accountTitle'),
    accountMeta:document.getElementById('accountMeta'),
    accountStatus:document.getElementById('accountStatus'),
    periodLabel:document.getElementById('periodLabel'),
    opening:document.getElementById('openingBalance'),
    debit:document.getElementById('periodDebit'),
    credit:document.getElementById('periodCredit'),
    closing:document.getElementById('closingBalance')
};

const esc=value=>AdminUI.escapeHtml(value??'');

function money(value){
    const number=Number(value||0);

    return `${number<0?'-':''}${currency}${Math.abs(number).toLocaleString(undefined,{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    })}`;
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

                    if(el.account.value){
                        loadLedger(1);
                    }

                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    if(el.account.value){
                        loadLedger(1);
                    }
                }
            }
        }
    );
}

async function loadAccounts(){
    try{
        const response=await api(
            '/api/finance/ledger/accounts'
        );

        const accounts=response.data??[];

        el.account.innerHTML=`
            <option value="">Select Account</option>
            ${accounts.map(account=>`
                <option value="${account.id}">
                    ${esc(account.code)} - ${esc(account.name)}
                    ${account.is_active?'':' (Inactive)'}
                </option>
            `).join('')}
        `;
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadLedger(page=1){
    const accountId=el.account.value;

    currentPage=page;

    if(!accountId){
        resetLedger();
        return;
    }

    el.table.innerHTML=AdminUI.loadingState(
        'Loading ledger...',
        8
    );

    const params=new URLSearchParams({
        page:String(page),
        per_page:'25'
    });

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
            `/api/finance/accounts/${accountId}/ledger?${params.toString()}`
        );

        const data=response.data??{};
        const account=data.account??{};
        const summary=data.summary??{};
        const paginator=data.entries??{};

        renderAccountInfo(
            account,
            summary
        );

        renderEntries(
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
            onPageChange:loadLedger
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            8
        );

        el.pagination.innerHTML='';

        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

function renderAccountInfo(account,summary){
    el.info.classList.remove(
        'hidden'
    );

    el.accountTitle.textContent=
        `${account.code} - ${account.name}`;

    el.accountMeta.textContent=
        `${AdminUI.titleCase(account.type)}${account.sub_type
            ?` • ${AdminUI.titleCase(account.sub_type)}`
            :''}`;

    el.accountStatus.innerHTML=
        AdminUI.statusBadge(
            account.is_active
                ?'active'
                :'inactive'
        );

    if(selectedFrom&&selectedTo){
        el.periodLabel.textContent=
            `${AdminUI.formatDate(selectedFrom)} - ${AdminUI.formatDate(selectedTo)}`;
    }else{
        el.periodLabel.textContent=
            'All posted transactions';
    }

    el.opening.textContent=
        money(
            summary.opening_balance
        );

    el.debit.textContent=
        money(
            summary.period_debit
        );

    el.credit.textContent=
        money(
            summary.period_credit
        );

    el.closing.textContent=
        money(
            summary.closing_balance
        );
}

function renderEntries(entries){
    if(!entries.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No posted ledger entries found for this period.',
            8
        );

        return;
    }

    el.table.innerHTML=entries.map(entry=>{
        const transaction=
            entry.transaction??{};

        const description=
            entry.description||
            transaction.description||
            '—';

        return `
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60">
                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                    ${
                        transaction.transaction_date
                            ?AdminUI.formatDate(
                                transaction.transaction_date
                            )
                            :'—'
                    }
                </td>

                <td class="px-4 py-3">
                    <div class="font-mono text-sm font-semibold text-indigo-600">
                        ${esc(
                            transaction.transaction_no||
                            '—'
                        )}
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="text-xs font-medium text-slate-700">
                        ${esc(
                            AdminUI.titleCase(
                                transaction.type||
                                '—'
                            )
                        )}
                    </div>

                    <div class="mt-0.5 text-[10px] text-slate-400">
                        ${esc(
                            AdminUI.titleCase(
                                transaction.source_module||
                                '—'
                            )
                        )}
                    </div>
                </td>

                <td class="max-w-[320px] px-4 py-3">
                    <p
                        class="truncate text-xs text-slate-600"
                        title="${esc(description)}">
                        ${esc(description)}
                    </p>
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-700">
                    ${
                        Number(entry.debit)>0
                            ?money(entry.debit)
                            :'—'
                    }
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-red-700">
                    ${
                        Number(entry.credit)>0
                            ?money(entry.credit)
                            :'—'
                    }
                </td>

                <td class="px-4 py-3 text-right text-sm font-bold text-indigo-700">
                    ${money(
                        entry.running_balance
                    )}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${esc(
                        transaction.poster?.name||
                        transaction.creator?.name||
                        'System'
                    )}
                </td>
            </tr>
        `;
    }).join('');
}

function resetLedger(){
    currentPage=1;

    el.info.classList.add(
        'hidden'
    );

    el.opening.textContent=
        money(0);

    el.debit.textContent=
        money(0);

    el.credit.textContent=
        money(0);

    el.closing.textContent=
        money(0);

    el.pagination.innerHTML='';

    el.table.innerHTML=`
        <tr>
            <td colspan="8" class="px-4 py-12 text-center text-base text-slate-400">
                Select an account to view the General Ledger.
            </td>
        </tr>
    `;
}

window.clearFilters=function(){
    el.account.value='';

    selectedFrom='';
    selectedTo='';

    if(dateRangePicker){
        dateRangePicker.clear(
            false
        );
    }else{
        el.dateRange.value='';
    }

    resetLedger();
};

function bindEvents(){
    el.account.addEventListener(
        'change',
        ()=>loadLedger(1)
    );
}

async function init(){
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
    bindEvents();

    await loadAccounts();
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