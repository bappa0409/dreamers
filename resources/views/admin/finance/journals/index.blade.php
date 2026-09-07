@extends('layouts.admin')

@section('title','Journal Entries')
@section('page_title','Journal Entries')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-journal-text"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Journal Entries</h1>
                <p class="text-xs text-slate-500">
                    Review automatic journals and post balanced manual entries.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openJournalModal()"
            class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Manual Journal
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Journals</p>
                    <p id="summaryTotal" class="mt-2 text-xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-journals"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-700">Posted</p>
                    <p id="summaryPosted" class="mt-2 text-xl font-bold text-emerald-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-indigo-700">Manual</p>
                    <p id="summaryManual" class="mt-2 text-xl font-bold text-indigo-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                    <i class="bi bi-pencil-square"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-700">Cancelled / Reversed</p>
                    <p id="summaryCancelled" class="mt-2 text-xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>
            </div>
        </div>

        <div class="col-span-2 rounded-md border border-slate-200 bg-slate-50 p-4 md:col-span-1">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">Posted Debit</p>
                    <p id="summaryDebit" class="mt-2 truncate text-xl font-bold text-slate-700">
                        {{ setting('currency_symbol','৳') }}0.00
                    </p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-slate-200 text-slate-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-3">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Journal, description, account..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Type
                </label>

                <select
                    id="typeFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Types</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Source
                </label>

                <select
                    id="sourceFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Sources</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    id="statusFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">All Status</option>
                    <option value="posted">Posted</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="draft">Draft</option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Date Range
                </label>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>
            </div>

            <div class="lg:col-span-1">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3  text-xs 2xl:text-sm font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full table-fixed text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[16%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Journal
                        </th>
                        <th class="w-[11%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Description
                        </th>
                        <th class="w-[13%] px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Debit
                        </th>
                        <th class="w-[13%] px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Credit
                        </th>
                        <th class="w-[10%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Status
                        </th>
                        <th class="w-[14%] px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody id="journalTable">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-xs 2xl:text-sm text-center text-slate-400">
                            Loading journal entries...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="journalCards" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-sm text-slate-400">Loading journal entries...</div>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- =========================================================
MANUAL JOURNAL MODAL
========================================================= --}}
<div id="journalModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-journal-plus"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Manual Journal Entry
                    </h2>

                    <p class="text-xs text-slate-500">
                        Create and post a balanced accounting journal.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeJournalModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="journalForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Journal Information
                            </h3>

                            <p class="text-[11px] text-slate-400">
                                Basic information for this accounting transaction.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="form-label">
                                Transaction Date
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="journalDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                    required>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">
                                Description
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="journalDescription"
                                type="text"
                                maxlength="3000"
                                class="app-input"
                                placeholder="Journal description"
                                required>
                        </div>
                    </div>
                </section>

                <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                                <i class="bi bi-list-columns"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">
                                    Journal Lines
                                </h3>

                                <p class="text-[11px] text-slate-400">
                                    Each line can contain debit or credit, never both.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            onclick="addJournalLine()"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs 2xl:text-sm font-semibold text-indigo-600 transition hover:bg-indigo-100">
                            <i class="bi bi-plus-lg"></i>
                            Add Line
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[850px] text-base">
                            <thead class="border-b border-slate-200 bg-white">
                                <tr>
                                    <th class="w-[35%] px-3 py-2 text-left text-sm font-semibold text-slate-500">
                                        Account
                                    </th>

                                    <th class="w-[18%] px-3 py-2 text-right text-sm font-semibold text-slate-500">
                                        Debit
                                    </th>

                                    <th class="w-[18%] px-3 py-2 text-right text-sm font-semibold text-slate-500">
                                        Credit
                                    </th>

                                    <th class="px-3 py-2 text-left text-sm font-semibold text-slate-500">
                                        Description
                                    </th>

                                    <th class="w-12 px-3 py-2"></th>
                                </tr>
                            </thead>

                            <tbody id="journalLines"></tbody>

                            <tfoot class="border-t border-slate-200 bg-slate-50">
                                <tr>
                                    <td class="px-3 py-3 text-right text-sm font-bold text-slate-600">
                                        Total
                                    </td>

                                    <td id="totalDebit" class="px-3 py-3 text-right text-base font-bold text-slate-800">
                                        0.00
                                    </td>

                                    <td id="totalCredit" class="px-3 py-3 text-right text-base font-bold text-slate-800">
                                        0.00
                                    </td>

                                    <td colspan="2" class="px-3 py-3">
                                        <span
                                            id="balanceIndicator"
                                            class="inline-flex rounded-md bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700">
                                            Not Balanced
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <div
                    id="journalError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-700">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeJournalModal()"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i>
                    Close
                </button>

                <button
                    id="postJournalButton"
                    type="submit"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    Post Journal
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =========================================================
DETAILS MODAL
========================================================= --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-journal-text"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Journal Voucher Details</h2>
                    <p class="text-xs text-slate-500">
                        Review the voucher, then download it as a PDF or print it.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeDetailsModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="detailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div
            id="detailsActions"
            class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
        </div>
    </div>
