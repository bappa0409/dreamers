@extends('layouts.admin')

@section('title','Chart of Accounts')
@section('page_title','Chart of Accounts')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-diagram-3"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Chart of Accounts</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">
                    Manage the accounting structure used by all finance modules.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openAccountModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Account
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">

    {{-- Total Accounts --}}
    <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 2xl:text-sm">
                    Total Accounts
                </p>

                <p id="totalAccounts" class="text-xl font-bold text-slate-800">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    {{-- Active --}}
    <div class="rounded-md border border-emerald-200 bg-emerald-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-emerald-700 2xl:text-sm">
                    Active
                </p>

                <p id="activeAccounts" class="text-xl font-bold text-emerald-600">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    {{-- Inactive --}}
    <div class="rounded-md border border-slate-200 bg-slate-50/70 px-5 py-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-slate-500 2xl:text-sm">
                    Inactive
                </p>

                <p id="inactiveAccounts" class="text-xl font-bold text-slate-600">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-500">
                <i class="bi bi-pause-circle"></i>
            </div>
        </div>
    </div>

    {{-- Posting Accounts --}}
    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-indigo-700 2xl:text-sm">
                    Posting Accounts
                </p>

                <p id="postingAccounts" class="text-xl font-bold text-indigo-600">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                <i class="bi bi-pencil-square"></i>
            </div>
        </div>
    </div>

    {{-- System Accounts --}}
    <div class="rounded-md border border-violet-200 bg-violet-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs text-violet-700 2xl:text-sm">
                    System Accounts
                </p>

                <p id="systemAccounts" class="text-xl font-bold text-violet-600">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-100 text-violet-600">
                <i class="bi bi-gear"></i>
            </div>
        </div>
    </div>

</div>

    {{-- Account Search --}}
