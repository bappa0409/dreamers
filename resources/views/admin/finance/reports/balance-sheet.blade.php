@extends('layouts.admin')

@section('title','Balance Sheet')
@section('page_title','Balance Sheet')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-balance-scale"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Balance Sheet</h1>
                <p class="text-xs text-slate-500">Assets, liabilities and equity derived from posted accounting entries.</p>
            </div>
        </div>
        <div id="balanceState" class="hidden rounded-md border px-3 py-2 text-sm font-semibold"></div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-4">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    As Of Date
                </label>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="asOfFilter"
                        type="text"
                        class="js-date-picker h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date"
                        autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="resetDate()"
                    class="h-9 w-full cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <i class="bi bi-calendar-check me-1"></i>
                    Today
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Assets</p>
            <p id="totalAssets" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Liabilities</p>
            <p id="totalLiabilities" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Equity</p>
            <p id="totalEquity" class="mt-1 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-white p-4">
            <p class="text-xs text-slate-500">Current Surplus / Deficit</p>
            <p id="currentSurplus" class="mt-1 truncate text-xl font-bold text-indigo-700">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-2">
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                <h2 class="text-base font-bold text-slate-700">Assets</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-base">
                    <thead class="border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-sm font-semibold text-slate-500">Account</th>
                            <th class="px-4 py-2.5 text-right text-sm font-semibold text-slate-500">Balance</th>
                        </tr>
                    </thead>

                    <tbody id="assetTable">
                        <tr>
                            <td colspan="2" class="px-4 py-10 text-xs text-center text-slate-400">Loading...</td>
                        </tr>
                    </tbody>

                    <tfoot class="border-t border-slate-200 bg-slate-50">
                        <tr>
                            <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">Total Assets</td>
                            <td id="assetFooter" class="px-4 py-3 text-right font-bold text-slate-800">
                                {{ setting('currency_symbol','৳') }}0.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                    <h2 class="text-base font-bold text-slate-700">Liabilities</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-base">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-sm font-semibold text-slate-500">Account</th>
                                <th class="px-4 py-2.5 text-right text-sm font-semibold text-slate-500">Balance</th>
                            </tr>
                        </thead>

                        <tbody id="liabilityTable">
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-400">Loading...</td>
                            </tr>
                        </tbody>

                        <tfoot class="border-t border-slate-200 bg-slate-50">
                            <tr>
                                <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">Total Liabilities</td>
                                <td id="liabilityFooter" class="px-4 py-3 text-right font-bold text-slate-800">
                                    {{ setting('currency_symbol','৳') }}0.00
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-3">
                    <h2 class="text-base font-bold text-slate-700">Equity</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-base">
                        <thead class="border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-sm font-semibold text-slate-500">Account</th>
                                <th class="px-4 py-2.5 text-right text-sm font-semibold text-slate-500">Balance</th>
                            </tr>
                        </thead>

                        <tbody id="equityTable">
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-400">Loading...</td>
                            </tr>
                        </tbody>

                        <tfoot class="border-t border-slate-200 bg-slate-50">
                            <tr>
                                <td class="px-4 py-3 text-right text-sm font-bold text-slate-700">Total Equity</td>
                                <td id="equityFooter" class="px-4 py-3 text-right font-bold text-slate-800">
                                    {{ setting('currency_symbol','৳') }}0.00
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="border-b border-slate-200 px-5 py-3">
            <h2 class="text-base font-bold text-slate-700">Accounting Equation</h2>
            <p id="reportDate" class="mt-0.5 text-[11px] text-slate-400"></p>
        </div>

        <div class="grid gap-3 p-5 md:grid-cols-[1fr_auto_1fr_auto_1fr] md:items-center">
            <div class="rounded-md border border-slate-200 p-4 text-center">
                <p class="text-xs text-slate-500">Assets</p>
                <p id="equationAssets" class="mt-1 text-lg font-bold text-slate-800">
                    {{ setting('currency_symbol','৳') }}0.00
                </p>
            </div>

            <div class="text-center text-xl font-bold text-slate-400">=</div>

            <div class="rounded-md border border-slate-200 p-4 text-center">
                <p class="text-xs text-slate-500">Liabilities</p>
                <p id="equationLiabilities" class="mt-1 text-lg font-bold text-slate-800">
                    {{ setting('currency_symbol','৳') }}0.00
                </p>
            </div>

            <div class="text-center text-xl font-bold text-slate-400">+</div>

            <div class="rounded-md border border-slate-200 p-4 text-center">
                <p class="text-xs text-slate-500">Equity</p>
                <p id="equationEquity" class="mt-1 text-lg font-bold text-slate-800">
                    {{ setting('currency_symbol','৳') }}0.00
                </p>
            </div>
        </div>

        <div id="differenceWrap" class="hidden border-t border-red-200 bg-red-50 px-5 py-3 text-base text-red-700">
            Difference:
            <span id="equationDifference" class="font-bold"></span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