</div>

{{-- =========================================================
REVERSE MODAL
========================================================= --}}
<div id="reverseModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Reverse Journal
                    </h2>

                    <p id="reverseJournalInfo" class="text-xs text-slate-500"></p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeReverseModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="reverseForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="reverseTransactionId" type="hidden">

            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Reversal Information
                            </h3>

                            <p class="text-[11px] text-slate-400">
                                A separate opposite journal will be created.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="form-label">
                                Reversal Date
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="reverseDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                    required>
                            </div>
                        </div>

                        <div>
                            <label class="form-label">
                                Reason
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="reverseReason"
                                rows="4"
                                minlength="3"
                                maxlength="1000"
                                class="app-input resize-none"
                                placeholder="Reason for reversing this journal..."
                                required></textarea>
                        </div>
                    </div>
                </section>

                <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <i class="bi bi-exclamation-triangle text-amber-600"></i>

                        <div>
                            <p class="text-base font-semibold text-amber-800">
                                Accounting Reversal
                            </p>

                            <p class="mt-1 text-sm leading-5 text-amber-700">
                                The original journal remains in history. A new opposite journal is posted and the original transaction is marked cancelled.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    id="reverseError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-700">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeReverseModal()"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i>
                    Close
                </button>

                <button
                    id="reverseButton"
                    type="submit"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    Reverse Journal
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
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const orgName=@json(setting('organization_name','Association'));
const orgAddress=@json(setting('organization_address',''));
const orgPhone=@json(setting('organization_phone',''));
const orgEmail=@json(setting('organization_email',''));
const orgLogoRaw=@json(setting('site_logo',null));
const orgLogo=orgLogoRaw
    ?(String(orgLogoRaw).startsWith('http')
        ?orgLogoRaw
        :`/storage/${String(orgLogoRaw).replace(/^storage\//,'')}`)
    :null;

let journals=[];
let accounts=[];
let currentPage=1;
let lineSequence=0;
let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;
let currentJournal=null;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>`${currency}${Number(value||0).toLocaleString(undefined,{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

function cardsLoadingHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-400">
            ${esc(message)}
        </div>
    `;
}

function cardsEmptyHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-400">
            ${esc(message)}
        </div>
    `;
}

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

function initRangePicker(){
    const input=$('dateRangeFilter');

    if(
        !input||
        typeof window.flatpickr==='undefined'
    ){
        return;
    }

    if(input._flatpickr){
        input._flatpickr.destroy();
    }

    dateRangePicker=flatpickr(
        input,
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

                    Promise.all([
                        loadJournals(1),
                        loadSummary()
                    ]);

                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    Promise.all([
                        loadJournals(1),
                        loadSummary()
                    ]);
                }
            }
        }
    );
}

function accountOptions(selected=''){
    return `
        <option value="">Select Account</option>
        ${accounts.map(account=>`
            <option
                value="${account.id}"
                ${String(account.id)===String(selected)?'selected':''}>
                ${esc(account.code)} - ${esc(account.name)}
            </option>
        `).join('')}
    `;
}

async function loadOptions(){
    try{
        const response=await api(
            '/api/finance/transactions/options'
        );

        accounts=response.data?.accounts||[];

        const types=response.data?.types||[];
        const sources=response.data?.source_modules||[];

        $('typeFilter').innerHTML=
            `<option value="">All Types</option>`+
            types.map(type=>`
                <option value="${esc(type)}">
                    ${esc(AdminUI.titleCase(type))}
                </option>
            `).join('');

        $('sourceFilter').innerHTML=
            `<option value="">All Sources</option>`+
            sources.map(source=>`
                <option value="${esc(source)}">
                    ${esc(AdminUI.titleCase(source))}
                </option>
            `).join('');
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadSummary(){
    const query=AdminUI.query({
        from:selectedFrom,
        to:selectedTo
    });

    try{
        const response=await api(
            `/api/finance/transactions/summary?${query}`
        );

        const data=response.data||{};

        $('summaryTotal').textContent=data.total||0;
        $('summaryPosted').textContent=data.posted||0;
        $('summaryManual').textContent=data.manual||0;
        $('summaryCancelled').textContent=data.cancelled||0;
        $('summaryDebit').textContent=money(data.posted_debit);
    }catch(error){
        console.error(error);
    }
}

async function loadJournals(page=1){
    currentPage=page;

    $('journalTable').innerHTML=
        AdminUI.loadingState(
            'Loading journal entries...',
            7
        );

    $('journalCards').innerHTML=
        cardsLoadingHtml(
            'Loading journal entries...'
        );

    const query=AdminUI.query({
        page,
        per_page:20,
        search:$('searchInput').value.trim(),
        type:$('typeFilter').value,
        source_module:$('sourceFilter').value,
        status:$('statusFilter').value,
        from:selectedFrom,
        to:selectedTo
    });

    try{
        const response=await api(
            `/api/finance/transactions?${query}`
        );

        const paginator=response.data;

        journals=paginator.data||[];

        renderJournals();

        AdminUI.renderPagination({
            container:$('paginationContainer'),
            currentPage:paginator.current_page,
            lastPage:paginator.last_page,
            total:paginator.total,
            onPageChange:loadJournals
        });
    }catch(error){
        $('journalTable').innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                7
            );

        $('journalCards').innerHTML=
            cardsEmptyHtml(
                AdminUI.extractError(error)
            );
    }
}

function journalActionButtons(journal,{withLabel=false}={}){
    const actions=[
        `
            <button
                type="button"
                onclick="viewJournal(${journal.id})"
                title="View Details"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 shrink-0 items-center justify-center rounded-md'} cursor-pointer bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                <i class="bi bi-eye text-sm"></i>
                ${withLabel?'View':''}
            </button>
        `
    ];

    if(
        canUpdate&&
        journal.status==='posted'&&
        journal.type==='manual_journal'&&
        journal.source_module==='manual'&&
        !journal.reversed_at
    ){
        actions.push(`
            <button
                type="button"
                onclick="openReverseModal(${journal.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-red-50 text-red-600 transition hover:bg-red-100"
                title="Reverse">
                <i class="bi bi-arrow-counterclockwise text-sm"></i>
                ${withLabel?'Reverse':''}
            </button>
        `);
    }

    return actions.join('');
}

function renderJournals(){
    if(!journals.length){
        $('journalTable').innerHTML=
            AdminUI.emptyState(
                'No journal entries found.',
                7
            );

        $('journalCards').innerHTML=
            cardsEmptyHtml(
                'No journal entries found.'
            );

        return;
    }

    $('journalTable').innerHTML=journals.map(journal=>{
        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50/60">
                <td class="px-4 py-3">
                    <div class="truncate text-xs font-semibold text-slate-700">
                        ${esc(journal.transaction_no)}
                    </div>

                    <div class="truncate text-[10px] text-slate-400">
                        #${journal.id} • ${esc(AdminUI.titleCase(journal.type))}
                    </div>
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${
                        journal.transaction_date
                            ?AdminUI.formatDate(journal.transaction_date)
                            :'—'
                    }
                </td>

                <td class="px-4 py-3">
                    <p class="truncate text-xs text-slate-600">
                        ${esc(journal.description||'—')}
                    </p>
                    <p class="truncate text-[10px] text-slate-400">
                        ${esc(AdminUI.titleCase(journal.source_module||'—'))}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-right font-semibold text-slate-700">
                    ${money(journal.total_debit)}
                </td>

                <td class="px-4 py-3 text-xs text-right font-semibold text-slate-700">
                    ${money(journal.total_credit)}
                </td>

                <td class="px-4 py-3">
                    ${AdminUI.statusBadge(journal.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-1">
                        ${journalActionButtons(journal)}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    $('journalCards').innerHTML=journals.map(journal=>{
        return`
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                            ${esc(journal.transaction_no)}
                        </p>

                        <p class="mt-0.5 truncate text-[10px] text-slate-400">
                            #${journal.id} • ${esc(AdminUI.titleCase(journal.type))}
                        </p>
                    </div>

                    <div class="shrink-0">
                        ${AdminUI.statusBadge(journal.status)}
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 rounded-md bg-slate-50/60 p-3 text-[11px]">
                    <div class="min-w-0">
                        <p class="text-slate-400 text-xs 2xl:text-sm">Date</p>
                        <p class="truncate font-medium text-slate-700">
                            ${
                                journal.transaction_date
                                    ?AdminUI.formatDate(journal.transaction_date)
                                    :'—'
                            }
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-400 text-xs 2xl:text-sm">Source</p>
                        <p class="truncate font-medium text-slate-700">
                            ${esc(AdminUI.titleCase(journal.source_module||'—'))}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-400 text-xs 2xl:text-sm">Debit</p>
                        <p class="truncate font-semibold text-slate-800">
                            ${money(journal.total_debit)}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-400 text-xs 2xl:text-sm">Credit</p>
                        <p class="truncate font-semibold text-slate-800">
                            ${money(journal.total_credit)}
                        </p>
                    </div>

                    <div class="col-span-2 min-w-0">
                        <p class="text-slate-400 text-xs 2xl:text-sm">Description</p>
                        <p class="truncate font-medium text-slate-700">
                            ${esc(journal.description||'—')}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3">
                    ${journalActionButtons(journal,{withLabel:true})}
                </div>
            </div>
        `;
    }).join('');
}

window.openJournalModal=function(){
    $('journalForm').reset();
    $('journalLines').innerHTML='';

    lineSequence=0;

    AdminUI.clearError(
        'journalError'
    );

    addJournalLine();
    addJournalLine();

    AdminUI.openModal(
        'journalModal'
    );

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    setPickerDate(
        $('journalDate'),
        localDate()
    );
};

window.closeJournalModal=function(){
    AdminUI.closeModal(
        'journalModal'
    );
};

window.closeDetailsModal=function(){
    AdminUI.closeModal(
        'detailsModal'
    );
};

window.printJournalReceipt=function(){
    const journal=currentJournal;

    if(!journal){
        Toast.error('No journal loaded to print.');
        return;
    }

    const win=window.open('','_blank','width=800,height=900');

    if(!win){
        Toast.error('Please allow popups to print the receipt.');
        return;
    }

    const entriesRows=(journal.entries||[]).map(entry=>`
        <tr>
            <td>${AdminUI.escapeHtml(entry.account?.code||'')} - ${AdminUI.escapeHtml(entry.account?.name||'')}</td>
            <td>${AdminUI.escapeHtml(entry.description||'—')}</td>
            <td class="right">${Number(entry.debit)>0?money(entry.debit):'—'}</td>
            <td class="right">${Number(entry.credit)>0?money(entry.credit):'—'}</td>
        </tr>
    `).join('');

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>${AdminUI.escapeHtml(journal.transaction_no)}</title>
            <style>
                *{box-sizing:border-box}
                body{font-family:Arial,"Noto Sans Bengali",sans-serif;color:#334155;padding:30px;max-width:700px;margin:0 auto}
                .header{text-align:center;border-bottom:2px dashed #cbd5e1;padding-bottom:14px;margin-bottom:16px}
                .logo{height:55px;max-width:150px;margin-bottom:6px}
                h1{margin:0;font-size:18px;color:#0f172a;text-transform:uppercase;letter-spacing:0.5px}
                .addr{margin-top:3px;font-size:11px;color:#64748b}
                .doc-title{margin-top:10px;font-size:13px;font-weight:bold;letter-spacing:2px;color:#4338ca;text-transform:uppercase}
                .meta{display:grid;grid-template-columns:1fr 1fr;gap:6px 10px;font-size:12px;margin-bottom:16px}
                .meta .label{color:#94a3b8}
                .meta .full{grid-column:1 / -1}
                table{width:100%;border-collapse:collapse;font-size:11px;margin-bottom:16px}
                th,td{border:1px solid #dbe1ea;padding:6px 8px;text-align:left;vertical-align:top}
                th{background:#eef2ff;color:#3730a3}
                td.right,th.right{text-align:right}
                tfoot td{font-weight:bold;background:#f8fafc}
                .sign{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:50px;text-align:center;font-size:11px;color:#64748b}
                .sign div{border-top:1px solid #94a3b8;padding-top:6px}
                @media print{body{padding:10px}}
            </style>
        </head>
        <body>
            <div class="header">
                ${orgLogo?`<img src="${orgLogo}" class="logo">`:''}
                <h1>${AdminUI.escapeHtml(orgName)}</h1>
                ${orgAddress?`<div class="addr">${AdminUI.escapeHtml(orgAddress)}</div>`:''}
                ${(orgPhone||orgEmail)?`<div class="addr">${[orgPhone,orgEmail].filter(Boolean).map(v=>AdminUI.escapeHtml(v)).join(' • ')}</div>`:''}
                <div class="doc-title">Journal Voucher</div>
            </div>

            <div class="meta">
                <div><span class="label">Voucher No:</span> <strong>${AdminUI.escapeHtml(journal.transaction_no)}</strong></div>
                <div><span class="label">Date:</span> <strong>${journal.transaction_date?AdminUI.formatDate(journal.transaction_date):'—'}</strong></div>
                <div><span class="label">Type:</span> <strong>${AdminUI.escapeHtml(AdminUI.titleCase(journal.type))}</strong></div>
                <div><span class="label">Status:</span> <strong>${AdminUI.escapeHtml(AdminUI.titleCase(journal.status))}</strong></div>
                <div><span class="label">Source:</span> <strong>${AdminUI.escapeHtml(AdminUI.titleCase(journal.source_module||'—'))}</strong></div>
                <div><span class="label">Posted At:</span> <strong>${journal.posted_at?AdminUI.formatDate(journal.posted_at):'—'}</strong></div>
                <div class="full"><span class="label">Description:</span> ${AdminUI.escapeHtml(journal.description||'—')}</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Particulars</th>
                        <th class="right">Debit</th>
                        <th class="right">Credit</th>
                    </tr>
                </thead>
                <tbody>
                    ${entriesRows}
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="right">Total</td>
                        <td class="right">${money(journal.total_debit)}</td>
                        <td class="right">${money(journal.total_credit)}</td>
                    </tr>
                </tfoot>
            </table>

            <div class="sign">
                <div>Prepared By: ${AdminUI.escapeHtml(journal.creator?.name||'System')}</div>
                <div>Authorized By: ${AdminUI.escapeHtml(journal.poster?.name||'System')}</div>
            </div>
        </body>
        </html>
    `);

    win.document.close();

    setTimeout(()=>{
        win.focus();
        win.print();
    },300);
};

