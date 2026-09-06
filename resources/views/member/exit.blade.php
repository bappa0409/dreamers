@extends('layouts.member')

@section('title','Exit Membership')
@section('page-title','Exit Membership')

@section('content')
@php
$currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-sm">
<i class="bi bi-box-arrow-right"></i>
</div>

<div>
<h1 class="text-base font-semibold tracking-tight text-slate-800">Exit Membership</h1>
<p class="mt-0.5 text-sm text-slate-500">Submit and track a permanent membership resignation request.</p>
</div>
</div>

<div class="flex flex-wrap gap-2">
<button id="refreshButton" type="button" onclick="loadExits(currentPage)" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:opacity-60">
<i id="refreshIcon" class="bi bi-arrow-clockwise"></i>
Refresh
</button>

<button type="button" onclick="openExitModal()" class="inline-flex h-9 items-center gap-2 rounded-md bg-red-600 px-4 text-sm font-semibold text-white transition hover:bg-red-700">
<i class="bi bi-box-arrow-right"></i>
Request Resignation
</button>
</div>
</div>

{{-- Warning --}}
<div class="flex gap-3 rounded-lg border border-amber-200 bg-amber-50/60 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-exclamation-triangle"></i>
</div>

<div>
<p class="text-sm font-semibold text-amber-800">Permanent Membership Cancel</p>
<p class="mt-0.5 text-[11px] leading-5 text-amber-700">
Membership cancel becomes permanent after final settlement and closure. Outstanding subscriptions, charges, loans or other liabilities must be resolved before approval.
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

<div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-red-600">Outstanding</p>
<p id="summaryLiability" class="mt-2 truncate text-xl font-bold text-red-700">{{ $currency }}0.00</p>
</div>
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
<i class="bi bi-exclamation-circle"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Share Refund</p>
<p id="summaryRefund" class="mt-2 truncate text-xl font-bold text-emerald-700">{{ $currency }}0.00</p>
</div>
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-cash-stack"></i>
</div>
</div>
</div>

</div>

{{-- Filters --}}
<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

<div class="lg:col-span-7">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
<div class="relative">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="exitSearch" type="text" placeholder="Search exit no or reason..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>
</div>

<div class="lg:col-span-3">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</label>
<select id="statusFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none">
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

<div class="lg:col-span-2">
<button type="button" onclick="clearFilters()" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
Clear
</button>
</div>

</div>
</div>

