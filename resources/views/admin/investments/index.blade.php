@extends('layouts.admin')

@section('title','Investments')
@section('page_title','Investments')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Investment Management</h1>
                <p class="text-sm text-slate-500">Manage investments, returns and accounting postings.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Investment.create'))
        <button type="button"
            onclick="openInvestmentModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Investment
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Investment</p>
            <p id="totalAmount" class="mt-2 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0
            </p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-white p-4">
            <p class="text-xs text-slate-500">Active</p>
            <p id="activeCount" class="mt-2 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-white p-4">
            <p class="text-xs text-slate-500">Income Received</p>
            <p id="incomeReceived" class="mt-2 truncate text-xl font-bold text-emerald-700">
                {{ setting('currency_symbol','৳') }}0
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Principal Returned</p>
            <p id="principalReceived" class="mt-2 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0
            </p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Expected Income</p>
            <p id="expectedReturn" class="mt-2 truncate text-xl font-bold text-slate-800">
                {{ setting('currency_symbol','৳') }}0
            </p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-200 p-4 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input id="searchInput"
                    type="text"
                    class="app-input w-full pl-9"
                    placeholder="Search investment, member, title...">
            </div>

            <select id="statusFilter" class="app-input lg:w-48">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button type="button"
                onclick="clearFilters()"
                class="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Clear
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Investment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Payment Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Income</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Principal Returned</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500">Actions</th>
                    </tr>
                </thead>

                <tbody id="investmentTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-400">
                            Loading investments...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationWrap" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

<div id="investmentModal" class="app-modal-overlay hidden">
    <div class="app-modal-panel w-full max-w-3xl">
        <div class="app-modal-header">
            <div>
                <h3 id="investmentModalTitle" class="font-bold text-slate-800">Add Investment</h3>
                <p class="text-xs text-slate-500">Investment purchase will create an accounting journal automatically.</p>
            </div>
            <button type="button"
                onclick="AdminUI.closeModal('investmentModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="investmentForm">
            <div class="max-h-[75vh] space-y-4 overflow-y-auto p-5">
                <div id="investmentError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Member <span class="text-red-500">*</span>
                        </label>
                        <select id="memberId" class="app-input w-full" required>
                            <option value="">Select Member</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Payment Account <span class="text-red-500">*</span>
                        </label>
                        <select id="paymentAccountId" class="app-input w-full" required>
                            <option value="">Select Cash/Bank</option>
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Journal: Dr Investment Asset, Cr selected Cash/Bank.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Investment Title <span class="text-red-500">*</span>
                        </label>
                        <input id="investmentTitle"
                            type="text"
                            class="app-input w-full"
                            maxlength="255"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Amount <span class="text-red-500">*</span>
                        </label>
                        <input id="investmentAmount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="app-input w-full"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Expected Income
                        </label>
                        <input id="expectedReturn"
                            type="number"
                            min="0"
                            step="0.01"
                            class="app-input w-full">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Investment Date <span class="text-red-500">*</span>
                        </label>
                        <input id="investmentDate"
                            type="date"
                            class="app-input w-full"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Maturity Date
                        </label>
                        <input id="maturityDate"
                            type="date"
                            class="app-input w-full">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Status
                        </label>
                        <select id="investmentStatus" class="app-input w-full">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Description
                        </label>
                        <textarea id="investmentDescription"
                            rows="3"
                            maxlength="5000"
                            class="app-input w-full"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                    onclick="AdminUI.closeModal('investmentModal')"
                    class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveInvestmentButton"
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Save Investment
                </button>
            </div>
        </form>
    </div>
</div>

<div id="returnModal" class="app-modal-overlay hidden">
    <div class="app-modal-panel w-full max-w-2xl">
        <div class="app-modal-header">
            <div>
                <h3 id="returnModalTitle" class="font-bold text-slate-800">Add Return</h3>
                <p id="returnInvestmentInfo" class="text-xs text-slate-500"></p>
            </div>

            <button type="button"
                onclick="AdminUI.closeModal('returnModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="returnForm">
            <input id="returnInvestmentId" type="hidden">
            <input id="returnId" type="hidden">

            <div class="max-h-[75vh] space-y-4 overflow-y-auto p-5">
                <div id="returnError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Return Type <span class="text-red-500">*</span>
                        </label>

                        <select id="returnType" class="app-input w-full" required>
                            <option value="income">Investment Income</option>
                            <option value="principal">Principal Return</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Status <span class="text-red-500">*</span>
                        </label>

                        <select id="returnStatus" class="app-input w-full" required>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Amount <span class="text-red-500">*</span>
                        </label>

                        <input id="returnAmount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="app-input w-full"
                            required>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Return Date <span class="text-red-500">*</span>
                        </label>

                        <input id="returnDate"
                            type="date"
                            class="app-input w-full"
                            required>
                    </div>

                    <div id="receiveAccountWrap" class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Receive Account
                        </label>

                        <select id="receiveAccountId" class="app-input w-full">
                            <option value="">Select Cash/Bank</option>
                        </select>

                        <p class="mt-1 text-[11px] text-slate-400">
                            Income: Dr Cash/Bank, Cr Investment Income. Principal: Dr Cash/Bank, Cr Investment Asset.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold text-slate-600">
                            Description
                        </label>

                        <textarea id="returnDescription"
                            rows="3"
                            maxlength="3000"
                            class="app-input w-full"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                    onclick="AdminUI.closeModal('returnModal')"
                    class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveReturnButton"
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Save Return
                </button>
            </div>
        </form>
    </div>
