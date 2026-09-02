@extends('layouts.admin')

@section('title','Income')
@section('page_title','Income')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                <i class="bi bi-arrow-down-circle"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Income</h1>
                <p class="text-xs text-slate-500">Record and manage association income transactions.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openIncomeModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Income
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Income</p>
            <p id="totalIncome" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">This Month</p>
            <p id="monthIncome" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Transactions</p>
            <p id="incomeCount" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>
    </div>

    {{-- Search / Filter --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Income</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by number, member, reference or description.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[minmax(220px,280px)_240px_120px_auto] lg:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search..."
                        class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3 text-sm outline-none focus:border-indigo-400 lg:rounded-r-none">
                </div>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:border-l-0"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 px-3 text-sm outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="rejected">Rejected</option>
                    <option value="posted">Posted</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[950px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Income No</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Income Account</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Receive Account</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="incomeTable">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            Loading income...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Add Income Modal --}}
<div id="incomeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-slate-800">Add Income</h2>
                    <p class="text-xs text-slate-500">
                        Record income and automatically post journal entries.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeIncomeModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="incomeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-receipt"></i>
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Income Information</h3>
                            <p class="text-[11px] text-slate-400">Enter income transaction details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">
                                Income Date
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="incomeDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">
                                Amount
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="app-input"
                                required>
                        </div>

                        <div>
                            <label class="form-label">
                                Income Account
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="incomeAccount" class="app-input" required>
                                <option value="">Select Account</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">
                                Receive Account
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="receiveAccount" class="app-input" required>
                                <option value="">Select Cash/Bank</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Member</label>

                            <select id="memberId" class="app-input">
                                <option value="">No Member</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Reference</label>

                            <input
                                id="reference"
                                type="text"
                                maxlength="150"
                                class="app-input">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Description</label>

                        <textarea
                            id="description"
                            rows="3"
                            class="app-input resize-none"></textarea>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-slate-50/50 p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-journal-check"></i>
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-slate-800">Automatic Journal</h3>
                            <p class="text-[11px] text-slate-400">
                                This journal will be posted automatically.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">
                                Debit
                            </p>

                            <p id="journalDebit" class="mt-1 text-base font-semibold text-slate-700">
                                Receive Account
                            </p>
                        </div>

                        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">
                                Credit
                            </p>

                            <p id="journalCredit" class="mt-1 text-base font-semibold text-slate-700">
                                Income Account
                            </p>
                        </div>
                    </div>
                </section>

                <div
                    id="formError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeIncomeModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="saveButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Save Income
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Income Details Modal --}}
<div id="incomeDetailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>

                <div>
                    <h2 class="text-base font-semibold text-slate-800">Income Details</h2>
                    <p class="text-xs text-slate-500">
                        View income transaction and journal information.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeIncomeDetailsModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="incomeDetailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="closeIncomeDetailsModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
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
    color:rgb(51 65 85);
}
</style>
@endsection

@push('scripts')
<script>
let incomes=[];
let currentPage=1;
let lastPage=1;
let total=0;
let optionsLoaded=false;
let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('incomeTable'),
    search:document.getElementById('searchInput'),
    dateRange:document.getElementById('dateRangeFilter'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('incomeForm'),
    date:document.getElementById('incomeDate'),
    amount:document.getElementById('amount'),
    incomeAccount:document.getElementById('incomeAccount'),
    receiveAccount:document.getElementById('receiveAccount'),
    member:document.getElementById('memberId'),
    reference:document.getElementById('reference'),
    description:document.getElementById('description'),
    saveButton:document.getElementById('saveButton')
};

function localDate(date=new Date()){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function setPickerDate(element,value){
    if(!element){
        return;
    }

    const date=value
        ?String(value).substring(0,10)
        :'';

    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(date,false)
            :element._flatpickr.clear();
    }
}

