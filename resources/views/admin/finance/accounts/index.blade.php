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
                <p class="text-sm text-slate-500">
                    Manage the accounting structure used by all finance modules.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openAccountModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Account
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Accounts</p>
            <p id="totalAccounts" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Active</p>
            <p id="activeAccounts" class="mt-1 text-xl font-bold text-emerald-700">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Inactive</p>
            <p id="inactiveAccounts" class="mt-1 text-xl font-bold text-slate-600">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Posting Accounts</p>
            <p id="postingAccounts" class="mt-1 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">System Accounts</p>
            <p id="systemAccounts" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-700">Account Search</p>
                <p class="text-[11px] text-slate-400">
                    Search by account code, name or subtype.
                </p>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-3 xl:w-auto xl:grid-cols-[280px_150px_150px_auto] xl:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search accounts..."
                        class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3 text-sm outline-none focus:border-indigo-400 xl:rounded-r-none">
                </div>

                <select
                    id="typeFilter"
                    class="h-9 rounded-md border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                    <option value="">All Types</option>
                    <option value="asset">Asset</option>
                    <option value="liability">Liability</option>
                    <option value="equity">Equity</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>

                <select
                    id="statusFilter"
                    class="h-9 rounded-md border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[980px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Code
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Account
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Type
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Sub Type
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Parent
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Opening
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Balance
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody id="accountTable">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                            Loading accounts...
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

<div
    id="accountModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2
                    id="accountModalTitle"
                    class="text-lg font-bold text-slate-800">
                    Add Account
                </h2>

                <p class="text-xs text-slate-500">
                    Create a posting or parent account.
                </p>
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
            class="flex min-h-0 flex-1 flex-col">

            <div class="space-y-4 overflow-y-auto p-5">
                <div
                    id="formError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
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

                <div class="rounded-md border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-500">
                    Parent and child accounts must have the same account type.
                    Only leaf accounts can receive journal postings.
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('accountModal')"
                    class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="saveAccountButton"
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Save Account
                </button>
            </div>
        </form>
    </div>
</div>

<div
    id="accountDetailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel w-full max-w-3xl overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Account Details
                </h2>

                <p class="text-xs text-slate-500">
                    Account structure and current balance.
                </p>
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
            class="max-h-[75vh] overflow-y-auto p-5">
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
    }
}

function renderAccounts(){
    if(!accounts.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No accounts found.',
                9
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

            const actions=[
                `
                    <button
                        type="button"
                        onclick="viewAccount(${account.id})"
                        class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
                        View
                    </button>
                `
            ];

            if(canUpdate){
                actions.push(`
                    <button
                        type="button"
                        onclick="editAccount(${account.id})"
                        class="rounded border border-indigo-300 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-50">
                        Edit
                    </button>
                `);

                if(!account.is_system){
                    actions.push(`
                        <button
                            type="button"
                            onclick="toggleAccount(${account.id})"
                            class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
                            ${account.is_active?'Deactivate':'Activate'}
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
                        class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                        Delete
                    </button>
                `);
            }

            return `
                <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-3">
                        <span class="font-semibold text-slate-700">
                            ${esc(account.code)}
                        </span>
                    </td>

                    <td class="px-4 py-3">
                        <div class="font-semibold text-slate-700">
                            ${esc(account.name)}
                        </div>

                        <div class="mt-1 flex flex-wrap gap-1">
                            ${systemBadge}
                            ${postingBadge}
                        </div>
                    </td>

                    <td class="px-4 py-3 text-slate-600">
                        ${esc(AdminUI.titleCase(account.type))}
                    </td>

                    <td class="px-4 py-3 text-slate-500">
                        ${account.sub_type
                            ?esc(AdminUI.titleCase(account.sub_type))
                            :'—'}
                    </td>

                    <td class="px-4 py-3 text-slate-500">
                        ${parent}
                    </td>

                    <td class="px-4 py-3 text-right text-slate-600">
                        ${money(account.opening_balance)}
                    </td>

                    <td class="px-4 py-3 text-right font-semibold text-slate-700">
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
                            ${actions.join('')}
                        </div>
                    </td>
                </tr>
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
                    <p class="text-[11px] text-slate-400">Code</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${esc(account.code)}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Type</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${esc(AdminUI.titleCase(account.type))}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Opening Balance</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${money(account.opening_balance)}
                    </p>
                </div>

                <div class="rounded-md border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Current Balance</p>
                    <p class="mt-1 font-semibold text-slate-700">
                        ${money(account.balance)}
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-md border border-slate-200 p-4">
                <div class="grid gap-3 text-sm md:grid-cols-2">
                    <div>
                        <span class="text-slate-400">Name:</span>
                        ${esc(account.name)}
                    </div>

                    <div>
                        <span class="text-slate-400">Sub Type:</span>
                        ${account.sub_type
                            ?esc(AdminUI.titleCase(account.sub_type))
                            :'—'}
                    </div>

                    <div>
                        <span class="text-slate-400">Parent:</span>
                        ${account.parent
                            ?`${esc(account.parent.code)} - ${esc(account.parent.name)}`
                            :'—'}
                    </div>

                    <div>
                        <span class="text-slate-400">Account Class:</span>
                        ${account.is_posting
                            ?'Posting Account'
                            :'Parent Account'}
                    </div>

                    <div>
                        <span class="text-slate-400">Journal Entries:</span>
                        ${account.entries_count||0}
                    </div>

                    <div>
                        <span class="text-slate-400">Status:</span>
                        ${AdminUI.statusBadge(
                            account.is_active
                                ?'active'
                                :'inactive'
                        )}
                    </div>

                    <div class="md:col-span-2">
                        <span class="text-slate-400">Description:</span>
                        ${esc(account.description||'—')}
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <h3 class="mb-2 text-sm font-bold text-slate-700">
                    Child Accounts
                </h3>

                ${
                    children.length
                        ?`
                            <div class="overflow-hidden rounded-md border border-slate-200">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs">
                                                Code
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs">
                                                Name
                                            </th>
                                            <th class="px-3 py-2 text-left text-xs">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        ${children.map(child=>`
                                            <tr class="border-t border-slate-100">
                                                <td class="px-3 py-2">
                                                    ${esc(child.code)}
                                                </td>

                                                <td class="px-3 py-2">
                                                    ${esc(child.name)}
                                                </td>

                                                <td class="px-3 py-2">
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
                            <div class="rounded-md border border-slate-200 p-5 text-center text-sm text-slate-400">
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