</div>

<div id="detailsModal" class="app-modal-overlay hidden">
    <div class="app-modal-panel w-full max-w-5xl">
        <div class="app-modal-header">
            <div>
                <h3 class="font-bold text-slate-800">Investment Details</h3>
                <p class="text-xs text-slate-500">Investment, returns and accounting journals.</p>
            </div>

            <button type="button"
                onclick="AdminUI.closeModal('detailsModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsContent" class="max-h-[80vh] overflow-y-auto p-5"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Investment.update'));
const canDelete=@json(auth()->user()->hasPermission('Investment.delete'));

let investments=[];
let cashBankAccounts=[];
let members=[];
let editingInvestment=null;
let currentPage=1;
let currentDetailReturns={};

const $=id=>document.getElementById(id);
const esc=v=>AdminUI.escapeHtml(v??'');
const money=v=>`${currency}${Number(v||0).toLocaleString(undefined,{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;
const date=v=>v?AdminUI.formatDate(v):'—';

function memberName(i){
    const user=i?.member?.user;
    const name=user?.name||'Unknown Member';
    const code=i?.member?.member_code;

    return code
        ?`${name} (${code})`
        :name;
}

async function loadOptions(){
    try{
        const [optionsResponse,membersResponse]=await Promise.all([
            api('/api/investments/options'),
            api('/api/investments/members')
        ]);

        cashBankAccounts=optionsResponse.data?.cash_bank_accounts||[];
        members=membersResponse.data||[];

        renderMemberOptions();
        renderAccountOptions();
    }catch(e){
        Toast.error(AdminUI.extractError(e));
    }
}

function renderMemberOptions(){
    $('memberId').innerHTML=
        `<option value="">Select Member</option>`+
        members.map(member=>{
            const name=member.user?.name||'Member';
            return `<option value="${member.id}">
                ${esc(member.member_code)} - ${esc(name)}
            </option>`;
        }).join('');
}

function renderAccountOptions(){
    const options=
        `<option value="">Select Cash/Bank</option>`+
        cashBankAccounts.map(account=>
            `<option value="${account.id}">
                ${esc(account.code)} - ${esc(account.name)}
            </option>`
        ).join('');

    $('paymentAccountId').innerHTML=options;
    $('receiveAccountId').innerHTML=options;
}

async function loadStatistics(){
    try{
        const response=await api('/api/investments/statistics');
        const data=response.data||{};

        $('totalAmount').textContent=money(data.total_amount);
        $('activeCount').textContent=data.active||0;
        $('incomeReceived').textContent=money(data.income_received);
        $('principalReceived').textContent=money(data.principal_received);
        $('expectedReturn').textContent=money(data.expected_return);
    }catch(e){
        console.error(e);
    }
}

async function loadInvestments(page=1){
    currentPage=page;

    const tbody=$('investmentTableBody');

    tbody.innerHTML=AdminUI.loadingState(
        'Loading investments...',
        9
    );

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;

    if(search){
        params.set('search',search);
    }

    if(status){
        params.set('status',status);
    }

    try{
        const response=await api(
            `/api/investments?${params.toString()}`
        );

        const paginator=response.data;

        investments=paginator.data||[];

        renderInvestments(investments);

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadInvestments
        );
    }catch(e){
        tbody.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(e),
            9
        );
    }
}

function renderInvestments(rows){
    const tbody=$('investmentTableBody');

    if(!rows.length){
        tbody.innerHTML=AdminUI.emptyState(
            'No investments found.',
            9
        );
        return;
    }

    tbody.innerHTML=rows.map(i=>{
        const actions=[
            `<button type="button"
                onclick="viewInvestment(${i.id})"
                class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
                View
            </button>`
        ];

        if(canUpdate&&i.status!=='cancelled'){
            actions.push(
                `<button type="button"
                    onclick="editInvestment(${i.id})"
                    class="rounded border border-indigo-300 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-50">
                    Edit
                </button>`
            );

            actions.push(
                `<button type="button"
                    onclick="openReturnModal(${i.id})"
                    class="rounded border border-emerald-300 px-2 py-1 text-xs text-emerald-700 hover:bg-emerald-50">
                    Add Return
                </button>`
            );
        }

        if(canUpdate&&i.status!=='cancelled'){
            actions.push(
                `<button type="button"
                    onclick="cancelInvestment(${i.id})"
                    class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                    Cancel
                </button>`
            );
        }

        if(canDelete&&!i.finance_transaction_id){
            actions.push(
                `<button type="button"
                    onclick="deleteInvestment(${i.id})"
                    class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
                    Delete
                </button>`
            );
        }

        const payment=i.payment_account
            ?`${esc(i.payment_account.code)} - ${esc(i.payment_account.name)}`
            :'—';

        return `
            <tr class="hover:bg-slate-50/60">
                <td class="px-4 py-3">
                    <div class="font-semibold text-slate-700">${esc(i.investment_no)}</div>
                    <div class="mt-0.5 max-w-[220px] truncate text-xs text-slate-400">${esc(i.title)}</div>
                </td>

                <td class="px-4 py-3 text-slate-600">
                    ${esc(memberName(i))}
                </td>

                <td class="px-4 py-3 text-slate-600">
                    ${payment}
                </td>

                <td class="px-4 py-3 text-right font-semibold text-slate-700">
                    ${money(i.amount)}
                </td>

                <td class="px-4 py-3 text-right text-emerald-700">
                    ${money(i.paid_return_total)}
                </td>

                <td class="px-4 py-3 text-right text-slate-600">
                    ${money(i.principal_return_total)}
                </td>

                <td class="px-4 py-3 text-slate-500">
                    ${date(i.investment_date)}
                </td>

                <td class="px-4 py-3">
                    ${AdminUI.statusBadge(i.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-1">
                        ${actions.join('')}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.openInvestmentModal=function(){
    editingInvestment=null;

    $('investmentForm').reset();
    $('investmentModalTitle').textContent='Add Investment';
    $('investmentDate').value=new Date()
        .toISOString()
        .slice(0,10);

    $('investmentStatus').value='active';

    [
        'memberId',
        'paymentAccountId',
        'investmentAmount',
        'investmentDate'
    ].forEach(id=>{
        $(id).disabled=false;
    });

    AdminUI.clearError('investmentError');
    AdminUI.openModal('investmentModal');
};

window.editInvestment=async function(id){
    try{
        const response=await api(
            `/api/investments/${id}`
        );

        const i=response.data;

        editingInvestment=i;

        $('investmentForm').reset();
        $('investmentModalTitle').textContent='Edit Investment';

        $('memberId').value=i.member_id||'';
        $('paymentAccountId').value=i.payment_account_id||'';
        $('investmentTitle').value=i.title||'';
        $('investmentAmount').value=i.amount||'';
        $('expectedReturn').value=i.expected_return||0;
        $('investmentDate').value=(i.investment_date||'').slice(0,10);
        $('maturityDate').value=(i.maturity_date||'').slice(0,10);
        $('investmentStatus').value=i.status;
        $('investmentDescription').value=i.description||'';

        const posted=Boolean(
            i.finance_transaction_id
        );

        [
            'memberId',
            'paymentAccountId',
            'investmentAmount',
            'investmentDate'
        ].forEach(id=>{
            $(id).disabled=posted;
        });

        AdminUI.clearError('investmentError');
        AdminUI.openModal('investmentModal');
    }catch(e){
        Toast.error(
            AdminUI.extractError(e)
        );
    }
};

$('investmentForm').addEventListener(
    'submit',
    async e=>{
        e.preventDefault();

        AdminUI.clearError('investmentError');

        const button=$('saveInvestmentButton');

        const data={
            member_id:Number(
                $('memberId').value
            ),
            payment_account_id:Number(
                $('paymentAccountId').value
            ),
            title:$('investmentTitle').value.trim(),
            amount:Number(
                $('investmentAmount').value
            ),
            expected_return:Number(
                $('expectedReturn').value||0
            ),
            investment_date:$('investmentDate').value,
            maturity_date:$('maturityDate').value||null,
            status:$('investmentStatus').value,
            description:$('investmentDescription').value.trim()||null
        };

        if(editingInvestment?.finance_transaction_id){
            delete data.member_id;
            delete data.payment_account_id;
            delete data.amount;
            delete data.investment_date;
        }

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
                    method:editingInvestment
                        ?'PUT'
                        :'POST',
                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal(
                'investmentModal'
            );

            Toast.success(
                editingInvestment
                    ?'Investment updated successfully.'
                    :'Investment created and posted successfully.'
            );

            await Promise.all([
                loadInvestments(
                    editingInvestment
                        ?currentPage
                        :1
                ),
                loadStatistics()
            ]);
        }catch(err){
            AdminUI.showError(
                'investmentError',
                AdminUI.extractError(err)
            );
        }finally{
            AdminUI.resetLoading(button);
        }
    }
);

window.openReturnModal=function(
    id,
    returnData=null
){
    const investment=investments.find(
        item=>Number(item.id)===Number(id)
    );

    if(!investment){
        Toast.error('Investment not found.');
        return;
    }

    $('returnForm').reset();

    AdminUI.clearError('returnError');

    $('returnInvestmentId').value=id;
    $('returnId').value=returnData?.id||'';

    $('returnModalTitle').textContent=
        returnData
            ?'Edit Return'
            :'Add Return';

    $('returnInvestmentInfo').textContent=
        `${investment.investment_no} • ${investment.title}`;

    $('returnType').value=
        returnData?.return_type||'income';

    $('returnStatus').value=
        returnData?.status||'paid';

    $('returnAmount').value=
        returnData?.amount||'';

    $('returnDate').value=
        returnData?.return_date?.slice(0,10)||
        new Date().toISOString().slice(0,10);

    $('receiveAccountId').value=
        returnData?.receive_account_id||'';

    $('returnDescription').value=
        returnData?.description||'';

    syncReturnAccount();

    AdminUI.openModal('returnModal');
};

$('returnStatus').addEventListener(
    'change',
    syncReturnAccount
);

function syncReturnAccount(){
    const paid=
        $('returnStatus').value==='paid';

    $('receiveAccountWrap')
        .classList.toggle(
            'opacity-60',
            !paid
        );

    $('receiveAccountId').disabled=!paid;
    $('receiveAccountId').required=paid;
}

$('returnForm').addEventListener(
    'submit',
    async e=>{
        e.preventDefault();

        AdminUI.clearError('returnError');

        const investmentId=
            $('returnInvestmentId').value;

        const returnId=
            $('returnId').value;

        const button=
            $('saveReturnButton');

        const status=
            $('returnStatus').value;

        const data={
            return_type:$('returnType').value,
            receive_account_id:status==='paid'
                ?Number($('receiveAccountId').value)
                :null,
            amount:Number(
                $('returnAmount').value
            ),
            return_date:$('returnDate').value,
            status,
            description:$('returnDescription').value.trim()||null
        };

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
                    method:returnId
                        ?'PUT'
                        :'POST',
                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal(
                'returnModal'
            );

            Toast.success(
                returnId
                    ?'Return updated successfully.'
                    :'Return saved successfully.'
            );

            await Promise.all([
                loadInvestments(currentPage),
                loadStatistics()
            ]);
        }catch(err){
            AdminUI.showError(
                'returnError',
                AdminUI.extractError(err)
            );
        }finally{
            AdminUI.resetLoading(button);
        }
    }
);

