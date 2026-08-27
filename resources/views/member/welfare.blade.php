@extends('layouts.member')

@section('title','Welfare Support')
@section('page-title','Welfare Support')

@section('content')
@php
$currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 text-white shadow-sm">
<i class="bi bi-heart-pulse"></i>
</div>

<div>
<h1 class="text-lg font-bold tracking-tight text-slate-800">Welfare Support</h1>
<p class="mt-0.5 text-xs text-slate-500">Request and track emergency or welfare assistance from the association.</p>
</div>
</div>

<div class="flex flex-wrap items-center gap-2">
<button id="refreshButton" type="button" onclick="loadWelfare(currentPage)" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:opacity-60">
<i id="refreshIcon" class="bi bi-arrow-clockwise"></i>
Refresh
</button>

<button type="button" onclick="openRequestModal()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-circle"></i>
Request Assistance
</button>
</div>
</div>

{{-- Information --}}
<div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-xs font-semibold text-indigo-800">Welfare Assistance</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
Welfare support is available for eligible emergency, medical, accident, disaster and financial-hardship situations. Requested amounts remain subject to review, available fund balance and final approval.
</p>
</div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">Total Requests</p>
<p id="summaryTotal" class="mt-2 text-xl font-bold text-slate-800">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
<i class="bi bi-collection"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">In Process</p>
<p id="summaryPending" class="mt-2 text-xl font-bold text-amber-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-hourglass-split"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Completed</p>
<p id="summaryCompleted" class="mt-2 text-xl font-bold text-emerald-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-check2-circle"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div class="min-w-0">
<p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">Total Approved</p>
<p id="summaryApproved" class="mt-2 truncate text-xl font-bold text-violet-700">{{ $currency }}0.00</p>
</div>

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
<i class="bi bi-cash-heart"></i>
</div>
</div>
</div>

</div>