<div class="rounded-md border border-slate-200 bg-white p-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        {{-- Header --}}
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-base"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">
                    Account Search
                </p>
                <p class="hidden text-[11px] text-slate-500 sm:block">
                    Search by account code, name or subtype.
                </p>
            </div>
        </div>

        <div class="flex w-full items-center gap-2 lg:w-auto">

            {{-- Search --}}
            <div class="relative min-w-0 flex-1 lg:w-[280px]">
                <i
                    class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                <input id="searchInput" type="text" placeholder="Search accounts..."
                    class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
            </div>

            {{-- Filter --}}
            <button type="button" onclick="toggleFilters()" id="filterButton"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 2xl:text-sm">
                <i class="bi bi-funnel text-xs"></i>
                <span>Filter</span>
                <i id="filterChevron" class="bi bi-chevron-down text-[10px]"></i>
            </button>

            {{-- Clear --}}
            <button type="button" onclick="clearFilters()"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 2xl:text-sm">
                <i class="bi bi-arrow-counterclockwise text-[10px]"></i>
                <span class="hidden sm:inline">Reset</span>
            </button>
        </div>
    </div>


    {{-- Filter Options --}}
    <div id="filterPanel" class="mt-3 hidden border-t border-slate-100 pt-3">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[180px_180px_auto]">

            {{-- Type --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">
                    Type
                </label>

                <select id="typeFilter"
                    class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                    <option value="">All Types</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">
                    Status
                </label>

                <select id="statusFilter"
                    class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>

        </div>
    </div>
</div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[980px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Code
                        </th>

                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Account
                        </th>

                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Type
                        </th>

                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Sub Type
                        </th>

                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Parent
                        </th>

                        <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">
                            Opening
                        </th>

                        <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">
                            Balance
                        </th>

                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody id="accountTable">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-xs 2xl:text-sm text-center text-slate-500">
                            Loading accounts...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="accountCards" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-sm text-slate-500">Loading accounts...</div>
        </div>

        <div
            id="paginationContainer"
            class="border-t border-slate-200 px-4 py-3">
        </div>
    </div>
</div>

<div
    id="accountModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <h2
                        id="accountModalTitle"
                        class="text-sm font-semibold text-slate-800">
                        Add Account
                    </h2>

                    <p class=" text-xs 2xl:text-sm text-slate-500">
                        Create a posting or parent account.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('accountModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form
            id="accountForm"
            class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">

            <div class="space-y-5 overflow-y-auto p-5">
                <div
                    id="formError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                </div>

                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-diagram-3"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Account Information
                            </h3>

                            <p class="text-[11px] text-slate-500">
                                Enter the account code, type and structure.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">
                            Account Code *
                        </label>

                        <input
                            id="accountCode"
                            type="text"
                            maxlength="30"
                            class="app-input"
                            required>
                    </div>

                    <div>
                        <label class="form-label">
                            Account Name *
                        </label>

                        <input
                            id="accountName"
                            type="text"
                            maxlength="150"
                            class="app-input"
                            required>
                    </div>

                    <div>
                        <label class="form-label">
                            Type *
                        </label>

                        <select
                            id="accountType"
                            class="app-input"
                            required>

                            <option value="">Select Type</option>
                            <option value="asset">Asset</option>
                            <option value="liability">Liability</option>
                            <option value="equity">Equity</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">
                            Sub Type
                        </label>

                        <input
                            id="accountSubType"
                            type="text"
                            maxlength="50"
                            placeholder="e.g. donation_income"
                            class="app-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Parent Account
                        </label>

                        <select
                            id="parentAccount"
                            class="app-input">

                            <option value="">
                                No Parent
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">
                            Opening Balance
                        </label>

                        <input
                            id="openingBalance"
                            type="number"
                            step="0.01"
                            value="0"
                            class="app-input">
                    </div>

                    <div>
                        <label class="form-label">
                            Status
                        </label>

                        <select
                            id="accountStatus"
                            class="app-input">

                            <option value="1">
                                Active
                            </option>

                            <option value="0">
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            id="accountDescription"
                            rows="3"
                            maxlength="3000"
                            class="app-input resize-none"></textarea>
                    </div>
                </div>
                </section>

                <div class="flex items-start gap-2 rounded-md border border-slate-200 bg-slate-50 p-3 text-[11px] leading-5 text-slate-500">
                    <i class="bi bi-info-circle mt-0.5 text-slate-500"></i>
                    <span>
                        Parent and child accounts must have the same account type.
                        Only leaf accounts can receive journal postings.
                    </span>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('accountModal')"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>

                <button
                    id="saveAccountButton"
                    type="submit"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    Save Account
                </button>
            </div>
        </form>
    </div>
</div>

<div
    id="accountDetailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-diagram-3"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Account Details
                    </h2>

                    <p class=" text-xs 2xl:text-sm text-slate-500">
                        Account structure and current balance.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('accountDetailsModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="accountDetailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="AdminUI.closeModal('accountDetailsModal')"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="bi bi-x-lg"></i>
                Close
            </button>
        </div>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85)
}
</style>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));
const canDelete=@json(auth()->user()->hasPermission('Finance.delete'));

let accounts=[];
let parents=[];
let editingAccount=null;
let currentPage=1;

const el={
    table:document.getElementById('accountTable'),
    cards:document.getElementById('accountCards'),
    pagination:document.getElementById('paginationContainer'),
    search:document.getElementById('searchInput'),
    type:document.getElementById('typeFilter'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('accountForm'),
    code:document.getElementById('accountCode'),
    name:document.getElementById('accountName'),
    accountType:document.getElementById('accountType'),
    subType:document.getElementById('accountSubType'),
    parent:document.getElementById('parentAccount'),
    opening:document.getElementById('openingBalance'),
    accountStatus:document.getElementById('accountStatus'),
    description:document.getElementById('accountDescription'),
    save:document.getElementById('saveAccountButton')
};

const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>
    `${currency}${AdminUI.formatNumber(
        Number(value??0),
        2
    )}`;

function cardsLoadingHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-500">
            ${esc(message)}
        </div>
    `;
}

function cardsEmptyHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-500">
            ${esc(message)}
        </div>
    `;
}

function accountBalance(account){
    const debit=Number(
        account.total_debit??0
    );

    const credit=Number(
        account.total_credit??0
    );

    const opening=Number(
        account.opening_balance??0
    );

    return ['asset','expense'].includes(
        account.type
    )
        ?opening+debit-credit
        :opening+credit-debit;
}

async function loadSummary(){
    try{
        const response=await api(
            '/api/finance/accounts/summary'
        );

        const data=response.data||{};

        document.getElementById(
            'totalAccounts'
        ).textContent=data.total||0;

        document.getElementById(
            'activeAccounts'
        ).textContent=data.active||0;

        document.getElementById(
            'inactiveAccounts'
        ).textContent=data.inactive||0;

        document.getElementById(
            'postingAccounts'
        ).textContent=data.posting_accounts||0;

        document.getElementById(
            'systemAccounts'
        ).textContent=data.system_accounts||0;
    }catch(error){
        console.error(error);
    }
}

async function loadAccounts(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading accounts...',
            9
        );

    el.cards.innerHTML=
        cardsLoadingHtml(
            'Loading accounts...'
        );

    const query=AdminUI.query({
        page,
        per_page:25,
        search:el.search.value.trim(),
        type:el.type.value,
        is_active:el.status.value
    });

    try{
        const response=await api(
            `/api/finance/accounts?${query}`
        );

        const paginator=response.data;

        accounts=paginator.data||[];

        renderAccounts();

        AdminUI.renderPagination({
            container:el.pagination,
            currentPage:paginator.current_page,
            lastPage:paginator.last_page,
            total:paginator.total,
            onPageChange:loadAccounts
        });
    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                9
            );

        el.cards.innerHTML=
            cardsEmptyHtml(
                AdminUI.extractError(error)
            );
    }
}

