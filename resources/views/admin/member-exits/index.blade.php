@extends('layouts.admin')

@section('title','Member Exit')
@section('page_title','Member Exit')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div
        class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-rose-50 text-rose-600">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Member Exit</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage resignation, termination, death and permanent
                    membership closure.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('MemberExit.create'))
        <button type="button" onclick="openCreateModal()"
            class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Start Exit Process
        </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @php
        $stats=[
        ['total','Total Cases','bi-collection','border-slate-200 bg-white','text-slate-800','bg-slate-100
        text-slate-500'],
        ['pending','In Process','bi-hourglass-split','border-amber-200 bg-amber-50/40','text-amber-700','bg-amber-100
        text-amber-600'],
        ['approved','Approved','bi-patch-check','border-indigo-200 bg-indigo-50/40','text-indigo-700','bg-indigo-100
        text-indigo-600'],
        ['closed','Closed','bi-check2-circle','border-emerald-200 bg-emerald-50/40','text-emerald-700','bg-emerald-100
        text-emerald-600'],
        ['death','Death Cases','bi-person-x','border-rose-200 bg-rose-50/40','text-rose-700','bg-rose-100
        text-rose-600'],
        ];
        @endphp

        @foreach($stats as [$key,$label,$icon,$box,$text,$iconBox])
        <div class="rounded-md border p-4 {{ $box }}">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class=" text-xs 2xl:text-sm text-slate-500">{{ $label }}</p>
                    <p id="stat-{{ $key }}" class="mt-2 text-xl font-bold {{ $text }}">0</p>
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $iconBox }}">
                    <i class="bi {{ $icon }} text-base"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            {{-- Header --}}
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Exit Records
                    </p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by exit number, member name or member code.
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center gap-2 lg:w-auto">

                {{-- Search --}}
                <div class="relative min-w-0 flex-1 lg:w-[280px]">
                    <i
                        class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 2xl:text-sm"></i>

                    <input id="searchInput" type="text" placeholder="Search exit records..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
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

                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Status
                    </label>

                    <select id="statusFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All Status</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="liabilities_pending">Liabilities Pending</option>
                        <option value="ready_for_approval">Ready for Approval</option>
                        <option value="approved">Approved</option>
                        <option value="settled">Settled</option>
                        <option value="closed">Closed</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>


                {{-- Type --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Type
                    </label>

                    <select id="typeFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All Types</option>
                        <option value="resignation">Resignation</option>
                        <option value="termination">Termination</option>
                        <option value="death">Death</option>
                        <option value="permanent_removal">Permanent Removal</option>
                        <option value="other">Other</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- Exit Records --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Exit</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Liabilities
                        </th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Share Refund
                        </th>
                        <th class="px-4 py-3 text-center  text-xs 2xl:text-sm font-semibold text-slate-600">Blockers
                        </th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>
                <tbody id="exitTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">Loading exit records...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="exitMobileGrid" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-xs 2xl:text-sm text-slate-400">Loading exit records...</div>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Create Modal --}}
<div id="createModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-rose-50 text-rose-600">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Start Member Exit</h3>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Create a resignation, termination, death or removal
                        process.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('createModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="createForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

                <div id="createError"
                    class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div class="rounded-md border border-slate-200 p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Member & Exit Type</h4>
                            <p class="text-[11px] text-slate-400">Select the member and reason category.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Member <span class="text-red-500">*</span></label>
                            <select id="memberId" class="app-input w-full"
                                data-validation-required-message="Please select a member.">
                                <option value="">Select Member</option>
                            </select>
                            <p data-field-error="memberId" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Exit Type <span class="text-red-500">*</span></label>
                            <select id="exitType" class="app-input w-full"
                                data-validation-required-message="Exit type is required.">
                                <option value="">Select Exit Type</option>
                                <option value="resignation">Resignation</option>
                                <option value="termination">Termination</option>
                                <option value="death">Death</option>
                                <option value="permanent_removal">Permanent Removal</option>
                                <option value="other">Other</option>
                            </select>
                            <p data-field-error="exitType" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Exit Information</h4>
                            <p class="text-[11px] text-slate-400">Reason and preferred effective date.</p>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Reason <span class="text-red-500">*</span></label>
                        <textarea id="reason" rows="4" maxlength="5000" class="app-input w-full resize-none"
                            placeholder="Describe the reason for membership cancel..."
                            data-validation-required-message="Reason is required."></textarea>
                        <p data-field-error="reason" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Proposed Cancel Date</label>
                        <div class="relative">
                            <i
                                class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs 2xl:text-sm text-slate-400"></i>
                            <input id="proposedExitDate" type="text" class="app-input js-date-picker w-full !pl-9"
                                placeholder="Select date" autocomplete="off">
                        </div>
                        <p data-field-error="proposedExitDate" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                    </div>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-3">
                    <div class="flex gap-2">
                        <i class="bi bi-info-circle mt-0.5 text-amber-600"></i>
                        <p class="text-[11px] leading-4 text-amber-800">Starting an exit process does not immediately
                            deactivate the member. Financial and operational blockers must be resolved before final
                            settlement and closure.</p>
                    </div>
                </div>
            </div>

            <div
                class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="AdminUI.closeModal('createModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50"><i
                        class="bi bi-x-lg mr-1"></i>Cancel</button>
                <button id="createButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"><i
                        class="bi bi-play-fill mr-1"></i>Start Process</button>
            </div>
        </form>
    </div>
</div>

{{-- Manage Modal --}}
<div id="manageModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h3 id="manageTitle" class="text-sm font-semibold text-slate-800">Member Exit</h3>
                    <p id="manageSubtitle" class=" text-xs 2xl:text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('manageModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
            <div id="manageError"
                class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

            <div id="summaryCards" class="grid grid-cols-2 gap-3 lg:grid-cols-5"></div>

            <div class="grid gap-4 xl:grid-cols-[1.2fr_.8fr]">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">Financial & Operational Assessment</h4>
                            <p class="text-[11px] text-slate-400">Blocking items must be resolved before approval.</p>
                        </div>
                        @if(auth()->user()->hasPermission('MemberExit.review'))
                        <button type="button" onclick="refreshAssessment()"
                            class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                            <i class="bi bi-arrow-clockwise"></i>
                            Refresh
                        </button>
                        @endif
                    </div>

                    <div id="assessmentItems" class="space-y-2"></div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <h4 class="text-sm font-semibold text-slate-800">Case Information</h4>
                    <div id="caseDetails" class="mt-3 grid grid-cols-2 gap-3"></div>
                </section>
            </div>

            <section id="nomineeAllocationSection"
                class="hidden rounded-md border border-violet-200 bg-violet-50/30 p-4">
                <div class="mb-3">
                    <h4 class="text-base font-semibold text-violet-800">Nominee Allocation</h4>
                    <p class="text-[11px] text-violet-600">Death settlement distribution snapshot.</p>
                </div>
                <div id="nomineeAllocations" class="space-y-2"></div>
            </section>

            <section id="reviewSection" class="rounded-md border border-slate-200 bg-white p-4">
                <label class="form-label">Review Note</label>
                <textarea id="reviewNote" rows="3" maxlength="5000" class="app-input w-full resize-none"
                    placeholder="Optional review note..."></textarea>
                <p data-field-error="reviewNote" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
            </section>

            <section id="settlementSection" class="hidden rounded-md border border-emerald-200 bg-emerald-50/30 p-4">
                <div class="mb-4">
                    <h4 class="text-base font-semibold text-emerald-800">Settlement</h4>
                    <p class="text-[11px] text-emerald-600">Complete share capital refund and membership closure
                        preparation.</p>
                </div>

                <form id="settlementForm" class="grid gap-4 md:grid-cols-2" novalidate data-js-validation="1">
                    <div>
                        <label class="form-label">Payout Account <span id="payoutRequiredMarker"
                                class="hidden text-red-500">*</span></label>
                        <select id="payoutAccountId" class="app-input w-full"
                            data-validation-required-message="Please select a payout account.">
                            <option value="">Select Cash / Bank Account</option>
                        </select>
                        <p data-field-error="payoutAccountId" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Settlement Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i
                                class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs 2xl:text-sm text-slate-400"></i>
                            <input id="settlementDate" type="text" class="app-input js-date-picker w-full !pl-9"
                                placeholder="Select date" autocomplete="off"
                                data-validation-required-message="Settlement date is required.">
                        </div>
                        <p data-field-error="settlementDate" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                    </div>
                </form>
            </section>
        </div>

        <div id="manageActions"
            class="flex shrink-0 flex-wrap justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4"></div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-lg rounded-md bg-white">

        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Reject Exit Request</h3>
                <p class=" text-xs 2xl:text-sm text-slate-500">Provide a reason for rejection.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('rejectModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="rejectForm" novalidate data-js-validation="1">
            <div class="p-5">
                <div id="rejectError"
                    class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700">
                </div>

                <label class="form-label">Rejection Reason <span class="text-red-500">*</span></label>
                <textarea id="rejectionReason" rows="4" maxlength="5000" class="app-input w-full resize-none"
                    placeholder="Enter rejection reason..."
                    data-validation-required-message="Rejection reason is required."></textarea>
                <p data-field-error="rejectionReason" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('rejectModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50"><i
                        class="bi bi-x-lg mr-1"></i>Cancel</button>
                <button id="rejectButton" type="submit"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60"><i
                        class="bi bi-x-circle mr-1"></i>Reject Request</button>
            </div>
        </form>
    </div>
</div>

<style>
    .form-label {
        display: block;
        margin-bottom: .4rem;
        font-size: .8rem;
        font-weight: 600;
        color: rgb(51 65 85)
    }
</style>
@endsection

@push('scripts')
<script>
    const API='/api/member-exits';
const permissions={
    create:@json(auth()->user()->hasPermission('MemberExit.create')),
    review:@json(auth()->user()->hasPermission('MemberExit.review')),
    approve:@json(auth()->user()->hasPermission('MemberExit.approve')),
    settle:@json(auth()->user()->hasPermission('MemberExit.settle'))
};

let records=[];
let options={members:[],accounts:[]};
let currentExit=null;
let currentPage=1;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const currency=@json(setting('currency_symbol','৳'));
const money=value=>currency+Number(value||0).toLocaleString('en-BD',{minimumFractionDigits:2,maximumFractionDigits:2});
const formatDate=value=>value?AdminUI.formatDate(value):'—';

const createFieldMap={
    member_id:'memberId',
    exit_type:'exitType',
    reason:'reason',
    proposed_exit_date:'proposedExitDate'
};

const settlementFieldMap={
    payout_account_id:'payoutAccountId',
    settlement_date:'settlementDate'
};

function syncSettlementValidation(item=currentExit){
    const payout=$('payoutAccountId');
    const marker=$('payoutRequiredMarker');
    const required=Number(item?.share_refund||0)>0;

    if(payout){
        if(required){
            payout.dataset.required='1';
        }else{
            delete payout.dataset.required;
            AdminUI.clearFieldError(payout);
        }
    }

    if(marker){
        marker.classList.toggle('hidden',!required);
    }
}

function setDate(id,value){
    const element=$(id);
    if(!element)return;

    const formatted=value?String(value).substring(0,10):'';
    element.value=formatted;

    if(element._flatpickr){
        formatted
            ?element._flatpickr.setDate(formatted,false,'Y-m-d')
            :element._flatpickr.clear();
    }
}

function statusBadge(status){
    const map={
        submitted:['Submitted','bg-sky-50 text-sky-700','bi-send'],
        under_review:['Under Review','bg-indigo-50 text-indigo-700','bi-search'],
        liabilities_pending:['Liabilities Pending','bg-red-50 text-red-700','bi-exclamation-triangle'],
        ready_for_approval:['Ready for Approval','bg-amber-50 text-amber-700','bi-hourglass-bottom'],
        approved:['Approved','bg-violet-50 text-violet-700','bi-patch-check'],
        settled:['Settled','bg-emerald-50 text-emerald-700','bi-cash-stack'],
        closed:['Closed','bg-slate-100 text-slate-600','bi-check2-circle'],
        rejected:['Rejected','bg-red-50 text-red-600','bi-x-circle'],
        cancelled:['Cancelled','bg-slate-100 text-slate-500','bi-slash-circle']
    };

    const item=map[status]??[status,'bg-slate-100 text-slate-600','bi-circle'];

    return`<span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${item[1]}"><i class="bi ${item[2]}"></i>${esc(item[0])}</span>`;
}

function typeLabel(type){
    return{
        resignation:'Resignation',
        termination:'Termination',
        death:'Death',
        permanent_removal:'Permanent Removal',
        other:'Other'
    }[type]??type;
}

async function loadOptions(){
    try{
        const response=await api(`${API}/options`);
        options=response.data??{members:[],accounts:[]};

        $('memberId').innerHTML='<option value="">Select Member</option>'+
            (options.members??[]).map(member=>`
                <option value="${member.id}">${esc(member.user?.name??'N/A')} (${esc(member.member_code??'')})</option>
            `).join('');

        $('payoutAccountId').innerHTML='<option value="">Select Cash / Bank Account</option>'+
            (options.accounts??[]).map(account=>`
                <option value="${account.id}">${esc(account.code)} - ${esc(account.name)}</option>
            `).join('');
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
}

async function loadStatistics(){
    try{
        const response=await api(`${API}/statistics`);
        const stats=response.data??{};

        ['total','pending','approved','closed','death'].forEach(key=>{
            const element=$(`stat-${key}`);
            if(element)element.textContent=Number(stats[key]??0);
        });
    }catch(error){
        console.error(error);
    }
}

async function loadExits(page=1){
    currentPage=page;

    $('exitTableBody').innerHTML=AdminUI.loadingState('Loading exit records...',8);

    $('exitMobileGrid').innerHTML=`
        <div class="px-4 py-10 text-center text-sm text-slate-400">Loading exit records...</div>`;

    const params=new URLSearchParams({page,per_page:15});

    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;
    const type=$('typeFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);
    if(type)params.set('exit_type',type);

    try{
        const response=await api(`${API}?${params.toString()}`);
        const paginator=response.data??{};

        records=paginator.data??[];
        renderRecords();

        $('paginationContainer').innerHTML='';
        AdminUI.renderPagination(paginator,$('paginationContainer'),loadExits);
    }catch(error){
        const message=AdminUI.extractError(error);

        $('exitTableBody').innerHTML=AdminUI.emptyState(message,8);
        $('exitMobileGrid').innerHTML=`
            <div class="px-4 py-10 text-center text-sm text-slate-400">${esc(message)}</div>`;
    }
}

function renderRecords(){
    if(!records.length){
        $('exitTableBody').innerHTML=AdminUI.emptyState('No member exit records found.',8);

        $('exitMobileGrid').innerHTML=`
            <div class="px-4 py-10 text-center text-sm text-slate-400">No exit records found.</div>`;
        return;
    }

    $('exitTableBody').innerHTML=records.map(item=>`
        <tr class="transition hover:bg-slate-50/70">
            <td class="px-4 py-3">
                <div class="font-semibold  text-xs 2xl:text-sm text-slate-700">${esc(item.exit_no)}</div>
                <div class="mt-0.5 text-[11px] text-slate-400">${formatDate(item.request_date)}</div>
            </td>

            <td class="px-4 py-3">
                <div class="font-semibold  text-xs 2xl:text-sm text-slate-700">${esc(item.member?.user?.name??'N/A')}</div>
                <div class="mt-0.5 text-[11px] font-medium text-indigo-600">${esc(item.member?.member_code??'')}</div>
            </td>

            <td class="px-4 py-3 text-sm font-medium text-slate-600">${esc(typeLabel(item.exit_type))}</td>

            <td class="px-4 py-3 text-right font-semibold ${Number(item.total_liabilities)>0?'text-red-600':'text-slate-600'}">${money(item.total_liabilities)}</td>

            <td class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-emerald-700">${money(item.share_refund)}</td>

            <td class="px-4 py-3 text-center">
                ${Number(item.blocking_items_count)>0
                    ?`<span class="inline-flex min-w-7 items-center justify-center rounded-md bg-red-50 px-2 py-1 text-sm font-bold text-red-600">${Number(item.blocking_items_count)}</span>`
                    :'<span class="inline-flex min-w-7 items-center justify-center rounded-md bg-emerald-50 px-2 py-1 text-sm font-bold text-emerald-600">0</span>'
                }
            </td>

            <td class="px-4 py-3">${statusBadge(item.status)}</td>

            <td class="px-4 py-3 text-right">
                <button type="button" onclick="openManageModal(${item.id})" class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <i class="bi bi-eye"></i>
                    Manage
                </button>
            </td>
        </tr>
    `).join('');

    $('exitMobileGrid').innerHTML=records.map(item=>`
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-800">${esc(item.exit_no)}</p>
                    <p class="mt-0.5 truncate text-[11px] text-slate-400">${esc(typeLabel(item.exit_type))} • ${formatDate(item.request_date)}</p>
                </div>

                <div class="shrink-0">
                    ${statusBadge(item.status)}
                </div>
            </div>

            <div class="mt-3 rounded-md bg-slate-50/60 p-3">
                <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-700">${esc(item.member?.user?.name??'N/A')}</p>
                <p class="mt-0.5 truncate text-[11px] text-indigo-600">${esc(item.member?.member_code??'')}</p>
            </div>

            <div class="mt-3 grid grid-cols-3 gap-x-3 gap-y-2.5 text-[11px]">
                <div class="min-w-0">
                    <p class="text-slate-400 text-xs 2xl:text-sm">Liabilities</p>
                    <p class="truncate font-semibold ${Number(item.total_liabilities)>0?'text-red-600':'text-slate-700'}">${money(item.total_liabilities)}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-slate-400 text-xs 2xl:text-sm">Share Refund</p>
                    <p class="truncate font-semibold text-emerald-700">${money(item.share_refund)}</p>
                </div>

                <div class="min-w-0">
                    <p class="text-slate-400 text-xs 2xl:text-sm">Blockers</p>
                    <p class="truncate font-semibold ${Number(item.blocking_items_count)>0?'text-red-600':'text-emerald-600'}">${item.blocking_items_count}</p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3">
                <button type="button" onclick="openManageModal(${item.id})" class="flex h-8 flex-1 items-center justify-center gap-1.5 rounded-md bg-indigo-50 px-2 text-[11px] font-semibold text-indigo-600 transition hover:bg-indigo-100">
                    <i class="bi bi-eye text-sm"></i>
                    Manage
                </button>
            </div>
        </div>
    `).join('');
}

window.openCreateModal=function(){
    $('createForm').reset();

    AdminUI.clearError('createError');
    AdminUI.clearFieldErrors('createForm');

    AdminUI.openModal('createModal');
    window.initDatePickers?.();

    setDate('proposedExitDate','');
};

$('createForm').addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('createError');
    AdminUI.clearFieldErrors('createForm');

    const payload={
        member_id:Number($('memberId').value),
        exit_type:$('exitType').value,
        reason:$('reason').value.trim(),
        proposed_exit_date:$('proposedExitDate').value||null
    };

    const button=$('createButton');

    AdminUI.setLoading(button,'Starting...');

    try{
        await api(API,{
            method:'POST',
            body:JSON.stringify(payload)
        });

        AdminUI.closeModal('createModal');
        Toast.success('Member exit process started successfully.');

        await Promise.all([
            loadExits(1),
            loadStatistics()
        ]);
    }catch(error){
        if(!AdminUI.showValidationErrors('createForm',error,createFieldMap)){
            AdminUI.showError('createError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.openManageModal=async function(id){
    AdminUI.clearError('manageError');

    try{
        const response=await api(`${API}/${id}`);
        currentExit=response.data;

        renderManageModal(currentExit);

        AdminUI.openModal('manageModal');
        window.initDatePickers?.();

        if(currentExit.status==='approved'){
            setDate('settlementDate',new Date().toISOString().substring(0,10));
        }
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

function renderManageModal(item){
    $('manageTitle').textContent=`${item.exit_no} — ${item.member?.user?.name??'Member'}`;
    $('manageSubtitle').textContent=`${typeLabel(item.exit_type)} • ${item.status.replaceAll('_',' ')}`;

    $('summaryCards').innerHTML=[
        ['Subscription Due',money(item.subscription_due),'text-amber-700','bg-amber-50'],
        ['Charges',money(item.charge_due),'text-orange-700','bg-orange-50'],
        ['Loan Due',money(item.loan_due),'text-red-700','bg-red-50'],
        ['Share Refund',money(item.share_refund),'text-emerald-700','bg-emerald-50'],
        ['Net Settlement',money(item.net_settlement_amount),'text-indigo-700','bg-indigo-50']
    ].map(([label,value,text,bg])=>`
        <div class="rounded-md ${bg} p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${label}</p>
            <p class="mt-1 text-base font-bold ${text}">${value}</p>
        </div>
    `).join('');

    $('caseDetails').innerHTML=[
        ['Member',item.member?.user?.name??'—'],
        ['Member Code',item.member?.member_code??'—'],
        ['Exit Type',typeLabel(item.exit_type)],
        ['Request Date',formatDate(item.request_date)],
        ['Proposed Exit',formatDate(item.proposed_exit_date)],
        ['Status',item.status.replaceAll('_',' ')],
        ['Blockers',String(item.blocking_items_count??0)],
        ['Reason',item.reason??'—']
    ].map(([label,value])=>`
        <div class="${label==='Reason'?'col-span-2':''} rounded-md border border-slate-200 bg-slate-50/60 p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${esc(label)}</p>
            <p class="mt-1 break-words text-sm font-semibold capitalize text-slate-700">${esc(value)}</p>
        </div>
    `).join('');

    renderAssessment(item.items??[]);
    renderNomineeAllocations(item.nominee_allocations??item.nomineeAllocations??[]);

    $('reviewNote').value=item.review_note??'';

    const settlementVisible=item.status==='approved';
    $('settlementSection').classList.toggle('hidden',!settlementVisible);

    syncSettlementValidation(item);
    renderManageActions(item);
}

function renderAssessment(items){
    if(!items.length){
        $('assessmentItems').innerHTML=`
            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4 text-center">
                <i class="bi bi-check2-circle text-lg text-emerald-600"></i>
                <p class="mt-1 text-sm font-semibold text-emerald-700">No blockers found</p>
            </div>`;
        return;
    }

    $('assessmentItems').innerHTML=items.map(item=>`
        <div class="flex items-start justify-between gap-3 rounded-md border ${item.is_blocking?'border-red-200 bg-red-50/50':'border-slate-200 bg-slate-50'} p-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <i class="bi ${item.is_blocking?'bi-exclamation-circle text-red-500':'bi-check-circle text-emerald-500'}"></i>
                    <p class="text-sm font-semibold text-slate-700">${esc(item.description)}</p>
                </div>
                <p class="ml-6 mt-1 text-[10px] uppercase text-slate-400">${esc(item.category?.replaceAll('_',' ')??'')}</p>
            </div>

            ${Number(item.amount)>0
                ?`<span class="shrink-0 text-sm font-bold ${item.is_blocking?'text-red-600':'text-slate-600'}">${money(item.amount)}</span>`
                :''
            }
        </div>
    `).join('');
}

function renderNomineeAllocations(allocations){
    const section=$('nomineeAllocationSection');

    if(!allocations.length){
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');

    $('nomineeAllocations').innerHTML=allocations.map(item=>`
        <div class="flex items-center justify-between rounded-md border border-violet-200 bg-white px-3 py-2">
            <div>
                <p class="text-sm font-semibold text-slate-700">${esc(item.nominee?.name??'Nominee')}</p>
                <p class="text-[10px] text-slate-400">${esc(item.nominee?.relationship??'')}</p>
            </div>

            <div class="text-right">
                <p class="text-sm font-bold text-violet-700">${money(item.amount)}</p>
                <p class="text-[10px] text-slate-400">${Number(item.allocation_percentage||0).toFixed(2)}%</p>
            </div>
        </div>
    `).join('');
}

function renderManageActions(item){
    const actions=[];

    if(permissions.review&&item.status==='submitted'){
        actions.push(actionButton(
            'Start Review','bi-search','indigo',
            `startReview()`
        ));
    }

    if(permissions.review&&['under_review','liabilities_pending','ready_for_approval','approved'].includes(item.status)){
        actions.push(actionButton(
            'Refresh Assessment','bi-arrow-clockwise','slate',
            `refreshAssessment()`
        ));
    }

    if(permissions.approve&&item.status==='ready_for_approval'){
        actions.push(actionButton(
            'Reject','bi-x-circle','red',
            `openRejectModal()`
        ));

        actions.push(actionButton(
            'Approve','bi-check2-circle','emerald',
            `approveExit()`
        ));
    }

    if(permissions.settle&&item.status==='approved'){
        actions.push(actionButton(
            'Complete Settlement','bi-cash-stack','emerald',
            `settleExit()`
        ));
    }

    if(permissions.settle&&item.status==='settled'){
        actions.push(actionButton(
            'Close Membership','bi-check2-square','indigo',
            `closeExit()`
        ));
    }

    if(permissions.review&&['submitted','under_review','liabilities_pending','ready_for_approval'].includes(item.status)){
        actions.unshift(actionButton(
            'Cancel Process','bi-slash-circle','red',
            `cancelExit()`,
            true
        ));
    }

    $('manageActions').innerHTML=actions.length
        ?actions.join('')
        :'<span class="text-xs 2xl:text-sm text-slate-400">No further action available.</span>';
}

function actionButton(label,icon,color,onclick,outline=false){
    const styles={
        indigo:outline?'border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50':'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700',
        emerald:outline?'border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50':'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700',
        red:'border-red-200 bg-white text-red-600 hover:bg-red-50',
        slate:'border-slate-300 bg-white text-slate-700 hover:bg-slate-50'
    };

    return`
        <button type="button" onclick="${onclick}" class="inline-flex items-center gap-1.5 rounded-md border px-3 py-2 text-xs 2xl:text-sm font-semibold transition ${styles[color]}">
            <i class="bi ${icon}"></i>
            ${label}
        </button>`;
}

window.startReview=function(){
    if(!currentExit)return;

    AdminUI.request(`${API}/${currentExit.id}/review`,{
        method:'POST',
        data:{
            review_note:$('reviewNote').value.trim()||null
        },
        confirmation:{
            title:'Start Exit Review?',
            message:'Start reviewing this member exit request?',
            confirmText:'Start Review',
            type:'primary'
        },
        successMessage:'Exit review started successfully.',
        onSuccess:async()=>refreshCurrent()
    });
};

window.refreshAssessment=function(){
    if(!currentExit)return;

    AdminUI.request(`${API}/${currentExit.id}/assess`,{
        method:'POST',
        data:{},
        confirmation:{
            title:'Refresh Assessment?',
            message:'Recalculate current subscriptions, charges, loans, shares and operational blockers?',
            confirmText:'Refresh Assessment',
            type:'primary'
        },
        successMessage:'Exit assessment refreshed.',
        onSuccess:async()=>refreshCurrent()
    });
};

window.approveExit=function(){
    if(!currentExit)return;

    AdminUI.request(`${API}/${currentExit.id}/approve`,{
        method:'POST',
        data:{},
        confirmation:{
            title:'Approve Member Exit?',
            message:'Approve this exit request after confirming that all blockers have been resolved?',
            confirmText:'Approve Exit',
            type:'success'
        },
        successMessage:'Member exit approved successfully.',
        onSuccess:async()=>refreshCurrent()
    });
};

window.openRejectModal=function(){
    $('rejectForm').reset();
    AdminUI.clearError('rejectError');
    AdminUI.clearFieldErrors('rejectForm');
    AdminUI.openModal('rejectModal');
};

window.submitRejection=async function(){
    if(!currentExit)return;

    AdminUI.clearError('rejectError');
    AdminUI.clearFieldErrors('rejectForm');

    const reason=$('rejectionReason').value.trim();

    const button=$('rejectButton');
    AdminUI.setLoading(button,'Rejecting...');

    try{
        await api(`${API}/${currentExit.id}/reject`,{
            method:'POST',
            body:JSON.stringify({rejection_reason:reason})
        });

        AdminUI.closeModal('rejectModal');
        Toast.success('Exit request rejected.');

        await refreshCurrent();
    }catch(error){
        if(!AdminUI.showValidationErrors('rejectForm',error,{
            rejection_reason:'rejectionReason'
        })){
            AdminUI.showError('rejectError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(button);
    }
};

$('rejectForm').addEventListener('submit',event=>{
    event.preventDefault();
    submitRejection();
});

window.cancelExit=function(){
    if(!currentExit)return;

    AdminUI.request(`${API}/${currentExit.id}/cancel`,{
        method:'POST',
        data:{},
        confirmation:{
            title:'Cancel Exit Process?',
            message:'Cancel this member exit process? Existing member and financial records will remain unchanged.',
            confirmText:'Cancel Process',
            type:'danger'
        },
        successMessage:'Exit process cancelled.',
        onSuccess:async()=>refreshCurrent()
    });
};

window.settleExit=async function(){
    if(!currentExit)return;

    AdminUI.clearError('manageError');
    AdminUI.clearFieldErrors('settlementForm');

    syncSettlementValidation(currentExit);

    if(!AdminUI.validateForm('settlementForm'))return;

    const accountValue=$('payoutAccountId').value;

    const payload={
        payout_account_id:accountValue?Number(accountValue):null,
        settlement_date:$('settlementDate').value
    };

    AdminUI.request(`${API}/${currentExit.id}/settle`,{
        method:'POST',
        data:payload,
        confirmation:{
            title:'Complete Exit Settlement?',
            message:`Disburse ${money(currentExit.share_refund)} and complete the financial settlement? This will retire active shares, stop subscriptions and deactivate the member login.`,
            confirmText:'Complete Settlement',
            type:'success'
        },
        successMessage:'Exit settlement completed successfully.',
        onSuccess:async()=>refreshCurrent(),
        onError:error=>{
            if(!AdminUI.showValidationErrors('settlementForm',error,settlementFieldMap)){
                AdminUI.showError('manageError',AdminUI.extractError(error));
            }
        }
    });
};

window.closeExit=function(){
    if(!currentExit)return;

    AdminUI.request(`${API}/${currentExit.id}/close`,{
        method:'POST',
        data:{},
        confirmation:{
            title:'Close Membership?',
            message:'Permanently close this membership after settlement? Financial and historical records will remain preserved.',
            confirmText:'Close Membership',
            type:'danger'
        },
        successMessage:'Membership closed successfully.',
        onSuccess:async()=>refreshCurrent()
    });
};

async function refreshCurrent(){
    if(!currentExit)return;

    const id=currentExit.id;

    await Promise.all([
        loadStatistics(),
        loadExits(currentPage)
    ]);

    const response=await api(`${API}/${id}`);
    currentExit=response.data;

    renderManageModal(currentExit);
    window.initDatePickers?.();
}

window.clearFilters=function(){
    $('searchInput').value='';
    $('statusFilter').value='';
    $('typeFilter').value='';
    loadExits(1);
};

async function init(){
    if(typeof AdminUI==='undefined'||typeof api==='undefined'){
        setTimeout(init,50);
        return;
    }

    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(()=>loadExits(1))
    );

    $('statusFilter').addEventListener('change',()=>loadExits(1));
    $('typeFilter').addEventListener('change',()=>loadExits(1));

    window.initDatePickers?.();

    await Promise.all([
        loadOptions(),
        loadStatistics(),
        loadExits()
    ]);
}

init();
</script>

@endpush