window.closeReverseModal=function(){
    AdminUI.closeModal(
        'reverseModal'
    );
};

window.addJournalLine=function(){
    const id=++lineSequence;

    $('journalLines').insertAdjacentHTML(
        'beforeend',
        `
            <tr
                id="journalLine-${id}"
                data-line-id="${id}"
                class="journal-line border-b border-slate-100">

                <td class="px-3 py-2 text-xs 2xl:text-sm">
                    <select
                        class="journal-account app-input w-full"
                        required>
                        ${accountOptions()}
                    </select>
                </td>

                <td class="px-3 py-2 text-xs 2xl:text-sm">
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        value=""
                        placeholder="0.00"
                        class="journal-debit app-input w-full text-right">
                </td>

                <td class="px-3 py-2 text-xs 2xl:text-sm">
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        value=""
                        placeholder="0.00"
                        class="journal-credit app-input w-full text-right">
                </td>

                <td class="px-3 py-2 text-xs 2xl:text-sm">
                    <input
                        type="text"
                        maxlength="500"
                        class="journal-description app-input w-full"
                        placeholder="Optional line description">
                </td>

                <td class="px-3 py-2 text-right">
                    <button
                        type="button"
                        onclick="removeJournalLine(${id})"
                        class="rounded p-2 text-red-500 hover:bg-red-50">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `
    );

    bindLineInputs(
        $('journalLine-'+id)
    );

    calculateTotals();
};

