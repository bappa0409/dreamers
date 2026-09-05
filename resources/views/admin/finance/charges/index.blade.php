@extends('layouts.admin')

@section('title','Member Charges')
@section('page_title','Member Charges')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-receipt"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Member Charges</h1>
                <p class="text-xs text-slate-500">Create, collect and manage member charges and receivables.</p>
            </div>
        </div>
        @if(auth()->user()->hasPermission('Finance.create'))
        <button type="button" onclick="openChargeModal()" class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Charge
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Charged</p>
            <p id="summaryCharged" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Collected</p>
            <p id="summaryPaid" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Outstanding</p>
            <p id="summaryOutstanding" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Unpaid / Partial</p>
            <p id="summaryUnpaid" class="mt-1 text-xl font-bold text-slate-800">0</p>
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
                    <p class="text-sm font-semibold text-slate-700">Search Charges</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by member, charge no, type or reference.</p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[minmax(220px,280px)_240px_130px_auto] lg:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search..." class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3 text-xs outline-none focus:border-indigo-400 lg:rounded-r-none">
                </div>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="dateRangeFilter" type="text" class="js-date-range h-9 w-full border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:border-l-0" placeholder="Select date range" autocomplete="off">
                </div>

                <select id="statusFilter" class="h-9 border border-slate-300 px-3 text-sm outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="rejected">Rejected</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="waived">Waived</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button type="button" onclick="clearFilters()" class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Charge</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Charge Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Due Date</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Outstanding</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="chargeTable">
                    <tr>
                        <td colspan="10" class="px-4 py-10 text-xs text-center text-slate-400">Loading charges...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Add Member Charge Modal --}}
<div id="chargeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-receipt"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Add Member Charge</h2>
                    <p class="text-xs text-slate-500">Create receivable and post accounting entry automatically.</p>
                </div>
            </div>
            <button type="button" onclick="closeChargeModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="chargeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Charge Information</h3>
                            <p class="text-[11px] text-slate-400">Enter member charge details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Member <span class="text-red-500">*</span></label>
                            <select id="memberId" class="app-input" required>
                                <option value="">Select Member</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Charge Type <span class="text-red-500">*</span></label>
                            <select id="chargeType" class="app-input" required>
                                <option value="">Select Type</option>
                                <option value="registration_fee">Registration Fee</option>
                                <option value="late_fee">Late Fee</option>
                                <option value="penalty">Penalty</option>
                                <option value="service_charge">Service Charge</option>
                                <option value="special_contribution">Special Contribution</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Income Account <span class="text-red-500">*</span></label>
                            <select id="incomeAccount" class="app-input" required>
                                <option value="">Select Income Account</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input id="amount" type="number" min="0.01" step="0.01" class="app-input" required>
                        </div>

                        <div>
                            <label class="form-label">Charge Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="chargeDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Due Date</label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="dueDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Reference</label>
                            <input id="reference" type="text" maxlength="150" class="app-input">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Description</label>
                        <textarea id="description" rows="3" class="app-input resize-none"></textarea>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-slate-50/50 p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-journal-check"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Automatic Journal</h3>
                            <p class="text-[11px] text-slate-400">Receivable journal will be posted automatically.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Debit</p>
                            <p class="mt-1 text-base font-semibold text-slate-700">Accounts Receivable</p>
                        </div>

                        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">Credit</p>
                            <p id="journalCredit" class="mt-1 text-base font-semibold text-slate-700">Income Account</p>
                        </div>
                    </div>
                </section>

                <div id="chargeError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeChargeModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button id="saveChargeButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    Create Charge
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Payment Modal --}}
<div id="paymentModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Receive Payment</h2>
                    <p id="paymentDescription" class="text-xs text-slate-500"></p>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="paymentForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="paymentChargeId" type="hidden">

            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input id="paymentAmount" type="number" min="0.01" step="0.01" class="app-input" required>
                        </div>

                        <div>
                            <label class="form-label">Payment Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="paymentDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off" required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Receive Account <span class="text-red-500">*</span></label>
                            <select id="receiveAccount" class="app-input" required>
                                <option value="">Select Cash/Bank</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Payment Method</label>
                            <select id="paymentMethod" class="app-input">
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile_banking">Mobile Banking</option>
                                <option value="online">Online</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="form-label">Reference</label>
                            <input id="paymentReference" type="text" maxlength="150" class="app-input">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Description</label>
                        <textarea id="paymentNote" rows="3" class="app-input resize-none"></textarea>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-slate-50/50 p-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Debit</p>
                            <p class="mt-1 text-base font-semibold text-slate-700">Cash / Bank</p>
                        </div>
                        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-600">Credit</p>
                            <p class="mt-1 text-base font-semibold text-slate-700">Accounts Receivable</p>
                        </div>
                    </div>
                </section>

                <div id="paymentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closePaymentModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button id="paymentButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    Post Payment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Charge Details</h2>
                    <p class="text-xs text-slate-500">View charge, accounting and payment information.</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailsModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button type="button" onclick="closeDetailsModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Reason Modal --}}