function initDateRangePicker(){
    if(
        !el.dateRange||
        typeof window.flatpickr==='undefined'
    ){
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

                    loadIncomes(1);

                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    loadIncomes(1);
                }
            }
        }
    );
}

async function loadIncomes(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading income...',
            8
        );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        from:selectedFrom,
        to:selectedTo,
        status:el.status.value,
        page
    });

    try{
        const response=await api(
            `/api/finance/incomes?${query}`
        );

        const paginator=
            response.data??{};

        incomes=
            paginator.data??[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??0
        );

        renderIncomeTable();
        updateSummary();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadIncomes
        });
    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                8
            );
    }
}

function renderIncomeTable(){
    if(!incomes.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No income records found.',
                8
            );

        return;
    }

    el.table.innerHTML=incomes.map(item=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">
                ${AdminUI.escapeHtml(item.income_no)}
            </td>

            <td class="px-4 py-3 text-sm text-slate-600">
                ${AdminUI.formatDate(item.income_date)}
            </td>

            <td class="px-4 py-3 text-sm text-slate-600">
                ${AdminUI.escapeHtml(
                    item.member?.user?.name??'—'
                )}
            </td>

            <td class="px-4 py-3">
                <p class="text-sm font-medium text-slate-700">
                    ${AdminUI.escapeHtml(
                        item.income_account?.name??'—'
                    )}
                </p>

                <p class="text-[10px] text-slate-400">
                    ${AdminUI.escapeHtml(
                        item.income_account?.code??''
                    )}
                </p>
            </td>

            <td class="px-4 py-3">
                <p class="text-sm font-medium text-slate-700">
                    ${AdminUI.escapeHtml(
                        item.receive_account?.name??'—'
                    )}
                </p>

                <p class="text-[10px] text-slate-400">
                    ${AdminUI.escapeHtml(
                        item.receive_account?.code??''
                    )}
                </p>
            </td>

            <td class="px-4 py-3 text-right text-sm font-bold text-slate-800">
                ${money(item.amount)}
            </td>

            <td class="px-4 py-3">
                ${AdminUI.statusBadge(item.status)}
            </td>

            <td class="px-4 py-3">
                <div class="flex justify-end gap-1">
                    <button
                        type="button"
                        onclick="viewIncome(${item.id})"
                        class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-50 text-slate-500 hover:bg-slate-100"
                        title="View">
                        <i class="bi bi-eye text-sm"></i>
                    </button>

                    ${
                        canUpdate&&item.status==='posted'
                            ?`
                                <button
                                    type="button"
                                    onclick="cancelIncome(${item.id})"
                                    class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100"
                                    title="Cancel">
                                    <i class="bi bi-x-circle text-sm"></i>
                                </button>
                            `
                            :''
                    }
                </div>
            </td>
        </tr>
    `).join('');
}

function updateSummary(){
    const posted=incomes.filter(
        item=>item.status==='posted'
    );

    const totalAmount=posted.reduce(
        (sum,item)=>
            sum+Number(
                item.amount??0
            ),
        0
    );

    const now=new Date();

    const monthAmount=posted
        .filter(item=>{
            const date=
                new Date(
                    `${String(item.income_date).substring(0,10)}T00:00:00`
                );

            return(
                date.getMonth()===now.getMonth()&&
                date.getFullYear()===now.getFullYear()
            );
        })
        .reduce(
            (sum,item)=>
                sum+Number(
                    item.amount??0
                ),
            0
        );

    document.getElementById(
        'totalIncome'
    ).textContent=
        money(totalAmount);

    document.getElementById(
        'monthIncome'
    ).textContent=
        money(monthAmount);

    document.getElementById(
        'incomeCount'
    ).textContent=
        total;
}

async function loadOptions(){
    if(optionsLoaded){
        return;
    }

    const response=await api(
        '/api/finance/incomes/options'
    );

    const data=
        response.data??{};

    el.incomeAccount.innerHTML=`
        <option value="">Select Account</option>

        ${(data.income_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    el.receiveAccount.innerHTML=`
        <option value="">Select Cash/Bank</option>

        ${(data.receive_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    el.member.innerHTML=`
        <option value="">No Member</option>

        ${(data.members??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.member_code)}
                -
                ${AdminUI.escapeHtml(
                    item.user?.name??''
                )}
            </option>
        `).join('')}
    `;

    optionsLoaded=true;
}

window.openIncomeModal=async function(){
    AdminUI.resetForm(
        el.form
    );

    AdminUI.clearError(
        'formError'
    );

    try{
        await loadOptions();

        AdminUI.openModal(
            'incomeModal'
        );

        if(
            typeof window.initDatePickers===
            'function'
        ){
            window.initDatePickers();
        }

        setPickerDate(
            el.date,
            localDate()
        );

        updateJournalPreview();
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.closeIncomeModal=function(){
    AdminUI.closeModal(
        'incomeModal'
    );
};

window.closeIncomeDetailsModal=function(){
    AdminUI.closeModal(
        'incomeDetailsModal'
    );
};

function updateJournalPreview(){
    const debit=
        el.receiveAccount.options[
            el.receiveAccount.selectedIndex
        ]?.text??'Receive Account';

    const credit=
        el.incomeAccount.options[
            el.incomeAccount.selectedIndex
        ]?.text??'Income Account';

    document.getElementById(
        'journalDebit'
    ).textContent=
        el.receiveAccount.value
            ?debit
            :'Receive Account';

    document.getElementById(
        'journalCredit'
    ).textContent=
        el.incomeAccount.value
            ?credit
            :'Income Account';
}

el.receiveAccount.addEventListener(
    'change',
    updateJournalPreview
);

el.incomeAccount.addEventListener(
    'change',
    updateJournalPreview
);

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'formError'
        );

        const data={
            member_id:
                el.member.value
                    ?Number(
                        el.member.value
                    )
                    :null,

            income_account_id:
                Number(
                    el.incomeAccount.value
                ),

            receive_account_id:
                Number(
                    el.receiveAccount.value
                ),

            amount:
                Number(
                    el.amount.value
                ),

            income_date:
                el.date.value,

            reference:
                el.reference.value.trim()||
                null,

            description:
                el.description.value.trim()||
                null
        };

        AdminUI.setLoading(
            el.saveButton,
            'Saving...'
        );

        try{
            const response=await api(
                '/api/finance/incomes',
                {
                    method:'POST',
                    body:JSON.stringify(data)
                }
            );

            closeIncomeModal();

            Toast.success(
                response.message||
                'Income recorded successfully.'
            );

            await loadIncomes(1);
        }catch(error){
            AdminUI.showError(
                'formError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                el.saveButton
            );
        }
    }
);

window.viewIncome=async function(id){
    AdminUI.openModal(
        'incomeDetailsModal'
    );

    const body=
        document.getElementById(
            'incomeDetailsBody'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading details...'
        );

    try{
        const response=await api(
            `/api/finance/incomes/${id}`
        );

        const item=
            response.data??{};

        const entries=
            item.finance_transaction
                ?.entries??[];

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                ${detail(
                    'Income No',
                    item.income_no
                )}

                ${detail(
                    'Date',
                    AdminUI.formatDate(
                        item.income_date
                    )
                )}

                ${detail(
                    'Member',
                    item.member?.user?.name??
                    '—'
                )}

                ${detail(
                    'Amount',
                    money(
                        item.amount
                    )
                )}

                ${detail(
                    'Income Account',
                    `${item.income_account?.code??''} ${item.income_account?.name??''}`.trim()||'—'
                )}

                ${detail(
                    'Receive Account',
                    `${item.receive_account?.code??''} ${item.receive_account?.name??''}`.trim()||'—'
                )}

                ${detail(
                    'Reference',
                    item.reference??'—'
                )}

                ${detail(
                    'Status',
                    AdminUI.titleCase(
                        item.status
                    )
                )}
            </div>

            <div class="mt-5">
                <div class="mb-2 flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-journal-text text-sm"></i>
                    </div>

                    <p class="text-sm font-semibold text-slate-700">
                        Journal Entry
                    </p>
                </div>

                <div class="overflow-x-auto rounded-md border border-slate-200">
                    <table class="w-full min-w-[500px] text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-semibold text-slate-500">
                                    Account
                                </th>

                                <th class="px-3 py-2 text-right font-semibold text-slate-500">
                                    Debit
                                </th>

                                <th class="px-3 py-2 text-right font-semibold text-slate-500">
                                    Credit
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            ${
                                entries.length
                                    ?entries.map(entry=>`
                                        <tr class="border-t border-slate-100">
                                            <td class="px-3 py-2 text-slate-600">
                                                ${AdminUI.escapeHtml(
                                                    entry.account?.code??''
                                                )}
                                                -
                                                ${AdminUI.escapeHtml(
                                                    entry.account?.name??''
                                                )}
                                            </td>

                                            <td class="px-3 py-2 text-right font-medium text-slate-700">
                                                ${
                                                    Number(entry.debit??0)>0
                                                        ?money(entry.debit)
                                                        :'—'
                                                }
                                            </td>

                                            <td class="px-3 py-2 text-right font-medium text-slate-700">
                                                ${
                                                    Number(entry.credit??0)>0
                                                        ?money(entry.credit)
                                                        :'—'
                                                }
                                            </td>
                                        </tr>
                                    `).join('')
                                    :`
                                        <tr>
                                            <td colspan="3" class="px-3 py-6 text-center text-slate-400">
                                                No journal entries found.
                                            </td>
                                        </tr>
                                    `
                            }
                        </tbody>
                    </table>
                </div>
            </div>

            ${
                item.description
                    ?`
                        <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Description
                            </p>

                            <p class="text-sm leading-5 text-slate-600">
                                ${AdminUI.escapeHtml(
                                    item.description
                                )}
                            </p>
                        </div>
                    `
                    :''
            }
        `;
    }catch(error){
        body.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

window.cancelIncome=async function(id){
    const item=incomes.find(
        row=>Number(row.id)===Number(id)
    );

    if(!item){
        return;
    }

    const confirmed=await AdminUI.confirm({
        title:'Cancel Income?',
        subtitle:'A reversing journal entry will be created.',
        message:`Income ${item.income_no} will be cancelled. The original accounting record will remain in history.`,
        confirmText:'Cancel Income',
        type:'danger'
    });

    if(!confirmed){
        return;
    }

    const reason=
        prompt(
            'Cancellation reason:'
        );

    if(
        !reason||
        !reason.trim()
    ){
        return;
    }

    try{
        const response=await api(
            `/api/finance/incomes/${id}/cancel`,
            {
                method:'POST',
                body:JSON.stringify({
                    reason:
                        reason.trim()
                })
            }
        );

        Toast.success(
            response.message||
            'Income cancelled and reversed successfully.'
        );

        await loadIncomes(
            currentPage
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

function detail(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 break-words text-base font-medium text-slate-700">
                ${AdminUI.escapeHtml(
                    value??'—'
                )}
            </p>
        </div>
    `;
}

function money(value){
    return `${@json(setting('currency_symbol','৳'))}${Number(
        value??0
    ).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';

    selectedFrom='';
    selectedTo='';

    if(dateRangePicker){
        dateRangePicker.clear(false);
    }else if(el.dateRange){
        el.dateRange.value='';
    }

    loadIncomes(1);
};

async function initIncomePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initIncomePage,
            50
        );

        return;
    }

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }

    initDateRangePicker();

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadIncomes(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadIncomes(1)
    );

    await loadIncomes();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initIncomePage
    );
}else{
    initIncomePage();
}
</script>
@endpush