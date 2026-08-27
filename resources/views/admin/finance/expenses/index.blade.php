@extends('layouts.admin')

@section('title','Expenses')
@section('page_title','Expenses')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                <i class="bi bi-arrow-up-circle"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Expenses</h1>
                <p class="text-sm text-slate-500">Record and manage association expenses.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openExpenseModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Expense
        </button>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Expenses</p>
            <p id="totalExpense" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">This Month</p>
            <p id="monthExpense" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Transactions</p>
            <p id="expenseCount" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>
    </div>

    {{-- Search / Filter --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Expenses</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by expense number, payee, reference or description.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[minmax(220px,280px)_240px_120px_auto] lg:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search..."
                        class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3 text-sm outline-none focus:border-indigo-400 lg:rounded-r-none">
                </div>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:border-l-0"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="posted">Posted</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Expense No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Payee</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Expense Account</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Payment Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="expenseTable">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            Loading expenses...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Add Expense Modal --}}
<div id="expenseModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-arrow-up-circle"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Add Expense</h2>
                    <p class="text-sm text-slate-500">
                        Record expense and post the accounting entry automatically.
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeExpenseModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="expenseForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600">
                            <i class="bi bi-receipt"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Expense Information</h3>
                            <p class="text-[11px] text-slate-400">Enter expense transaction details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">
                                Expense Date <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                                <input
                                    id="expenseDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">
                                Amount <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="amount"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="app-input"
                                required>
                        </div>

                        <div>
                            <label class="form-label">
                                Expense Account <span class="text-red-500">*</span>
                            </label>

                            <select id="expenseAccount" class="app-input" required>
                                <option value="">Select Expense Account</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">
                                Payment Account <span class="text-red-500">*</span>
                            </label>

                            <select id="paymentAccount" class="app-input" required>
                                <option value="">Select Cash/Bank</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Payee</label>

                            <input
                                id="payee"
                                type="text"
                                maxlength="150"
                                class="app-input">
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
                            <h3 class="text-sm font-semibold text-slate-800">Automatic Journal</h3>
                            <p class="text-[11px] text-slate-400">
                                This journal will be posted automatically.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-md border border-red-200 bg-red-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-red-600">
                                Debit
                            </p>

                            <p id="journalDebit" class="mt-1 text-sm font-semibold text-slate-700">
                                Expense Account
                            </p>
                        </div>

                        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">
                                Credit
                            </p>

                            <p id="journalCredit" class="mt-1 text-sm font-semibold text-slate-700">
                                Payment Account
                            </p>
                        </div>
                    </div>
                </section>

                <div
                    id="formError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeExpenseModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="saveButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Save Expense
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Expense Details Modal --}}
<div id="expenseDetailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Expense Details</h2>
                    <p class="text-sm text-slate-500">
                        View expense transaction and journal information.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeExpenseDetailsModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="expenseDetailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="closeExpenseDetailsModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Cancel Expense Modal --}}
<div id="cancelExpenseModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Cancel Expense</h2>
                    <p id="cancelExpenseDescription" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeCancelExpenseModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="cancelExpenseForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="cancelExpenseId" type="hidden">

            <div class="space-y-4 overflow-y-auto p-5">
                <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <i class="bi bi-exclamation-triangle text-amber-600"></i>

                        <div>
                            <p class="text-sm font-semibold text-amber-800">
                                Accounting Reversal
                            </p>

                            <p class="mt-1 text-xs leading-5 text-amber-700">
                                The original transaction will remain in the audit trail and a reversing journal entry will be created.
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">
                        Cancellation Reason <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="cancelReason"
                        rows="4"
                        maxlength="1000"
                        class="app-input resize-none"
                        placeholder="Enter cancellation reason..."
                        required></textarea>
                </div>

                <div
                    id="cancelError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeCancelExpenseModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back
                </button>

                <button
                    id="cancelExpenseButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    Cancel Expense
                </button>
            </div>
        </form>
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
let expenses=[];
let currentPage=1;
let lastPage=1;
let total=0;
let optionsLoaded=false;
let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('expenseTable'),
    search:document.getElementById('searchInput'),
    dateRange:document.getElementById('dateRangeFilter'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('expenseForm'),
    date:document.getElementById('expenseDate'),
    amount:document.getElementById('amount'),
    expenseAccount:document.getElementById('expenseAccount'),
    paymentAccount:document.getElementById('paymentAccount'),
    payee:document.getElementById('payee'),
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

                    loadExpenses(1);

                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    loadExpenses(1);
                }
            }
        }
    );
}

