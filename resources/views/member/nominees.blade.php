@extends('layouts.member')

@section('title','Nominees')
@section('page-title','Nominees')

@section('content')
<div class="space-y-3">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
<i class="bi bi-person-heart"></i>
</div>

<div>
<h1 class="text-base font-semibold tracking-tight text-slate-800">Nominees</h1>
<p class="mt-0.5 text-sm text-slate-500">Manage nominees, allocation, identity information and verification status.</p>
</div>
</div>

<div class="flex flex-wrap items-center gap-2">
<button type="button" onclick="loadNominees()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
<i class="bi bi-arrow-clockwise"></i>
Refresh
</button>

<button type="button" onclick="openNomineeModal()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-circle"></i>
Add Nominee
</button>
</div>
</div>

{{-- Information --}}
<div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-indigo-800">Nominee Allocation</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
You may add multiple nominees. The combined allocation of all active nominees cannot exceed 100%. Identity information and supporting documents may be submitted for verification.
</p>
</div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Total Nominees</p>
<p id="summaryTotal" class="text-xl font-bold text-slate-800">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-people"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Verified</p>
<p id="summaryVerified" class="text-xl font-bold text-emerald-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-patch-check"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">Allocated</p>
<p id="summaryAllocation" class="mt-2 text-xl font-bold text-sky-700">0%</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
<i class="bi bi-pie-chart"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">Remaining</p>
<p id="summaryRemaining" class="text-xl font-bold text-amber-700">100%</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-percent"></i>
</div>
</div>
</div>

</div>

{{-- Allocation Notice --}}
<div id="allocationNotice"></div>

{{-- Filters --}}
<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

<div class="lg:col-span-7">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>

<div class="relative">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>

<input id="nomineeSearch" type="text" placeholder="Search nominee, relationship, phone or identity..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>
</div>

<div class="lg:col-span-3">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Verification</label>

<select id="verificationFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
<option value="">All Status</option>
<option value="unverified">Unverified</option>
<option value="pending">Pending</option>
<option value="verified">Verified</option>
<option value="rejected">Rejected</option>
</select>
</div>

<div class="lg:col-span-2">
<button type="button" onclick="clearNomineeFilters()" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
Clear
</button>
</div>

</div>
</div>