function bindLineInputs(row){
    const debit=row.querySelector(
        '.journal-debit'
    );

    const credit=row.querySelector(
        '.journal-credit'
    );

    debit.addEventListener(
        'input',
        ()=>{
            if(Number(debit.value)>0){
                credit.value='';
            }

            calculateTotals();
        }
    );

    credit.addEventListener(
        'input',
        ()=>{
            if(Number(credit.value)>0){
                debit.value='';
            }

            calculateTotals();
        }
    );
}

window.removeJournalLine=function(id){
    const rows=document.querySelectorAll(
        '.journal-line'
    );

    if(rows.length<=2){
        Toast.error(
            'At least two journal lines are required.'
        );

        return;
    }

    $('journalLine-'+id)?.remove();

    calculateTotals();
};

function calculateTotals(){
    let debit=0;
    let credit=0;

    document.querySelectorAll(
        '.journal-line'
    ).forEach(row=>{
        debit+=Number(
            row.querySelector(
                '.journal-debit'
            ).value||0
        );

        credit+=Number(
            row.querySelector(
                '.journal-credit'
            ).value||0
        );
    });

    debit=Math.round(
        debit*100
    )/100;

    credit=Math.round(
        credit*100
    )/100;

    $('totalDebit').textContent=
        debit.toFixed(2);

    $('totalCredit').textContent=
        credit.toFixed(2);

    const balanced=
        debit>0&&
        debit===credit;

    $('balanceIndicator').textContent=
        balanced
            ?'Balanced'
            :'Not Balanced';

    $('balanceIndicator').className=
        balanced
            ?'inline-flex rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700'
            :'inline-flex rounded-md bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-700';

    return{
        debit,
        credit,
        balanced
    };
}

