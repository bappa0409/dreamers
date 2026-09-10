@extends('layouts.admin')

@section('title','Accounts & Finance')
@section('page_title','Accounts & Finance')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Accounts & Finance</h1>
                <p class="text-xs text-slate-500">
                    Live financial position, monthly performance and accounting activity.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="refreshFinance()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-slate-600 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid gap-3 xl:grid-cols-[1.1fr_1.7fr]">
        {{-- Hero: Net Position --}}
        <div class="overflow-hidden rounded-md border border-indigo-200 bg-indigo-50/40 p-5">
            <p class="text-xs font-medium text-indigo-600">Net surplus this month</p>
            <p id="netSurplus" class="mt-1 truncate text-3xl font-bold text-indigo-700">
                {{ setting('currency_symbol','৳') }}0.00
            </p>
            <p class="mt-1 text-[11px] text-indigo-500/80">Income minus expense, current month</p>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700">
                    <i class="bi bi-arrow-down-circle text-emerald-500"></i>
                    <span id="monthlyIncome">{{ setting('currency_symbol','৳') }}0.00</span>
                </span>

                <span class="inline-flex items-center gap-1.5 rounded-md bg-white px-2.5 py-1.5 text-[11px] font-semibold text-red-700">
                    <i class="bi bi-arrow-up-circle text-red-500"></i>
                    <span id="monthlyExpense">{{ setting('currency_symbol','৳') }}0.00</span>
                </span>
            </div>
        </div>

        {{-- Position stat strip --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Cash</p>
                    <p id="cashBalance" class="truncate text-sm font-bold text-slate-800">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-bank"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Bank</p>
                    <p id="bankBalance" class="truncate text-sm font-bold text-slate-800">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i class="bi bi-arrow-down-left-circle"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Receivable</p>
                    <p id="receivableBalance" class="truncate text-sm font-bold text-slate-800">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Investments</p>
                    <p id="investmentBalance" class="truncate text-sm font-bold text-slate-800">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i class="bi bi-building"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Asset Book Value</p>
                    <p id="assetBookValue" class="truncate text-sm font-bold text-slate-800">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 rounded-md border border-slate-200 bg-white p-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Posted This Month</p>
                    <p id="postedTransactionCount" class="text-sm font-bold text-slate-800">
                        0
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 xl:grid-cols-[1.4fr_1fr]">
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-bold text-slate-700">
                    Income vs Expense
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Last six months
                </p>
            </div>

            <div id="trendChart" class="space-y-4 p-5">
                <div class="py-10 text-center text-base text-slate-500">
                    Loading trend...
                </div>
            </div>

            <div class="flex items-center gap-4 border-t border-slate-100 px-5 py-3 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span>
                    Income
                </span>

                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-sm bg-red-400"></span>
                    Expense
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-bold text-slate-700">
                    Financial Position
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Current ledger balances
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600"><i class="bi bi-wallet2 text-xs"></i></span>
                        Cash + Bank
                    </span>
                    <span id="positionCashBank" class="text-sm 2xl:text-base font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600"><i class="bi bi-arrow-down-left-circle text-xs"></i></span>
                        Accounts Receivable
                    </span>
                    <span id="positionReceivable" class="text-base font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-violet-50 text-violet-600"><i class="bi bi-graph-up-arrow text-xs"></i></span>
                        Investments
                    </span>
                    <span id="positionInvestment" class="text-base font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600"><i class="bi bi-building text-xs"></i></span>
                        Fixed Assets
                    </span>
                    <span id="positionAssets" class="text-base font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600"><i class="bi bi-exclamation-circle text-xs"></i></span>
                        Subscription Outstanding
                    </span>
                    <span id="subscriptionOutstanding" class="text-base font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600"><i class="bi bi-check-circle text-xs"></i></span>
                        Subscription Collected
                        <span class="text-[10px] text-slate-400">(this month)</span>
                    </span>
                    <span id="subscriptionCollected" class="text-base font-bold text-emerald-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>

                <div class="flex items-center justify-between gap-3 bg-slate-50 px-5 py-3">
                    <span class="flex items-center gap-2.5 text-xs 2xl:text-sm font-semibold text-slate-600">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-600"><i class="bi bi-bar-chart-line text-xs"></i></span>
                        Net Monthly Result
                    </span>
                    <span id="positionNet" class="text-base font-bold text-indigo-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-base font-bold text-slate-700">
                    Recent Posted Journals
                </h2>
                <p class="mt-0.5 text-[11px] text-slate-500">
                    Latest accounting activity
                </p>
            </div>

            @if(Route::has('admin.finance.journals'))
            <a
                href="{{ route('admin.finance.journals') }}"
                class="text-xs 2xl:text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                View Journal Entries
                <i class="bi bi-arrow-right"></i>
            </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Journal
                        </th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Type
                        </th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Source
                        </th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Accounts
                        </th>
                        <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">
                            Amount
                        </th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Posted By
                        </th>
                    </tr>
                </thead>

                <tbody id="recentTransactionTable">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">
                            Loading recent journals...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

const el={
    cash:document.getElementById('cashBalance'),
    bank:document.getElementById('bankBalance'),
    receivable:document.getElementById('receivableBalance'),
    investment:document.getElementById('investmentBalance'),
    asset:document.getElementById('assetBookValue'),
    posted:document.getElementById('postedTransactionCount'),
    income:document.getElementById('monthlyIncome'),
    expense:document.getElementById('monthlyExpense'),
    net:document.getElementById('netSurplus'),
    subscriptionOutstanding:document.getElementById('subscriptionOutstanding'),
    subscriptionCollected:document.getElementById('subscriptionCollected'),
    positionCashBank:document.getElementById('positionCashBank'),
    positionReceivable:document.getElementById('positionReceivable'),
    positionInvestment:document.getElementById('positionInvestment'),
    positionAssets:document.getElementById('positionAssets'),
    positionNet:document.getElementById('positionNet'),
    trend:document.getElementById('trendChart'),
    recent:document.getElementById('recentTransactionTable')
};

const esc=value=>AdminUI.escapeHtml(value??'');

function money(value){
    const number=Number(value||0);

    return `${number<0?'-':''}${currency}${Math.abs(number).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}

async function loadDashboard(){
    try{
        const response=await api(
            '/api/finance/dashboard'
        );

        const data=response.data||{};
        const summary=data.summary||{};

        renderSummary(summary);
        renderTrend(data.monthly_trend||[]);
        renderRecent(data.recent_transactions||[]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );

        el.trend.innerHTML=AdminUI.emptyState(
            'Finance dashboard could not be loaded.',
            1
        );

        el.recent.innerHTML=AdminUI.emptyState(
            'Recent journals could not be loaded.',
            7
        );
    }
}

function renderSummary(summary){
    el.cash.textContent=money(summary.cash);
    el.bank.textContent=money(summary.bank);
    el.receivable.textContent=money(summary.receivable);
    el.investment.textContent=money(summary.investment_balance);
    el.asset.textContent=money(summary.asset_book_value);
    el.posted.textContent=Number(summary.posted_transactions||0);

    el.income.textContent=money(summary.monthly_income);
    el.expense.textContent=money(summary.monthly_expense);
    el.net.textContent=money(summary.net_surplus);
    el.subscriptionOutstanding.textContent=money(summary.subscription_outstanding);
    el.subscriptionCollected.textContent=money(summary.subscription_collected);

    el.positionCashBank.textContent=money(summary.cash_bank);
    el.positionReceivable.textContent=money(summary.receivable);
    el.positionInvestment.textContent=money(summary.investment_balance);
    el.positionAssets.textContent=money(summary.asset_book_value);
    el.positionNet.textContent=money(summary.net_surplus);

    const net=Number(summary.net_surplus||0);

    el.net.className=
        net<0
            ?'mt-2 truncate text-lg font-bold text-red-700'
            :'mt-2 truncate text-lg font-bold text-indigo-700';

    el.positionNet.className=
        net<0
            ?'text-base font-bold text-red-700'
            :'text-base font-bold text-indigo-700';
}

function renderTrend(rows){
    if(!rows.length){
        el.trend.innerHTML=`
            <div class="py-10 text-center text-base text-slate-500">
                No trend data available.
            </div>
        `;
        return;
    }

    const maxValue=Math.max(
        1,
        ...rows.flatMap(row=>[
            Number(row.income||0),
            Number(row.expense||0)
        ])
    );

    el.trend.innerHTML=rows.map(row=>{
        const income=Math.max(
            0,
            Number(row.income||0)
        );

        const expense=Math.max(
            0,
            Number(row.expense||0)
        );

        const incomeWidth=Math.max(
            income>0?2:0,
            income/maxValue*100
        );

        const expenseWidth=Math.max(
            expense>0?2:0,
            expense/maxValue*100
        );

        return `
            <div class="grid grid-cols-[68px_1fr] gap-3">
                <div class="pt-1 text-[11px] font-medium text-slate-500">
                    ${esc(row.label)}
                </div>

                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="h-3 flex-1 overflow-hidden rounded-sm bg-slate-100">
                            <div
                                class="h-full rounded-sm bg-emerald-500"
                                style="width:${incomeWidth}%">
                            </div>
                        </div>

                        <div class="w-28 truncate text-right text-[10px] text-slate-500">
                            ${money(income)}
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="h-3 flex-1 overflow-hidden rounded-sm bg-slate-100">
                            <div
                                class="h-full rounded-sm bg-red-400"
                                style="width:${expenseWidth}%">
                            </div>
                        </div>

                        <div class="w-28 truncate text-right text-[10px] text-slate-500">
                            ${money(expense)}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function renderRecent(rows){
    if(!rows.length){
        el.recent.innerHTML=AdminUI.emptyState(
            'No posted journals found.',
            7
        );
        return;
    }

    el.recent.innerHTML=rows.map(transaction=>{
        const accounts=(transaction.entries||[])
            .slice(0,2)
            .map(entry=>
                `${esc(entry.account?.code||'')} ${esc(entry.account?.name||'')}`
            );

        const more=(transaction.entries||[]).length-2;

        return `
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60">
                <td class="px-4 py-3">
                    <div class="font-mono text-xs 2xl:text-sm font-semibold text-indigo-600">
                        ${esc(transaction.transaction_no)}
                    </div>

                    <div class="mt-0.5 max-w-[240px] truncate text-[10px] text-slate-500">
                        ${esc(transaction.description||'No description')}
                    </div>
                </td>

                <td class="whitespace-nowrap px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                    ${transaction.transaction_date
                        ?AdminUI.formatDate(transaction.transaction_date)
                        :'—'}
                </td>

                <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                    ${esc(AdminUI.titleCase(transaction.type||'—'))}
                </td>

                <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                    ${esc(AdminUI.titleCase(transaction.source_module||'—'))}
                </td>

                <td class="px-4 py-3">
                    <div class="space-y-0.5 text-[10px] text-slate-500">
                        ${accounts.map(account=>`
                            <div class="truncate">${account}</div>
                        `).join('')}

                        ${more>0
                            ?`<div class="font-medium text-indigo-500">+${more} more</div>`
                            :''}
                    </div>
                </td>

                <td class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-700">
                    ${money(transaction.total_debit)}
                </td>

                <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                    ${esc(transaction.poster?.name||'System')}
                </td>
            </tr>
        `;
    }).join('');
}

window.refreshFinance=async function(){
    await loadDashboard();

    Toast.success(
        'Finance dashboard refreshed.'
    );
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

    loadDashboard();
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