window.viewInvestment=async function(id){
    try{
        const response=await api(
            `/api/investments/${id}`
        );

        const i=response.data;
        const returns=i.returns||[];

        currentDetailReturns=
            Object.fromEntries(
                returns.map(item=>[
                    Number(item.id),
                    item
                ])
            );

        const journal=i.finance_transaction;

        let html=`
            <div class="grid gap-3 md:grid-cols-4">
                <div class="rounded border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Investment No</p>
                    <p class="mt-1 font-semibold">${esc(i.investment_no)}</p>
                </div>

                <div class="rounded border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Amount</p>
                    <p class="mt-1 font-semibold">${money(i.amount)}</p>
                </div>

                <div class="rounded border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Income</p>
                    <p class="mt-1 font-semibold">${money(i.paid_return_total)}</p>
                </div>

                <div class="rounded border border-slate-200 p-3">
                    <p class="text-[11px] text-slate-400">Principal Returned</p>
                    <p class="mt-1 font-semibold">${money(i.principal_return_total)}</p>
                </div>
            </div>

            <div class="mt-4 rounded border border-slate-200 p-4">
                <div class="grid gap-3 text-sm md:grid-cols-2">
                    <div>
                        <span class="text-slate-400">Title:</span>
                        ${esc(i.title)}
                    </div>

                    <div>
                        <span class="text-slate-400">Member:</span>
                        ${esc(memberName(i))}
                    </div>

                    <div>
                        <span class="text-slate-400">Payment:</span>
                        ${esc(i.payment_account?.code||'')}
                        ${esc(i.payment_account?.name||'')}
                    </div>

                    <div>
                        <span class="text-slate-400">Status:</span>
                        ${AdminUI.statusBadge(i.status)}
                    </div>

                    <div>
                        <span class="text-slate-400">Date:</span>
                        ${date(i.investment_date)}
                    </div>

                    <div>
                        <span class="text-slate-400">Maturity:</span>
                        ${date(i.maturity_date)}
                    </div>

                    <div class="md:col-span-2">
                        <span class="text-slate-400">Description:</span>
                        ${esc(i.description||'—')}
                    </div>
                </div>
            </div>
        `;

        if(journal){
            html+=journalHtml(
                'Purchase Journal',
                journal
            );
        }

        html+=`
            <div class="mt-5">
                <div class="mb-2 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-slate-700">
                        Returns
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs">Date</th>
                                <th class="px-3 py-2 text-left text-xs">Type</th>
                                <th class="px-3 py-2 text-right text-xs">Amount</th>
                                <th class="px-3 py-2 text-left text-xs">Account</th>
                                <th class="px-3 py-2 text-left text-xs">Status</th>
                                <th class="px-3 py-2 text-right text-xs">Actions</th>
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
                                                ${esc(AdminUI.titleCase(item.return_type))}
                                            </td>

                                            <td class="px-3 py-2 text-right">
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
                                                    canUpdate&&!item.finance_transaction_id
                                                        ?`
                                                            <button type="button"
                                                                onclick="editReturnFromDetails(${item.id},${i.id})"
                                                                class="mr-1 rounded border border-indigo-300 px-2 py-1 text-xs text-indigo-700 hover:bg-indigo-50">
                                                                Edit
                                                            </button>

                                                            <button type="button"
                                                                onclick="deleteReturn(${item.id},${i.id})"
                                                                class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">
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
                                            <td colspan="6" class="px-3 py-8 text-center text-slate-400">
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

        $('detailsContent').innerHTML=html;

        AdminUI.openModal(
            'detailsModal'
        );
    }catch(e){
        Toast.error(
            AdminUI.extractError(e)
        );
    }
};