const el={
    date:document.getElementById('asOfFilter'),
    state:document.getElementById('balanceState'),
    assets:document.getElementById('assetTable'),
    liabilities:document.getElementById('liabilityTable'),
    equity:document.getElementById('equityTable'),
    totalAssets:document.getElementById('totalAssets'),
    totalLiabilities:document.getElementById('totalLiabilities'),
    totalEquity:document.getElementById('totalEquity'),
    surplus:document.getElementById('currentSurplus'),
    assetFooter:document.getElementById('assetFooter'),
    liabilityFooter:document.getElementById('liabilityFooter'),
    equityFooter:document.getElementById('equityFooter'),
    equationAssets:document.getElementById('equationAssets'),
    equationLiabilities:document.getElementById('equationLiabilities'),
    equationEquity:document.getElementById('equationEquity'),
    differenceWrap:document.getElementById('differenceWrap'),
    difference:document.getElementById('equationDifference'),
    reportDate:document.getElementById('reportDate')
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

function setDate(value){
    if(el.date._flatpickr){
        el.date._flatpickr.setDate(
            value,
            false,
            'Y-m-d'
        );
    }else{
        el.date.value=value;
    }
}

async function loadBalanceSheet(){
    el.assets.innerHTML=AdminUI.loadingState(
        'Loading assets...',
        2
    );

    el.liabilities.innerHTML=AdminUI.loadingState(
        'Loading liabilities...',
        2
    );

    el.equity.innerHTML=AdminUI.loadingState(
        'Loading equity...',
        2
    );

    const params=new URLSearchParams();

    if(el.date.value){
        params.set(
            'as_of',
            el.date.value
        );
    }

    try{
        const response=await api(
            `/api/finance/balance-sheet?${params.toString()}`
        );

        renderReport(
            response.data||{}
        );
    }catch(error){
        const message=AdminUI.extractError(error);

        el.assets.innerHTML=AdminUI.emptyState(
            message,
            2
        );

        el.liabilities.innerHTML=AdminUI.emptyState(
            message,
            2
        );

        el.equity.innerHTML=AdminUI.emptyState(
            message,
            2
        );
    }
}

function renderReport(data){
    const summary=data.summary||{};

    renderAccounts(
        el.assets,
        data.assets||[]
    );

    renderAccounts(
        el.liabilities,
        data.liabilities||[]
    );

    renderEquity(
        data.equity||[],
        Number(
            data.current_surplus||0
        )
    );

    el.totalAssets.textContent=
        money(
            summary.total_assets
        );

    el.totalLiabilities.textContent=
        money(
            summary.total_liabilities
        );

    el.totalEquity.textContent=
        money(
            summary.total_equity
        );

    el.surplus.textContent=
        money(
            summary.current_surplus
        );

    el.assetFooter.textContent=
        money(
            summary.total_assets
        );

    el.liabilityFooter.textContent=
        money(
            summary.total_liabilities
        );

    el.equityFooter.textContent=
        money(
            summary.total_equity
        );

    el.equationAssets.textContent=
        money(
            summary.total_assets
        );

    el.equationLiabilities.textContent=
        money(
            summary.total_liabilities
        );

    el.equationEquity.textContent=
        money(
            summary.total_equity
        );

    el.reportDate.textContent=
        data.as_of
            ?`As of ${AdminUI.formatDate(data.as_of)}`
            :'';

    renderBalanceState(summary);

    el.surplus.className=
        Number(summary.current_surplus)<0
            ?'mt-1 truncate text-xl font-bold text-red-700'
            :'mt-1 truncate text-xl font-bold text-indigo-700';
}

function renderAccounts(target,accounts){
    if(!accounts.length){
        target.innerHTML=AdminUI.emptyState(
            'No balances.',
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

                    <span class="text-base font-medium text-slate-700">
                        ${esc(account.name)}
                    </span>
                </div>

                ${
                    account.sub_type
                        ?`
                            <p class="mt-0.5 text-[10px] text-slate-400">
                                ${esc(
                                    AdminUI.titleCase(
                                        account.sub_type
                                    )
                                )}
                            </p>
                        `
                        :''
                }
            </td>

            <td class="px-4 py-3 text-right font-semibold ${
                Number(account.balance)<0
                    ?'text-red-700'
                    :'text-slate-700'
            }">
                ${money(account.balance)}
            </td>
        </tr>
    `).join('');
}

function renderEquity(accounts,surplus){
    let html=accounts.map(account=>`
        <tr class="border-b border-slate-100 hover:bg-slate-50/60">
            <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm font-semibold text-indigo-600">
                        ${esc(account.code)}
                    </span>

                    <span class="text-base font-medium text-slate-700">
                        ${esc(account.name)}
                    </span>
                </div>
            </td>

            <td class="px-4 py-3 text-right font-semibold ${
                Number(account.balance)<0
                    ?'text-red-700'
                    :'text-slate-700'
            }">
                ${money(account.balance)}
            </td>
        </tr>
    `).join('');

    html+=`
        <tr class="border-t border-indigo-100 bg-indigo-50/40">
            <td class="px-4 py-3">
                <div class="font-semibold text-xs text-slate-700">
                    ${surplus>=0
                        ?'Current Surplus'
                        :'Current Deficit'}
                </div>

                <p class="mt-0.5 text-[10px] text-slate-400">
                    Current income less current expenses
                </p>
            </td>

            <td class="px-4 py-3 text-right font-bold ${
                surplus<0
                    ?'text-red-700'
                    :'text-indigo-700'
            }">
                ${money(surplus)}
            </td>
        </tr>
    `;

    el.equity.innerHTML=html;
}

function renderBalanceState(summary){
    const balanced=Boolean(
        summary.is_balanced
    );

    el.state.classList.remove(
        'hidden',
        'border-emerald-200',
        'bg-emerald-50',
        'text-emerald-700',
        'border-red-200',
        'bg-red-50',
        'text-red-700'
    );

    if(balanced){
        el.state.textContent=
            'Balance Sheet Balanced';

        el.state.classList.add(
            'border-emerald-200',
            'bg-emerald-50',
            'text-emerald-700'
        );

        el.differenceWrap.classList.add(
            'hidden'
        );
    }else{
        el.state.textContent=
            'Balance Sheet Out of Balance';

        el.state.classList.add(
            'border-red-200',
            'bg-red-50',
            'text-red-700'
        );

        el.differenceWrap.classList.remove(
            'hidden'
        );

        el.difference.textContent=
            money(
                summary.difference
            );
    }
}

window.resetDate=function(){
    setDate(
        localDate()
    );

    loadBalanceSheet();
};

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

    setTimeout(()=>{
        setDate(
            localDate()
        );

        el.date.addEventListener(
            'change',
            loadBalanceSheet
        );

        loadBalanceSheet();
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