<div id="reasonModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div id="reasonIcon" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
                <div>
                    <h2 id="reasonTitle" class="text-sm font-semibold text-slate-800">Update Charge</h2>
                    <p id="reasonDescription" class="text-xs text-slate-500"></p>
                </div>
            </div>
            <button type="button" onclick="closeReasonModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="reasonForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="reasonChargeId" type="hidden">
            <input id="reasonAction" type="hidden">

            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Reason <span class="text-red-500">*</span></label>
                    <textarea id="reasonText" rows="4" maxlength="1000" class="app-input resize-none" required></textarea>
                </div>

                <div id="reasonError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeReasonModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Back
                </button>
                <button id="reasonButton" type="submit" class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                    Continue
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
let charges=[];
let detailPayments=[];
let detailsChargeId=null;
let currentPage=1;
let lastPage=1;
let total=0;
let options=null;
let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

const canCreate=@json(auth()->user()->hasPermission('Finance.create'));
const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('chargeTable'),
    search:document.getElementById('searchInput'),
    dateRange:document.getElementById('dateRangeFilter'),
    status:document.getElementById('statusFilter'),
    chargeForm:document.getElementById('chargeForm'),
    member:document.getElementById('memberId'),
    type:document.getElementById('chargeType'),
    incomeAccount:document.getElementById('incomeAccount'),
    amount:document.getElementById('amount'),
    chargeDate:document.getElementById('chargeDate'),
    dueDate:document.getElementById('dueDate'),
    reference:document.getElementById('reference'),
    description:document.getElementById('description'),
    saveChargeButton:document.getElementById('saveChargeButton'),
    receiveAccount:document.getElementById('receiveAccount')
};

function localDate(date=new Date()){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');
    return `${year}-${month}-${day}`;
}

function setPickerDate(element,value){
    if(!element)return;
    const date=value?String(value).substring(0,10):'';
    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(date,false)
            :element._flatpickr.clear();
    }
}

function initDateRangePicker(){
    if(!el.dateRange||typeof window.flatpickr==='undefined'){
        return;
    }

    if(el.dateRange._flatpickr){
        el.dateRange._flatpickr.destroy();
    }

    dateRangePicker=flatpickr(el.dateRange,{
        mode:'range',
        dateFormat:'Y-m-d',
        allowInput:false,
        disableMobile:true,
        onChange(selectedDates,dateStr,instance){
            if(selectedDates.length===2){
                selectedFrom=instance.formatDate(selectedDates[0],'Y-m-d');
                selectedTo=instance.formatDate(selectedDates[1],'Y-m-d');
                loadCharges(1);
                return;
            }

            if(selectedDates.length===0){
                selectedFrom='';
                selectedTo='';
                loadCharges(1);
            }
        }
    });
}

async function loadCharges(page=1){
    currentPage=page;
    el.table.innerHTML=AdminUI.loadingState('Loading charges...',10);

    const query=AdminUI.query({
        search:el.search.value.trim(),
        from:selectedFrom,
        to:selectedTo,
        status:el.status.value,
        page
    });

    try{
        const [listResponse,summaryResponse]=await Promise.all([
            api(`/api/finance/charges?${query}`),
            api('/api/finance/charges/summary')
        ]);

        const paginator=listResponse.data??{};

        charges=paginator.data??[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??0);

        renderCharges();
        renderSummary(summaryResponse.data??{});

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadCharges
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),10);
    }
}

