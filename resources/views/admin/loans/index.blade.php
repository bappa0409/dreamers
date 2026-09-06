@extends('layouts.admin')

@section('title','Loan Management')
@section('page_title','Loan Management')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
<div class="flex items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>
<div>
<h1 class="text-base font-bold text-slate-800">Loan Management</h1>
<p class="text-xs text-slate-500">Manage member loan requests, approvals, disbursements and repayments.</p>
</div>
</div>

@if(auth()->user()->hasPermission('Loan.create'))
<button type="button" onclick="openCreateLoan()" class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-lg"></i>
New Loan
</button>
@endif
</div>

{{-- Statistics --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
@php
$stats=[
['total','Total Loans','bi-collection','border-slate-200 bg-white','text-slate-800','bg-slate-100 text-slate-500'],
['pending','Pending','bi-hourglass-split','border-amber-200 bg-amber-50/40','text-amber-700','bg-amber-100 text-amber-600'],
['approved','Approved','bi-patch-check','border-sky-200 bg-sky-50/40','text-sky-700','bg-sky-100 text-sky-600'],
['active','Outstanding','bi-cash-stack','border-indigo-200 bg-indigo-50/40','text-indigo-700','bg-indigo-100 text-indigo-600'],
['overdue','Overdue','bi-exclamation-triangle','border-red-200 bg-red-50/40','text-red-700','bg-red-100 text-red-600'],
['repaid','Repaid','bi-check2-circle','border-emerald-200 bg-emerald-50/40','text-emerald-700','bg-emerald-100 text-emerald-600'],
];
@endphp

@foreach($stats as [$key,$label,$icon,$box,$text,$iconBox])
<div class="rounded-md border p-4 {{ $box }}">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-xs text-slate-500">{{ $label }}</p>
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
<div class="flex items-center gap-2">
<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-search text-base"></i>
</div>
<div>
<p class="text-sm font-semibold text-slate-700">Search Loans</p>
<p class="hidden text-[11px] text-slate-400 sm:block">Search by loan number, member name or member code.</p>
</div>
</div>

<div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[290px_180px_auto] lg:gap-0">
<div class="relative sm:col-span-2 lg:col-span-1">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="searchInput" type="text" placeholder="Search loans..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
</div>

<select id="statusFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
<option value="">All Status</option>
<option value="pending">Pending</option>
<option value="approved">Approved</option>
<option value="active">Active</option>
<option value="overdue">Overdue</option>
<option value="defaulted">Defaulted</option>
<option value="repaid">Repaid</option>
<option value="rejected">Rejected</option>
<option value="cancelled">Cancelled</option>
</select>

<button type="button" onclick="clearFilters()" class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
<i class="bi bi-x-lg text-[10px]"></i>
Clear
</button>
</div>
</div>
</div>

{{-- Desktop Table --}}
<div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
<div class="overflow-x-auto">
<table class="w-full min-w-[1000px] text-sm">
<thead class="border-b border-slate-200 bg-slate-50">
<tr>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Loan</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Requested</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Approved</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Outstanding</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Maturity</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
</tr>
</thead>
<tbody id="loanTableBody" class="divide-y divide-slate-100">
<tr>
<td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">Loading loans...</td>
</tr>
</tbody>
</table>
</div>
</div>

{{-- Mobile Cards --}}
<div id="loanMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
<div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">Loading loans...</div>
</div>

<div id="paginationContainer" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- Create Loan Modal --}}
<div id="loanModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>
<div>
<h3 class="text-sm font-semibold text-slate-800">Create Loan Request</h3>
<p class="text-xs text-slate-500">Create a new loan request for an eligible member.</p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('loanModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="loanForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
<div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

<div id="loanError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div class="rounded-md border border-slate-200 p-4">
<div class="mb-4 flex items-center gap-3">
<div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-person"></i>
</div>
<div>
<h4 class="text-sm font-semibold text-slate-800">Loan Request</h4>
<p class="text-[11px] text-slate-400">Select member and requested loan amount.</p>
</div>
</div>

<div class="grid gap-4 md:grid-cols-2">
<div>
<label class="form-label">Member <span class="text-red-500">*</span></label>
<select id="memberId" class="app-input w-full">
<option value="">Select Member</option>
</select>
<p data-field-error="memberId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Requested Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="requestedAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8" placeholder="0.00">
</div>
<p data-field-error="requestedAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Request Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="requestDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="requestDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="mt-4">
<label class="form-label">Purpose <span class="text-red-500">*</span></label>
<textarea id="purpose" rows="4" maxlength="5000" class="app-input w-full resize-none" placeholder="Describe the purpose of this loan..."></textarea>
<p data-field-error="purpose" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="rounded-md border border-sky-200 bg-sky-50 px-3 py-3">
<div class="flex gap-2">
<i class="bi bi-info-circle mt-0.5 text-sky-600"></i>
<p class="text-[11px] leading-4 text-sky-700">Creating a request does not affect accounting. Financial entries are posted only when an approved loan is disbursed.</p>
</div>
</div>
</div>

<div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
<button type="button" onclick="AdminUI.closeModal('loanModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="createLoanButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Create Loan</button>
</div>
</form>
</div>
</div>

{{-- Manage Loan Modal --}}
<div id="manageLoanModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-bank2"></i>
</div>
<div>
<h3 id="manageLoanTitle" class="text-sm font-semibold text-slate-800">Loan Details</h3>
<p id="manageLoanSubtitle" class="text-xs text-slate-500"></p>
</div>
</div>

<button type="button" onclick="AdminUI.closeModal('manageLoanModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

<div id="manageLoanError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div id="loanSummaryCards" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6"></div>

<div class="grid gap-4 xl:grid-cols-[1.1fr_.9fr]">

<section class="rounded-md border border-slate-200 bg-white p-4">
<div class="mb-3">
<h4 class="text-sm font-semibold text-slate-800">Loan Information</h4>
<p class="text-[11px] text-slate-400">Request, approval and repayment details.</p>
</div>
<div id="loanDetails" class="grid grid-cols-2 gap-3"></div>
</section>

<section class="rounded-md border border-slate-200 bg-white p-4">
<div class="mb-3">
<h4 class="text-sm font-semibold text-slate-800">Purpose</h4>
</div>
<div id="loanPurpose" class="whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm leading-5 text-slate-600"></div>

<div id="loanRejectionWrap" class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3">
<p class="text-[10px] font-semibold uppercase tracking-wide text-red-500">Rejection Reason</p>
<p id="loanRejectionReason" class="mt-1 whitespace-pre-line text-sm text-red-700"></p>
</div>
</section>
</div>

{{-- Installments / repayments --}}
<section id="repaymentHistorySection" class="hidden rounded-md border border-slate-200 bg-white p-4">
<div class="mb-3">
<h4 class="text-sm font-semibold text-slate-800">Repayment History</h4>
<p class="text-[11px] text-slate-400">Recorded repayments for this loan.</p>
</div>
<div id="repaymentHistory" class="space-y-2"></div>
</section>

</div>

<div id="manageLoanActions" class="flex shrink-0 flex-wrap justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4"></div>
</div>
</div>

{{-- Approval Modal --}}
<div id="approvalModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Approve Loan</h3>
<p id="approvalSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('approvalModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="approvalForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">

<div id="approvalError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Approved Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="approvedAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8">
</div>
<p data-field-error="approvedAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="grid gap-4 sm:grid-cols-2">
<div>
<label class="form-label">Interest Rate (%) <span class="text-red-500">*</span></label>
<input id="interestRate" type="number" min="0" step="0.01" class="app-input w-full">
<p data-field-error="interestRate" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Duration (Months) <span class="text-red-500">*</span></label>
<input id="durationMonths" type="number" min="1" step="1" class="app-input w-full">
<p data-field-error="durationMonths" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div id="approvalPreview" class="rounded-md border border-indigo-200 bg-indigo-50 p-3"></div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('approvalModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="approveButton" type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">Approve Loan</button>
</div>
</form>
</div>
</div>

{{-- Reject Modal --}}
<div id="rejectLoanModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Reject Loan</h3>
<p class="text-xs text-slate-500">Provide a reason for rejection.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('rejectLoanModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="rejectLoanForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="rejectLoanError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<label class="form-label">Rejection Reason <span class="text-red-500">*</span></label>
<textarea id="rejectionReason" rows="4" maxlength="5000" class="app-input w-full resize-none" placeholder="Enter rejection reason..."></textarea>
<p data-field-error="rejectionReason" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('rejectLoanModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="rejectLoanButton" type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">Reject Loan</button>
</div>
</form>
</div>
</div>

{{-- Disbursement Modal --}}
<div id="disbursementModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Disburse Loan</h3>
<p id="disbursementSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('disbursementModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="disbursementForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">

<div id="disbursementError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Payment Account <span class="text-red-500">*</span></label>
<select id="disbursementAccountId" class="app-input w-full">
<option value="">Select Cash / Bank Account</option>
</select>
<p data-field-error="disbursementAccountId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Disbursement Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="disbursementDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="disbursementDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="rounded-md border border-amber-200 bg-amber-50 p-3">
<div class="flex gap-2">
<i class="bi bi-exclamation-triangle mt-0.5 text-amber-600"></i>
<p class="text-[11px] leading-4 text-amber-800">Disbursement posts the loan principal to accounting and credits the selected Cash/Bank account.</p>
</div>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('disbursementModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="disburseButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Disburse Loan</button>
</div>
</form>
</div>
</div>

{{-- Repayment Modal --}}
<div id="repaymentModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Receive Loan Repayment</h3>
<p id="repaymentSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('repaymentModal')" class="app-modal-close">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="repaymentForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">

<div id="repaymentError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Receive Account <span class="text-red-500">*</span></label>
<select id="receiveAccountId" class="app-input w-full">
<option value="">Select Cash / Bank Account</option>
</select>
<p data-field-error="receiveAccountId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Total Repayment Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="repaymentAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8">
</div>
<p data-field-error="repaymentAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Repayment Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="repaymentDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="repaymentDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('repaymentModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="repayButton" type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">Receive Repayment</button>
</div>
</form>
</div>
</div>

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>
@endsection

@push('scripts')
<script>
const API='/api/loans';
const permissions={
    create:@json(auth()->user()->hasPermission('Loan.create')),
    approve:@json(auth()->user()->hasPermission('Loan.approve')),
    disburse:@json(auth()->user()->hasPermission('Loan.disburse')),
    repay:@json(auth()->user()->hasPermission('Loan.repay'))
};

let loans=[];
let optionsData={members:[],cash_bank_accounts:[],settings:{}};
let currentLoan=null;
let currentPage=1;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const money=value=>'৳'+Number(value||0).toLocaleString('en-BD',{minimumFractionDigits:2,maximumFractionDigits:2});
const date=value=>value?AdminUI.formatDate(value):'—';

const createFieldMap={
    member_id:'memberId',
    requested_amount:'requestedAmount',
    purpose:'purpose',
    request_date:'requestDate'
};

const approvalFieldMap={
    approved_amount:'approvedAmount',
    interest_rate:'interestRate',
    duration_months:'durationMonths'
};

const disbursementFieldMap={
    disbursement_account_id:'disbursementAccountId',
    disbursement_date:'disbursementDate'
};

const repaymentFieldMap={
    receive_account_id:'receiveAccountId',
    total_amount:'repaymentAmount',
    repayment_date:'repaymentDate'
};

function today(){
    const now=new Date();
    const offset=now.getTimezoneOffset();
    return new Date(now.getTime()-offset*60000).toISOString().substring(0,10);
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
        pending:['Pending','bg-amber-50 text-amber-700','bi-hourglass-split'],
        approved:['Approved','bg-sky-50 text-sky-700','bi-patch-check'],
        active:['Active','bg-emerald-50 text-emerald-700','bi-play-circle'],
        overdue:['Overdue','bg-red-50 text-red-700','bi-exclamation-triangle'],
        defaulted:['Defaulted','bg-red-100 text-red-800','bi-exclamation-octagon'],
        repaid:['Repaid','bg-indigo-50 text-indigo-700','bi-check2-circle'],
        rejected:['Rejected','bg-rose-50 text-rose-700','bi-x-circle'],
        cancelled:['Cancelled','bg-slate-100 text-slate-500','bi-slash-circle']
    };

    const item=map[status]??[status,'bg-slate-100 text-slate-500','bi-circle'];

    return`
        <span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${item[1]}">
            <i class="bi ${item[2]}"></i>
            ${esc(item[0])}
        </span>
    `;
}

function outstandingAmount(loan){
    if(loan.outstanding_amount!==undefined&&loan.outstanding_amount!==null){
        return Number(loan.outstanding_amount);
    }

    if(loan.outstanding_principal!==undefined&&loan.outstanding_principal!==null){
        return Number(loan.outstanding_principal);
    }

    if(['active','overdue','defaulted'].includes(loan.status)){
        return Number(loan.total_payable||loan.approved_amount||0);
    }

    return 0;
}

async function loadStatistics(){
    try{
        const response=await api(`${API}/statistics`);
        const stats=response.data??{};

        ['total','pending','approved','active','overdue','repaid'].forEach(key=>{
            const element=$(`stat-${key}`);
            if(element)element.textContent=Number(stats[key]??0);
        });
    }catch(error){
        console.error(error);
    }
}

async function loadOptions(){
    try{
        const response=await api(`${API}/options`);
        optionsData=response.data??{members:[],cash_bank_accounts:[],settings:{}};

        $('memberId').innerHTML=
            '<option value="">Select Member</option>'+
            (optionsData.members??[]).map(member=>`
                <option value="${member.id}">
                    ${esc(member.user?.name??'N/A')} (${esc(member.member_code??'')})
                </option>
            `).join('');

        const accountOptions=
            '<option value="">Select Cash / Bank Account</option>'+
            (optionsData.cash_bank_accounts??[]).map(account=>`
                <option value="${account.id}">
                    ${esc(account.code)} - ${esc(account.name)}
                </option>
            `).join('');

        $('disbursementAccountId').innerHTML=accountOptions;
        $('receiveAccountId').innerHTML=accountOptions;

    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
}

async function loadLoans(page=1){
    currentPage=page;

    $('loanTableBody').innerHTML=AdminUI.loadingState('Loading loans...',8);

    $('loanMobileGrid').innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
            <span class="inline-flex items-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading loans...
            </span>
        </div>
    `;

    const params=new URLSearchParams({page,per_page:15});
    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);

    try{
        const response=await api(`${API}?${params.toString()}`);
        const paginator=response.data??{};

        loans=paginator.data??[];

        renderLoans();

        $('paginationContainer').innerHTML='';
        AdminUI.renderPagination(paginator,$('paginationContainer'),loadLoans);

    }catch(error){
        const message=AdminUI.extractError(error);

        $('loanTableBody').innerHTML=AdminUI.emptyState(message,8);
        $('loanMobileGrid').innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(message)}
            </div>
        `;
    }
}

function renderLoans(){
    if(!loans.length){
        $('loanTableBody').innerHTML=AdminUI.emptyState('No loans found.',8);

        $('loanMobileGrid').innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-bank"></i>
                </div>
                <p class="mt-3 text-base font-semibold text-slate-600">No loans found</p>
                <p class="mt-1 text-sm text-slate-400">Try changing the search or status filter.</p>
            </div>
        `;
        return;
    }

    $('loanTableBody').innerHTML=loans.map(loan=>`
        <tr class="transition hover:bg-slate-50/70">
            <td class="px-4 py-3">
                <div class="font-semibold text-xs text-slate-700">${esc(loan.loan_no)}</div>
                <div class="mt-0.5 text-[11px] text-slate-400">${date(loan.request_date)}</div>
            </td>

            <td class="px-4 py-3">
                <div class="font-semibold text-xs text-slate-700">${esc(loan.member?.user?.name??'N/A')}</div>
                <div class="mt-0.5 text-[11px] font-medium text-indigo-600">${esc(loan.member?.member_code??'')}</div>
            </td>

            <td class="px-4 py-3 text-xs text-right font-semibold text-slate-700">
                ${money(loan.requested_amount)}
            </td>

            <td class="px-4 py-3 text-right text-xs font-semibold text-indigo-700">
                ${loan.approved_amount?money(loan.approved_amount):'—'}
            </td>

            <td class="px-4 py-3 text-right font-semibold ${outstandingAmount(loan)>0?'text-red-600':'text-slate-400'}">
                ${outstandingAmount(loan)>0?money(outstandingAmount(loan)):'—'}
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                ${loan.maturity_date?date(loan.maturity_date):'—'}
            </td>

            <td class="px-4 py-3">
                ${statusBadge(loan.status)}
            </td>

            <td class="px-4 py-3 text-right">
                <button type="button" onclick="openManageLoan(${loan.id})"
                    class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                    <i class="bi bi-eye"></i>
                    Manage
                </button>
            </td>
        </tr>
    `).join('');

    $('loanMobileGrid').innerHTML=loans.map(loan=>`
        <article class="overflow-hidden rounded-md border border-slate-200 bg-white">

            <div class="border-b border-slate-100 px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-base font-bold text-slate-700">${esc(loan.loan_no)}</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">${date(loan.request_date)}</p>
                    </div>
                    ${statusBadge(loan.status)}
                </div>
            </div>

            <div class="space-y-3 p-4">
                <div>
                    <p class="text-sm font-semibold text-slate-700">${esc(loan.member?.user?.name??'N/A')}</p>
                    <p class="text-[11px] text-indigo-600">${esc(loan.member?.member_code??'')}</p>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="rounded-md bg-slate-50 p-3">
                        <p class="text-[10px] uppercase text-slate-400">Requested</p>
                        <p class="mt-1 text-base font-bold text-slate-700">${money(loan.requested_amount)}</p>
                    </div>

                    <div class="rounded-md bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase text-indigo-400">Approved</p>
                        <p class="mt-1 text-base font-bold text-indigo-700">${loan.approved_amount?money(loan.approved_amount):'—'}</p>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 pt-3 text-sm">
                    <span class="text-slate-400">Maturity</span>
                    <span class="font-medium text-slate-600">${loan.maturity_date?date(loan.maturity_date):'—'}</span>
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                <button type="button" onclick="openManageLoan(${loan.id})"
                    class="inline-flex w-full items-center justify-center gap-1 rounded-md border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700">
                    <i class="bi bi-eye"></i>
                    Manage Loan
                </button>
            </div>
        </article>
    `).join('');
}

window.openCreateLoan=function(){
    $('loanForm').reset();

    AdminUI.clearError('loanError');
    AdminUI.clearFieldErrors('loanForm');

    AdminUI.openModal('loanModal');
    window.initDatePickers?.();

    setDate('requestDate',today());
};

$('loanForm').addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('loanError');
    AdminUI.clearFieldErrors('loanForm');

    if(!AdminUI.validateForm('loanForm',{
        memberId:'Please select a member.',
        requestedAmount:'Requested amount is required.',
        requestDate:'Request date is required.',
        purpose:'Loan purpose is required.'
    }))return;

    const amount=Number($('requestedAmount').value);

    if(!Number.isFinite(amount)||amount<=0){
        AdminUI.showFieldError('requestedAmount','Requested amount must be greater than zero.');
        return;
    }

    const payload={
        member_id:Number($('memberId').value),
        requested_amount:amount,
        purpose:$('purpose').value.trim(),
        request_date:$('requestDate').value
    };

    const button=$('createLoanButton');
    AdminUI.setLoading(button,'Creating...');

    try{
        await api(API,{
            method:'POST',
            body:JSON.stringify(payload)
        });

        AdminUI.closeModal('loanModal');
        Toast.success('Loan request created successfully.');

        await Promise.all([
            loadLoans(1),
            loadStatistics()
        ]);

    }catch(error){
        if(!AdminUI.showValidationErrors('loanForm',error,createFieldMap)){
            AdminUI.showError('loanError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.openManageLoan=async function(id){
    AdminUI.clearError('manageLoanError');

    try{
        const response=await api(`${API}/${id}`);
        currentLoan=response.data;

        renderManageLoan(currentLoan);

        AdminUI.openModal('manageLoanModal');

    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

function renderManageLoan(loan){
    $('manageLoanTitle').textContent=`${loan.loan_no} — ${loan.member?.user?.name??'Member'}`;
    $('manageLoanSubtitle').textContent=`${loan.member?.member_code??''} • ${loan.status.replaceAll('_',' ')}`;

    const outstanding=outstandingAmount(loan);

    $('loanSummaryCards').innerHTML=[
        ['Requested',money(loan.requested_amount),'bg-slate-50','text-slate-700'],
        ['Approved',loan.approved_amount?money(loan.approved_amount):'—','bg-indigo-50','text-indigo-700'],
        ['Interest',loan.interest_amount?money(loan.interest_amount):'—','bg-amber-50','text-amber-700'],
        ['Total Payable',loan.total_payable?money(loan.total_payable):'—','bg-sky-50','text-sky-700'],
        ['Outstanding',outstanding>0?money(outstanding):'—','bg-red-50','text-red-700'],
        ['Repaid',loan.total_repaid?money(loan.total_repaid):(loan.status==='repaid'&&loan.total_payable?money(loan.total_payable):'—'),'bg-emerald-50','text-emerald-700']
    ].map(([label,value,bg,text])=>`
        <div class="rounded-md ${bg} p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${label}</p>
            <p class="mt-1 text-base font-bold ${text}">${value}</p>
        </div>
    `).join('');

    $('loanDetails').innerHTML=[
        ['Member',loan.member?.user?.name??'—'],
        ['Member Code',loan.member?.member_code??'—'],
        ['Request Date',date(loan.request_date)],
        ['Approved Date',date(loan.approved_at??loan.approval_date)],
        ['Interest Rate',loan.interest_rate!==null&&loan.interest_rate!==undefined?`${Number(loan.interest_rate).toFixed(2)}%`:'—'],
        ['Duration',loan.duration_months?`${loan.duration_months} months`:'—'],
        ['Disbursement Date',date(loan.disbursement_date)],
        ['Maturity Date',date(loan.maturity_date)],
        ['Status',loan.status.replaceAll('_',' ')],
        ['Loan Number',loan.loan_no]
    ].map(([label,value])=>`
        <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${esc(label)}</p>
            <p class="mt-1 break-words text-sm font-semibold capitalize text-slate-700">${esc(value)}</p>
        </div>
    `).join('');

    $('loanPurpose').textContent=loan.purpose||'No purpose provided.';

    const rejection=loan.rejection_reason??'';
    $('loanRejectionWrap').classList.toggle('hidden',!rejection);
    $('loanRejectionReason').textContent=rejection;

    renderRepayments(loan.repayments??[]);
    renderLoanActions(loan);
}

function renderRepayments(repayments){
    const section=$('repaymentHistorySection');

    if(!repayments.length){
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');

    $('repaymentHistory').innerHTML=repayments.map(payment=>`
        <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50/60 px-3 py-2.5">
            <div>
                <p class="text-sm font-semibold text-slate-700">${money(payment.total_amount??payment.amount)}</p>
                <p class="mt-0.5 text-[10px] text-slate-400">${date(payment.repayment_date??payment.payment_date)}</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
                    Received
                </span>
                <button
                    type="button"
                    onclick="downloadPdf('/api/finance/loans/${Number(currentLoan.id)}/repayments/${Number(payment.id)}/receipt','loan-repayment-${AdminUI.escapeHtml(currentLoan.loan_no??currentLoan.id)}-${Number(payment.id)}.pdf',this)"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                    title="Download PDF">
                    <i class="bi bi-file-earmark-pdf"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function renderLoanActions(loan){
    const actions=[];

    if(loan.status==='pending'){
        if(permissions.approve){
            actions.push(actionButton('Reject','bi-x-circle','red','openRejectLoanModal()'));
            actions.push(actionButton('Approve Loan','bi-check2-circle','emerald','openApprovalModal()'));
        }

        actions.unshift(actionButton('Cancel Request','bi-slash-circle','slate','cancelLoan()'));
    }

    if(loan.status==='approved'&&permissions.disburse){
        actions.push(actionButton('Disburse Loan','bi-cash-stack','indigo','openDisbursementModal()'));
    }

    if(['active','overdue','defaulted'].includes(loan.status)&&permissions.repay){
        actions.push(actionButton('Receive Repayment','bi-wallet2','emerald','openRepaymentModal()'));
    }

    $('manageLoanActions').innerHTML=actions.length
        ?actions.join('')
        :'<span class="text-sm text-slate-400">No further action available.</span>';
}

function actionButton(label,icon,color,onclick){
    const styles={
        indigo:'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700',
        emerald:'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700',
        red:'border-red-200 bg-white text-red-600 hover:bg-red-50',
        slate:'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'
    };

    return`
        <button type="button" onclick="${onclick}" class="inline-flex items-center gap-1.5 rounded-md border px-3 py-2 text-sm font-semibold transition ${styles[color]}">
            <i class="bi ${icon}"></i>
            ${label}
        </button>
    `;
}

window.openApprovalModal=function(){
    if(!currentLoan)return;

    $('approvalForm').reset();

    AdminUI.clearError('approvalError');
    AdminUI.clearFieldErrors('approvalForm');

    $('approvalSubtitle').textContent=`${currentLoan.loan_no} • Requested ${money(currentLoan.requested_amount)}`;

    $('approvedAmount').value=Number(currentLoan.requested_amount||0).toFixed(2);
    $('interestRate').value=optionsData.settings?.interest_rate??0;
    $('durationMonths').value=optionsData.settings?.duration_months??12;

    updateApprovalPreview();

    AdminUI.openModal('approvalModal');
};

['approvedAmount','interestRate','durationMonths'].forEach(id=>{
    $(id).addEventListener('input',updateApprovalPreview);
});

function updateApprovalPreview(){
    const principal=Number($('approvedAmount').value||0);
    const rate=Number($('interestRate').value||0);
    const months=Number($('durationMonths').value||0);

    const interest=principal*(rate/100);
    const total=principal+interest;

    $('approvalPreview').innerHTML=`
        <div class="grid grid-cols-3 gap-2">
            <div>
                <p class="text-[10px] uppercase text-indigo-400">Principal</p>
                <p class="mt-1 text-sm font-bold text-indigo-700">${money(principal)}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase text-indigo-400">Interest</p>
                <p class="mt-1 text-sm font-bold text-indigo-700">${money(interest)}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase text-indigo-400">Total</p>
                <p class="mt-1 text-sm font-bold text-indigo-700">${money(total)}</p>
            </div>
        </div>
        <p class="mt-2 text-[10px] text-indigo-500">Duration: ${months||0} month${months===1?'':'s'}</p>
    `;
}

$('approvalForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!currentLoan)return;

    AdminUI.clearError('approvalError');
    AdminUI.clearFieldErrors('approvalForm');

    if(!AdminUI.validateForm('approvalForm',{
        approvedAmount:'Approved amount is required.',
        interestRate:'Interest rate is required.',
        durationMonths:'Duration is required.'
    }))return;

    const amount=Number($('approvedAmount').value);
    const rate=Number($('interestRate').value);
    const duration=Number($('durationMonths').value);

    if(!Number.isFinite(amount)||amount<=0){
        AdminUI.showFieldError('approvedAmount','Approved amount must be greater than zero.');
        return;
    }

    if(amount>Number(currentLoan.requested_amount)){
        AdminUI.showFieldError('approvedAmount','Approved amount cannot exceed the requested amount.');
        return;
    }

    if(!Number.isFinite(rate)||rate<0){
        AdminUI.showFieldError('interestRate','Interest rate cannot be negative.');
        return;
    }

    if(!Number.isInteger(duration)||duration<1){
        AdminUI.showFieldError('durationMonths','Duration must be at least 1 month.');
        return;
    }

    const button=$('approveButton');
    AdminUI.setLoading(button,'Approving...');

    try{
        await api(`${API}/${currentLoan.id}/approve`,{
            method:'POST',
            body:JSON.stringify({
                approved_amount:amount,
                interest_rate:rate,
                duration_months:duration
            })
        });

        AdminUI.closeModal('approvalModal');
        Toast.success('Loan approved successfully.');

        await refreshCurrentLoan();

    }catch(error){
        if(!AdminUI.showValidationErrors('approvalForm',error,approvalFieldMap)){
            AdminUI.showError('approvalError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.openRejectLoanModal=function(){
    $('rejectLoanForm').reset();

    AdminUI.clearError('rejectLoanError');
    AdminUI.clearFieldErrors('rejectLoanForm');

    AdminUI.openModal('rejectLoanModal');
};

$('rejectLoanForm').addEventListener('submit',event=>{
    event.preventDefault();

    if(!currentLoan)return;

    AdminUI.clearError('rejectLoanError');
    AdminUI.clearFieldErrors('rejectLoanForm');

    const reason=$('rejectionReason').value.trim();

    if(!reason){
        AdminUI.showFieldError('rejectionReason','Rejection reason is required.');
        return;
    }

    AdminUI.request(`${API}/${currentLoan.id}/reject`,{
        method:'POST',
        data:{rejection_reason:reason},
        confirmation:{
            title:'Reject Loan Request?',
            message:`Reject ${currentLoan.loan_no} for ${currentLoan.member?.user?.name??'this member'}?`,
            confirmText:'Reject Loan',
            type:'danger'
        },
        successMessage:'Loan request rejected successfully.',
        onSuccess:async()=>{
            AdminUI.closeModal('rejectLoanModal');
            await refreshCurrentLoan();
        },
        onError:error=>{
            if(!AdminUI.showValidationErrors('rejectLoanForm',error,{rejection_reason:'rejectionReason'})){
                AdminUI.showError('rejectLoanError',AdminUI.extractError(error));
            }
        }
    });
});

window.cancelLoan=function(){
    if(!currentLoan)return;

    AdminUI.request(`${API}/${currentLoan.id}/cancel`,{
        method:'POST',
        data:{},
        confirmation:{
            title:'Cancel Loan Request?',
            message:`Cancel ${currentLoan.loan_no}? No accounting entry will be created.`,
            confirmText:'Cancel Request',
            type:'danger'
        },
        successMessage:'Loan request cancelled successfully.',
        onSuccess:async()=>refreshCurrentLoan()
    });
};

window.openDisbursementModal=function(){
    if(!currentLoan)return;

    $('disbursementForm').reset();

    AdminUI.clearError('disbursementError');
    AdminUI.clearFieldErrors('disbursementForm');

    $('disbursementSubtitle').textContent=
        `${currentLoan.loan_no} • ${currentLoan.approved_amount?money(currentLoan.approved_amount):''}`;

    AdminUI.openModal('disbursementModal');

    window.initDatePickers?.();
    setDate('disbursementDate',today());
};

$('disbursementForm').addEventListener('submit',event=>{
    event.preventDefault();

    if(!currentLoan)return;

    AdminUI.clearError('disbursementError');
    AdminUI.clearFieldErrors('disbursementForm');

    if(!AdminUI.validateForm('disbursementForm',{
        disbursementAccountId:'Please select a payment account.',
        disbursementDate:'Disbursement date is required.'
    }))return;

    AdminUI.request(`${API}/${currentLoan.id}/disburse`,{
        method:'POST',
        data:{
            disbursement_account_id:Number($('disbursementAccountId').value),
            disbursement_date:$('disbursementDate').value
        },
        confirmation:{
            title:'Disburse Loan?',
            message:`Disburse ${money(currentLoan.approved_amount)} to ${currentLoan.member?.user?.name??'the member'}? This will post the loan transaction to accounting.`,
            confirmText:'Disburse Loan',
            type:'success'
        },
        successMessage:'Loan disbursed successfully.',
        onSuccess:async()=>{
            AdminUI.closeModal('disbursementModal');
            await refreshCurrentLoan();
        },
        onError:error=>{
            if(!AdminUI.showValidationErrors('disbursementForm',error,disbursementFieldMap)){
                AdminUI.showError('disbursementError',AdminUI.extractError(error));
            }
        }
    });
});

window.openRepaymentModal=function(){
    if(!currentLoan)return;

    $('repaymentForm').reset();

    AdminUI.clearError('repaymentError');
    AdminUI.clearFieldErrors('repaymentForm');

    const amount=
        currentLoan.outstanding_amount??
        currentLoan.total_payable??
        currentLoan.approved_amount??
        '';

    $('repaymentAmount').value=amount;

    $('repaymentSubtitle').textContent=
        `${currentLoan.loan_no} • Outstanding ${money(outstandingAmount(currentLoan))}`;

    AdminUI.openModal('repaymentModal');

    window.initDatePickers?.();
    setDate('repaymentDate',today());
};

$('repaymentForm').addEventListener('submit',event=>{
    event.preventDefault();

    if(!currentLoan)return;

    AdminUI.clearError('repaymentError');
    AdminUI.clearFieldErrors('repaymentForm');

    if(!AdminUI.validateForm('repaymentForm',{
        receiveAccountId:'Please select a receive account.',
        repaymentAmount:'Repayment amount is required.',
        repaymentDate:'Repayment date is required.'
    }))return;

    const amount=Number($('repaymentAmount').value);

    if(!Number.isFinite(amount)||amount<=0){
        AdminUI.showFieldError('repaymentAmount','Repayment amount must be greater than zero.');
        return;
    }

    AdminUI.request(`${API}/${currentLoan.id}/repay`,{
        method:'POST',
        data:{
            receive_account_id:Number($('receiveAccountId').value),
            total_amount:amount,
            repayment_date:$('repaymentDate').value
        },
        confirmation:{
            title:'Receive Loan Repayment?',
            message:`Receive ${money(amount)} for ${currentLoan.loan_no}? The repayment will be posted to accounting.`,
            confirmText:'Receive Payment',
            type:'success'
        },
        successMessage:'Loan repayment received successfully.',
        onSuccess:async()=>{
            AdminUI.closeModal('repaymentModal');
            await refreshCurrentLoan();
        },
        onError:error=>{
            if(!AdminUI.showValidationErrors('repaymentForm',error,repaymentFieldMap)){
                AdminUI.showError('repaymentError',AdminUI.extractError(error));
            }
        }
    });
});

async function refreshCurrentLoan(){
    if(!currentLoan)return;

    const id=currentLoan.id;

    await Promise.all([
        loadLoans(currentPage),
        loadStatistics()
    ]);

    try{
        const response=await api(`${API}/${id}`);
        currentLoan=response.data;
        renderManageLoan(currentLoan);
    }catch(error){
        AdminUI.closeModal('manageLoanModal');
    }
}

window.clearFilters=function(){
    $('searchInput').value='';
    $('statusFilter').value='';
    loadLoans(1);
};

async function init(){
    if(typeof AdminUI==='undefined'||typeof api==='undefined'){
        setTimeout(init,50);
        return;
    }

    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(()=>loadLoans(1))
    );

    $('statusFilter').addEventListener(
        'change',
        ()=>loadLoans(1)
    );
window.initDatePickers?.();

    await Promise.all([
        loadOptions(),
        loadStatistics(),
        loadLoans()
    ]);
}

init();
</script>
@endpush