$('journalForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'journalError'
        );

        const totals=calculateTotals();

        if(!totals.balanced){
            AdminUI.showError(
                'journalError',
                'Total debit and total credit must be equal and greater than zero.'
            );

            return;
        }

        const entries=[
            ...document.querySelectorAll(
                '.journal-line'
            )
        ].map(row=>({
            account_id:Number(
                row.querySelector(
                    '.journal-account'
                ).value
            ),
            debit:Number(
                row.querySelector(
                    '.journal-debit'
                ).value||0
            ),
            credit:Number(
                row.querySelector(
                    '.journal-credit'
                ).value||0
            ),
            description:
                row.querySelector(
                    '.journal-description'
                ).value.trim()||null
        }));

        const data={
            transaction_date:$('journalDate').value,
            description:$('journalDescription').value.trim(),
            entries
        };

        const confirmed=await AdminUI.confirm({
            title:'Post Journal Entry?',
            subtitle:'This journal cannot be edited after posting.',
            message:`Debit ${money(totals.debit)} and Credit ${money(totals.credit)} will be posted to the ledger.`,
            confirmText:'Post Journal',
            type:'primary'
        });

        if(!confirmed){
            return;
        }

        AdminUI.setLoading(
            'postJournalButton',
            'Posting...'
        );

        try{
            const response=await api(
                '/api/finance/transactions',
                {
                    method:'POST',
                    body:JSON.stringify(data)
                }
            );

            closeJournalModal();

            Toast.success(
                response.message||
                'Journal entry recorded and sent for approval.'
            );

            await Promise.all([
                loadJournals(1),
                loadSummary(),
                loadOptions()
            ]);
        }catch(error){
            AdminUI.showError(
                'journalError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                'postJournalButton'
            );
        }
    }
);

