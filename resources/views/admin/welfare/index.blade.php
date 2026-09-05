@extends('layouts.admin')

@section('title','Welfare Fund')
@section('page_title','Welfare Fund')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
<div class="flex items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-rose-50 text-rose-600">
<i class="bi bi-heart-pulse"></i>
</div>
<div>
<h1 class="text-base font-bold text-slate-800">Welfare Fund</h1>
<p class="text-xs text-slate-500">Manage welfare funds, assistance requests, approvals and disbursements.</p>
</div>
</div>

<div class="flex flex-wrap gap-2">
@if(auth()->user()->hasPermission('Welfare.manage'))
<button type="button" onclick="openFundModal()" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
<i class="bi bi-wallet2"></i>
New Fund
</button>
@endif

@if(auth()->user()->hasPermission('Welfare.create'))
<button type="button" onclick="openRequestModal()" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-lg"></i>
New Request
</button>
@endif
</div>
</div>

{{-- Statistics --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
@php
$stats=[
['funds','Active Funds','bi-wallet2','border-slate-200 bg-white','text-slate-800','bg-slate-100 text-slate-500'],
['submitted','Pending','bi-hourglass-split','border-amber-200 bg-amber-50/40','text-amber-700','bg-amber-100 text-amber-600'],
['approved','Approved','bi-patch-check','border-indigo-200 bg-indigo-50/40','text-indigo-700','bg-indigo-100 text-indigo-600'],
['completed','Completed','bi-check2-circle','border-emerald-200 bg-emerald-50/40','text-emerald-700','bg-emerald-100 text-emerald-600'],
['total_disbursed','Disbursed','bi-cash-stack','border-rose-200 bg-rose-50/40','text-rose-700','bg-rose-100 text-rose-600'],
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

{{-- Funds --}}
<div>
<div class="mb-2 flex items-center justify-between">
<div>
<h2 class="text-base font-semibold text-slate-700">Welfare Funds</h2>
<p class="text-[11px] text-slate-400">Current fund balances and allocations.</p>
</div>
</div>

<div id="fundGrid" class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
<div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">Loading funds...</div>
</div>
</div>

{{-- Filters --}}
<div class="rounded-md border border-slate-200 bg-white p-3">
<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
<div class="flex items-center gap-2">
<div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-search text-base"></i>
</div>
<div>
<p class="text-sm font-semibold text-slate-700">Search Welfare Requests</p>
<p class="hidden text-[11px] text-slate-400 sm:block">Search by request number, member name or code.</p>
</div>
</div>

<div class="grid w-full gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[290px_180px_auto] lg:gap-0">
<div class="relative sm:col-span-2 lg:col-span-1">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="searchInput" type="text" placeholder="Search welfare requests..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
</div>

<select id="statusFilter" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
<option value="">All Status</option>
<option value="submitted">Submitted</option>
<option value="under_review">Under Review</option>
<option value="approved">Approved</option>
<option value="completed">Completed</option>
<option value="rejected">Rejected</option>
<option value="cancelled">Cancelled</option>
<option value="reversed">Reversed</option>
</select>

<button type="button" onclick="clearFilters()" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
<i class="bi bi-x-lg text-[10px]"></i>
Clear
</button>
</div>
</div>
</div>

{{-- Desktop Table --}}
<div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
<div class="overflow-x-auto">
<table class="w-full min-w-[1050px] text-sm">
<thead class="border-b border-slate-200 bg-slate-50">
<tr>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Request</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Fund</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Requested</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Approved</th>
<th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
<th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
</tr>
</thead>
<tbody id="requestTableBody" class="divide-y divide-slate-100">
<tr><td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">Loading welfare requests...</td></tr>
</tbody>
</table>
</div>
</div>

{{-- Mobile --}}
<div id="requestMobileGrid" class="grid gap-3 sm:grid-cols-2 lg:hidden">
<div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">Loading welfare requests...</div>
</div>

<div id="paginationContainer" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- Fund Modal --}}
<div id="fundModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Create Welfare Fund</h3>
<p class="text-xs text-slate-500">Create a dedicated fund for welfare assistance.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('fundModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="fundForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">
<div id="fundError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div class="grid gap-4 sm:grid-cols-2">
<div>
<label class="form-label">Fund Code <span class="text-red-500">*</span></label>
<input id="fundCode" type="text" maxlength="50" class="app-input w-full" placeholder="WF-GENERAL">
<p data-field-error="fundCode" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Fund Name <span class="text-red-500">*</span></label>
<input id="fundName" type="text" maxlength="150" class="app-input w-full" placeholder="General Welfare Fund">
<p data-field-error="fundName" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div>
<label class="form-label">Expense Posting Account <span class="text-red-500">*</span></label>
<select id="expenseAccountId" class="app-input w-full">
<option value="">Select Expense Account</option>
</select>
<p data-field-error="expenseAccountId" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('fundModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="fundButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Create Fund</button>
</div>
</form>
</div>
</div>

{{-- Allocation Modal --}}
<div id="allocationModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Add Fund Allocation</h3>
<p id="allocationSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('allocationModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="allocationForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">
<div id="allocationError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Allocation Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="allocationAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8" placeholder="0.00">
</div>
<p data-field-error="allocationAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="grid gap-4 sm:grid-cols-2">
<div>
<label class="form-label">Source <span class="text-red-500">*</span></label>
<select id="sourceType" class="app-input w-full">
<option value="association_fund">Association Fund</option>
<option value="donation">Donation</option>
<option value="special_allocation">Special Allocation</option>
<option value="other">Other</option>
</select>
<p data-field-error="sourceType" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Allocation Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="allocationDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="allocationDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('allocationModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="allocationButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Add Allocation</button>
</div>
</form>
</div>
</div>

{{-- Request Modal --}}
<div id="requestModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-md bg-rose-50 text-rose-600"><i class="bi bi-heart-pulse"></i></div>
<div>
<h3 class="text-sm font-semibold text-slate-800">New Welfare Request</h3>
<p class="text-xs text-slate-500">Submit emergency or welfare assistance for a member.</p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('requestModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="requestForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
<div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
<div id="requestError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div class="grid gap-4 md:grid-cols-2">
<div>
<label class="form-label">Member <span class="text-red-500">*</span></label>
<select id="requestMemberId" class="app-input w-full"><option value="">Select Member</option></select>
<p data-field-error="requestMemberId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Welfare Fund <span class="text-red-500">*</span></label>
<select id="welfareFundId" class="app-input w-full"><option value="">Select Fund</option></select>
<p data-field-error="welfareFundId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Assistance Type <span class="text-red-500">*</span></label>
<select id="assistanceType" class="app-input w-full">
<option value="">Select Type</option>
<option value="illness">Illness</option>
<option value="accident">Accident</option>
<option value="death">Death</option>
<option value="natural_disaster">Natural Disaster</option>
<option value="emergency">Emergency</option>
<option value="financial_hardship">Financial Hardship</option>
<option value="other">Other</option>
</select>
<p data-field-error="assistanceType" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Requested Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="requestAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8" placeholder="0.00">
</div>
<p data-field-error="requestAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div>
<label class="form-label">Reason <span class="text-red-500">*</span></label>
<textarea id="requestReason" rows="4" maxlength="5000" class="app-input w-full resize-none" placeholder="Describe the reason for requesting assistance..."></textarea>
<p data-field-error="requestReason" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('requestModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="requestButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Create Request</button>
</div>
</form>
</div>
</div>

{{-- Manage Modal --}}
<div id="manageModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">

<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600"><i class="bi bi-heart-pulse"></i></div>
<div>
<h3 id="manageTitle" class="text-sm font-semibold text-slate-800">Welfare Request</h3>
<p id="manageSubtitle" class="text-xs text-slate-500"></p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('manageModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
<div id="manageError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div id="summaryCards" class="grid grid-cols-2 gap-3 md:grid-cols-4"></div>

<div class="grid gap-4 xl:grid-cols-[1.1fr_.9fr]">
<section class="rounded-md border border-slate-200 p-4">
<h4 class="text-sm font-semibold text-slate-800">Request Information</h4>
<div id="requestDetails" class="mt-3 grid grid-cols-2 gap-3"></div>
</section>

<section class="rounded-md border border-slate-200 p-4">
<h4 class="text-sm font-semibold text-slate-800">Reason</h4>
<div id="manageReason" class="mt-3 whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm leading-5 text-slate-600"></div>

<div id="manageRejectionWrap" class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3">
<p class="text-[10px] font-semibold uppercase text-red-500">Rejection Reason</p>
<p id="manageRejectionReason" class="mt-1 whitespace-pre-line text-sm text-red-700"></p>
</div>
</section>
</div>
</div>

<div id="manageActions" class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4"></div>
</div>
</div>

{{-- Review Modal --}}
<div id="reviewModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg rounded-md bg-white">
<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Start Review</h3>
<p class="text-xs text-slate-500">Add an optional review note.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('reviewModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="reviewForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="reviewError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>
<label class="form-label">Review Note</label>
<textarea id="reviewNote" rows="4" maxlength="5000" class="app-input w-full resize-none"></textarea>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('reviewModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="reviewButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Start Review</button>
</div>
</form>
</div>
</div>

{{-- Approve Modal --}}
<div id="approveModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg rounded-md bg-white">
<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Approve Welfare Request</h3>
<p id="approveSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('approveModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="approveForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">
<div id="approveError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Approved Amount <span class="text-red-500">*</span></label>
<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-400">৳</span>
<input id="approvedAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8">
</div>
<p data-field-error="approvedAmount" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('approveModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="approveButton" type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Approve</button>
</div>
</form>
</div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg rounded-md bg-white">
<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Reject Welfare Request</h3>
<p class="text-xs text-slate-500">Provide a reason for rejection.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('rejectModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="rejectForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="rejectError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>
<label class="form-label">Rejection Reason <span class="text-red-500">*</span></label>
<textarea id="rejectionReason" rows="4" maxlength="5000" class="app-input w-full resize-none"></textarea>
<p data-field-error="rejectionReason" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('rejectModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="rejectButton" type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white">Reject</button>
</div>
</form>
</div>
</div>

{{-- Disburse Modal --}}
<div id="disburseModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg rounded-md bg-white">
<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Disburse Welfare Assistance</h3>
<p id="disburseSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('disburseModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="disburseForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">
<div id="disburseError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Payment Account <span class="text-red-500">*</span></label>
<select id="paymentAccountId" class="app-input w-full"><option value="">Select Cash / Bank Account</option></select>
<p data-field-error="paymentAccountId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Disbursement Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="disbursementDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="disbursementDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('disburseModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="disburseButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Disburse</button>
</div>
</form>
</div>
</div>

{{-- Reverse Modal --}}
<div id="reverseModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg rounded-md bg-white">
<div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Reverse Disbursement</h3>
<p class="text-xs text-slate-500">Provide the reason for reversal.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('reverseModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="reverseForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="reverseError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>
<label class="form-label">Reversal Reason <span class="text-red-500">*</span></label>
<textarea id="reverseReason" rows="4" maxlength="5000" class="app-input w-full resize-none"></textarea>
<p data-field-error="reverseReason" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('reverseModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="reverseButton" type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white">Reverse</button>
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
const API='/api/welfare';
const permissions={
manage:@json(auth()->user()->hasPermission('Welfare.manage')),
create:@json(auth()->user()->hasPermission('Welfare.create')),
review:@json(auth()->user()->hasPermission('Welfare.review')),
approve:@json(auth()->user()->hasPermission('Welfare.approve')),
disburse:@json(auth()->user()->hasPermission('Welfare.disburse'))
};

let options={members:[],funds:[],expense_accounts:[],cash_bank_accounts:[]};
let funds=[];
let requests=[];
let currentRequest=null;
let currentFund=null;
let currentPage=1;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const money=value=>'৳'+Number(value||0).toLocaleString('en-BD',{minimumFractionDigits:2,maximumFractionDigits:2});
const date=value=>value?AdminUI.formatDate(value):'—';

const fieldMaps={
fund:{code:'fundCode',name:'fundName',expense_account_id:'expenseAccountId'},
allocation:{amount:'allocationAmount',source_type:'sourceType',allocation_date:'allocationDate'},
request:{member_id:'requestMemberId',welfare_fund_id:'welfareFundId',assistance_type:'assistanceType',requested_amount:'requestAmount',reason:'requestReason'},
approve:{approved_amount:'approvedAmount'},
disburse:{payment_account_id:'paymentAccountId',disbursement_date:'disbursementDate'}
};

function today(){
const now=new Date();
return new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().substring(0,10);
}

function setDate(id,value){
const el=$(id);
if(!el)return;
const v=value?String(value).substring(0,10):'';
el.value=v;
if(el._flatpickr){
v?el._flatpickr.setDate(v,false,'Y-m-d'):el._flatpickr.clear();
}
}

function statusBadge(status){
const map={
submitted:['Submitted','bg-sky-50 text-sky-700','bi-send'],
under_review:['Under Review','bg-indigo-50 text-indigo-700','bi-search'],
approved:['Approved','bg-amber-50 text-amber-700','bi-patch-check'],
completed:['Completed','bg-emerald-50 text-emerald-700','bi-check2-circle'],
rejected:['Rejected','bg-red-50 text-red-600','bi-x-circle'],
cancelled:['Cancelled','bg-slate-100 text-slate-500','bi-slash-circle'],
reversed:['Reversed','bg-rose-50 text-rose-700','bi-arrow-counterclockwise']
};
const item=map[status]??[status,'bg-slate-100 text-slate-500','bi-circle'];
return`<span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${item[1]}"><i class="bi ${item[2]}"></i>${esc(item[0])}</span>`;
}

function assistanceLabel(type){
return{
illness:'Illness',
accident:'Accident',
death:'Death',
natural_disaster:'Natural Disaster',
emergency:'Emergency',
financial_hardship:'Financial Hardship',
other:'Other'
}[type]??type;
}

async function loadOptions(){
try{
const response=await api(`${API}/options`);
options=response.data??{};

$('requestMemberId').innerHTML='<option value="">Select Member</option>'+
(options.members??[]).map(m=>`<option value="${m.id}">${esc(m.user?.name??'N/A')} (${esc(m.member_code??'')})</option>`).join('');

$('expenseAccountId').innerHTML='<option value="">Select Expense Account</option>'+
(options.expense_accounts??[]).map(a=>`<option value="${a.id}">${esc(a.code)} - ${esc(a.name)}</option>`).join('');

$('paymentAccountId').innerHTML='<option value="">Select Cash / Bank Account</option>'+
(options.cash_bank_accounts??[]).map(a=>`<option value="${a.id}">${esc(a.code)} - ${esc(a.name)}</option>`).join('');

refreshFundSelect();
}catch(error){
Toast.error(AdminUI.extractError(error));
}
}

function refreshFundSelect(){
$('welfareFundId').innerHTML='<option value="">Select Fund</option>'+
(options.funds??funds).filter(f=>f.is_active!==false).map(f=>`<option value="${f.id}">${esc(f.name)}</option>`).join('');
}

async function loadStats(){
try{
const response=await api(`${API}/statistics`);
Object.entries(response.data??{}).forEach(([key,value])=>{
const el=$(`stat-${key}`);
if(el)el.textContent=key==='total_disbursed'?money(value):Number(value??0);
});
}catch(error){
console.error(error);
}
}

async function loadFunds(){
try{
const response=await api(`${API}/funds`);
funds=response.data??[];
options.funds=funds;
refreshFundSelect();
renderFunds();
}catch(error){
$('fundGrid').innerHTML=`<div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">${esc(AdminUI.extractError(error))}</div>`;
}
}

function renderFunds(){
if(!funds.length){
$('fundGrid').innerHTML=`<div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">No welfare funds found.</div>`;
return;
}

$('fundGrid').innerHTML=funds.map(f=>`
<div class="overflow-hidden rounded-md border border-slate-200 bg-white">
<div class="border-b border-slate-100 px-4 py-3">
<div class="flex items-start justify-between gap-3">
<div>
<h3 class="text-base font-bold text-slate-800">${esc(f.name)}</h3>
<p class="mt-0.5 font-mono text-[10px] font-semibold text-indigo-500">${esc(f.code)}</p>
</div>
<span class="rounded-md px-2 py-1 text-[10px] font-semibold ${f.is_active?'bg-emerald-50 text-emerald-700':'bg-slate-100 text-slate-500'}">${f.is_active?'Active':'Inactive'}</span>
</div>
</div>

<div class="grid grid-cols-2 gap-2 p-4">
${fundBox('Allocated',money(f.allocated_amount),'text-indigo-700')}
${fundBox('Available',money(f.available_amount),'text-emerald-700')}
${fundBox('Committed',money(f.committed_amount),'text-amber-700')}
${fundBox('Spent',money(f.spent_amount),'text-rose-700')}
</div>

${permissions.manage?`
<div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
<button type="button" onclick="openAllocationModal(${f.id})" class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-50">
<i class="bi bi-plus-circle"></i>
Add Allocation
</button>
</div>`:''}
</div>
`).join('');
}

function fundBox(label,value,text){
return`<div class="rounded-md bg-slate-50 p-3"><p class="text-[10px] uppercase text-slate-400">${label}</p><p class="mt-1 text-base font-bold ${text}">${value}</p></div>`;
}

async function loadRequests(page=1){
currentPage=page;
$('requestTableBody').innerHTML=AdminUI.loadingState('Loading welfare requests...',8);

const params=new URLSearchParams({page,per_page:15});
const search=$('searchInput').value.trim();
const status=$('statusFilter').value;
if(search)params.set('search',search);
if(status)params.set('status',status);

try{
const response=await api(`${API}?${params.toString()}`);
const paginator=response.data??{};
requests=paginator.data??[];
renderRequests();
$('paginationContainer').innerHTML='';
AdminUI.renderPagination(paginator,$('paginationContainer'),loadRequests);
}catch(error){
const message=AdminUI.extractError(error);
$('requestTableBody').innerHTML=AdminUI.emptyState(message,8);
$('requestMobileGrid').innerHTML=`<div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">${esc(message)}</div>`;
}
}

function renderRequests(){
if(!requests.length){
$('requestTableBody').innerHTML=AdminUI.emptyState('No welfare requests found.',8);
$('requestMobileGrid').innerHTML=`<div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">No welfare requests found.</div>`;
return;
}

$('requestTableBody').innerHTML=requests.map(r=>`
<tr class="transition hover:bg-slate-50/70">
<td class="px-4 py-3">
<div class="font-semibold text-xs text-slate-700">${esc(r.request_no)}</div>
<div class="mt-0.5 text-[11px] text-slate-400">${date(r.request_date??r.created_at)}</div>
</td>
<td class="px-4 py-3">
<div class="font-semibold text-xs text-slate-700">${esc(r.member?.user?.name??'N/A')}</div>
<div class="mt-0.5 text-[11px] font-medium text-indigo-600">${esc(r.member?.member_code??'')}</div>
</td>
<td class="px-4 py-3 text-xs text-slate-600">${esc(r.fund?.name??'—')}</td>
<td class="px-4 py-3 text-xs text-slate-600">${esc(assistanceLabel(r.assistance_type))}</td>
<td class="px-4 py-3 text-xs text-right font-semibold text-slate-700">${money(r.requested_amount)}</td>
<td class="px-4 py-3 text-right text-xs font-semibold text-indigo-700">${r.approved_amount?money(r.approved_amount):'—'}</td>
<td class="px-4 py-3">${statusBadge(r.status)}</td>
<td class="px-4 py-3 text-right">
<button type="button" onclick="openManageModal(${r.id})" class="inline-flex items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
<i class="bi bi-eye"></i>Manage
</button>
</td>
</tr>`).join('');

$('requestMobileGrid').innerHTML=requests.map(r=>`
<article class="overflow-hidden rounded-md border border-slate-200 bg-white">
<div class="border-b border-slate-100 px-4 py-3">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-base font-bold text-slate-700">${esc(r.request_no)}</p>
<p class="mt-0.5 text-[11px] text-slate-400">${esc(assistanceLabel(r.assistance_type))}</p>
</div>
${statusBadge(r.status)}
</div>
</div>

<div class="space-y-3 p-4">
<div>
<p class="text-sm font-semibold text-slate-700">${esc(r.member?.user?.name??'N/A')}</p>
<p class="text-[11px] text-indigo-600">${esc(r.member?.member_code??'')}</p>
</div>
<div class="grid grid-cols-2 gap-2">
${fundBox('Requested',money(r.requested_amount),'text-slate-700')}
${fundBox('Approved',r.approved_amount?money(r.approved_amount):'—','text-indigo-700')}
</div>
</div>

<div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
<button type="button" onclick="openManageModal(${r.id})" class="inline-flex w-full items-center justify-center gap-1 rounded-md border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700"><i class="bi bi-eye"></i>Manage Request</button>
</div>
</article>`).join('');
}

window.openFundModal=function(){
$('fundForm').reset();
AdminUI.clearError('fundError');
AdminUI.clearFieldErrors('fundForm');
$('fundCode').value='WF-GENERAL';
$('fundName').value='General Welfare Fund';

const preferred=(options.expense_accounts??[]).find(a=>a.sub_type==='welfare_assistance_expense')??options.expense_accounts?.[0];
if(preferred)$('expenseAccountId').value=preferred.id;

AdminUI.openModal('fundModal');
};

$('fundForm').addEventListener('submit',async e=>{
e.preventDefault();
AdminUI.clearError('fundError');
AdminUI.clearFieldErrors('fundForm');

if(!AdminUI.validateForm('fundForm',{
fundCode:'Fund code is required.',
fundName:'Fund name is required.',
expenseAccountId:'Expense posting account is required.'
}))return;

const button=$('fundButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(`${API}/funds`,{
method:'POST',
body:JSON.stringify({
code:$('fundCode').value.trim(),
name:$('fundName').value.trim(),
expense_account_id:Number($('expenseAccountId').value),
is_active:true
})
});

AdminUI.closeModal('fundModal');
Toast.success('Welfare fund created successfully.');
await Promise.all([loadFunds(),loadStats()]);
}catch(error){
if(!AdminUI.showValidationErrors('fundForm',error,fieldMaps.fund)){
AdminUI.showError('fundError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openAllocationModal=function(id){
currentFund=funds.find(f=>Number(f.id)===Number(id));
if(!currentFund)return;

$('allocationForm').reset();
AdminUI.clearError('allocationError');
AdminUI.clearFieldErrors('allocationForm');
$('allocationSubtitle').textContent=`${currentFund.name} • Available ${money(currentFund.available_amount)}`;

AdminUI.openModal('allocationModal');
window.initDatePickers?.();
setDate('allocationDate',today());
};

$('allocationForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!currentFund)return;

AdminUI.clearError('allocationError');
AdminUI.clearFieldErrors('allocationForm');

if(!AdminUI.validateForm('allocationForm',{
allocationAmount:'Allocation amount is required.',
sourceType:'Source type is required.',
allocationDate:'Allocation date is required.'
}))return;

const amount=Number($('allocationAmount').value);
if(!Number.isFinite(amount)||amount<=0){
AdminUI.showFieldError('allocationAmount','Allocation amount must be greater than zero.');
return;
}

const button=$('allocationButton');
AdminUI.setLoading(button,'Allocating...');

try{
await api(`${API}/funds/${currentFund.id}/allocations`,{
method:'POST',
body:JSON.stringify({
amount,
allocation_date:$('allocationDate').value,
source_type:$('sourceType').value
})
});

AdminUI.closeModal('allocationModal');
Toast.success('Fund allocation added successfully.');
await Promise.all([loadFunds(),loadStats()]);
}catch(error){
if(!AdminUI.showValidationErrors('allocationForm',error,fieldMaps.allocation)){
AdminUI.showError('allocationError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openRequestModal=function(){
$('requestForm').reset();
AdminUI.clearError('requestError');
AdminUI.clearFieldErrors('requestForm');
AdminUI.openModal('requestModal');
};

$('requestForm').addEventListener('submit',async e=>{
e.preventDefault();

AdminUI.clearError('requestError');
AdminUI.clearFieldErrors('requestForm');

if(!AdminUI.validateForm('requestForm',{
requestMemberId:'Please select a member.',
welfareFundId:'Please select a welfare fund.',
assistanceType:'Assistance type is required.',
requestAmount:'Requested amount is required.',
requestReason:'Reason is required.'
}))return;

const amount=Number($('requestAmount').value);
if(!Number.isFinite(amount)||amount<=0){
AdminUI.showFieldError('requestAmount','Requested amount must be greater than zero.');
return;
}

const button=$('requestButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(API,{
method:'POST',
body:JSON.stringify({
member_id:Number($('requestMemberId').value),
welfare_fund_id:Number($('welfareFundId').value),
assistance_type:$('assistanceType').value,
requested_amount:amount,
reason:$('requestReason').value.trim()
})
});

AdminUI.closeModal('requestModal');
Toast.success('Welfare request created successfully.');
await Promise.all([loadRequests(1),loadStats(),loadFunds()]);
}catch(error){
if(!AdminUI.showValidationErrors('requestForm',error,fieldMaps.request)){
AdminUI.showError('requestError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openManageModal=async function(id){
try{
const response=await api(`${API}/${id}`);
currentRequest=response.data;
renderManage(currentRequest);
AdminUI.openModal('manageModal');
}catch(error){
Toast.error(AdminUI.extractError(error));
}
};

function renderManage(r){
$('manageTitle').textContent=`${r.request_no} — ${r.member?.user?.name??'Member'}`;
$('manageSubtitle').textContent=`${assistanceLabel(r.assistance_type)} • ${r.status.replaceAll('_',' ')}`;

$('summaryCards').innerHTML=[
['Requested',money(r.requested_amount),'bg-slate-50','text-slate-700'],
['Approved',r.approved_amount?money(r.approved_amount):'—','bg-indigo-50','text-indigo-700'],
['Fund Available',money(r.fund?.available_amount??0),'bg-emerald-50','text-emerald-700'],
['Status',r.status.replaceAll('_',' '),'bg-amber-50','text-amber-700']
].map(([label,value,bg,text])=>`<div class="rounded-md ${bg} p-3"><p class="text-[10px] uppercase text-slate-400">${label}</p><p class="mt-1 text-base font-bold capitalize ${text}">${esc(value)}</p></div>`).join('');

$('requestDetails').innerHTML=[
['Member',r.member?.user?.name??'—'],
['Member Code',r.member?.member_code??'—'],
['Fund',r.fund?.name??'—'],
['Assistance Type',assistanceLabel(r.assistance_type)],
['Request Date',date(r.request_date??r.created_at)],
['Approved Date',date(r.approved_at)],
['Completed Date',date(r.completed_at)],
['Status',r.status.replaceAll('_',' ')]
].map(([label,value])=>`<div class="rounded-md border border-slate-200 bg-slate-50 p-3"><p class="text-[10px] uppercase text-slate-400">${esc(label)}</p><p class="mt-1 text-sm font-semibold capitalize text-slate-700">${esc(value)}</p></div>`).join('');

$('manageReason').textContent=r.reason??'—';

const rejection=r.rejection_reason??'';
$('manageRejectionWrap').classList.toggle('hidden',!rejection);
$('manageRejectionReason').textContent=rejection;

renderActions(r);
}

function renderActions(r){
const actions=[];

if(permissions.review&&r.status==='submitted'){
actions.push(actionButton('Cancel','bi-slash-circle','slate','cancelRequest()'));
actions.push(actionButton('Start Review','bi-search','indigo','openReviewModal()'));
}

if(permissions.approve&&r.status==='under_review'){
actions.push(actionButton('Reject','bi-x-circle','red','openRejectModal()'));
actions.push(actionButton('Approve','bi-check2-circle','emerald','openApproveModal()'));
}

if(permissions.disburse&&r.status==='approved'){
actions.push(actionButton('Disburse','bi-cash-stack','indigo','openDisburseModal()'));
}

if(permissions.manage&&r.status==='completed'){
actions.push(actionButton('Reverse','bi-arrow-counterclockwise','red','openReverseModal()'));
}

$('manageActions').innerHTML=actions.length?actions.join(''):'<span class="text-sm text-slate-400">No further action available.</span>';
}

function actionButton(label,icon,color,handler){
const styles={
indigo:'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700',
emerald:'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700',
red:'border-red-200 bg-white text-red-600 hover:bg-red-50',
slate:'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'
};
return`<button type="button" onclick="${handler}" class="inline-flex items-center gap-1.5 rounded-md border px-3 py-2 text-sm font-semibold transition ${styles[color]}"><i class="bi ${icon}"></i>${label}</button>`;
}

window.openReviewModal=function(){
$('reviewForm').reset();
AdminUI.clearError('reviewError');
AdminUI.openModal('reviewModal');
};

$('reviewForm').addEventListener('submit',e=>{
e.preventDefault();
if(!currentRequest)return;

AdminUI.request(`${API}/${currentRequest.id}/review`,{
method:'POST',
data:{review_note:$('reviewNote').value.trim()||null},
confirmation:{
title:'Start Welfare Review?',
message:`Start reviewing ${currentRequest.request_no}?`,
confirmText:'Start Review',
type:'primary'
},
successMessage:'Welfare request moved to review.',
onSuccess:async()=>{
AdminUI.closeModal('reviewModal');
await refreshCurrent();
}
});
});

window.openApproveModal=function(){
$('approveForm').reset();
AdminUI.clearError('approveError');
AdminUI.clearFieldErrors('approveForm');
$('approvedAmount').value=currentRequest?.requested_amount??'';
$('approveSubtitle').textContent=`Requested ${money(currentRequest?.requested_amount)}`;
AdminUI.openModal('approveModal');
};

$('approveForm').addEventListener('submit',e=>{
e.preventDefault();
if(!currentRequest)return;

AdminUI.clearFieldErrors('approveForm');

if(!AdminUI.validateForm('approveForm',{
approvedAmount:'Approved amount is required.'
}))return;

const amount=Number($('approvedAmount').value);
if(!Number.isFinite(amount)||amount<=0){
AdminUI.showFieldError('approvedAmount','Approved amount must be greater than zero.');
return;
}

if(amount>Number(currentRequest.requested_amount)){
AdminUI.showFieldError('approvedAmount','Approved amount cannot exceed requested amount.');
return;
}

AdminUI.request(`${API}/${currentRequest.id}/approve`,{
method:'POST',
data:{approved_amount:amount},
confirmation:{
title:'Approve Welfare Request?',
message:`Approve ${money(amount)} for ${currentRequest.member?.user?.name??'this member'}?`,
confirmText:'Approve Request',
type:'success'
},
successMessage:'Welfare request approved successfully.',
onSuccess:async()=>{
AdminUI.closeModal('approveModal');
await refreshCurrent();
},
onError:error=>{
if(!AdminUI.showValidationErrors('approveForm',error,fieldMaps.approve)){
AdminUI.showError('approveError',AdminUI.extractError(error));
}
}
});
});

window.openRejectModal=function(){
$('rejectForm').reset();
AdminUI.clearError('rejectError');
AdminUI.clearFieldErrors('rejectForm');
AdminUI.openModal('rejectModal');
};

$('rejectForm').addEventListener('submit',e=>{
e.preventDefault();
if(!currentRequest)return;

const reason=$('rejectionReason').value.trim();
if(!reason){
AdminUI.showFieldError('rejectionReason','Rejection reason is required.');
return;
}

AdminUI.request(`${API}/${currentRequest.id}/reject`,{
method:'POST',
data:{rejection_reason:reason},
confirmation:{
title:'Reject Welfare Request?',
message:`Reject ${currentRequest.request_no}?`,
confirmText:'Reject Request',
type:'danger'
},
successMessage:'Welfare request rejected.',
onSuccess:async()=>{
AdminUI.closeModal('rejectModal');
await refreshCurrent();
}
});
});

window.openDisburseModal=function(){
$('disburseForm').reset();
AdminUI.clearError('disburseError');
AdminUI.clearFieldErrors('disburseForm');
$('disburseSubtitle').textContent=`Approved ${money(currentRequest?.approved_amount)}`;
AdminUI.openModal('disburseModal');
window.initDatePickers?.();
setDate('disbursementDate',today());
};

$('disburseForm').addEventListener('submit',e=>{
e.preventDefault();
if(!currentRequest)return;

if(!AdminUI.validateForm('disburseForm',{
paymentAccountId:'Please select a payment account.',
disbursementDate:'Disbursement date is required.'
}))return;

AdminUI.request(`${API}/${currentRequest.id}/disburse`,{
method:'POST',
data:{
payment_account_id:Number($('paymentAccountId').value),
disbursement_date:$('disbursementDate').value
},
confirmation:{
title:'Disburse Welfare Assistance?',
message:`Disburse ${money(currentRequest.approved_amount)} from the welfare fund? This will post the financial transaction.`,
confirmText:'Disburse',
type:'success'
},
successMessage:'Welfare assistance disbursed successfully.',
onSuccess:async()=>{
AdminUI.closeModal('disburseModal');
await refreshCurrent();
},
onError:error=>{
if(!AdminUI.showValidationErrors('disburseForm',error,fieldMaps.disburse)){
AdminUI.showError('disburseError',AdminUI.extractError(error));
}
}
});
});

window.cancelRequest=function(){
if(!currentRequest)return;

AdminUI.request(`${API}/${currentRequest.id}/cancel`,{
method:'POST',
data:{},
confirmation:{
title:'Cancel Welfare Request?',
message:`Cancel ${currentRequest.request_no}?`,
confirmText:'Cancel Request',
type:'danger'
},
successMessage:'Welfare request cancelled.',
onSuccess:async()=>refreshCurrent()
});
};

window.openReverseModal=function(){
$('reverseForm').reset();
AdminUI.clearError('reverseError');
AdminUI.clearFieldErrors('reverseForm');
AdminUI.openModal('reverseModal');
};

$('reverseForm').addEventListener('submit',e=>{
e.preventDefault();
if(!currentRequest)return;

const reason=$('reverseReason').value.trim();
if(!reason){
AdminUI.showFieldError('reverseReason','Reversal reason is required.');
return;
}

AdminUI.request(`${API}/${currentRequest.id}/reverse`,{
method:'POST',
data:{reason},
confirmation:{
title:'Reverse Welfare Disbursement?',
message:'Reverse this completed welfare disbursement? The accounting reversal will also be posted.',
confirmText:'Reverse Disbursement',
type:'danger'
},
successMessage:'Welfare disbursement reversed successfully.',
onSuccess:async()=>{
AdminUI.closeModal('reverseModal');
await refreshCurrent();
}
});
});

async function refreshCurrent(){
if(!currentRequest)return;
const id=currentRequest.id;

await Promise.all([
loadStats(),
loadFunds(),
loadRequests(currentPage)
]);

try{
const response=await api(`${API}/${id}`);
currentRequest=response.data;
renderManage(currentRequest);
}catch{
AdminUI.closeModal('manageModal');
}
}

window.clearFilters=function(){
$('searchInput').value='';
$('statusFilter').value='';
loadRequests(1);
};

async function init(){
if(typeof AdminUI==='undefined'||typeof api==='undefined'){
setTimeout(init,50);
return;
}

$('searchInput').addEventListener('input',AdminUI.debounce(()=>loadRequests(1)));
$('statusFilter').addEventListener('change',()=>loadRequests(1));
window.initDatePickers?.();

await Promise.all([
loadOptions(),
loadStats(),
loadFunds(),
loadRequests()
]);
}

init();
</script>
@endpush