function accountActionButtons(account,{withLabel=false}={}){
    const posting=
        Number(account.children_count||0)===0;

    const actions=[
        `
            <button
                type="button"
                onclick="viewAccount(${account.id})"
                title="View Details"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 shrink-0 items-center justify-center rounded-md'} cursor-pointer bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                <i class="bi bi-eye text-sm"></i>
                ${withLabel?'View':''}
            </button>
        `
    ];

    if(canUpdate){
        actions.push(`
            <button
                type="button"
                onclick="editAccount(${account.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                title="Edit">
                <i class="bi bi-pencil-square text-sm"></i>
                ${withLabel?'Edit':''}
            </button>
        `);

        if(!account.is_system){
            actions.push(`
                <button
                    type="button"
                    onclick="toggleAccount(${account.id})"
                    class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} ${account.is_active?'bg-amber-50 text-amber-600 hover:bg-amber-100':'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'} transition"
                    title="${account.is_active?'Deactivate':'Activate'}">
                    <i class="bi ${account.is_active?'bi-toggle-off':'bi-toggle-on'} text-sm"></i>
                    ${withLabel?(account.is_active?'Deactivate':'Activate'):''}
                </button>
            `);
        }
    }

    if(
        canDelete&&
        !account.is_system&&
        Number(account.children_count||0)===0&&
        Number(account.entries_count||0)===0
    ){
        actions.push(`
            <button
                type="button"
                onclick="deleteAccount(${account.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-red-50 text-red-600 transition hover:bg-red-100"
                title="Delete">
                <i class="bi bi-trash3 text-sm"></i>
                ${withLabel?'Delete':''}
            </button>
        `);
    }

    return actions.join('');
}