{{-- Available Funds --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
<i class="bi bi-wallet2"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Available Welfare Funds</h2>
<p class="text-[11px] text-slate-400">Funds currently available for member assistance</p>
</div>
</div>

<span id="fundCount" class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500">0</span>
</div>

<div id="fundGrid" class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-3">
<div class="col-span-full py-8 text-center text-xs text-slate-400">Loading funds...</div>
</div>
</div>

{{-- Filters --}}
<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

<div class="lg:col-span-7">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>

<div class="relative">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

<input id="requestSearch" type="text" placeholder="Search request no, fund, type or reason..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>
</div>

<div class="lg:col-span-3">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</label>

<select id="statusFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
<option value="">All Status</option>
<option value="submitted">Submitted</option>
<option value="under_review">Under Review</option>
<option value="approved">Approved</option>
<option value="completed">Completed</option>
<option value="rejected">Rejected</option>
<option value="cancelled">Cancelled</option>
<option value="reversed">Reversed</option>
</select>
</div>

<div class="lg:col-span-2">
<button type="button" onclick="clearFilters()" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
Clear
</button>
</div>

</div>
</div>

{{-- Request Portfolio --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
<i class="bi bi-heart-pulse"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Assistance Requests</h2>
<p class="text-[11px] text-slate-400">Your welfare support request and approval history</p>
</div>
</div>

<div id="requestList" class="grid gap-3 p-4 lg:grid-cols-2">
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-xs text-slate-400">Loading requests...</p>
</div>
</div>

<div id="paginationContainer"></div>
</div>

</div>

{{-- Request Modal --}}
<div id="requestModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

<div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
<i class="bi bi-heart-pulse"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Request Welfare Assistance</h2>
<p class="text-[11px] text-slate-400">Submit an emergency or welfare support request.</p>
</div>
</div>

<button type="button" onclick="closeRequestModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="requestForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">

<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

<div id="requestError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>

<section class="rounded-lg border border-slate-200 p-4">
<div class="mb-4">
<h3 class="text-sm font-semibold text-slate-800">Assistance Information</h3>
<p class="text-[11px] text-slate-400">Select the welfare fund and type of assistance.</p>
</div>

<div class="grid gap-4 sm:grid-cols-2">

<div>
<label class="form-label">Welfare Fund <span class="text-red-500">*</span></label>
<select id="fundId" class="app-input w-full">
<option value="">Select Welfare Fund</option>
</select>
<p data-field-error="fundId" class="mt-1 hidden text-xs text-red-600"></p>
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
<p data-field-error="assistanceType" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div class="sm:col-span-2">
<label class="form-label">Requested Amount <span class="text-red-500">*</span></label>

<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">{{ $currency }}</span>

<input id="requestAmount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8" placeholder="0.00" data-validation-min-message="Requested amount must be greater than zero.">
</div>

<p data-field-error="requestAmount" class="mt-1 hidden text-xs text-red-600"></p>
</div>

</div>
</section>

<section class="rounded-lg border border-slate-200 p-4">
<label class="form-label">Reason <span class="text-red-500">*</span></label>

<textarea id="requestReason" rows="5" maxlength="5000" class="app-input w-full resize-none" placeholder="Describe why assistance is required..."></textarea>

<p data-field-error="requestReason" class="mt-1 hidden text-xs text-red-600"></p>
</section>

<div id="selectedFundInfo"></div>

</div>

<div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeRequestModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">
Cancel
</button>

<button id="submitRequestButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
Submit Request
</button>
</div>

</form>
</div>
</div>

{{-- Document Modal --}}
<div id="documentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

<div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
<i class="bi bi-file-earmark-arrow-up"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Add Supporting Document</h2>
<p id="documentSubtitle" class="text-[11px] text-slate-400"></p>
</div>
</div>

<button type="button" onclick="closeDocumentModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="documentForm" novalidate data-js-validation="1">
<input id="documentRequestId" type="hidden">

<div class="space-y-4 p-5">
<div id="documentError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>

<div>
<label class="form-label">Document Type <span class="text-red-500">*</span></label>

<select id="documentType" class="app-input w-full" data-validation-required-message="Please select a document type.">
<option value="">Select Document Type</option>
<option value="supporting_document">Supporting Document</option>
<option value="medical_report">Medical Report</option>
<option value="prescription">Prescription</option>
<option value="hospital_bill">Hospital Bill</option>
<option value="death_certificate">Death Certificate</option>
<option value="disaster_evidence">Disaster Evidence</option>
<option value="financial_document">Financial Document</option>
<option value="other">Other</option>
</select>

<p data-field-error="documentType" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">File <span class="text-red-500">*</span></label>

<input id="documentFile" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="block w-full rounded-md border border-slate-300 bg-white p-2 text-xs text-slate-600" data-validation-required-message="Please select a file." data-validation-file-message="Only PDF, JPG, PNG and WEBP files are allowed.">

<p class="mt-1 text-[10px] text-slate-400">PDF, JPG, JPEG, PNG or WEBP.</p>

<p data-field-error="documentFile" class="mt-1 hidden text-xs text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeDocumentModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600">
Cancel
</button>

<button id="uploadButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white disabled:opacity-60">
Upload
</button>
</div>
</form>

</div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

<div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
<i class="bi bi-heart-pulse"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Welfare Request</h2>
<p id="detailsRequestNo" class="font-mono text-[10px] text-slate-400"></p>
</div>
</div>

<button type="button" onclick="closeDetailsModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<div id="detailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

<div class="flex justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeDetailsModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600">
Close
</button>
</div>

</div>
</div>

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>
@endsection

@push('scripts')
<script>
const API='/api/member/welfare';
const currency=@json($currency);

let welfareData={
funds:[],
requests:[]
};

let currentPage=1;
let lastPage=1;
let total=0;

const $=id=>document.getElementById(id);

function money(value){
return currency+Number(value??0).toLocaleString('en-US',{
minimumFractionDigits:2,
maximumFractionDigits:2
});
}

function assistanceLabel(value){
return{
illness:'Illness',
accident:'Accident',
death:'Death',
natural_disaster:'Natural Disaster',
emergency:'Emergency',
financial_hardship:'Financial Hardship',
other:'Other'
}[value]??titleCase(value);
}

function statusBadge(status){
const map={
submitted:['Submitted','bg-sky-50 text-sky-700','bi-send'],
under_review:['Under Review','bg-indigo-50 text-indigo-700','bi-search'],
approved:['Approved','bg-amber-50 text-amber-700','bi-patch-check'],
completed:['Completed','bg-emerald-50 text-emerald-700','bi-check2-circle'],
rejected:['Rejected','bg-red-50 text-red-700','bi-x-circle'],
cancelled:['Cancelled','bg-slate-100 text-slate-500','bi-slash-circle'],
reversed:['Reversed','bg-rose-50 text-rose-700','bi-arrow-counterclockwise']
};

const item=map[status]??[
titleCase(status),
'bg-slate-100 text-slate-500',
'bi-circle'
];

return`
<span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[9px] font-semibold uppercase ${item[1]}">
<i class="bi ${item[2]}"></i>
${escapeHtml(item[0])}
</span>`;
}

async function loadWelfare(page=1){
currentPage=page;

const button=$('refreshButton');
const icon=$('refreshIcon');

if(button)button.disabled=true;
if(icon)icon.classList.add('animate-spin');

$('requestList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-xs text-slate-400">Loading requests...</p>
</div>`;

try{
const params=new URLSearchParams({
page,
per_page:10
});

const search=$('requestSearch').value.trim();
const status=$('statusFilter').value;

if(search)params.set('search',search);
if(status)params.set('status',status);

const response=await api(`${API}?${params.toString()}`);
const payload=response.data??{};

welfareData.funds=Array.isArray(payload.funds)
?payload.funds
:[];

const requestPayload=payload.requests??[];

if(Array.isArray(requestPayload)){
welfareData.requests=requestPayload;
currentPage=1;
lastPage=1;
total=requestPayload.length;
}else{
welfareData.requests=Array.isArray(requestPayload.data)
?requestPayload.data
:[];

currentPage=Number(requestPayload.current_page??1);
lastPage=Number(requestPayload.last_page??1);
total=Number(requestPayload.total??welfareData.requests.length);
}

renderFunds();
renderRequests();
renderSummary(payload.summary??null);
renderPagination(requestPayload);

}catch(error){
$('requestList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-exclamation-circle"></i>
</div>

<p class="mt-3 text-sm font-semibold text-red-600">Failed to load welfare requests</p>
<p class="mt-1 text-xs text-red-400">${escapeHtml(AdminUI.extractError(error))}</p>

<button type="button" onclick="loadWelfare(currentPage)" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-arrow-clockwise"></i>
Try Again
</button>
</div>`;

$('paginationContainer').innerHTML='';
}finally{
if(button)button.disabled=false;
if(icon)icon.classList.remove('animate-spin');
}
}

function renderFunds(){
const funds=welfareData.funds??[];

$('fundCount').textContent=funds.length;

$('fundId').innerHTML=
'<option value="">Select Welfare Fund</option>'+
funds
.filter(fund=>fund.is_active!==false)
.map(fund=>`
<option value="${fund.id}">
${escapeHtml(fund.name)}
</option>
`).join('');

if(!funds.length){
$('fundGrid').innerHTML=`
<div class="col-span-full py-8 text-center text-xs text-slate-400">
No active welfare funds available.
</div>`;
return;
}

$('fundGrid').innerHTML=funds.map(fund=>`
<div class="rounded-lg border border-slate-200 bg-slate-50/40 p-4">

<div class="flex items-start justify-between gap-3">
<div class="min-w-0">
<p class="truncate text-xs font-bold text-slate-700">
${escapeHtml(fund.name)}
</p>

${fund.code?`
<p class="mt-0.5 font-mono text-[9px] font-medium text-indigo-500">
${escapeHtml(fund.code)}
</p>`:''}
</div>

<span class="h-2 w-2 shrink-0 rounded-full ${fund.is_active===false?'bg-slate-300':'bg-emerald-500'}"></span>
</div>

<div class="mt-3 grid grid-cols-2 gap-2">
${smallBox('Available',money(fund.available_amount??0),'emerald')}
${smallBox('Allocated',money(fund.allocated_amount??0),'indigo')}
</div>

</div>
`).join('');
}

function renderSummary(serverSummary){
if(serverSummary){
$('summaryTotal').textContent=Number(serverSummary.total??0);
$('summaryPending').textContent=Number(serverSummary.pending??serverSummary.in_process??0);
$('summaryCompleted').textContent=Number(serverSummary.completed??0);
$('summaryApproved').textContent=money(serverSummary.total_approved??serverSummary.approved_amount??0);
return;
}

const requests=welfareData.requests;

$('summaryTotal').textContent=total||requests.length;

$('summaryPending').textContent=requests.filter(r=>
['submitted','under_review','approved'].includes(r.status)
).length;

$('summaryCompleted').textContent=requests.filter(
r=>r.status==='completed'
).length;

$('summaryApproved').textContent=money(
requests.reduce(
(sum,r)=>sum+Number(r.approved_amount??0),
0
)
);
}

function renderRequests(){
const requests=welfareData.requests;

if(!requests.length){
$('requestList').innerHTML=`
<div class="col-span-full py-14 text-center">

<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-500">
<i class="bi bi-heart-pulse text-xl"></i>
</div>

<p class="mt-4 text-sm font-semibold text-slate-700">
No Welfare Requests
</p>

<p class="mt-1 text-xs text-slate-400">
You have no welfare assistance requests matching the current filters.
</p>

</div>`;
return;
}

$('requestList').innerHTML=requests.map(request=>`
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-indigo-200 hover:shadow-sm">

<div class="border-b border-slate-100 p-4">

<div class="flex items-start justify-between gap-3">

<div class="flex min-w-0 items-start gap-3">

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
<i class="bi bi-heart-pulse"></i>
</div>

<div class="min-w-0">

<div class="flex flex-wrap items-center gap-2">

<p class="font-mono text-xs font-semibold text-indigo-600">
${escapeHtml(request.request_no)}
</p>

${statusBadge(request.status)}

</div>

<p class="mt-1 truncate text-[11px] text-slate-400">
${escapeHtml(request.fund?.name??'Welfare Fund')}
</p>

</div>
</div>

<button type="button" onclick="openDetails(${Number(request.id)})" class="inline-flex h-8 shrink-0 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
<i class="bi bi-eye"></i>
View
</button>

</div>
</div>

<div class="space-y-3 p-4">

<div class="grid grid-cols-2 gap-2">

${smallBox(
'Requested',
money(request.requested_amount),
'slate'
)}

${smallBox(
'Approved',
request.approved_amount
?money(request.approved_amount)
:'—',
'indigo'
)}

</div>

<div class="flex items-center justify-between rounded-md bg-slate-50 px-3 py-2.5">
<div>
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
Assistance Type
</p>

<p class="mt-1 text-xs font-semibold text-slate-700">
${escapeHtml(
assistanceLabel(request.assistance_type)
)}
</p>
</div>

<i class="bi ${assistanceIcon(request.assistance_type)} text-lg text-slate-300"></i>
</div>

<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
Reason
</p>

<p class="mt-1 line-clamp-3 text-[11px] leading-5 text-slate-600">
${escapeHtml(request.reason)}
</p>
</div>

${request.rejection_reason?`
<div class="rounded-md border border-red-200 bg-red-50 p-3">
<p class="text-[10px] font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Request Rejected
</p>

<p class="mt-1 text-[11px] leading-5 text-red-600">
${escapeHtml(request.rejection_reason)}
</p>
</div>`:''}

${renderDocumentSummary(request)}

</div>

${['submitted','under_review'].includes(request.status)?`
<div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/50 px-4 py-3">

<button type="button" onclick="openDocumentModal(${Number(request.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[11px] font-semibold text-sky-700 hover:bg-sky-100">
<i class="bi bi-file-earmark-arrow-up"></i>
Add Document
</button>

<button type="button" onclick="cancelRequest(${Number(request.id)})" class="ml-auto inline-flex h-8 items-center gap-1.5 rounded-md border border-red-200 bg-white px-2.5 text-[11px] font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-x-circle"></i>
Cancel
</button>

</div>`:''}

</article>
`).join('');
}

function renderDocumentSummary(request){
const documents=request.documents??[];

if(!documents.length)return'';

return`
<div class="border-t border-slate-100 pt-3">
<div class="flex items-center gap-2 text-[10px] text-slate-500">
<i class="bi bi-paperclip text-indigo-500"></i>
<span class="font-medium">
${documents.length} supporting document${documents.length===1?'':'s'}
</span>
</div>
</div>`;
}

function smallBox(label,value,tone='slate'){
const colors={
slate:'text-slate-700',
indigo:'text-indigo-700',
emerald:'text-emerald-700',
amber:'text-amber-700',
red:'text-red-700'
};

return`
<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
${escapeHtml(label)}
</p>

<p class="mt-1 truncate text-xs font-bold ${colors[tone]??colors.slate}">
${escapeHtml(value)}
</p>
</div>`;
}

function renderPagination(meta){
const container=$('paginationContainer');

if(Array.isArray(meta)||lastPage<=1){
container.innerHTML='';
return;
}

const from=Number(
meta.from??
((currentPage-1)*Number(meta.per_page??10))+1
);

const to=Number(
meta.to??
Math.min(
currentPage*Number(meta.per_page??10),
total
)
);

container.innerHTML=`
<div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

<p class="text-[11px] text-slate-500">
Showing
<span class="font-semibold text-slate-700">${from}</span>
–
<span class="font-semibold text-slate-700">${to}</span>
of
<span class="font-semibold text-slate-700">${total}</span>
requests
</p>

<div class="flex items-center gap-1">

<button type="button"
${currentPage<=1?'disabled':''}
onclick="loadWelfare(${currentPage-1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
<i class="bi bi-chevron-left"></i>
Previous
</button>

<span class="px-3 text-[11px] font-medium text-slate-500">
${currentPage} / ${lastPage}
</span>

<button type="button"
${currentPage>=lastPage?'disabled':''}
onclick="loadWelfare(${currentPage+1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
Next
<i class="bi bi-chevron-right"></i>
</button>

</div>
</div>`;
}

window.openRequestModal=function(){
$('requestForm').reset();

AdminUI.clearError('requestError');
AdminUI.clearFieldErrors('requestForm');

$('selectedFundInfo').innerHTML='';

openModal('requestModal');
};

window.closeRequestModal=function(){
closeModal('requestModal');
};

$('fundId').addEventListener('change',()=>{
const fund=welfareData.funds.find(
item=>Number(item.id)===Number($('fundId').value)
);

if(!fund){
$('selectedFundInfo').innerHTML='';
return;
}

$('selectedFundInfo').innerHTML=`
<div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">

<div class="flex items-start justify-between gap-3">

<div>
<p class="text-xs font-semibold text-indigo-800">
${escapeHtml(fund.name)}
</p>

<p class="mt-1 text-[11px] text-indigo-600">
Available welfare balance
</p>
</div>

<p class="text-sm font-bold text-indigo-700">
${money(fund.available_amount??0)}
</p>

</div>
</div>`;
});

$('requestForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('requestError');
AdminUI.clearFieldErrors('requestForm');

if(!AdminUI.validateForm('requestForm',{
fundId:'Please select a welfare fund.',
assistanceType:'Assistance type is required.',
requestAmount:'Requested amount is required.',
requestReason:'Reason is required.'
})){
return;
}

const amount=Number(
$('requestAmount').value
);

const selectedFund=welfareData.funds.find(
fund=>Number(fund.id)===Number($('fundId').value)
);

if(
selectedFund&&
Number(selectedFund.available_amount??0)>0&&
amount>Number(selectedFund.available_amount)
){
AdminUI.showFieldError(
'requestAmount',
`Requested amount exceeds the current available fund balance of ${money(selectedFund.available_amount)}.`
);
return;
}

const payload={
welfare_fund_id:Number(
$('fundId').value
),
assistance_type:$('assistanceType').value,
requested_amount:amount,
reason:$('requestReason').value.trim()
};

const button=$('submitRequestButton');

AdminUI.setLoading(
button,
'Submitting...'
);

try{
const response=await api(API,{
method:'POST',
body:JSON.stringify(payload)
});

closeRequestModal();

Toast.success(
response.message??
'Welfare assistance request submitted successfully.'
);

await loadWelfare(1);

}catch(error){

if(!AdminUI.showValidationErrors(
'requestForm',
error,
{
welfare_fund_id:'fundId',
assistance_type:'assistanceType',
requested_amount:'requestAmount',
reason:'requestReason'
}
)){
AdminUI.showError(
'requestError',
AdminUI.extractError(error)
);
}

}finally{
AdminUI.resetLoading(button);
}
});

window.openDocumentModal=function(id){
const request=welfareData.requests.find(
item=>Number(item.id)===Number(id)
);

if(!request){
Toast.error('Welfare request not found.');
return;
}

$('documentForm').reset();

AdminUI.clearError('documentError');
AdminUI.clearFieldErrors('documentForm');

$('documentRequestId').value=id;

$('documentSubtitle').textContent=
`Supporting document for ${request.request_no}`;

openModal('documentModal');
};

window.closeDocumentModal=function(){
closeModal('documentModal');
};

$('documentForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('documentError');
AdminUI.clearFieldErrors('documentForm');

const id=$('documentRequestId').value;
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

const button=$('uploadButton');

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
'Supporting document uploaded successfully.'
);

await loadWelfare(currentPage);

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
});

window.cancelRequest=function(id){
const request=welfareData.requests.find(
item=>Number(item.id)===Number(id)
);

if(!request){
Toast.error('Welfare request not found.');
return;
}

AdminUI.request(
`${API}/${id}/cancel`,
{
method:'POST',
data:{},
confirmation:{
title:'Cancel Welfare Request?',
message:`Cancel welfare request ${request.request_no}?`,
confirmText:'Cancel Request',
type:'danger'
},
successMessage:'Welfare assistance request cancelled.',
onSuccess:async()=>{
await loadWelfare(currentPage);
}
}
);
};

window.openDetails=function(id){
const request=welfareData.requests.find(
item=>Number(item.id)===Number(id)
);

if(!request){
Toast.error('Welfare request not found.');
return;
}

$('detailsRequestNo').textContent=
request.request_no??'';

$('detailsBody').innerHTML=`
<div class="space-y-5">

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

${detailSummary(
'Requested',
money(request.requested_amount),
'bi-wallet2',
'slate'
)}

${detailSummary(
'Approved',
request.approved_amount
?money(request.approved_amount)
:'—',
'bi-check2-circle',
'indigo'
)}

${detailSummary(
'Type',
assistanceLabel(request.assistance_type),
'assistance',
'sky'
)}

${detailSummary(
'Status',
titleCase(request.status),
'bi-patch-check',
statusTone(request.status)
)}

</div>

<div class="overflow-hidden rounded-lg border border-slate-200">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">
Request Information
</h3>
</div>

<div class="grid sm:grid-cols-2">

${detailItem(
'Request Number',
request.request_no??'—'
)}

${detailItem(
'Welfare Fund',
request.fund?.name??'—'
)}

${detailItem(
'Assistance Type',
assistanceLabel(request.assistance_type)
)}

${detailItem(
'Status',
statusBadge(request.status),
true
)}

${detailItem(
'Requested Amount',
money(request.requested_amount)
)}

${detailItem(
'Approved Amount',
request.approved_amount
?money(request.approved_amount)
:'—'
)}

${detailItem(
'Request Date',
dateText(request.request_date??request.created_at)
)}

${detailItem(
'Approved Date',
dateText(request.approved_at)
)}

${detailItem(
'Completed Date',
dateText(request.completed_at)
)}

</div>
</div>

<div class="rounded-lg border border-slate-200 p-4">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
Reason
</p>

<p class="mt-2 whitespace-pre-line text-xs leading-5 text-slate-600">
${escapeHtml(request.reason??'—')}
</p>
</div>

${request.rejection_reason?`
<div class="rounded-lg border border-red-200 bg-red-50 p-4">
<p class="text-xs font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Rejection Reason
</p>

<p class="mt-2 whitespace-pre-line text-xs leading-5 text-red-600">
${escapeHtml(request.rejection_reason)}
</p>
</div>`:''}

${renderDocuments(
request.documents??[]
)}

</div>`;

openModal('detailsModal');
};

function renderDocuments(documents){
if(!documents.length)return'';

return`
<div class="overflow-hidden rounded-lg border border-slate-200">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">
Supporting Documents
</h3>
<p class="text-[11px] text-slate-400">
${documents.length} uploaded document${documents.length===1?'':'s'}
</p>
</div>

<div class="divide-y divide-slate-100">

${documents.map(document=>`
<div class="flex items-center gap-3 px-4 py-3">

<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-file-earmark"></i>
</div>

<div class="min-w-0 flex-1">
<p class="truncate text-xs font-semibold text-slate-700">
${escapeHtml(
document.original_name??
document.file_name??
'Document'
)}
</p>

<p class="mt-0.5 text-[10px] text-slate-400">
${escapeHtml(
titleCase(document.document_type)
)}
</p>
</div>

<a href="/api/member/welfare/documents/${Number(document.id)}" target="_blank" rel="noopener" class="inline-flex h-8 items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-[10px] font-semibold text-indigo-600 hover:bg-indigo-100">
<i class="bi bi-box-arrow-up-right"></i>
View
</a>

</div>
`).join('')}

</div>
</div>`;
}

window.closeDetailsModal=function(){
closeModal('detailsModal');
};

function detailSummary(label,value,icon,tone){
const tones={
slate:'bg-slate-100 text-slate-600',
indigo:'bg-indigo-50 text-indigo-600',
sky:'bg-sky-50 text-sky-600',
emerald:'bg-emerald-50 text-emerald-600',
amber:'bg-amber-50 text-amber-600',
red:'bg-red-50 text-red-600'
};

return`
<div class="rounded-lg border border-slate-200 bg-white p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
${escapeHtml(label)}
</p>

<p class="mt-2 truncate text-xs font-bold text-slate-700">
${escapeHtml(value)}
</p>
</div>`;
}

function detailItem(label,value,html=false){
return`
<div class="border-b border-slate-100 px-5 py-3 sm:odd:border-r">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">
${escapeHtml(label)}
</p>

<div class="mt-1 text-xs font-semibold text-slate-700">
${html?value:escapeHtml(value)}
</div>
</div>`;
}

function assistanceIcon(type){
return{
illness:'bi-hospital',
accident:'bi-bandaid',
death:'bi-person-x',
natural_disaster:'bi-cloud-lightning-rain',
emergency:'bi-exclamation-triangle',
financial_hardship:'bi-wallet2',
other:'bi-three-dots'
}[type]??'bi-heart-pulse';
}

function statusTone(status){
return{
submitted:'sky',
under_review:'indigo',
approved:'amber',
completed:'emerald',
rejected:'red',
cancelled:'slate',
reversed:'red'
}[status]??'slate';
}

function dateText(value){
if(!value)return'—';

const raw=String(value).substring(0,10);
const parsed=new Date(raw+'T00:00:00');

if(Number.isNaN(parsed.getTime())){
return raw;
}

return parsed.toLocaleDateString('en-GB',{
day:'2-digit',
month:'short',
year:'numeric'
});
}

function titleCase(value){
return String(value??'')
.replaceAll('_',' ')
.replace(/\b\w/g,char=>char.toUpperCase());
}

window.clearFilters=function(){
$('requestSearch').value='';
$('statusFilter').value='';
loadWelfare(1);
};

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

function escapeHtml(value){
const div=document.createElement('div');
div.textContent=String(value??'');
return div.innerHTML;
}

function escapeAttribute(value){
return String(value??'')
.replaceAll('&','&amp;')
.replaceAll('"','&quot;')
.replaceAll("'",'&#039;')
.replaceAll('<','&lt;')
.replaceAll('>','&gt;');
}

async function init(){
if(
typeof AdminUI==='undefined'||
typeof api==='undefined'
){
setTimeout(init,50);
return;
}

$('requestSearch').addEventListener(
'input',
AdminUI.debounce(
()=>loadWelfare(1)
)
);

$('statusFilter').addEventListener(
'change',
()=>loadWelfare(1)
);

await loadWelfare();
}

init();
</script>
@endpush