async function loadExpenses(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading expenses...',
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
            `/api/finance/expenses?${query}`
        );

        const paginator=
            response.data??{};

        expenses=
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

        renderExpenseTable();
        updateSummary();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadExpenses
        });
    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                8
            );
    }
}

function renderExpenseTable(){
    if(!expenses.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No expense records found.',
                8
            );

        return;
    }

    el.table.innerHTML=expenses.map(item=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-600">
                ${AdminUI.escapeHtml(item.expense_no)}
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${AdminUI.formatDate(item.expense_date)}
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${AdminUI.escapeHtml(item.payee??'—')}
            </td>

            <td class="px-4 py-3">
                <p class="text-xs font-medium text-slate-700">
                    ${AdminUI.escapeHtml(
                        item.expense_account?.name??'—'
                    )}
                </p>

                <p class="text-[10px] text-slate-400">
                    ${AdminUI.escapeHtml(
                        item.expense_account?.code??''
                    )}
                </p>
            </td>

            <td class="px-4 py-3">
                <p class="text-xs font-medium text-slate-700">
                    ${AdminUI.escapeHtml(
                        item.payment_account?.name??'—'
                    )}
                </p>

                <p class="text-[10px] text-slate-400">
                    ${AdminUI.escapeHtml(
                        item.payment_account?.code??''
                    )}
                </p>
            </td>

            <td class="px-4 py-3 text-right text-xs font-bold text-slate-800">
                ${money(item.amount)}
            </td>

            <td class="px-4 py-3">
                ${AdminUI.statusBadge(item.status)}
            </td>

            <td class="px-4 py-3">
                <div class="flex justify-end gap-1">
                    <button
                        type="button"
                        onclick="viewExpense(${item.id})"
                        class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-50 text-slate-500 hover:bg-slate-100"
                        title="View">
                        <i class="bi bi-eye text-xs"></i>
                    </button>

                    ${
                        canUpdate&&item.status==='posted'
                            ?`
                                <button
                                    type="button"
                                    onclick="openCancelExpenseModal(${item.id})"
                                    class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100"
                                    title="Cancel">
                                    <i class="bi bi-x-circle text-xs"></i>
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
    const posted=expenses.filter(
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
                    `${String(item.expense_date).substring(0,10)}T00:00:00`
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
        'totalExpense'
    ).textContent=
        money(totalAmount);

    document.getElementById(
        'monthExpense'
    ).textContent=
        money(monthAmount);

    document.getElementById(
        'expenseCount'
    ).textContent=
        total;
}

async function loadOptions(){
    if(optionsLoaded){
        return;
    }

    const response=await api(
        '/api/finance/expenses/options'
    );

    const data=
        response.data??{};

    el.expenseAccount.innerHTML=`
        <option value="">Select Expense Account</option>

        ${(data.expense_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    el.paymentAccount.innerHTML=`
        <option value="">Select Cash/Bank</option>

        ${(data.payment_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    optionsLoaded=true;
}

window.openExpenseModal=async function(){
    AdminUI.resetForm(
        el.form
    );

    AdminUI.clearError(
        'formError'
    );

    try{
        await loadOptions();

        AdminUI.openModal(
            'expenseModal'
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

window.closeExpenseModal=function(){
    AdminUI.closeModal(
        'expenseModal'
    );
};

window.closeExpenseDetailsModal=function(){
    AdminUI.closeModal(
        'expenseDetailsModal'
    );
};

function updateJournalPreview(){
    const debit=
        el.expenseAccount.options[
            el.expenseAccount.selectedIndex
        ]?.text?.trim()||
        'Expense Account';

    const credit=
        el.paymentAccount.options[
            el.paymentAccount.selectedIndex
        ]?.text?.trim()||
        'Payment Account';

    document.getElementById(
        'journalDebit'
    ).textContent=
        el.expenseAccount.value
            ?debit
            :'Expense Account';

    document.getElementById(
        'journalCredit'
    ).textContent=
        el.paymentAccount.value
            ?credit
            :'Payment Account';
}

el.expenseAccount.addEventListener(
    'change',
    updateJournalPreview
);

el.paymentAccount.addEventListener(
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
            expense_account_id:
                Number(
                    el.expenseAccount.value
                ),

            payment_account_id:
                Number(
                    el.paymentAccount.value
                ),

            amount:
                Number(
                    el.amount.value
                ),

            expense_date:
                el.date.value,

            payee:
                el.payee.value.trim()||
                null,

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
                '/api/finance/expenses',
                {
                    method:'POST',
                    body:JSON.stringify(data)
                }
            );

            closeExpenseModal();

            Toast.success(
                response.message||
                'Expense recorded successfully.'
            );

            await loadExpenses(1);
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

window.viewExpense=async function(id){
    AdminUI.openModal(
        'expenseDetailsModal'
    );

    const body=
        document.getElementById(
            'expenseDetailsBody'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading details...'
        );

    try{
        const response=await api(
            `/api/finance/expenses/${id}`
        );

        const item=
            response.data??{};

        const entries=
            item.finance_transaction
                ?.entries??[];

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                ${detail(
                    'Expense No',
                    item.expense_no
                )}

                ${detail(
                    'Date',
                    AdminUI.formatDate(
                        item.expense_date
                    )
                )}

                ${detail(
                    'Payee',
                    item.payee??'—'
                )}

                ${detail(
                    'Amount',
                    money(
                        item.amount
                    )
                )}

                ${detail(
                    'Expense Account',
                    `${item.expense_account?.code??''} ${item.expense_account?.name??''}`.trim()||'—'
                )}

                ${detail(
                    'Payment Account',
                    `${item.payment_account?.code??''} ${item.payment_account?.name??''}`.trim()||'—'
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
                        <i class="bi bi-journal-text text-xs"></i>
                    </div>

                    <p class="text-xs font-semibold text-slate-700">
                        Journal Entry
                    </p>
                </div>

                <div class="overflow-x-auto rounded-md border border-slate-200">
                    <table class="w-full min-w-[500px] text-xs">
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

                            <p class="text-xs leading-5 text-slate-600">
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
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

window.openCancelExpenseModal=function(id){
    const item=expenses.find(
        expense=>Number(expense.id)===Number(id)
    );

    if(!item){
        Toast.error(
            'Expense not found.'
        );

        return;
    }

    document.getElementById(
        'cancelExpenseId'
    ).value=id;

    document.getElementById(
        'cancelReason'
    ).value='';

    document.getElementById(
        'cancelExpenseDescription'
    ).textContent=
        `${item.expense_no} • ${money(item.amount)}`;

    AdminUI.clearError(
        'cancelError'
    );

    AdminUI.openModal(
        'cancelExpenseModal'
    );
};

window.closeCancelExpenseModal=function(){
    AdminUI.closeModal(
        'cancelExpenseModal'
    );
};

document.getElementById(
    'cancelExpenseForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'cancelError'
        );

        const id=Number(
            document.getElementById(
                'cancelExpenseId'
            ).value
        );

        const reason=
            document
                .getElementById(
                    'cancelReason'
                )
                .value
                .trim();

        if(!reason){
            AdminUI.showError(
                'cancelError',
                'Cancellation reason is required.'
            );

            return;
        }

        const button=
            document.getElementById(
                'cancelExpenseButton'
            );

        AdminUI.setLoading(
            button,
            'Cancelling...'
        );

        try{
            const response=await api(
                `/api/finance/expenses/${id}/cancel`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        reason
                    })
                }
            );

            closeCancelExpenseModal();

            Toast.success(
                response.message||
                'Expense cancelled and reversed successfully.'
            );

            await loadExpenses(
                currentPage
            );
        }catch(error){
            AdminUI.showError(
                'cancelError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

function detail(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 break-words text-sm font-medium text-slate-700">
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

    loadExpenses(1);
};

async function initExpensePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initExpensePage,
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
            ()=>loadExpenses(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadExpenses(1)
    );

    await loadExpenses();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initExpensePage
    );
}else{
    initExpensePage();
}
</script>
@endpush