{{-- Exit Portfolio --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
<i class="bi bi-box-arrow-right"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Exit Requests</h2>
<p class="text-[11px] text-slate-400">Your resignation, assessment and settlement history</p>
</div>
</div>

<div id="exitList" class="grid gap-3 p-4 lg:grid-cols-2">
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-red-600"></div>
<p class="mt-3 text-sm text-slate-400">Loading requests...</p>
</div>
</div>

<div id="paginationContainer"></div>
</div>

</div>

{{-- Request Modal --}}
<div id="exitModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
<i class="bi bi-box-arrow-right"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Resignation Request</h2>
<p class="text-[11px] text-slate-400">Submit a permanent membership cancel request.</p>
</div>
</div>

<button type="button" onclick="closeExitModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="exitForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

<div id="exitError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<div class="rounded-lg border border-red-200 bg-red-50/50 p-4">
<div class="flex gap-3">
<i class="bi bi-exclamation-triangle mt-0.5 text-red-500"></i>
<p class="text-[11px] leading-5 text-red-700">
Submitting this request starts the exit process only. Your membership remains active until review, settlement and final closure are completed.
</p>
</div>
</div>

<div>
<label class="form-label">Reason <span class="text-red-500">*</span></label>
<textarea id="reason" rows="5" maxlength="5000" class="app-input w-full resize-none" placeholder="Describe the reason for leaving the association..."></textarea>
<p data-field-error="reason" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Preferred Exit Date</label>

<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

<input id="exitDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select preferred date" autocomplete="off">
</div>

<p data-field-error="exitDate" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeExitModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">
Cancel
</button>

<button id="submitExitButton" type="submit" class="h-9 rounded-md bg-red-600 px-4 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
Submit Request
</button>
</div>

</form>
</div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
<i class="bi bi-box-arrow-right"></i>
</div>
<div>
<h2 class="text-base font-bold text-slate-800">Membership Cancel</h2>
<p id="detailsExitNo" class="font-mono text-[10px] text-slate-400"></p>
</div>
</div>

<button type="button" onclick="closeDetailsModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<div id="detailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

<div class="flex justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeDetailsModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">
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
const API='/api/member/exit';
const currency=@json($currency);

let exits=[];
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

function totalLiability(item){
return Number(item.subscription_due??0)
+Number(item.charge_due??0)
+Number(item.loan_due??0);
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

async function loadExits(page=1){
currentPage=page;

const button=$('refreshButton');
const icon=$('refreshIcon');

button.disabled=true;
icon.classList.add('animate-spin');

$('exitList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-red-600"></div>
<p class="mt-3 text-sm text-slate-400">Loading requests...</p>
</div>`;

try{
const params=new URLSearchParams({
page,
per_page:10
});

const search=$('exitSearch').value.trim();
const status=$('statusFilter').value;

if(search)params.set('search',search);
if(status)params.set('status',status);

const response=await api(`${API}?${params.toString()}`);
const payload=response.data??[];

if(Array.isArray(payload)){
exits=payload;
currentPage=1;
lastPage=1;
total=payload.length;
}else{
exits=Array.isArray(payload.data)?payload.data:[];
currentPage=Number(payload.current_page??1);
lastPage=Number(payload.last_page??1);
total=Number(payload.total??exits.length);
}

renderExits();
renderSummary(response.summary??payload.summary??null);
renderPagination(payload);

}catch(error){
$('exitList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-exclamation-circle"></i>
</div>

<p class="mt-3 text-base font-semibold text-red-600">Failed to load exit requests</p>
<p class="mt-1 text-sm text-red-400">${escapeHtml(AdminUI.extractError(error))}</p>

<button type="button" onclick="loadExits(currentPage)" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-arrow-clockwise"></i>
Try Again
</button>
</div>`;

$('paginationContainer').innerHTML='';
}finally{
button.disabled=false;
icon.classList.remove('animate-spin');
}
}

function renderSummary(summary){
if(summary){
$('summaryTotal').textContent=Number(summary.total??0);
$('summaryPending').textContent=Number(summary.pending??summary.in_process??0);
$('summaryLiability').textContent=money(summary.total_liabilities??0);
$('summaryRefund').textContent=money(summary.share_refund??summary.total_share_refund??0);
return;
}

$('summaryTotal').textContent=total||exits.length;

$('summaryPending').textContent=exits.filter(item=>
['submitted','under_review','liabilities_pending','ready_for_approval','approved'].includes(item.status)
).length;

$('summaryLiability').textContent=money(
exits.reduce((sum,item)=>sum+totalLiability(item),0)
);

$('summaryRefund').textContent=money(
exits.reduce((sum,item)=>sum+Number(item.share_refund??0),0)
);
}

function renderExits(){
if(!exits.length){
$('exitList').innerHTML=`
<div class="col-span-full py-14 text-center">
<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-box-arrow-right text-xl"></i>
</div>

<p class="mt-4 text-base font-semibold text-slate-700">No Exit Request</p>
<p class="mt-1 text-sm text-slate-400">No membership resignation request matches the current filters.</p>
</div>`;
return;
}

$('exitList').innerHTML=exits.map(item=>{
const blockers=(item.items??[]).filter(row=>row.is_blocking);
const liability=totalLiability(item);

return`
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-red-200 hover:shadow-sm">

<div class="border-b border-slate-100 p-4">
<div class="flex items-start justify-between gap-3">

<div class="flex min-w-0 items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
<i class="bi bi-box-arrow-right"></i>
</div>

<div>
<div class="flex flex-wrap items-center gap-2">
<p class="font-mono text-sm font-semibold text-red-600">
${escapeHtml(item.exit_no)}
</p>
${statusBadge(item.status)}
</div>

<p class="mt-1 text-[11px] text-slate-400">
Requested ${dateText(item.request_date)}
</p>
</div>
</div>

<button type="button" onclick="openDetails(${Number(item.id)})" class="inline-flex h-8 shrink-0 items-center gap-1 rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600 hover:border-red-200 hover:bg-red-50 hover:text-red-700">
<i class="bi bi-eye"></i>
View
</button>

</div>
</div>

<div class="space-y-3 p-4">

<div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
${amountBox('Subscription',money(item.subscription_due),'amber')}
${amountBox('Charges',money(item.charge_due),'orange')}
${amountBox('Loan',money(item.loan_due),'red')}
${amountBox('Share Refund',money(item.share_refund),'emerald')}
</div>

<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">Reason</p>
<p class="mt-1 line-clamp-3 text-[11px] leading-5 text-slate-600">
${escapeHtml(item.reason)}
</p>
</div>

<div class="grid grid-cols-2 gap-2">
${miniBox('Total Liability',money(liability))}
${miniBox('Blockers',String(blockers.length))}
</div>

${blockers.length?`
<div class="rounded-md border border-red-200 bg-red-50 p-3">
<p class="text-[10px] font-semibold text-red-700">
<i class="bi bi-exclamation-triangle mr-1"></i>
${blockers.length} item${blockers.length===1?'':'s'} must be resolved
</p>

<div class="mt-2 space-y-1">
${blockers.slice(0,3).map(blocker=>`
<p class="text-[10px] text-red-600">
• ${escapeHtml(blocker.description)}
${Number(blocker.amount??0)>0?' — '+money(blocker.amount):''}
</p>
`).join('')}
</div>

${blockers.length>3?`
<p class="mt-2 text-[10px] font-medium text-red-500">
+${blockers.length-3} more
</p>`:''}
</div>`:`
<div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
<p class="text-[10px] font-semibold text-emerald-700">
<i class="bi bi-check2-circle mr-1"></i>
No current blocking items
</p>
</div>`}

</div>

${canCancel(item)?`
<div class="flex justify-end border-t border-slate-100 bg-slate-50/50 px-4 py-3">
<button type="button" onclick="cancelExit(${Number(item.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-red-200 bg-white px-3 text-[11px] font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-x-circle"></i>
Cancel Request
</button>
</div>`:''}

</article>`;
}).join('');
}

function canCancel(item){
return item.member_initiated&&[
'submitted',
'under_review',
'liabilities_pending',
'ready_for_approval'
].includes(item.status);
}

function amountBox(label,value,tone){
const map={
amber:'text-amber-700',
orange:'text-orange-700',
red:'text-red-700',
emerald:'text-emerald-700'
};

return`
<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
<p class="mt-1 truncate text-sm font-bold ${map[tone]??'text-slate-700'}">${escapeHtml(value)}</p>
</div>`;
}

function miniBox(label,value){
return`
<div class="rounded-md border border-slate-200 bg-slate-50/50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
<p class="mt-1 text-sm font-bold text-slate-700">${escapeHtml(value)}</p>
</div>`;
}

function renderPagination(meta){
const container=$('paginationContainer');

if(Array.isArray(meta)||lastPage<=1){
container.innerHTML='';
return;
}

const perPage=Number(meta.per_page??10);
const from=Number(meta.from??((currentPage-1)*perPage)+1);
const to=Number(meta.to??Math.min(currentPage*perPage,total));

container.innerHTML=`
<div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

<p class="text-[11px] text-slate-500">
Showing
<span class="font-semibold  text-xs 2xl:text-sm text-slate-700">${from}</span>
–
<span class="font-semibold  text-xs 2xl:text-sm text-slate-700">${to}</span>
of
<span class="font-semibold  text-xs 2xl:text-sm text-slate-700">${total}</span>
requests
</p>

<div class="flex items-center gap-1">
<button type="button"
${currentPage<=1?'disabled':''}
onclick="loadExits(${currentPage-1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 disabled:opacity-40">
<i class="bi bi-chevron-left"></i>
Previous
</button>

<span class="px-3 text-[11px] font-medium text-slate-500">
${currentPage} / ${lastPage}
</span>

<button type="button"
${currentPage>=lastPage?'disabled':''}
onclick="loadExits(${currentPage+1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 disabled:opacity-40">
Next
<i class="bi bi-chevron-right"></i>
</button>
</div>

</div>`;
}

window.openExitModal=function(){
$('exitForm').reset();

AdminUI.clearError('exitError');
AdminUI.clearFieldErrors('exitForm');

openModal('exitModal');
window.initDatePickers?.();
};

window.closeExitModal=function(){
closeModal('exitModal');
};

$('exitForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('exitError');
AdminUI.clearFieldErrors('exitForm');

if(!AdminUI.validateForm('exitForm',{
reason:'Reason is required.'
})){
return;
}

const button=$('submitExitButton');
AdminUI.setLoading(button,'Submitting...');

try{
const response=await api(API,{
method:'POST',
body:JSON.stringify({
reason:$('reason').value.trim(),
proposed_exit_date:$('exitDate').value||null
})
});

closeExitModal();

Toast.success(
response.message??
'Membership cancel request submitted successfully.'
);

await loadExits(1);

}catch(error){
if(!AdminUI.showValidationErrors('exitForm',error,{
reason:'reason',
proposed_exit_date:'exitDate'
})){
AdminUI.showError(
'exitError',
AdminUI.extractError(error)
);
}
}finally{
AdminUI.resetLoading(button);
}
});

window.cancelExit=function(id){
const item=exits.find(row=>Number(row.id)===Number(id));

if(!item){
Toast.error('Exit request not found.');
return;
}

AdminUI.request(
`${API}/${id}/cancel`,
{
method:'POST',
data:{},
confirmation:{
title:'Cancel Resignation Request?',
message:`Cancel membership cancel request ${item.exit_no}?`,
confirmText:'Cancel Request',
type:'danger'
},
successMessage:'Membership cancel request cancelled successfully.',
onSuccess:async()=>{
await loadExits(currentPage);
}
}
);
};

window.openDetails=function(id){
const item=exits.find(row=>Number(row.id)===Number(id));

if(!item){
Toast.error('Exit request not found.');
return;
}

const blockers=(item.items??[]).filter(row=>row.is_blocking);

$('detailsExitNo').textContent=item.exit_no??'';

$('detailsBody').innerHTML=`
<div class="space-y-5">

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
${summaryBox('Liability',money(totalLiability(item)))}
${summaryBox('Share Refund',money(item.share_refund))}
${summaryBox('Blockers',String(blockers.length))}
${summaryBox('Status',titleCase(item.status))}
</div>

<div class="overflow-hidden rounded-lg border border-slate-200">
<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">Exit Information</h3>
</div>

<div class="grid sm:grid-cols-2">
${detailItem('Exit Number',item.exit_no??'—')}
${detailItem('Status',statusBadge(item.status),true)}
${detailItem('Request Date',dateText(item.request_date))}
${detailItem('Preferred Exit',dateText(item.proposed_exit_date))}
${detailItem('Subscription Due',money(item.subscription_due))}
${detailItem('Charges Due',money(item.charge_due))}
${detailItem('Loan Due',money(item.loan_due))}
${detailItem('Share Refund',money(item.share_refund))}
${detailItem('Net Settlement',money(item.net_settlement_amount))}
${detailItem('Final Exit Date',dateText(item.final_exit_date??item.closed_at))}
</div>
</div>

<div class="rounded-lg border border-slate-200 p-4">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Reason</p>
<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">
${escapeHtml(item.reason??'—')}
</p>
</div>

${renderBlockers(item.items??[])}

${item.rejection_reason?`
<div class="rounded-lg border border-red-200 bg-red-50 p-4">
<p class="text-sm font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Rejection Reason
</p>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-red-600">
${escapeHtml(item.rejection_reason)}
</p>
</div>`:''}

</div>`;

openModal('detailsModal');
};

function renderBlockers(items){
if(!items.length)return'';

return`
<div class="overflow-hidden rounded-lg border border-slate-200">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">Assessment Items</h3>
<p class="text-[11px] text-slate-400">Financial and operational exit assessment</p>
</div>

<div class="divide-y divide-slate-100">
${items.map(item=>`
<div class="flex items-start justify-between gap-3 px-4 py-3">

<div class="flex min-w-0 gap-3">
<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md ${item.is_blocking?'bg-red-50 text-red-600':'bg-emerald-50 text-emerald-600'}">
<i class="bi ${item.is_blocking?'bi-exclamation-circle':'bi-check2-circle'}"></i>
</div>

<div>
<p class="text-sm font-semibold text-slate-700">${escapeHtml(item.description)}</p>
<p class="mt-0.5 text-[10px] capitalize text-slate-400">
${escapeHtml(String(item.category??'').replaceAll('_',' '))}
</p>
</div>
</div>

${Number(item.amount??0)>0?`
<span class="shrink-0 text-sm font-bold ${item.is_blocking?'text-red-600':'text-slate-600'}">
${money(item.amount)}
</span>`:''}

</div>
`).join('')}
</div>

</div>`;
}

window.closeDetailsModal=function(){
closeModal('detailsModal');
};

function summaryBox(label,value){
return`
<div class="rounded-lg border border-slate-200 bg-white p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
<p class="mt-2 truncate text-sm font-bold text-slate-700">${escapeHtml(value)}</p>
</div>`;
}

function detailItem(label,value,html=false){
return`
<div class="border-b border-slate-100 px-5 py-3 sm:odd:border-r">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
<div class="mt-1 text-sm font-semibold text-slate-700">
${html?value:escapeHtml(value)}
</div>
</div>`;
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
$('exitSearch').value='';
$('statusFilter').value='';
loadExits(1);
};

function openModal(id){
if(typeof AdminUI!=='undefined'){
AdminUI.openModal(id);
return;
}

$(id)?.classList.remove('hidden');
$(id)?.classList.add('flex');
}

function closeModal(id){
if(typeof AdminUI!=='undefined'){
AdminUI.closeModal(id);
return;
}

$(id)?.classList.add('hidden');
$(id)?.classList.remove('flex');
}

function escapeHtml(value){
const div=document.createElement('div');
div.textContent=String(value??'');
return div.innerHTML;
}

async function init(){
if(typeof AdminUI==='undefined'||typeof api==='undefined'){
setTimeout(init,50);
return;
}

$('exitSearch').addEventListener(
'input',
AdminUI.debounce(()=>loadExits(1))
);

$('statusFilter').addEventListener(
'change',
()=>loadExits(1)
);
window.initDatePickers?.();

await loadExits();
}

init();
</script>
@endpush