window.viewJournal=async function(id){
    AdminUI.openModal(
        'detailsModal'
    );

    $('detailsActions').innerHTML='';

    $('detailsBody').innerHTML=
        AdminUI.loadingState(
            'Loading details...'
        );

    try{
        const response=await api(
            `/api/finance/transactions/${id}`
        );

        const journal=response.data;
        currentJournal=journal;

        $('detailsBody').innerHTML=`
            <div class="mb-4 flex flex-col gap-4 rounded-md border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white">
                        <i class="bi bi-journal-text text-lg"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="truncate font-mono text-base font-bold text-slate-800">
                            ${esc(journal.transaction_no)}
                        </p>
                        <p class="text-xs text-slate-500">Journal Voucher</p>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end sm:justify-start">
                    ${AdminUI.statusBadge(journal.status)}
                    <p class="text-xl font-bold text-emerald-600">
                        ${money(journal.total_debit)}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                ${journalDetail('Date',journal.transaction_date?AdminUI.formatDate(journal.transaction_date):'—','bi-calendar3')}
                ${journalDetail('Type',AdminUI.titleCase(journal.type),'bi-tag')}
                ${journalDetail('Source',AdminUI.titleCase(journal.source_module||'—'),'bi-diagram-3')}
                ${journalDetail('Posted At',journal.posted_at?AdminUI.formatDate(journal.posted_at):'—','bi-clock-history')}
                ${journalDetail('Prepared By',journal.creator?.name||'System','bi-person')}
                ${journalDetail('Authorized By',journal.poster?.name||'System','bi-person-check')}
            </div>

            ${
                (journal.entries||[]).length
                    ?`
                        <div class="mt-4">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                    <i class="bi bi-list-columns text-sm"></i>
                                </div>

                                <p class="text-sm font-semibold text-slate-700">
                                    Journal Entries
                                </p>
                            </div>

                            <div class="overflow-hidden overflow-x-auto rounded-md border border-slate-200">
                                <table class="w-full min-w-[500px] text-sm">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-3 py-2 text-xs text-left font-semibold text-slate-500">Account</th>
                                            <th class="px-3 py-2 text-xs text-left font-semibold text-slate-500">Particulars</th>
                                            <th class="px-3 py-2 text-xs text-right font-semibold text-slate-500">Debit</th>
                                            <th class="px-3 py-2 text-xs text-right font-semibold text-slate-500">Credit</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        ${journal.entries.map(entry=>`
                                            <tr class="border-t border-slate-100 odd:bg-white even:bg-slate-50/60">
                                                <td class="px-3 py-2 text-xs text-slate-600">
                                                    ${esc(entry.account?.code||'')}
                                                    -
                                                    ${esc(entry.account?.name||'')}
                                                </td>

                                                <td class="px-3 py-2 text-xs text-slate-500">
                                                    ${esc(entry.description||'—')}
                                                </td>

                                                <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                                    ${Number(entry.debit)>0?money(entry.debit):'—'}
                                                </td>

                                                <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                                    ${Number(entry.credit)>0?money(entry.credit):'—'}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>

                                    <tfoot class="border-t border-slate-200 bg-slate-50">
                                        <tr>
                                            <td colspan="2" class="px-3 py-2 text-right text-xs font-bold text-slate-600">Total</td>
                                            <td class="px-3 py-2 text-right text-xs font-bold text-slate-800">${money(journal.total_debit)}</td>
                                            <td class="px-3 py-2 text-right text-xs font-bold text-slate-800">${money(journal.total_credit)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    `
                    :''
            }

            ${
                journal.description
                    ?`
                        <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                <i class="bi bi-sticky"></i>
                                Description
                            </p>

                            <p class="text-xs leading-5 text-slate-600">
                                ${esc(journal.description)}
                            </p>
                        </div>
                    `
                    :''
            }

            ${
                journal.cancel_reason
                    ?`
                        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-3">
                            <p class="mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-red-400">
                                <i class="bi bi-exclamation-triangle"></i>
                                Cancellation Reason
                            </p>

                            <p class="text-xs leading-5 text-red-700">
                                ${esc(journal.cancel_reason)}
                            </p>
                        </div>
                    `
                    :''
            }
        `;

        $('detailsActions').innerHTML=`
            <button
                type="button"
                onclick="closeDetailsModal()"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="bi bi-x-lg"></i>
                Close
            </button>

            <button
                type="button"
                onclick="downloadPdf('/api/finance/transactions/${journal.id}/voucher','journal-voucher-${journal.transaction_no}.pdf',this)"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                <i class="bi bi-file-earmark-pdf"></i>
                Download PDF
            </button>
        `;
    }catch(error){
        $('detailsBody').innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${esc(AdminUI.extractError(error))}
            </div>
        `;
    }
};

function journalDetail(label,value,icon){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${icon?`<i class="bi ${icon}"></i>`:''}
                ${esc(label)}
            </p>

            <p class="mt-1 break-words text-sm font-medium text-slate-700">
                ${esc(value??'—')}
            </p>
        </div>
    `;
}

window.openReverseModal=function(id){
    const journal=journals.find(
        item=>Number(item.id)===Number(id)
    );

    if(!journal){
        Toast.error(
            'Journal not found.'
        );

        return;
    }

    $('reverseForm').reset();

    $('reverseTransactionId').value=id;

    $('reverseJournalInfo').textContent=
        `${journal.transaction_no} • ${money(journal.total_debit)}`;

    AdminUI.clearError(
        'reverseError'
    );

    AdminUI.openModal(
        'reverseModal'
    );

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    setPickerDate(
        $('reverseDate'),
        localDate()
    );
};

$('reverseForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'reverseError'
        );

        const id=
            $('reverseTransactionId').value;

        const reason=
            $('reverseReason').value.trim();

        const confirmed=await AdminUI.confirm({
            title:'Reverse Journal?',
            subtitle:'A separate opposite journal will be posted.',
            message:'The original journal will remain posted in history and will be marked reversed by the opposite journal.',
            confirmText:'Reverse Journal',
            type:'danger'
        });

        if(!confirmed){
            return;
        }

        AdminUI.setLoading(
            'reverseButton',
            'Reversing...'
        );

        try{
            const response=await api(
                `/api/finance/transactions/${id}/reverse`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        transaction_date:$('reverseDate').value,
                        reason
                    })
                }
            );

            closeReverseModal();

            Toast.success(
                response.message||
                'Journal reversed successfully.'
            );

            await Promise.all([
                loadJournals(currentPage),
                loadSummary(),
                loadOptions()
            ]);
        }catch(error){
            AdminUI.showError(
                'reverseError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                'reverseButton'
            );
        }
    }
);

window.clearFilters=function(){
    $('searchInput').value='';
    $('typeFilter').value='';
    $('sourceFilter').value='';
    $('statusFilter').value='';

    selectedFrom='';
    selectedTo='';

    if(dateRangePicker){
        dateRangePicker.clear(
            false
        );
    }else{
        $('dateRangeFilter').value='';
    }

    Promise.all([
        loadJournals(1),
        loadSummary()
    ]);
};

function bindFilters(){
    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadJournals(1)
        )
    );

    [
        'typeFilter',
        'sourceFilter',
        'statusFilter'
    ].forEach(id=>{
        $(id).addEventListener(
            'change',
            ()=>loadJournals(1)
        );
    });
}

async function init(){
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

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    initRangePicker();
    bindFilters();

    await loadOptions();

    await Promise.all([
        loadJournals(),
        loadSummary()
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