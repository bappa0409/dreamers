@extends('layouts.member')

@section('title','Investments')
@section('page-title','Investments')

@section('content')

@php
    $currency=setting('currency_symbol','৳');
    $investmentStatus=$investmentStatus??'';
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-graph-up-arrow"></i>
            </div>

            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">
                    Association Investments
                </h1>

                <p class="mt-0.5 text-sm text-slate-500">
                    View active and completed association investments, principal recovery and received returns.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="loadInvestments(1)"
            class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    {{-- Read-only Notice --}}
    <div class="flex gap-3 rounded-lg border border-sky-200 bg-sky-50/60 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
            <i class="bi bi-eye"></i>
        </div>

        <div>
            <p class="text-sm font-semibold text-sky-800">
                Read-only Investment Portfolio
            </p>

            <p class="mt-0.5 text-[11px] leading-5 text-sky-700">
                Investment information is available for member transparency. Members cannot create, update, cancel, delete or modify investment records.
            </p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Total Investments
                    </p>

                    <p id="summaryTotal" class="mt-2 text-xl font-bold text-slate-800">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-briefcase"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">
                        Visible Investment
                    </p>

                    <p id="summaryInvestment" class="mt-2 text-xl font-bold text-sky-700">
                        {{ $currency }}0.00
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                        Income Received
                    </p>

                    <p id="summaryIncome" class="mt-2 text-xl font-bold text-emerald-700">
                        {{ $currency }}0.00
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-graph-up"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">
                        Principal Returned
                    </p>

                    <p id="summaryPrincipal" class="mt-2 text-xl font-bold text-violet-700">
                        {{ $currency }}0.00
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                    <i class="bi bi-arrow-return-left"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-7">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search investment no, title or description..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    id="statusFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">

                    <option
                        value=""
                        {{ $investmentStatus===''?'selected':'' }}>
                        All Status
                    </option>

                    <option
                        value="active"
                        {{ $investmentStatus==='active'?'selected':'' }}>
                        Active
                    </option>

                    <option
                        value="completed"
                        {{ $investmentStatus==='completed'?'selected':'' }}>
                        Completed
                    </option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Clear
                </button>
            </div>

        </div>
    </div>

    {{-- Investment Table --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="bi bi-building"></i>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Investment Portfolio
                </h2>

                <p class="text-[11px] text-slate-400">
                    Active and completed association investments
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Investment
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Expected Return
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Income Received
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Principal Returned
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Remaining
                        </th>

                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Date
                        </th>

                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Maturity
                        </th>

                        <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="investmentTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="10" class="px-4 py-14 text-center">
                            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                            <p class="mt-3 text-sm text-slate-400">
                                Loading investments...
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer"></div>
    </div>
</div>

{{-- Investment Details Modal --}}
<div
    id="investmentModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

    <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>

                <div>
                    <h2 id="modalTitle" class="text-base font-bold text-slate-800">
                        Investment Details
                    </h2>

                    <p id="modalNumber" class="font-mono text-[10px] text-slate-400">
                        -
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeInvestmentModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="investmentModalBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
            <button
                type="button"
                onclick="closeInvestmentModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
let currentPage=1;
let lastPage=1;
let totalInvestments=0;
let searchTimer=null;

const CURRENCY=@json($currency);
const searchInput=document.getElementById('searchInput');
const statusFilter=document.getElementById('statusFilter');

async function loadInvestments(page=1){
    currentPage=page;

    const tbody=document.getElementById('investmentTableBody');

    tbody.innerHTML=`
        <tr>
            <td colspan="10" class="px-4 py-14 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                <p class="mt-3 text-sm text-slate-400">
                    Loading investments...
                </p>
            </td>
        </tr>
    `;

    try{
        const params=new URLSearchParams({
            page:String(page),
            per_page:'15'
        });

        const search=searchInput.value.trim();
        const status=statusFilter.value;

        if(search){
            params.set('search',search);
        }

        if(status){
            params.set('status',status);
        }

        const response=await api(
            `/api/member/investments?${params.toString()}`
        );

        const paginator=response.data?.data??
            response.data??
            {};

        const investments=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        totalInvestments=Number(
            paginator.total??investments.length
        );

        renderInvestments(investments);

        renderPagination();

        renderSummary(
            investments,
            totalInvestments
        );

    }catch(error){
        tbody.innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-14 text-center">
                    <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                        <i class="bi bi-exclamation-circle text-lg"></i>
                    </div>

                    <p class="mt-3 text-base font-semibold text-red-600">
                        Failed to load investments
                    </p>

                    <p class="mt-1 text-sm text-red-400">
                        ${escapeValue(extractError(error))}
                    </p>

                    <button
                        type="button"
                        onclick="loadInvestments(${currentPage})"
                        class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                        <i class="bi bi-arrow-clockwise"></i>
                        Try Again
                    </button>
                </td>
            </tr>
        `;

        document.getElementById(
            'paginationContainer'
        ).innerHTML='';
    }
}

function renderInvestments(investments){
    const tbody=document.getElementById(
        'investmentTableBody'
    );

    if(!investments.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="10" class="px-4 py-16 text-center">

                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                        <i class="bi bi-graph-up-arrow text-xl"></i>
                    </div>

                    <p class="mt-4 text-base font-semibold text-slate-700">
                        No investments found
                    </p>

                    <p class="mt-1 text-sm text-slate-400">
                        No active or completed investments match the current filters.
                    </p>
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML=investments
        .map(item=>`
            <tr class="transition hover:bg-slate-50/70">

                <td class="px-4 py-3">
                    <div class="max-w-[250px]">
                        <p class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">
                            ${escapeValue(item.title??'-')}
                        </p>

                        <p class="mt-1 font-mono text-[10px] font-medium text-indigo-600">
                            ${escapeValue(item.investment_no??'-')}
                        </p>
                    </div>
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                    ${money(item.amount)}
                </td>

                <td class="px-4 py-3 text-right text-sm text-slate-600">
                    ${money(item.expected_return)}
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-emerald-600">
                    ${money(item.income_received)}
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-violet-600">
                    ${money(item.principal_returned)}
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                    ${money(item.remaining_principal)}
                </td>

                <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-500">
                    ${formatDate(item.investment_date)}
                </td>

                <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-500">
                    ${formatDate(item.maturity_date)}
                </td>

                <td class="px-4 py-3 text-center">
                    ${statusBadge(item.status)}
                </td>

                <td class="px-4 py-3 text-right">
                    <button
                        type="button"
                        onclick="openInvestment(${Number(item.id)})"
                        class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
                        <i class="bi bi-eye"></i>
                        View
                    </button>
                </td>
            </tr>
        `)
        .join('');
}

function renderSummary(
    investments,
    total
){
    const investmentAmount=investments.reduce(
        (sum,item)=>
            sum+
            Number(item.amount??0),
        0
    );

    const income=investments.reduce(
        (sum,item)=>
            sum+
            Number(item.income_received??0),
        0
    );

    const principal=investments.reduce(
        (sum,item)=>
            sum+
            Number(item.principal_returned??0),
        0
    );

    document.getElementById(
        'summaryTotal'
    ).textContent=total;

    document.getElementById(
        'summaryInvestment'
    ).textContent=money(investmentAmount);

    document.getElementById(
        'summaryIncome'
    ).textContent=money(income);

    document.getElementById(
        'summaryPrincipal'
    ).textContent=money(principal);
}

function renderPagination(){
    const container=document.getElementById(
        'paginationContainer'
    );

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    const from=
        totalInvestments===0
            ?0
            :((currentPage-1)*15)+1;

    const to=Math.min(
        currentPage*15,
        totalInvestments
    );

    container.innerHTML=`
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-[11px] text-slate-500">
                Showing
                <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">
                    ${from}
                </span>
                –
                <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">
                    ${to}
                </span>
                of
                <span class="font-semibold  text-xs 2xl:text-sm text-slate-700">
                    ${totalInvestments}
                </span>
                investments
            </p>

            <div class="flex items-center gap-1">

                <button
                    type="button"
                    ${currentPage<=1?'disabled':''}
                    onclick="loadInvestments(${currentPage-1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <i class="bi bi-chevron-left"></i>
                    Previous
                </button>

                <span class="px-3 text-[11px] font-medium text-slate-500">
                    ${currentPage} / ${lastPage}
                </span>

                <button
                    type="button"
                    ${currentPage>=lastPage?'disabled':''}
                    onclick="loadInvestments(${currentPage+1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    Next
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    `;
}

window.openInvestment=async function(id){
    const modal=document.getElementById(
        'investmentModal'
    );

    const body=document.getElementById(
        'investmentModalBody'
    );

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add(
        'overflow-hidden'
    );

    body.innerHTML=`
        <div class="py-14 text-center">
            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

            <p class="mt-3 text-sm text-slate-400">
                Loading investment details...
            </p>
        </div>
    `;

    try{
        const response=await api(
            `/api/member/investments/${id}`
        );

        const item=response.data?.data??
            response.data??
            {};

        document.getElementById(
            'modalTitle'
        ).textContent=
            item.title??
            'Investment Details';

        document.getElementById(
            'modalNumber'
        ).textContent=
            item.investment_no??
            '-';

        renderInvestmentDetails(item);

    }catch(error){
        body.innerHTML=`
            <div class="py-14 text-center">

                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                    <i class="bi bi-exclamation-circle"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-red-600">
                    Failed to load investment
                </p>

                <p class="mt-1 text-sm text-red-400">
                    ${escapeValue(extractError(error))}
                </p>
            </div>
        `;
    }
};

function renderInvestmentDetails(item){
    const body=document.getElementById(
        'investmentModalBody'
    );

    const summary=item.summary??{};
    const returns=Array.isArray(item.returns)
        ?item.returns
        :[];

    body.innerHTML=`
        <div class="space-y-5">

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

                ${summaryCard(
                    'Investment Amount',
                    money(item.amount),
                    'bi-cash-stack',
                    'indigo'
                )}

                ${summaryCard(
                    'Expected Return',
                    money(item.expected_return),
                    'bi-graph-up-arrow',
                    'sky'
                )}

                ${summaryCard(
                    'Income Received',
                    money(summary.income_received),
                    'bi-arrow-down-circle',
                    'emerald'
                )}

                ${summaryCard(
                    'Remaining Principal',
                    money(summary.remaining_principal),
                    'bi-wallet2',
                    'violet'
                )}

            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white lg:col-span-2">

                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">
                            Investment Information
                        </h3>

                        <p class="text-[11px] text-slate-400">
                            General investment details
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2">

                        ${informationItem(
                            'Investment No.',
                            item.investment_no
                        )}

                        ${informationItem(
                            'Status',
                            statusBadge(
                                item.status
                            ),
                            true
                        )}

                        ${informationItem(
                            'Investment Date',
                            formatDate(
                                item.investment_date
                            )
                        )}

                        ${informationItem(
                            'Maturity Date',
                            formatDate(
                                item.maturity_date
                            )
                        )}

                        ${informationItem(
                            'Principal Returned',
                            money(
                                summary.principal_returned
                            )
                        )}

                        ${informationItem(
                            'Income Received',
                            money(
                                summary.income_received
                            )
                        )}

                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-800">
                            Description
                        </h3>

                        <p class="text-[11px] text-slate-400">
                            Investment purpose or details
                        </p>
                    </div>

                    <div class="p-5">
                        <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-600">
                            ${
                                item.description
                                    ?escapeValue(item.description)
                                    :'No description provided.'
                            }
                        </p>
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">

                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">
                            Returns Received
                        </h3>

                        <p class="text-[11px] text-slate-400">
                            Paid principal and income return history
                        </p>
                    </div>

                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px]">

                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Type
                                </th>

                                <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Amount
                                </th>

                                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Return Date
                                </th>

                                <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Description
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            ${
                                returns.length
                                    ?returns.map(returnItem=>`
                                        <tr>

                                            <td class="px-4 py-3">
                                                ${returnTypeBadge(
                                                    returnItem.return_type
                                                )}
                                            </td>

                                            <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                                                ${money(
                                                    returnItem.amount
                                                )}
                                            </td>

                                            <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-500">
                                                ${formatDate(
                                                    returnItem.return_date
                                                )}
                                            </td>

                                            <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-500">
                                                ${
                                                    escapeValue(
                                                        returnItem.description??
                                                        '-'
                                                    )
                                                }
                                            </td>

                                        </tr>
                                    `).join('')
                                    :`
                                        <tr>
                                            <td colspan="4" class="px-4 py-12 text-center">

                                                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </div>

                                                <p class="mt-3 text-sm font-semibold text-slate-600">
                                                    No returns received yet
                                                </p>

                                            </td>
                                        </tr>
                                    `
                            }

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function summaryCard(
    label,
    value,
    icon,
    tone
){
    const styles={
        indigo:'border-indigo-200 bg-indigo-50 text-indigo-600',
        sky:'border-sky-200 bg-sky-50 text-sky-600',
        emerald:'border-emerald-200 bg-emerald-50 text-emerald-600',
        violet:'border-violet-200 bg-violet-50 text-violet-600'
    };

    return`
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                        ${escapeValue(label)}
                    </p>

                    <p class="mt-2 text-base font-bold text-slate-700">
                        ${value}
                    </p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-lg ${styles[tone]??styles.indigo}">
                    <i class="bi ${icon}"></i>
                </div>

            </div>
        </div>
    `;
}

function informationItem(
    label,
    value,
    raw=false
){
    return`
        <div class="border-b border-slate-100 px-5 py-4 sm:border-r">

            <p class="text-[10px] uppercase tracking-wide text-slate-400">
                ${escapeValue(label)}
            </p>

            <div class="mt-1.5 text-sm font-semibold text-slate-700">
                ${
                    raw
                        ?value
                        :escapeValue(value??'-')
                }
            </div>

        </div>
    `;
}

function returnTypeBadge(type){
    if(type==='principal'){
        return`
            <span class="inline-flex rounded-md border border-violet-200 bg-violet-50 px-2.5 py-1 text-[10px] font-semibold text-violet-700">
                Principal
            </span>
        `;
    }

    return`
        <span class="inline-flex rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
            Income
        </span>
    `;
}

function statusBadge(status){
    const styles={
        active:
            'border-emerald-200 bg-emerald-50 text-emerald-700',

        completed:
            'border-sky-200 bg-sky-50 text-sky-700'
    };

    return`
        <span class="inline-flex rounded-md border px-2.5 py-1 text-[10px] font-semibold capitalize ${
            styles[status]??
            'border-slate-200 bg-slate-50 text-slate-600'
        }">
            ${escapeValue(status??'-')}
        </span>
    `;
}

window.closeInvestmentModal=function(){
    const modal=document.getElementById(
        'investmentModal'
    );

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.body.classList.remove(
        'overflow-hidden'
    );
};

window.clearFilters=function(){
    searchInput.value='';

    statusFilter.value='';

    loadInvestments(1);
};

function money(value){
    return CURRENCY+
        Number(value??0)
            .toLocaleString(
                'en-US',
                {
                    minimumFractionDigits:2,
                    maximumFractionDigits:2
                }
            );
}

function formatDate(value){
    if(!value){
        return'—';
    }

    const date=new Date(
        `${value}T00:00:00`
    );

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

function extractError(error){
    if(error?.data?.errors){
        const errors=
            Object.values(
                error.data.errors
            ).flat();

        if(errors.length){
            return errors[0];
        }
    }

    return error?.data?.message??
        error?.message??
        'Something went wrong.';
}

function escapeValue(value){
    const div=document.createElement(
        'div'
    );

    div.textContent=String(
        value??''
    );

    return div.innerHTML;
}

searchInput.addEventListener(
    'input',
    ()=>{
        clearTimeout(searchTimer);

        searchTimer=setTimeout(
            ()=>loadInvestments(1),
            400
        );
    }
);

statusFilter.addEventListener(
    'change',
    ()=>loadInvestments(1)
);

document
    .getElementById('investmentModal')
    .addEventListener(
        'click',
        event=>{
            if(
                event.target===
                event.currentTarget
            ){
                closeInvestmentModal();
            }
        }
    );

document.addEventListener(
    'keydown',
    event=>{
        if(event.key==='Escape'){
            closeInvestmentModal();
        }
    }
);

document.addEventListener(
    'DOMContentLoaded',
    ()=>loadInvestments(1)
);
</script>

@endpush