function renderAccounts(){
    if(!accounts.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No accounts found.',
                9
            );

        el.cards.innerHTML=
            cardsEmptyHtml(
                'No accounts found.'
            );

        return;
    }

    el.table.innerHTML=accounts
        .map(account=>{
            const posting=
                Number(account.children_count||0)===0;

            const parent=account.parent
                ?`${esc(account.parent.code)} - ${esc(account.parent.name)}`
                :'—';

            const systemBadge=account.is_system
                ?`
                    <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-semibold text-indigo-700">
                        System
                    </span>
                `
                :'';

            const postingBadge=posting
                ?`
                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[9px] font-semibold text-emerald-700">
                        Posting
                    </span>
                `
                :`
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-600">
                        Parent
                    </span>
                `;

            return `
                <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-3">
                        <span class="font-semibold text-xs 2xl:text-sm text-slate-700">
                            ${esc(account.code)}
                        </span>
                    </td>

                    <td class="px-4 py-3">
                        <div class="font-semibold text-xs 2xl:text-sm text-slate-700">
                            ${esc(account.name)}
                        </div>

                        <div class="mt-1 flex flex-wrap gap-1">
                            ${systemBadge}
                            ${postingBadge}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                        ${esc(AdminUI.titleCase(account.type))}
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-500">
                        ${account.sub_type
                            ?esc(AdminUI.titleCase(account.sub_type))
                            :'—'}
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-500">
                        ${parent}
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-right text-slate-600">
                        ${money(account.opening_balance)}
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-right font-semibold text-slate-700">
                        ${money(accountBalance(account))}
                    </td>

                    <td class="px-4 py-3">
                        ${AdminUI.statusBadge(
                            account.is_active
                                ?'active'
                                :'inactive'
                        )}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex flex-wrap justify-end gap-1">
                            ${accountActionButtons(account)}
                        </div>
                    </td>
                </tr>
            `;
        })
        .join('');

    el.cards.innerHTML=accounts
        .map(account=>{
            const posting=
                Number(account.children_count||0)===0;

            const parent=account.parent
                ?`${esc(account.parent.code)} - ${esc(account.parent.name)}`
                :'—';

            const systemBadge=account.is_system
                ?`
                    <span class="rounded bg-indigo-50 px-1.5 py-0.5 text-[9px] font-semibold text-indigo-700">
                        System
                    </span>
                `
                :'';

            const postingBadge=posting
                ?`
                    <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[9px] font-semibold text-emerald-700">
                        Posting
                    </span>
                `
                :`
                    <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-600">
                        Parent
                    </span>
                `;

            return`
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-xs 2xl:text-sm font-semibold text-slate-700">
                                ${esc(account.code)}
                            </p>

                            <p class="mt-1 truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                                ${esc(account.name)}
                            </p>

                            <div class="mt-1.5 flex flex-wrap gap-1">
                                ${systemBadge}
                                ${postingBadge}
                            </div>
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(
                                account.is_active
                                    ?'active'
                                    :'inactive'
                            )}
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 rounded-md bg-slate-50/60 p-3 text-[11px]">
                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs 2xl:text-sm">Type</p>
                            <p class="truncate font-medium text-slate-700">
                                ${esc(AdminUI.titleCase(account.type))}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs 2xl:text-sm">Sub Type</p>
                            <p class="truncate font-medium text-slate-700">
                                ${account.sub_type
                                    ?esc(AdminUI.titleCase(account.sub_type))
                                    :'—'}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs 2xl:text-sm">Parent</p>
                            <p class="truncate font-medium text-slate-700">
                                ${parent}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-500 text-xs 2xl:text-sm">Opening</p>
                            <p class="truncate font-medium text-slate-700">
                                ${money(account.opening_balance)}
                            </p>
                        </div>

                        <div class="col-span-2 min-w-0">
                            <p class="text-slate-500 text-xs 2xl:text-sm">Balance</p>
                            <p class="truncate font-semibold text-slate-800">
                                ${money(accountBalance(account))}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3">
                        ${accountActionButtons(account,{withLabel:true})}
                    </div>
                </div>
            `;
        })
        .join('');
}