function renderSummary(data){
    document.getElementById('summaryCharged').textContent=
        money(data.total_charged);

    document.getElementById('summaryPaid').textContent=
        money(data.total_paid);

    document.getElementById('summaryOutstanding').textContent=
        money(data.outstanding);

    document.getElementById('summaryUnpaid').textContent=
        Number(data.unpaid_count??0);
}

function renderCharges(){
    if(!charges.length){
        el.table.innerHTML=AdminUI.emptyState('No member charges found.',10);
        return;
    }

    el.table.innerHTML=charges.map(item=>{
        const outstanding=Math.max(
            Number(item.amount??0)-Number(item.paid_amount??0),
            0
        );

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="px-4 py-3">
                    <p class="font-mono text-sm font-semibold text-indigo-600">
                        ${AdminUI.escapeHtml(item.charge_no)}
                    </p>
                    <p class="mt-0.5 text-[10px] text-slate-400">
                        ${AdminUI.escapeHtml(item.reference??'')}
                    </p>
                </td>

                <td class="px-4 py-3">
                    <p class="text-sm font-semibold text-slate-700">
                        ${AdminUI.escapeHtml(item.member?.user?.name??'—')}
                    </p>
                    <p class="text-[10px] text-slate-400">
                        ${AdminUI.escapeHtml(item.member?.member_code??'')}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${AdminUI.escapeHtml(titleCase(item.charge_type))}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${AdminUI.formatDate(item.charge_date)}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${item.due_date?AdminUI.formatDate(item.due_date):'—'}
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold text-slate-800">
                    ${money(item.amount)}
                </td>

                <td class="px-4 py-3 text-right text-sm text-slate-600">
                    ${money(item.paid_amount)}
                </td>

                <td class="px-4 py-3 text-right text-sm font-semibold ${outstanding>0?'text-red-600':'text-slate-600'}">
                    ${money(outstanding)}
                </td>

                <td class="px-4 py-3">
                    ${chargeBadge(item.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <button type="button" onclick="viewCharge(${item.id})" class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-50 text-slate-500 hover:bg-slate-100" title="View">
                            <i class="bi bi-eye text-sm"></i>
                        </button>

                        ${canCreate&&['unpaid','partial'].includes(item.status)?`
                            <button type="button" onclick="openPaymentModal(${item.id})" class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 hover:bg-emerald-100" title="Receive Payment">
                                <i class="bi bi-cash-coin text-sm"></i>
                            </button>
                        `:''}

                        ${canUpdate&&item.status==='unpaid'?`
                            <button type="button" onclick="openReasonModal(${item.id},'waive')" class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-600 hover:bg-amber-100" title="Waive">
                                <i class="bi bi-percent text-sm"></i>
                            </button>
                            <button type="button" onclick="openReasonModal(${item.id},'cancel')" class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100" title="Cancel">
                                <i class="bi bi-x-circle text-sm"></i>
                            </button>
                        `:''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

async function loadOptions(){
    if(options)return options;

    const response=await api('/api/finance/charges/options');
    options=response.data??{};

    el.member.innerHTML=`
        <option value="">Select Member</option>
        ${(options.members??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.member_code)} - ${AdminUI.escapeHtml(item.user?.name??'')}
            </option>
        `).join('')}
    `;

    el.incomeAccount.innerHTML=`
        <option value="">Select Income Account</option>
        ${(options.income_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)} - ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    el.receiveAccount.innerHTML=`
        <option value="">Select Cash/Bank</option>
        ${(options.receive_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)} - ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    return options;
}

window.openChargeModal=async function(){
    AdminUI.resetForm(el.chargeForm);
    AdminUI.clearError('chargeError');

    try{
        await loadOptions();

        AdminUI.openModal('chargeModal');

        if(typeof window.initDatePickers==='function'){
            window.initDatePickers();
        }

        const today=localDate();

        setPickerDate(el.chargeDate,today);
        setPickerDate(el.dueDate,today);

        updateJournalPreview();
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

window.closeChargeModal=function(){
    AdminUI.closeModal('chargeModal');
};

function updateJournalPreview(){
    document.getElementById('journalCredit').textContent=
        el.incomeAccount.value
            ?el.incomeAccount.options[el.incomeAccount.selectedIndex]?.text?.trim()
            :'Income Account';
}

el.incomeAccount.addEventListener('change',updateJournalPreview);

el.chargeForm.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('chargeError');

    const data={
        member_id:Number(el.member.value),
        income_account_id:Number(el.incomeAccount.value),
        amount:Number(el.amount.value),
        charge_date:el.chargeDate.value,
        due_date:el.dueDate.value||null,
        charge_type:el.type.value,
        reference:el.reference.value.trim()||null,
        description:el.description.value.trim()||null
    };

    AdminUI.setLoading(el.saveChargeButton,'Creating...');

    try{
        const response=await api('/api/finance/charges',{
            method:'POST',
            body:JSON.stringify(data)
        });

        closeChargeModal();

        Toast.success(
            response.message||
            'Member charge created successfully.'
        );

        await loadCharges(1);
    }catch(error){
        AdminUI.showError('chargeError',AdminUI.extractError(error));
    }finally{
        AdminUI.resetLoading(el.saveChargeButton);
    }
});

window.openPaymentModal=async function(id){
    const item=charges.find(
        charge=>Number(charge.id)===Number(id)
    );

    if(!item){
        Toast.error('Charge not found.');
        return;
    }

    try{
        await loadOptions();

        const outstanding=Math.max(
            Number(item.amount??0)-Number(item.paid_amount??0),
            0
        );

        document.getElementById('paymentChargeId').value=id;
        document.getElementById('paymentAmount').value=outstanding.toFixed(2);
        document.getElementById('paymentAmount').max=outstanding.toFixed(2);
        document.getElementById('paymentMethod').value='cash';
        document.getElementById('paymentReference').value='';
        document.getElementById('paymentNote').value='';
        document.getElementById('paymentDescription').textContent=
            `${item.charge_no} • Outstanding ${money(outstanding)}`;

        AdminUI.clearError('paymentError');
        AdminUI.openModal('paymentModal');

        if(typeof window.initDatePickers==='function'){
            window.initDatePickers();
        }

        setPickerDate(
            document.getElementById('paymentDate'),
            localDate()
        );
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

window.closePaymentModal=function(){
    AdminUI.closeModal('paymentModal');
};

document.getElementById('paymentForm').addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('paymentError');

    const id=Number(
        document.getElementById('paymentChargeId').value
    );

    const button=document.getElementById('paymentButton');

    const data={
        amount:Number(document.getElementById('paymentAmount').value),
        receive_account_id:Number(el.receiveAccount.value),
        payment_date:document.getElementById('paymentDate').value,
        payment_method:document.getElementById('paymentMethod').value,
        reference:document.getElementById('paymentReference').value.trim()||null,
        description:document.getElementById('paymentNote').value.trim()||null
    };

    AdminUI.setLoading(button,'Posting...');

    try{
        const response=await api(
            `/api/finance/charges/${id}/pay`,
            {
                method:'POST',
                body:JSON.stringify(data)
            }
        );

        closePaymentModal();

        Toast.success(
            response.message||
            'Charge payment posted successfully.'
        );

        await loadCharges(currentPage);
    }catch(error){
        AdminUI.showError('paymentError',AdminUI.extractError(error));
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.closeDetailsModal=function(){
    AdminUI.closeModal('detailsModal');
};

window.viewCharge=async function(id){
    AdminUI.openModal('detailsModal');

    const body=document.getElementById('detailsBody');
    body.innerHTML=AdminUI.loadingState('Loading details...');

    try{
        const response=await api(`/api/finance/charges/${id}`);
        const item=response.data??{};
        const payments=item.payments??[];
        const entries=item.finance_transaction?.entries??[];

        detailsChargeId=Number(id);
        detailPayments=payments;

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-4 text-base sm:grid-cols-2 md:grid-cols-4">
                ${detail('Charge No',item.charge_no)}
                ${detail('Member',item.member?.user?.name??'—')}
                ${detail('Member Code',item.member?.member_code??'—')}
                ${detail('Type',titleCase(item.charge_type))}
                ${detail('Amount',money(item.amount))}
                ${detail('Paid',money(item.paid_amount))}
                ${detail('Outstanding',money(Math.max(Number(item.amount??0)-Number(item.paid_amount??0),0)))}
                ${detail('Status',titleCase(item.status))}
                ${detail('Charge Date',AdminUI.formatDate(item.charge_date))}
                ${detail('Due Date',item.due_date?AdminUI.formatDate(item.due_date):'—')}
                ${detail('Income Account',`${item.income_account?.code??''} ${item.income_account?.name??''}`.trim()||'—')}
                ${detail('Reference',item.reference??'—')}
            </div>

            <div class="mt-5">
                <p class="mb-2 text-xs font-semibold text-slate-700">Charge Journal</p>
                ${journalTable(entries)}
            </div>

            <div class="mt-5">
                <p class="mb-2 text-xs font-semibold text-slate-700">Payment History</p>

                <div class="overflow-x-auto rounded-md border border-slate-200">
                    <table class="w-full min-w-[620px] text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Payment No</th>
                                <th class="px-3 py-2 text-left">Date</th>
                                <th class="px-3 py-2 text-left">Account</th>
                                <th class="px-3 py-2 text-left">Reference</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-right">Amount</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${payments.length?payments.map(payment=>`
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2 font-mono">
                                        ${AdminUI.escapeHtml(payment.payment_no)}
                                    </td>
                                    <td class="px-3 py-2">
                                        ${AdminUI.formatDate(payment.payment_date)}
                                    </td>
                                    <td class="px-3 py-2">
                                        ${AdminUI.escapeHtml(payment.receive_account?.name??'—')}
                                    </td>
                                    <td class="px-3 py-2">
                                        ${AdminUI.escapeHtml(payment.reference??'—')}
                                    </td>
                                    <td class="px-3 py-2">
                                        ${AdminUI.escapeHtml(titleCase(payment.status))}
                                    </td>
                                    <td class="px-3 py-2 text-right font-semibold">
                                        ${money(payment.amount)}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        ${canUpdate&&payment.status==='posted'?`
                                            <button
                                                type="button"
                                                onclick="openPaymentCancelModal(${payment.id})"
                                                class="rounded border border-red-200 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50">
                                                Cancel
                                            </button>
                                        `:'—'}
                                    </td>
                                </tr>
                            `).join(''):`
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-slate-400">
                                        No payments found.
                                    </td>
                                </tr>
                            `}
                        </tbody>
                    </table>
                </div>
            </div>

            ${item.description?`
                <div class="mt-4 rounded-md bg-slate-50 p-3 text-sm leading-5 text-slate-600">
                    ${AdminUI.escapeHtml(item.description)}
                </div>
            `:''}
        `;
    }catch(error){
        body.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        `;
    }
};

window.openPaymentCancelModal=function(paymentId){
    const payment=detailPayments.find(
        item=>Number(item.id)===Number(paymentId)
    );

    if(!payment){
        Toast.error('Payment not found.');
        return;
    }

    document.getElementById('reasonChargeId').value=payment.id;
    document.getElementById('reasonAction').value='cancel-payment';
    document.getElementById('reasonText').value='';
    document.getElementById('reasonTitle').textContent='Cancel Payment';
    document.getElementById('reasonDescription').textContent=
        `${payment.payment_no} • ${money(payment.amount)}`;

    const icon=document.getElementById('reasonIcon');
    icon.className='flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600';
    icon.innerHTML='<i class="bi bi-x-circle"></i>';

    const button=document.getElementById('reasonButton');
    button.textContent='Cancel Payment';
    button.className='cursor-pointer rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60';

    AdminUI.clearError('reasonError');
    AdminUI.openModal('reasonModal');
};

window.openReasonModal=function(id,action){
    const item=charges.find(
        charge=>Number(charge.id)===Number(id)
    );

    if(!item){
        Toast.error('Charge not found.');
        return;
    }

    const waive=action==='waive';

    document.getElementById('reasonChargeId').value=id;
    document.getElementById('reasonAction').value=action;
    document.getElementById('reasonText').value='';
    document.getElementById('reasonTitle').textContent=
        waive?'Waive Charge':'Cancel Charge';

    document.getElementById('reasonDescription').textContent=
        `${item.charge_no} • ${money(item.amount)}`;

    const icon=document.getElementById('reasonIcon');

    icon.className=waive
        ?'flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600'
        :'flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600';

    icon.innerHTML=waive
        ?'<i class="bi bi-percent"></i>'
        :'<i class="bi bi-x-circle"></i>';

    const button=document.getElementById('reasonButton');

    button.textContent=
        waive?'Waive Charge':'Cancel Charge';

    button.className=waive
        ?'cursor-pointer rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-60'
        :'cursor-pointer rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60';

    AdminUI.clearError('reasonError');
    AdminUI.openModal('reasonModal');
};

window.closeReasonModal=function(){
    AdminUI.closeModal('reasonModal');
};

document.getElementById('reasonForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const id=Number(
        document.getElementById('reasonChargeId').value
    );

    const action=
        document.getElementById('reasonAction').value;

    const reason=
        document.getElementById('reasonText').value.trim();

    const button=
        document.getElementById('reasonButton');

    AdminUI.clearError('reasonError');

    if(!reason){
        AdminUI.showError(
            'reasonError',
            'Reason is required.'
        );
        return;
    }

    AdminUI.setLoading(
        button,
        action==='waive'
            ?'Waiving...'
            :(action==='cancel-payment'
                ?'Cancelling Payment...'
                :'Cancelling...')
    );

    try{
        const endpoint=action==='cancel-payment'
            ?`/api/finance/charge-payments/${id}/cancel`
            :`/api/finance/charges/${id}/${action}`;

        const response=await api(
            endpoint,
            {
                method:'POST',
                body:JSON.stringify({reason})
            }
        );

        closeReasonModal();

        Toast.success(
            response.message||
            (
                action==='waive'
                    ?'Charge waived successfully.'
                    :(action==='cancel-payment'
                        ?'Charge payment cancelled successfully.'
                        :'Charge cancelled successfully.')
            )
        );

        await loadCharges(currentPage);

        if(action==='cancel-payment'&&detailsChargeId){
            await viewCharge(detailsChargeId);
        }
    }catch(error){
        AdminUI.showError(
            'reasonError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

function journalTable(entries){
    return`
        <div class="overflow-x-auto rounded-md border border-slate-200">
            <table class="w-full min-w-[500px] text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Account</th>
                        <th class="px-3 py-2 text-right">Debit</th>
                        <th class="px-3 py-2 text-right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    ${entries.length?entries.map(entry=>`
                        <tr class="border-t border-slate-100">
                            <td class="px-3 py-2">
                                ${AdminUI.escapeHtml(entry.account?.code??'')}
                                -
                                ${AdminUI.escapeHtml(entry.account?.name??'')}
                            </td>
                            <td class="px-3 py-2 text-right">
                                ${Number(entry.debit??0)>0?money(entry.debit):'—'}
                            </td>
                            <td class="px-3 py-2 text-right">
                                ${Number(entry.credit??0)>0?money(entry.credit):'—'}
                            </td>
                        </tr>
                    `).join(''):`
                        <tr>
                            <td colspan="3" class="px-3 py-6 text-center text-slate-400">
                                No journal entries found.
                            </td>
                        </tr>
                    `}
                </tbody>
            </table>
        </div>
    `;
}

function detail(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[10px] uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>
            <p class="mt-1 break-words font-medium text-slate-700">
                ${AdminUI.escapeHtml(value??'—')}
            </p>
        </div>
    `;
}

function chargeBadge(status){
    const map={
        pending_approval:'bg-amber-50 text-amber-700',
        rejected:'bg-red-50 text-red-700',
        unpaid:'bg-red-50 text-red-700',
        partial:'bg-amber-50 text-amber-700',
        paid:'bg-emerald-50 text-emerald-700',
        waived:'bg-sky-50 text-sky-700',
        cancelled:'bg-slate-100 text-slate-500'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${map[status]??map.unpaid}">
            ${AdminUI.escapeHtml(titleCase(status))}
        </span>
    `;
}

function titleCase(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function money(value){
    return `${@json(setting('currency_symbol','৳'))}${Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    })}`;
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

    loadCharges(1);
};

async function initChargePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initChargePage,50);
        return;
    }

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    initDateRangePicker();

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadCharges(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadCharges(1)
    );

    await loadCharges();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initChargePage
    );
}else{
    initChargePage();
}
</script>
@endpush