function journalHtml(title,journal){
    return `
        <div class="mt-4 rounded border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600">
                ${esc(title)} • ${esc(journal.transaction_no||'')}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-xs">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-3 py-2 text-left">Account</th>
                            <th class="px-3 py-2 text-right">Debit</th>
                            <th class="px-3 py-2 text-right">Credit</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${(journal.entries||[]).map(entry=>`
                            <tr class="border-b border-slate-50">
                                <td class="px-3 py-2">
                                    ${esc(entry.account?.code||'')} -
                                    ${esc(entry.account?.name||'')}
                                </td>

                                <td class="px-3 py-2 text-right">
                                    ${money(entry.debit)}
                                </td>

                                <td class="px-3 py-2 text-right">
                                    ${money(entry.credit)}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

window.editReturnFromDetails=function(
    returnId,
    investmentId
){
    const item=currentDetailReturns[
        Number(returnId)
    ];

    if(!item){
        Toast.error('Return not found.');
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
            message:'Delete this pending investment return?',
            successMessage:'Return deleted successfully.',
            onSuccess:async()=>{
                AdminUI.closeModal(
                    'detailsModal'
                );

                await Promise.all([
                    loadInvestments(currentPage),
                    loadStatistics()
                ]);
            }
        }
    );
};

window.cancelInvestment=function(id){
    const investment=investments.find(
        item=>Number(item.id)===Number(id)
    );

    if(!investment){
        return;
    }

    AdminUI.request(
        `/api/investments/${id}/cancel`,
        {
            method:'POST',
            data:{},
            confirmation:{
                title:'Cancel Investment?',
                message:`Cancel ${investment.investment_no}? The purchase journal will be reversed.`,
                confirmText:'Cancel Investment',
                type:'danger'
            },
            successMessage:'Investment cancelled and journal reversed.',
            onSuccess:async()=>Promise.all([
                loadInvestments(currentPage),
                loadStatistics()
            ])
        }
    );
};

window.deleteInvestment=function(id){
    AdminUI.deleteRequest(
        `/api/investments/${id}`,
        {
            message:'Delete this unposted investment permanently?',
            successMessage:'Investment deleted successfully.',
            onSuccess:async()=>Promise.all([
                loadInvestments(currentPage),
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
        setTimeout(init,50);
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

    await loadOptions();

    await Promise.all([
        loadInvestments(),
        loadStatistics()
    ]);
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