async function loadParentOptions(
    account=null
){
    try{
        const response=await api(
            account
                ?`/api/finance/accounts/${account.id}/options`
                :'/api/finance/accounts/options'
        );

        parents=response.data?.parents||[];

        renderParentOptions();
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

function renderParentOptions(){
    const type=el.accountType.value;

    const selected=String(
        editingAccount?.parent_id??''
    );

    const options=parents
        .filter(account=>
            !type||
            account.type===type
        )
        .map(account=>`
            <option
                value="${account.id}"
                ${String(account.id)===selected?'selected':''}>
                ${esc(account.code)} - ${esc(account.name)}
            </option>
        `)
        .join('');

    el.parent.innerHTML=
        `<option value="">No Parent</option>`+
        options;
}

window.openAccountModal=async function(){
    editingAccount=null;

    el.form.reset();

    el.accountStatus.value='1';
    el.opening.value='0';

    [
        el.code,
        el.accountType,
        el.subType,
        el.parent,
        el.opening,
        el.accountStatus
    ].forEach(input=>{
        input.disabled=false;
    });

    document.getElementById(
        'accountModalTitle'
    ).textContent='Add Account';

    AdminUI.clearError(
        'formError'
    );

    await loadParentOptions();

    AdminUI.openModal(
        'accountModal'
    );
};

window.editAccount=async function(id){
    try{
        const response=await api(
            `/api/finance/accounts/${id}`
        );

        const account=response.data;

        editingAccount=account;

        el.form.reset();

        document.getElementById(
            'accountModalTitle'
        ).textContent='Edit Account';

        el.code.value=account.code||'';
        el.name.value=account.name||'';
        el.accountType.value=account.type||'';
        el.subType.value=account.sub_type||'';
        el.opening.value=account.opening_balance||0;
        el.accountStatus.value=account.is_active
            ?'1'
            :'0';
        el.description.value=account.description||'';

        const hasEntries=
            Number(account.entries_count||0)>0;

        if(account.is_system){
            el.code.disabled=true;
            el.accountType.disabled=true;
            el.subType.disabled=true;
            el.parent.disabled=true;
            el.opening.disabled=true;
            el.accountStatus.disabled=true;
        }else{
            el.code.disabled=false;
            el.accountType.disabled=
                hasEntries||
                Number(account.children_count||0)>0;

            el.subType.disabled=false;
            el.parent.disabled=hasEntries;
            el.opening.disabled=hasEntries;
            el.accountStatus.disabled=false;
        }

        await loadParentOptions(
            account
        );

        el.parent.value=
            account.parent_id||'';

        AdminUI.clearError(
            'formError'
        );

        AdminUI.openModal(
            'accountModal'
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

el.accountType.addEventListener(
    'change',
    renderParentOptions
);

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'formError'
        );

        const data={
            parent_id:el.parent.value
                ?Number(el.parent.value)
                :null,
            code:el.code.value.trim(),
            name:el.name.value.trim(),
            type:el.accountType.value,
            sub_type:el.subType.value.trim()||null,
            opening_balance:Number(
                el.opening.value||0
            ),
            is_active:
                el.accountStatus.value==='1',
            description:
                el.description.value.trim()||null
        };

        if(editingAccount?.is_system){
            delete data.parent_id;
            delete data.code;
            delete data.type;
            delete data.sub_type;
            delete data.opening_balance;
            delete data.is_active;
        }else if(editingAccount){
            if(el.accountType.disabled){
                delete data.type;
            }

            if(el.parent.disabled){
                delete data.parent_id;
            }

            if(el.opening.disabled){
                delete data.opening_balance;
            }
        }

        AdminUI.setLoading(
            el.save,
            editingAccount
                ?'Updating...'
                :'Saving...'
        );

        try{
            const response=await api(
                editingAccount
                    ?`/api/finance/accounts/${editingAccount.id}`
                    :'/api/finance/accounts',
                {
                    method:editingAccount
                        ?'PUT'
                        :'POST',
                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal(
                'accountModal'
            );

            Toast.success(
                response.message||
                'Account saved successfully.'
            );

            await Promise.all([
                loadAccounts(
                    editingAccount
                        ?currentPage
                        :1
                ),
                loadSummary()
            ]);
        }catch(error){
            AdminUI.showError(
                'formError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                el.save
            );
        }
    }
);

window.viewAccount=async function(id){
    try{
        const response=await api(
            `/api/finance/accounts/${id}`
        );

        const account=response.data;

        const children=account.children||[];

        document.getElementById(
            'accountDetailsBody'
        ).innerHTML=`
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-500">Code</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${esc(account.code)}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-500">Type</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${esc(AdminUI.titleCase(account.type))}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-500">Opening Balance</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${money(account.opening_balance)}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-500">Current Balance</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${money(account.balance)}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-md border border-slate-200 p-4">
                <div class="grid gap-1 md:grid-cols-2">
                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Name:
                        ${esc(account.name)}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Sub Type:
                        ${account.sub_type
                            ?esc(AdminUI.titleCase(account.sub_type))
                            :'—'}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Parent:
                        ${account.parent
                            ?`${esc(account.parent.code)} - ${esc(account.parent.name)}`
                            :'—'}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Account Class:
                        ${account.is_posting
                            ?'Posting Account'
                            :'Parent Account'}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Journal Entries:
                        ${account.entries_count||0}</span>
                    </div>

                    <div>
                        <span class="text-slate-500 text-xs 2xl:text-sm">Status:
                        ${AdminUI.statusBadge(
                            account.is_active
                                ?'active'
                                :'inactive'
                        )}</span>
                    </div>

                    <div class="md:col-span-2">
                        <span class="text-slate-500 text-xs 2xl:text-sm">Description:
                        ${esc(account.description||'—')}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="mb-2 text-sm 2xl:text-base font-bold text-slate-700">
                    Child Accounts
                </h3>

                ${
                    children.length
                        ?`
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-base">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs 2xl:text-sm">
                                                Code
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs 2xl:text-sm">
                                                Name
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs 2xl:text-sm">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        ${children.map(child=>`
                                            <tr class="border-t border-slate-100">
                                                <td class="px-3 py-2 text-xs 2xl:text-sm">
                                                    ${esc(child.code)}
                                                </td>

                                                <td class="px-3 py-2 text-xs 2xl:text-sm">
                                                    ${esc(child.name)}
                                                </td>

                                                <td class="px-3 py-2 text-xs 2xl:text-sm">
                                                    ${AdminUI.statusBadge(
                                                        child.is_active
                                                            ?'active'
                                                            :'inactive'
                                                    )}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        `
                        :`
                            <div class="rounded-md border border-slate-200 p-5 text-center text-base text-slate-500">
                                No child accounts.
                            </div>
                        `
                }
            </div>
        `;

        AdminUI.openModal(
            'accountDetailsModal'
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.toggleAccount=async function(id){
    const account=accounts.find(
        item=>Number(item.id)===Number(id)
    );

    if(!account){
        return;
    }

    const activating=!account.is_active;

    const confirmed=await AdminUI.confirm({
        title:activating
            ?'Activate Account?'
            :'Deactivate Account?',
        message:activating
            ?`Activate ${account.code} - ${account.name}?`
            :`Deactivate ${account.code} - ${account.name}? New journal postings will no longer be allowed.`,
        confirmText:activating
            ?'Activate'
            :'Deactivate',
        type:activating
            ?'success'
            :'warning'
    });

    if(!confirmed){
        return;
    }

    try{
        const response=await api(
            `/api/finance/accounts/${id}/toggle`,
            {
                method:'PATCH'
            }
        );

        Toast.success(
            response.message
        );

        await Promise.all([
            loadAccounts(currentPage),
            loadSummary()
        ]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.deleteAccount=async function(id){
    const account=accounts.find(
        item=>Number(item.id)===Number(id)
    );

    if(!account){
        return;
    }

    const confirmed=await AdminUI.confirm({
        title:'Delete Account?',
        message:`Delete ${account.code} - ${account.name}? This action is only allowed when the account has no child accounts or journal history.`,
        confirmText:'Delete Account',
        type:'danger'
    });

    if(!confirmed){
        return;
    }

    try{
        const response=await api(
            `/api/finance/accounts/${id}`,
            {
                method:'DELETE'
            }
        );

        Toast.success(
            response.message
        );

        await Promise.all([
            loadAccounts(currentPage),
            loadSummary()
        ]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.clearFilters=function(){
    el.search.value='';
    el.type.value='';
    el.status.value='';

    loadAccounts(1);
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

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadAccounts(1)
        )
    );

    el.type.addEventListener(
        'change',
        ()=>loadAccounts(1)
    );

    el.status.addEventListener(
        'change',
        ()=>loadAccounts(1)
    );

    Promise.all([
        loadAccounts(),
        loadSummary()
    ]);
}

if(
    document.readyState==='loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        init
    );
}else{
    init();
}
</script>
@endpush