{{-- Nominee Portfolio --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-heart"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Nominee Portfolio</h2>
<p class="text-[11px] text-slate-500">Your nominee, allocation and verification records</p>
</div>
</div>

<div id="nomineeList" class="grid gap-3 p-4 lg:grid-cols-2">
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-500">Loading nominees...</p>
</div>
</div>

<div id="nomineePagination"></div>
</div>

</div>

{{-- Add / Edit Nominee Modal --}}
<div id="nomineeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

<div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-heart"></i>
</div>

<div>
<h2 id="modalTitle" class="text-base font-bold text-slate-800">Add Nominee</h2>
<p id="modalSubtitle" class="text-[11px] text-slate-500">Add nominee information and allocation.</p>
</div>
</div>

<button type="button" onclick="closeNomineeModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="nomineeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">

<input id="nomineeId" type="hidden">

<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

<div id="nomineeError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

{{-- Personal --}}
<section class="rounded-lg border border-slate-200 bg-white p-4">

<div class="mb-4">
<h3 class="text-sm font-semibold text-slate-800">Personal Information</h3>
<p class="text-[11px] text-slate-500">Basic information about your nominee.</p>
</div>

<div class="grid gap-4 sm:grid-cols-2">

<div>
<label class="form-label">Full Name <span class="text-red-500">*</span></label>
<input id="name" type="text" maxlength="150" class="app-input w-full" placeholder="Nominee full name">
<p data-field-error="name" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Relationship <span class="text-red-500">*</span></label>
<input id="relationship" type="text" maxlength="80" class="app-input w-full" placeholder="Spouse / Son / Daughter">
<p data-field-error="relationship" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Father / Husband Name</label>
<input id="fatherOrHusbandName" type="text" maxlength="150" class="app-input w-full" placeholder="Father or husband name">
<p data-field-error="fatherOrHusbandName" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Mother's Name</label>
<input id="motherName" type="text" maxlength="150" class="app-input w-full" placeholder="Mother's name">
<p data-field-error="motherName" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Phone</label>
<input id="phone" type="text" maxlength="30" class="app-input w-full" placeholder="Phone number">
<p data-field-error="phone" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Date of Birth</label>

<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>

<input id="dob" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>

<p data-field-error="dob" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Gender</label>
<select id="gender" class="app-input w-full">
<option value="">Select Gender</option>
<option value="male">Male</option>
<option value="female">Female</option>
<option value="other">Other</option>
</select>
<p data-field-error="gender" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Profession</label>
<input id="profession" type="text" maxlength="150" class="app-input w-full" placeholder="Profession">
<p data-field-error="profession" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="mt-4">
<label class="form-label">Present Address</label>
<textarea id="address" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Nominee address"></textarea>
<p data-field-error="address" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="mt-4">
<label class="form-label">Permanent Address</label>
<textarea id="permanentAddress" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Nominee permanent address"></textarea>
<p data-field-error="permanentAddress" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</section>

{{-- Identity --}}
<section class="rounded-lg border border-slate-200 bg-white p-4">

<div class="mb-4">
<h3 class="text-sm font-semibold text-slate-800">Identity Information</h3>
<p class="text-[11px] text-slate-500">Provide identity details for nominee verification.</p>
</div>

<div class="grid gap-4 sm:grid-cols-2">

<div>
<label class="form-label">Identity Type</label>

<select id="identityType" class="app-input w-full">
<option value="">Select Identity Type</option>
<option value="nid">National ID (NID)</option>
<option value="birth_certificate">Birth Certificate</option>
<option value="passport">Passport</option>
<option value="other">Other</option>
</select>

<p data-field-error="identityType" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Identity Number</label>

<input id="identityNumber" type="text" maxlength="100" class="app-input w-full" placeholder="Identity number">

<p data-field-error="identityNumber" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>
</section>

{{-- Allocation --}}
<section class="rounded-lg border border-slate-200 bg-white p-4">

<div class="mb-4">
<h3 class="text-sm font-semibold text-slate-800">Allocation</h3>
<p class="text-[11px] text-slate-500">Define entitlement allocation and nominee priority.</p>
</div>

<div class="grid gap-4 sm:grid-cols-2">

<div>
<label class="form-label">Allocation Percentage <span class="text-red-500">*</span></label>

<div class="relative">
<input id="allocation" type="number" min="0.01" max="100" step="0.01" class="app-input w-full !pr-9" placeholder="0.00" data-validation-min-message="Allocation must be between 0.01% and 100%." data-validation-max-message="Allocation must be between 0.01% and 100%.">

<span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-sm font-semibold text-slate-500">%</span>
</div>

<p data-field-error="allocation" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Priority <span class="text-red-500">*</span></label>

<input id="priority" type="number" min="1" value="1" pattern="[1-9]\d*" class="app-input w-full" data-validation-min-message="Priority must be at least 1." data-validation-pattern-message="Priority must be a whole number of at least 1.">

<p data-field-error="priority" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="mt-4 rounded-md border border-indigo-200 bg-indigo-50/50 p-3">
<p class="text-[11px] leading-5 text-indigo-700">
<i class="bi bi-info-circle mr-1"></i>
Total active nominee allocation cannot exceed 100%.
</p>
</div>
</section>

<div>
<label class="form-label">Notes</label>
<textarea id="notes" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Optional notes"></textarea>
<p data-field-error="notes" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

<button type="button" onclick="closeNomineeModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
Cancel
</button>

<button id="saveNomineeButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
Save Nominee
</button>

</div>

</form>
</div>
</div>

{{-- Document Modal --}}
<div id="documentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

<div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
<i class="bi bi-file-earmark-arrow-up"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Upload Document</h2>
<p id="documentModalSubtitle" class="text-[11px] text-slate-500">Add supporting document for nominee verification.</p>
</div>
</div>

<button type="button" onclick="closeDocumentModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="documentForm" novalidate data-js-validation="1">

<input id="documentNomineeId" type="hidden">

<div class="space-y-4 p-5">

<div id="documentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<div>
<label class="form-label">Document Type <span class="text-red-500">*</span></label>

<select id="documentType" class="app-input w-full" data-validation-required-message="Please select a document type.">
<option value="">Select Document Type</option>
<option value="nid">National ID</option>
<option value="birth_certificate">Birth Certificate</option>
<option value="passport">Passport</option>
<option value="photo">Photo</option>
<option value="other">Other</option>
</select>

<p data-field-error="documentType" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Document <span class="text-red-500">*</span></label>

<input id="documentFile" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="block w-full rounded-md border border-slate-300 bg-white p-2 text-sm text-slate-600" data-validation-required-message="Please select a document." data-validation-file-message="Only PDF, JPG, PNG or WEBP files are allowed.">

<p class="mt-1 text-[10px] text-slate-500">PDF, JPG, JPEG, PNG or WEBP.</p>

<p data-field-error="documentFile" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

<button type="button" onclick="closeDocumentModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">
Cancel
</button>

<button id="uploadDocumentButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white disabled:opacity-60">
Upload Document
</button>

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
const API='/api/member/nominees';

let nominees=[];
let filteredNominees=[];
let nomineeSummary={};
let currentNomineePage=1;
const nomineesPerPage=10;

const $=id=>document.getElementById(id);
const searchInput=$('nomineeSearch');
const verificationFilter=$('verificationFilter');

const nomineeFieldMap={
name:'name',
relationship:'relationship',
phone:'phone',
date_of_birth:'dob',
identity_type:'identityType',
identity_number:'identityNumber',
allocation_percentage:'allocation',
priority:'priority',
address:'address',
notes:'notes'
};

async function loadNominees(){
const root=$('nomineeList');

root.innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-500">Loading nominees...</p>
</div>`;

try{
const response=await api(API);
const data=response.data??{};

nominees=Array.isArray(data.nominees)?data.nominees:[];
nomineeSummary=data.summary??{};

renderSummary(nomineeSummary);

currentNomineePage=1;
applyNomineeFilters();

}catch(error){
root.innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-exclamation-circle text-lg"></i>
</div>

<p class="mt-3 text-base font-semibold text-red-600">Failed to load nominees</p>

<p class="mt-1 text-sm text-red-400">${escapeHtml(AdminUI.extractError(error))}</p>

<button type="button" onclick="loadNominees()" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-arrow-clockwise"></i>
Try Again
</button>
</div>`;

$('nomineePagination').innerHTML='';
}
}

function renderSummary(summary){
$('summaryTotal').textContent=Number(summary.total??0);
$('summaryVerified').textContent=Number(summary.verified??0);

const allocated=Number(summary.allocation_total??0);
const remaining=Number(summary.allocation_remaining??Math.max(100-allocated,0));

$('summaryAllocation').textContent=allocated.toFixed(2)+'%';
$('summaryRemaining').textContent=remaining.toFixed(2)+'%';

$('allocationNotice').innerHTML=summary.allocation_complete
?`
<div class="flex gap-3 rounded-lg border border-emerald-200 bg-emerald-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-check-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-emerald-800">Allocation Complete</p>
<p class="mt-0.5 text-[11px] text-emerald-700">Nominee allocation has reached 100%.</p>
</div>
</div>`
:`
<div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-exclamation-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-amber-800">Allocation Incomplete</p>
<p class="mt-0.5 text-[11px] text-amber-700">${remaining.toFixed(2)}% allocation is still unassigned.</p>
</div>
</div>`;
}

function applyNomineeFilters(){
const search=searchInput.value.trim().toLowerCase();
const verification=verificationFilter.value;

filteredNominees=nominees.filter(nominee=>{
if(verification&&nominee.verification_status!==verification){
return false;
}

if(search){
const haystack=[
nominee.name,
nominee.relationship,
nominee.phone,
nominee.identity_type,
nominee.identity_number,
nominee.notes
]
.map(value=>String(value??'').toLowerCase())
.join(' ');

if(!haystack.includes(search)){
return false;
}
}

return true;
});

const lastPage=Math.max(
Math.ceil(filteredNominees.length/nomineesPerPage),
1
);

if(currentNomineePage>lastPage){
currentNomineePage=lastPage;
}

renderNominees();
renderNomineePagination();
}

function renderNominees(){
const root=$('nomineeList');

if(!filteredNominees.length){
root.innerHTML=`
<div class="col-span-full py-14 text-center">
<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
<i class="bi bi-person-heart text-xl"></i>
</div>

<p class="mt-4 text-base font-semibold text-slate-700">No nominees found</p>

<p class="mt-1 text-sm text-slate-500">No nominee records match the current filters.</p>
</div>`;
return;
}

const start=(currentNomineePage-1)*nomineesPerPage;

const pageItems=filteredNominees.slice(
start,
start+nomineesPerPage
);

root.innerHTML=pageItems.map(nominee=>`
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-indigo-200 hover:shadow-sm">

<div class="border-b border-slate-100 p-4">

<div class="flex items-start justify-between gap-3">

<div class="flex min-w-0 items-start gap-3">

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-person-heart"></i>
</div>

<div class="min-w-0">
<div class="flex flex-wrap items-center gap-2">
<h3 class="truncate text-base font-bold text-slate-800">
${escapeHtml(nominee.name)}
</h3>

${verificationBadge(nominee.verification_status)}
</div>

<p class="mt-1 truncate text-[11px] text-slate-500">
${escapeHtml(nominee.relationship)}
${nominee.phone?' • '+escapeHtml(nominee.phone):''}
</p>
</div>
</div>

<div class="shrink-0 rounded-lg bg-indigo-50 px-3 py-2 text-center">
<p class="text-[9px] font-semibold uppercase tracking-wide text-indigo-400">Allocation</p>
<p class="mt-0.5 text-base font-bold text-indigo-700">
${Number(nominee.allocation_percentage??0).toFixed(2)}%
</p>
</div>

</div>
</div>

<div class="space-y-3 p-4">

<div class="grid grid-cols-2 gap-2">

${infoBox(
'Priority',
'#'+Number(nominee.priority??1),
'bi-sort-numeric-down'
)}

${infoBox(
'Identity',
nominee.identity_type
?identityLabel(nominee.identity_type)
:'Not provided',
'bi-person-vcard'
)}

${infoBox(
'Date of Birth',
nominee.date_of_birth
?formatDate(nominee.date_of_birth)
:'Not provided',
'bi-calendar3'
)}

${infoBox(
'Status',
nominee.is_active?'Active':'Inactive',
nominee.is_active?'bi-check-circle':'bi-pause-circle'
)}

</div>

${nominee.identity_number?`
<div class="rounded-md border border-slate-200 bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">Identity Number</p>
<p class="mt-1 break-all font-mono text-sm font-medium text-slate-600">
${escapeHtml(nominee.identity_number)}
</p>
</div>`:''}

${nominee.rejection_reason?`
<div class="rounded-md border border-red-200 bg-red-50 p-3">
<p class="text-[10px] font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Verification Rejected
</p>

<p class="mt-1 text-[11px] leading-5 text-red-600">
${escapeHtml(nominee.rejection_reason)}
</p>
</div>`:''}

${renderDocuments(nominee)}

</div>

<div class="flex flex-wrap items-center gap-1.5 border-t border-slate-100 bg-slate-50/50 px-4 py-3">

<button type="button" onclick="editNominee(${Number(nominee.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:text-indigo-700">
<i class="bi bi-pencil"></i>
Edit
</button>

${!['verified','pending'].includes(nominee.verification_status)&&nominee.identity_number?`
<button type="button" onclick="submitVerification(${Number(nominee.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-2.5 text-[11px] font-semibold text-amber-700 hover:bg-amber-100">
<i class="bi bi-patch-check"></i>
Submit
</button>`:''}

<button type="button" onclick="openDocumentModal(${Number(nominee.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[11px] font-semibold text-sky-700 hover:bg-sky-100">
<i class="bi bi-file-earmark-arrow-up"></i>
Document
</button>

<button type="button" onclick="toggleNominee(${Number(nominee.id)},${nominee.is_active?'false':'true'})" class="inline-flex h-8 items-center gap-1.5 rounded-md border px-2.5 text-[11px] font-semibold transition ${nominee.is_active?'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100':'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'}">
<i class="bi ${nominee.is_active?'bi-pause-circle':'bi-play-circle'}"></i>
${nominee.is_active?'Deactivate':'Activate'}
</button>

<button type="button" onclick="removeNominee(${Number(nominee.id)})" class="ml-auto inline-flex h-8 items-center gap-1 rounded-md px-2 text-[11px] font-semibold text-red-500 hover:bg-red-50">
<i class="bi bi-trash3"></i>
Remove
</button>

</div>
</article>
`).join('');
}

function renderDocuments(nominee){
const documents=nominee.documents??[];

if(!documents.length){
return'';
}

return`
<div class="border-t border-slate-100 pt-3">
<div class="mb-2 flex items-center justify-between">
<p class="text-[9px] font-bold uppercase tracking-wide text-slate-500">
Documents
</p>

<span class="text-[10px] text-slate-500">
${documents.length}
</span>
</div>

<div class="space-y-1.5">
${documents.map(document=>`
<div class="flex items-center gap-2 rounded-md bg-slate-50 px-3 py-2">

<div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-white text-indigo-500">
<i class="bi bi-file-earmark"></i>
</div>

<a href="/api/member/nominees/documents/${Number(document.id)}" target="_blank" class="min-w-0 flex-1 truncate text-[11px] font-medium text-indigo-600 hover:underline">
${escapeHtml(document.original_name)}
</a>

<button type="button" onclick="removeDocument(${Number(document.id)})" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-red-400 hover:bg-red-50 hover:text-red-600">
<i class="bi bi-trash3 text-sm"></i>
</button>

</div>
`).join('')}
</div>
</div>`;
}

function infoBox(label,value,icon){
return`
<div class="rounded-md bg-slate-50 p-3">
<div class="flex items-center gap-1.5">
<i class="bi ${icon} text-[10px] text-slate-500"></i>
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">
${escapeHtml(label)}
</p>
</div>

<p class="mt-1 truncate text-sm font-bold text-slate-700">
${escapeHtml(value)}
</p>
</div>`;
}

function renderNomineePagination(){
const container=$('nomineePagination');
const total=filteredNominees.length;

if(total<=nomineesPerPage){
container.innerHTML='';
return;
}

const lastPage=Math.max(
Math.ceil(total/nomineesPerPage),
1
);

const from=((currentNomineePage-1)*nomineesPerPage)+1;
const to=Math.min(
currentNomineePage*nomineesPerPage,
total
);

container.innerHTML=`
<div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

<p class="text-[11px] text-slate-500">
Showing
<span class="font-semibold text-xs 2xl:text-sm text-slate-700">${from}</span>
–
<span class="font-semibold text-xs 2xl:text-sm text-slate-700">${to}</span>
of
<span class="font-semibold text-xs 2xl:text-sm text-slate-700">${total}</span>
nominees
</p>

<div class="flex items-center gap-1">

<button type="button"
${currentNomineePage<=1?'disabled':''}
onclick="changeNomineePage(${currentNomineePage-1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
<i class="bi bi-chevron-left"></i>
Previous
</button>

<span class="px-3 text-[11px] font-medium text-slate-500">
${currentNomineePage} / ${lastPage}
</span>

<button type="button"
${currentNomineePage>=lastPage?'disabled':''}
onclick="changeNomineePage(${currentNomineePage+1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
Next
<i class="bi bi-chevron-right"></i>
</button>

</div>
</div>`;
}

window.changeNomineePage=function(page){
const lastPage=Math.max(
Math.ceil(filteredNominees.length/nomineesPerPage),
1
);

if(page<1||page>lastPage){
return;
}

currentNomineePage=page;

renderNominees();
renderNomineePagination();
};

window.clearNomineeFilters=function(){
searchInput.value='';
verificationFilter.value='';
currentNomineePage=1;

applyNomineeFilters();
};

window.openNomineeModal=function(){
$('nomineeForm').reset();

AdminUI.clearError('nomineeError');
AdminUI.clearFieldErrors('nomineeForm');

$('nomineeId').value='';
$('priority').value=nominees.length+1;

$('modalTitle').textContent='Add Nominee';
$('modalSubtitle').textContent='Add nominee information and allocation.';
$('saveNomineeButton').textContent='Save Nominee';

openModal('nomineeModal');

window.initDatePickers?.();

setPickerDate('dob','');
};

window.editNominee=function(id){
const nominee=nominees.find(
item=>Number(item.id)===Number(id)
);

if(!nominee){
Toast.error('Nominee not found.');
return;
}

$('nomineeForm').reset();

AdminUI.clearError('nomineeError');
AdminUI.clearFieldErrors('nomineeForm');

$('nomineeId').value=nominee.id;
$('name').value=nominee.name??'';
$('relationship').value=nominee.relationship??'';
$('fatherOrHusbandName').value=nominee.father_or_husband_name??'';
$('motherName').value=nominee.mother_name??'';
$('phone').value=nominee.phone??'';
$('gender').value=nominee.gender??'';
$('profession').value=nominee.profession??'';
$('identityType').value=nominee.identity_type??'';
$('identityNumber').value=nominee.identity_number??'';
$('allocation').value=nominee.allocation_percentage??'';
$('priority').value=nominee.priority??1;
$('address').value=nominee.address??'';
$('permanentAddress').value=nominee.permanent_address??'';
$('notes').value=nominee.notes??'';

$('modalTitle').textContent='Edit Nominee';
$('modalSubtitle').textContent='Update nominee information and allocation.';
$('saveNomineeButton').textContent='Update Nominee';

openModal('nomineeModal');

window.initDatePickers?.();

setPickerDate(
'dob',
nominee.date_of_birth
);
};

function setPickerDate(id,value){
const element=$(id);
if(!element)return;

const dateValue=value
?String(value).substring(0,10)
:'';

element.value=dateValue;

if(element._flatpickr){
dateValue
?element._flatpickr.setDate(dateValue,false,'Y-m-d')
:element._flatpickr.clear();
}
}

$('nomineeForm').addEventListener(
'submit',
async event=>{
event.preventDefault();

AdminUI.clearError('nomineeError');
AdminUI.clearFieldErrors('nomineeForm');

if(!AdminUI.validateForm('nomineeForm',{
name:'Nominee name is required.',
relationship:'Relationship is required.',
allocation:'Allocation percentage is required.',
priority:'Priority is required.'
})){
return;
}

const allocation=Number($('allocation').value);
const priority=Number($('priority').value);

const identityType=$('identityType').value;
const identityNumber=$('identityNumber').value.trim();

if(identityType&&!identityNumber){
AdminUI.showFieldError(
'identityNumber',
'Identity number is required when identity type is selected.'
);
return;
}

if(identityNumber&&!identityType){
AdminUI.showFieldError(
'identityType',
'Please select an identity type.'
);
return;
}

const id=$('nomineeId').value;

const payload={
name:$('name').value.trim(),
relationship:$('relationship').value.trim(),
father_or_husband_name:$('fatherOrHusbandName').value.trim()||null,
mother_name:$('motherName').value.trim()||null,
phone:$('phone').value.trim()||null,
date_of_birth:$('dob').value||null,
gender:$('gender').value||null,
profession:$('profession').value.trim()||null,
identity_type:identityType||null,
identity_number:identityNumber||null,
allocation_percentage:allocation,
priority,
address:$('address').value.trim()||null,
permanent_address:$('permanentAddress').value.trim()||null,
notes:$('notes').value.trim()||null
};

const button=$('saveNomineeButton');

AdminUI.setLoading(
button,
id?'Updating...':'Saving...'
);

try{
const response=await api(
id?`${API}/${id}`:API,
{
method:id?'PUT':'POST',
body:JSON.stringify(payload)
}
);

closeNomineeModal();

Toast.success(
response.message??
(id
?'Nominee updated successfully.'
:'Nominee added successfully.')
);

await loadNominees();

}catch(error){

if(
!AdminUI.showValidationErrors(
'nomineeForm',
error,
nomineeFieldMap
)
){
AdminUI.showError(
'nomineeError',
AdminUI.extractError(error)
);
}

}finally{
AdminUI.resetLoading(button);
}
}
);

window.submitVerification=function(id){
const nominee=nominees.find(
item=>Number(item.id)===Number(id)
);

if(!nominee)return;

AdminUI.request(
`${API}/${id}/submit`,
{
method:'POST',
data:{},
confirmation:{
title:'Submit for Verification?',
message:`Submit ${nominee.name} for nominee verification?`,
confirmText:'Submit Verification',
type:'primary'
},
successMessage:'Nominee submitted for verification.',
onSuccess:loadNominees
}
);
};

window.toggleNominee=function(id,active){
const nominee=nominees.find(
item=>Number(item.id)===Number(id)
);

if(!nominee)return;

AdminUI.request(
`${API}/${id}/active`,
{
method:'PATCH',
data:{
is_active:active
},
confirmation:{
title:active
?'Activate Nominee?'
:'Deactivate Nominee?',
message:active
?`Activate ${nominee.name}?`
:`Deactivate ${nominee.name}? The nominee will no longer be included in active allocation.`,
confirmText:active
?'Activate'
:'Deactivate',
type:active
?'success'
:'danger'
},
successMessage:active
?'Nominee activated successfully.'
:'Nominee deactivated successfully.',
onSuccess:loadNominees
}
);
};

window.removeNominee=function(id){
const nominee=nominees.find(
item=>Number(item.id)===Number(id)
);

if(!nominee)return;

AdminUI.deleteRequest(
`${API}/${id}`,
{
message:`Remove nominee ${nominee.name}?`,
successMessage:'Nominee removed successfully.',
onSuccess:loadNominees
}
);
};

window.openDocumentModal=function(id){
const nominee=nominees.find(
item=>Number(item.id)===Number(id)
);

if(!nominee){
Toast.error('Nominee not found.');
return;
}

$('documentForm').reset();

AdminUI.clearError('documentError');
AdminUI.clearFieldErrors('documentForm');

$('documentNomineeId').value=id;
$('documentModalSubtitle').textContent=
`Upload supporting document for ${nominee.name}.`;

openModal('documentModal');
};

$('documentForm').addEventListener(
'submit',
async event=>{
event.preventDefault();

AdminUI.clearError('documentError');
AdminUI.clearFieldErrors('documentForm');

const id=$('documentNomineeId').value;
const type=$('documentType').value;
const file=$('documentFile').files?.[0];

const form=new FormData();

form.append(
'document_type',
type
);

form.append(
'file',
file
);

const button=$('uploadDocumentButton');

AdminUI.setLoading(
button,
'Uploading...'
);

try{
const response=await api(
`${API}/${id}/documents`,
{
method:'POST',
body:form
}
);

closeDocumentModal();

Toast.success(
response.message??
'Document uploaded successfully.'
);

await loadNominees();

}catch(error){
if(!AdminUI.showValidationErrors(
'documentForm',
error,
{
document_type:'documentType',
file:'documentFile'
}
)){
AdminUI.showError(
'documentError',
AdminUI.extractError(error)
);
}
}finally{
AdminUI.resetLoading(button);
}
}
);

window.removeDocument=function(id){
AdminUI.deleteRequest(
`${API}/documents/${id}`,
{
message:'Remove this nominee document?',
successMessage:'Document removed successfully.',
onSuccess:loadNominees
}
);
};

function verificationBadge(status){
const map={
verified:[
'Verified',
'bg-emerald-50 text-emerald-700',
'bi-patch-check-fill'
],
pending:[
'Pending',
'bg-amber-50 text-amber-700',
'bi-hourglass-split'
],
rejected:[
'Rejected',
'bg-red-50 text-red-700',
'bi-x-circle'
],
unverified:[
'Unverified',
'bg-slate-100 text-slate-600',
'bi-question-circle'
]
};

const item=map[status]??map.unverified;

return`
<span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[9px] font-semibold uppercase ${item[1]}">
<i class="bi ${item[2]}"></i>
${item[0]}
</span>`;
}

function identityLabel(value){
return{
nid:'National ID',
birth_certificate:'Birth Certificate',
passport:'Passport',
other:'Other'
}[value]??value;
}

function formatDate(value){
if(!value)return'—';

const parsed=new Date(value);

if(Number.isNaN(parsed.getTime())){
return value;
}

return parsed.toLocaleDateString(
'en-GB',
{
day:'2-digit',
month:'short',
year:'numeric'
}
);
}

function escapeHtml(value){
const div=document.createElement('div');
div.textContent=String(value??'');
return div.innerHTML;
}

function openModal(id){
if(typeof AdminUI!=='undefined'){
AdminUI.openModal(id);
return;
}

const modal=$(id);
modal?.classList.remove('hidden');
modal?.classList.add('flex');
}

function closeModal(id){
if(typeof AdminUI!=='undefined'){
AdminUI.closeModal(id);
return;
}

const modal=$(id);
modal?.classList.add('hidden');
modal?.classList.remove('flex');
}

window.closeNomineeModal=function(){
closeModal('nomineeModal');
};

window.closeDocumentModal=function(){
closeModal('documentModal');
};

async function init(){
if(
typeof AdminUI==='undefined'||
typeof api==='undefined'
){
setTimeout(
init,
50
);
return;
}

searchInput.addEventListener(
'input',
AdminUI.debounce(()=>{
currentNomineePage=1;
applyNomineeFilters();
})
);

verificationFilter.addEventListener(
'change',
()=>{
currentNomineePage=1;
applyNomineeFilters();
}
);

window.initDatePickers?.();

await loadNominees();
}

init();
</script>
@endpush