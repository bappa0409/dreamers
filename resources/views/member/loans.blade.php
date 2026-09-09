@extends('layouts.member')

@section('title','Loans')
@section('page-title','Loans')

@section('content')
@php
$currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-3">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
<i class="bi bi-bank"></i>
</div>

<div>
<h1 class="text-base font-semibold tracking-tight text-slate-800">Loans</h1>
<p class="mt-0.5 text-sm text-slate-500">Request and track association loans, approvals, repayment status and maturity.</p>
</div>
</div>

<div class="flex flex-wrap items-center gap-2">
<button type="button" onclick="loadLoans(currentPage)" class="inline-flex h-9 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
<i class="bi bi-arrow-clockwise"></i>
Refresh
</button>

<button type="button" onclick="openRequestModal()" class="inline-flex h-9 items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-circle"></i>
Request Loan
</button>
</div>
</div>

{{-- Information --}}
<div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-indigo-800">Association Loan</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
Loan requests remain pending until reviewed and approved. After disbursement, repayments reduce the outstanding balance until the loan is fully repaid.
</p>
</div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Total Loans</p>
<p id="summaryTotal" class="text-xl font-bold text-slate-800">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">Pending</p>
<p id="summaryPending" class="text-xl font-bold text-amber-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-hourglass-split"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Active Loans</p>
<p id="summaryActive" class="text-xl font-bold text-emerald-700">0</p>
</div>

<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-cash-stack"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div class="min-w-0">
<p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">Outstanding</p>
<p id="summaryOutstanding" class="mt-2 truncate text-xl font-bold text-violet-700">{{ $currency }}0.00</p>
</div>

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
<i class="bi bi-wallet2"></i>
</div>
</div>
</div>

</div>

{{-- Filters --}}
<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

<div class="lg:col-span-7">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
Search
</label>

<div class="relative">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>

<input id="loanSearch" type="text" placeholder="Search loan no, purpose or status..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>
</div>

<div class="lg:col-span-3">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
Status
</label>

<select id="loanStatusFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
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
</div>

<div class="lg:col-span-2">
<button type="button" onclick="clearLoanFilters()" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
Clear
</button>
</div>

</div>
</div>

{{-- Loan Portfolio --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>

<div>
<h2 class="text-sm font-semibold text-slate-800">Loan Portfolio</h2>
<p class="text-[11px] text-slate-500">Your association loan request and repayment history</p>
</div>
</div>

<div id="loanList" class="grid gap-3 p-4 lg:grid-cols-2">
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-500">Loading loans...</p>
</div>
</div>

<div id="pagination"></div>
</div>

</div>

{{-- Loan Details Modal --}}
<div id="loanDetailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Loan Details</h2>
<p id="loanDetailNumber" class="font-mono text-[10px] text-slate-500">-</p>
</div>
</div>

<button type="button" onclick="closeLoanDetails()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600">
<i class="bi bi-x-lg"></i>
</button>
</div>

<div id="loanDetailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

<div class="flex shrink-0 justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeLoanDetails()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
Close
</button>
</div>

</div>
</div>

{{-- Request Loan Modal --}}
<div id="requestLoanModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-plus-circle"></i>
</div>

<div>
<h2 class="text-base font-bold text-slate-800">Request Loan</h2>
<p class="text-[11px] text-slate-500">Submit a new association loan request.</p>
</div>
</div>

<button type="button" onclick="closeRequestModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="requestLoanForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">

<div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

<div id="loanRequestError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex gap-3">
<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-indigo-800">Loan Request</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
Requested amount may be adjusted during approval. No financial transaction is created until the approved loan is disbursed.
</p>
</div>
</div>
</div>

<div>
<label class="form-label">Requested Amount <span class="text-red-500">*</span></label>

<div class="relative">
<span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-500">{{ $currency }}</span>

<input id="amount" type="number" min="0.01" step="0.01" class="app-input w-full !pl-8" placeholder="0.00">
</div>

<p data-field-error="amount" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Purpose <span class="text-red-500">*</span></label>

<textarea id="purpose" rows="4" maxlength="5000" class="app-input w-full resize-none" placeholder="Describe the purpose of this loan..."></textarea>

<p data-field-error="purpose" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Additional Note</label>

<textarea id="notes" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Optional note"></textarea>

<p data-field-error="notes" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

<button type="button" onclick="closeRequestModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
Cancel
</button>

<button id="requestLoanButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">
Submit Request
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
const API='/api/member/loans';
const loanCurrency=@json($currency);

let loans=[];
let currentPage=1;
let lastPage=1;
let totalLoans=0;

const $=id=>document.getElementById(id);

function money(value){
return loanCurrency+Number(value??0).toLocaleString('en-US',{
minimumFractionDigits:2,
maximumFractionDigits:2
});
}

function dateText(value){
if(!value)return'—';

const parsed=new Date(value);

if(Number.isNaN(parsed.getTime())){
return value;
}

return parsed.toLocaleDateString('en-GB',{
day:'2-digit',
month:'short',
year:'numeric'
});
}

function outstandingAmount(loan){
if(loan.outstanding_amount!==undefined&&loan.outstanding_amount!==null){
return Number(loan.outstanding_amount);
}

if(loan.outstanding_principal!==undefined&&loan.outstanding_principal!==null){
return Number(loan.outstanding_principal);
}

if(loan.status==='repaid'){
return 0;
}

if(['active','overdue','defaulted'].includes(loan.status)){
return Number(loan.total_payable??loan.approved_amount??0);
}

return 0;
}

async function loadLoans(page=1){
currentPage=page;

$('loanList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-500">Loading loans...</p>
</div>`;

$('pagination').innerHTML='';

const params=new URLSearchParams({
page,
per_page:10
});

const search=$('loanSearch').value.trim();
const status=$('loanStatusFilter').value;

if(search)params.set('search',search);
if(status)params.set('status',status);

try{
const response=await api(`${API}?${params.toString()}`);
const paginator=response.data??{};

loans=Array.isArray(paginator.data)
?paginator.data
:[];

currentPage=Number(paginator.current_page??1);
lastPage=Number(paginator.last_page??1);
totalLoans=Number(paginator.total??loans.length);

renderLoans();
renderPagination(paginator);
renderSummary(paginator);

}catch(error){

$('loanList').innerHTML=`
<div class="col-span-full py-12 text-center">

<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-exclamation-circle text-lg"></i>
</div>

<p class="mt-3 text-base font-semibold text-red-600">
Failed to load loans
</p>

<p class="mt-1 text-sm text-red-400">
${escapeHtml(AdminUI.extractError(error))}
</p>

<button type="button" onclick="loadLoans(currentPage)" class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">
<i class="bi bi-arrow-clockwise"></i>
Try Again
</button>

</div>`;

}
}

function renderSummary(paginator){
const meta=paginator.summary??paginator.meta?.summary??null;

if(meta){
$('summaryTotal').textContent=Number(meta.total??0);
$('summaryPending').textContent=Number(meta.pending??0);
$('summaryActive').textContent=Number(meta.active??0);
$('summaryOutstanding').textContent=money(meta.outstanding??0);
return;
}

const pageLoans=loans;

$('summaryTotal').textContent=Number(
paginator.total??pageLoans.length
);

$('summaryPending').textContent=pageLoans.filter(
loan=>loan.status==='pending'
).length;

$('summaryActive').textContent=pageLoans.filter(
loan=>['active','overdue','defaulted'].includes(loan.status)
).length;

$('summaryOutstanding').textContent=money(
pageLoans.reduce(
(total,loan)=>total+outstandingAmount(loan),
0
)
);
}

function renderLoans(){
const root=$('loanList');

if(!loans.length){
root.innerHTML=`
<div class="col-span-full py-14 text-center">

<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
<i class="bi bi-bank text-xl"></i>
</div>

<p class="mt-4 text-base font-semibold text-slate-700">
No loans found
</p>

<p class="mt-1 text-sm text-slate-500">
No loan records match the current filters.
</p>

</div>`;
return;
}

root.innerHTML=loans.map(loan=>renderLoan(loan)).join('');
}

function renderLoan(loan){
const outstanding=outstandingAmount(loan);

return`
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-indigo-200 hover:shadow-sm">

<div class="border-b border-slate-100 p-4">

<div class="flex items-start justify-between gap-3">

<div class="flex min-w-0 items-start gap-3">

<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-bank"></i>
</div>

<div class="min-w-0">
<div class="flex flex-wrap items-center gap-2">

<p class="font-mono text-sm font-semibold text-indigo-600">
${escapeHtml(loan.loan_no??'-')}
</p>

${statusBadge(loan.status)}

</div>

<p class="mt-1 text-[11px] text-slate-500">
Requested ${dateText(loan.request_date)}
</p>
</div>

</div>

<button type="button" onclick="openLoanDetails(${Number(loan.id)})" class="inline-flex h-8 shrink-0 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-2.5 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
<i class="bi bi-eye"></i>
View
</button>

</div>
</div>

<div class="space-y-3 p-4">

<div class="grid grid-cols-2 gap-2 sm:grid-cols-4">

${box(
'Requested',
money(loan.requested_amount),
'slate'
)}

${box(
'Approved',
loan.approved_amount
?money(loan.approved_amount)
:'—',
'indigo'
)}

${box(
'Payable',
loan.total_payable
?money(loan.total_payable)
:'—',
'sky'
)}

${box(
'Outstanding',
outstanding>0
?money(outstanding)
:'—',
outstanding>0?'red':'emerald'
)}

</div>

${loan.maturity_date?`
<div class="flex items-center justify-between rounded-md border border-slate-200 bg-slate-50 p-3">
<div class="flex items-center gap-2">
<i class="bi bi-calendar-check text-indigo-500"></i>
<span class="text-[11px] text-slate-500">Maturity Date</span>
</div>

<span class="text-sm font-semibold text-slate-700">
${dateText(loan.maturity_date)}
</span>
</div>`:''}

${loan.purpose?`
<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">
Purpose
</p>

<p class="mt-1 line-clamp-2 text-[11px] leading-5 text-slate-600">
${escapeHtml(loan.purpose)}
</p>
</div>`:''}

${loan.rejection_reason?`
<div class="rounded-md border border-red-200 bg-red-50 p-3">

<p class="text-[10px] font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Loan Request Rejected
</p>

<p class="mt-1 text-[11px] leading-5 text-red-600">
${escapeHtml(loan.rejection_reason)}
</p>

</div>`:''}

</div>

${['pending','approved'].includes(loan.status)?`
<div class="flex justify-end border-t border-slate-100 bg-slate-50/50 px-4 py-3">

<button type="button" onclick="cancelLoan(${Number(loan.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-red-200 bg-white px-3 text-[11px] font-semibold text-red-600 transition hover:bg-red-50">
<i class="bi bi-x-circle"></i>
Cancel Request
</button>

</div>`:''}

</article>`;
}

function box(label,value,tone='slate'){
const tones={
slate:'text-slate-700',
indigo:'text-indigo-700',
sky:'text-sky-700',
emerald:'text-emerald-700',
amber:'text-amber-700',
red:'text-red-700'
};

return`
<div class="rounded-md bg-slate-50 p-3">

<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">
${escapeHtml(label)}
</p>

<p class="mt-1 truncate text-sm font-bold ${tones[tone]??tones.slate}">
${escapeHtml(value)}
</p>

</div>`;
}

function renderPagination(meta){
const container=$('pagination');

const current=Number(meta.current_page??1);
const last=Number(meta.last_page??1);
const total=Number(meta.total??0);

if(last<=1){
container.innerHTML='';
return;
}

const perPage=Number(meta.per_page??10);

const from=Number(
meta.from??
((current-1)*perPage)+1
);

const to=Number(
meta.to??
Math.min(current*perPage,total)
);

container.innerHTML=`
<div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

<p class="text-[11px] text-slate-500">
Showing
<span class="font-semibold text-xs text-slate-700">${from}</span>
–
<span class="font-semibold text-xs text-slate-700">${to}</span>
of
<span class="font-semibold text-xs text-slate-700">${total}</span>
loans
</p>

<div class="flex items-center gap-1">

<button
type="button"
${current<=1?'disabled':''}
onclick="loadLoans(${current-1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

<i class="bi bi-chevron-left"></i>
Previous
</button>

<span class="px-3 text-[11px] font-medium text-slate-500">
${current} / ${last}
</span>

<button
type="button"
${current>=last?'disabled':''}
onclick="loadLoans(${current+1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

Next
<i class="bi bi-chevron-right"></i>
</button>

</div>
</div>`;
}

window.openRequestModal=function(){
$('requestLoanForm').reset();

AdminUI.clearError('loanRequestError');
AdminUI.clearFieldErrors('requestLoanForm');

openModal('requestLoanModal');
};

window.closeRequestModal=function(){
closeModal('requestLoanModal');
};

$('requestLoanForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('loanRequestError');
AdminUI.clearFieldErrors('requestLoanForm');

if(!AdminUI.validateForm('requestLoanForm',{
amount:'Requested amount is required.',
purpose:'Loan purpose is required.'
})){
return;
}

const amount=Number($('amount').value);

if(!Number.isFinite(amount)||amount<=0){
AdminUI.showFieldError(
'amount',
'Requested amount must be greater than zero.'
);
return;
}

const payload={
requested_amount:amount,
purpose:$('purpose').value.trim(),
notes:$('notes').value.trim()||null
};

const button=$('requestLoanButton');

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
'Loan request submitted successfully.'
);

await loadLoans(1);

}catch(error){

if(!AdminUI.showValidationErrors(
'requestLoanForm',
error,
{
requested_amount:'amount',
purpose:'purpose',
notes:'notes'
}
)){
AdminUI.showError(
'loanRequestError',
AdminUI.extractError(error)
);
}

}finally{
AdminUI.resetLoading(button);
}
});

window.cancelLoan=function(id){
const loan=loans.find(
item=>Number(item.id)===Number(id)
);

if(!loan){
Toast.error('Loan record not found.');
return;
}

AdminUI.request(
`${API}/${id}/cancel`,
{
method:'POST',
data:{},
confirmation:{
title:'Cancel Loan Request?',
message:`Cancel loan request ${loan.loan_no}?`,
confirmText:'Cancel Request',
type:'danger'
},
successMessage:'Loan request cancelled successfully.',
onSuccess:async()=>{
await loadLoans(currentPage);
}
}
);
};

window.openLoanDetails=function(id){
const loan=loans.find(
item=>Number(item.id)===Number(id)
);

if(!loan){
Toast.error('Loan record not found.');
return;
}

$('loanDetailNumber').textContent=
loan.loan_no??'-';

const outstanding=outstandingAmount(loan);

$('loanDetailsBody').innerHTML=`
<div class="space-y-3">

<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">

${summaryCard(
'Approved Amount',
loan.approved_amount
?money(loan.approved_amount)
:'—',
'bi-cash-stack',
'indigo'
)}

${summaryCard(
'Total Payable',
loan.total_payable
?money(loan.total_payable)
:'—',
'bi-wallet2',
'sky'
)}

${summaryCard(
'Outstanding',
outstanding>0
?money(outstanding)
:money(0),
'bi-hourglass-split',
outstanding>0?'amber':'emerald'
)}

${summaryCard(
'Status',
title(loan.status),
'bi-patch-check',
statusTone(loan.status)
)}

</div>

<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">
Loan Information
</h3>

<p class="text-[11px] text-slate-500">
Request, approval, disbursement and repayment details
</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2">

${detailItem(
'Loan Number',
loan.loan_no??'—'
)}

${detailItem(
'Status',
statusBadge(loan.status),
true
)}

${detailItem(
'Requested Amount',
money(loan.requested_amount)
)}

${detailItem(
'Approved Amount',
loan.approved_amount
?money(loan.approved_amount)
:'—'
)}

${detailItem(
'Interest Rate',
loan.interest_rate!==null&&
loan.interest_rate!==undefined
?Number(loan.interest_rate).toFixed(2)+'%'
:'—'
)}

${detailItem(
'Interest Amount',
loan.interest_amount
?money(loan.interest_amount)
:'—'
)}

${detailItem(
'Total Payable',
loan.total_payable
?money(loan.total_payable)
:'—'
)}

${detailItem(
'Outstanding',
money(outstanding)
)}

${detailItem(
'Request Date',
dateText(loan.request_date)
)}

${detailItem(
'Approval Date',
dateText(
loan.approved_at??
loan.approval_date
)
)}

${detailItem(
'Disbursement Date',
dateText(loan.disbursement_date)
)}

${detailItem(
'Maturity Date',
dateText(loan.maturity_date)
)}

${detailItem(
'Duration',
loan.duration_months
?loan.duration_months+' months'
:'—'
)}

${detailItem(
'Total Repaid',
loan.total_repaid!==undefined
?money(loan.total_repaid)
:'—'
)}

</div>
</div>

<div class="rounded-lg border border-slate-200 bg-white p-4">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
Purpose
</p>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">
${escapeHtml(loan.purpose??'—')}
</p>
</div>

${loan.notes?`
<div class="rounded-lg border border-slate-200 bg-white p-4">

<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
Additional Note
</p>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">
${escapeHtml(loan.notes)}
</p>

</div>`:''}

${loan.rejection_reason?`
<div class="rounded-lg border border-red-200 bg-red-50 p-4">

<p class="text-sm font-semibold text-red-700">
<i class="bi bi-x-circle mr-1"></i>
Rejection Reason
</p>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-red-600">
${escapeHtml(loan.rejection_reason)}
</p>

</div>`:''}

${renderRepayments(
loan.id,
loan.repayments??[]
)}

</div>`;

openModal('loanDetailsModal');
};

function renderRepayments(loanId,repayments){
if(!repayments.length){
return'';
}

return`
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-sm font-semibold text-slate-800">
Repayment History
</h3>

<p class="text-[11px] text-slate-500">
Payments received against this loan
</p>
</div>

<div class="divide-y divide-slate-100">

${repayments.map(payment=>`
<div class="flex items-center justify-between gap-3 px-4 py-3">

<div class="flex items-center gap-3">

<div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
<i class="bi bi-wallet2"></i>
</div>

<div>
<p class="text-sm font-semibold text-slate-700">
${money(
payment.total_amount??
payment.amount
)}
</p>

<p class="mt-0.5 text-[10px] text-slate-500">
${dateText(
payment.repayment_date??
payment.payment_date
)}
</p>
</div>
</div>

<div class="flex items-center gap-2">
<span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
Received
</span>
<button type="button"
onclick="downloadPdf('/api/member/loans/${Number(loanId)}/repayments/${Number(payment.id)}/receipt','loan-repayment-${Number(loanId)}-${Number(payment.id)}.pdf',this)"
class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
title="Download PDF">
<i class="bi bi-file-earmark-pdf"></i>
</button>
</div>

</div>
`).join('')}

</div>
</div>`;
}

window.closeLoanDetails=function(){
closeModal('loanDetailsModal');
};

function summaryCard(label,value,icon,tone){
const tones={
indigo:'bg-indigo-50 text-indigo-600',
sky:'bg-sky-50 text-sky-600',
emerald:'bg-emerald-50 text-emerald-600',
amber:'bg-amber-50 text-amber-600',
red:'bg-red-50 text-red-600',
slate:'bg-slate-100 text-slate-600'
};

return`
<div class="rounded-lg border border-slate-200 bg-white p-4">

<div class="flex items-start justify-between gap-2">

<div class="min-w-0">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">
${escapeHtml(label)}
</p>

<p class="mt-2 truncate text-base font-bold text-slate-700">
${escapeHtml(value)}
</p>
</div>

<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${tones[tone]??tones.slate}">
<i class="bi ${icon}"></i>
</div>

</div>
</div>`;
}

function detailItem(label,value,html=false){
return`
<div class="border-b border-slate-100 px-5 py-3 sm:odd:border-r">

<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-500">
${escapeHtml(label)}
</p>

<div class="mt-1 text-sm font-semibold text-slate-700">
${html?value:escapeHtml(value)}
</div>

</div>`;
}

function statusBadge(status){
const map={
pending:[
'Pending',
'bg-amber-50 text-amber-700',
'bi-hourglass-split'
],
approved:[
'Approved',
'bg-blue-50 text-blue-700',
'bi-patch-check'
],
active:[
'Active',
'bg-emerald-50 text-emerald-700',
'bi-play-circle'
],
overdue:[
'Overdue',
'bg-red-50 text-red-700',
'bi-exclamation-triangle'
],
defaulted:[
'Defaulted',
'bg-red-100 text-red-800',
'bi-exclamation-octagon'
],
repaid:[
'Repaid',
'bg-indigo-50 text-indigo-700',
'bi-check2-circle'
],
rejected:[
'Rejected',
'bg-rose-50 text-rose-700',
'bi-x-circle'
],
cancelled:[
'Cancelled',
'bg-slate-100 text-slate-500',
'bi-slash-circle'
]
};

const item=map[status]??[
title(status),
'bg-slate-100 text-slate-500',
'bi-circle'
];

return`
<span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[9px] font-semibold uppercase ${item[1]}">
<i class="bi ${item[2]}"></i>
${escapeHtml(item[0])}
</span>`;
}

function statusTone(status){
return{
pending:'amber',
approved:'sky',
active:'emerald',
overdue:'red',
defaulted:'red',
repaid:'indigo',
rejected:'red',
cancelled:'slate'
}[status]??'slate';
}

function title(value){
return String(value??'')
.replaceAll('_',' ')
.replace(/\b\w/g,char=>char.toUpperCase());
}

window.clearLoanFilters=function(){
$('loanSearch').value='';
$('loanStatusFilter').value='';

loadLoans(1);
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

async function init(){
if(
typeof AdminUI==='undefined'||
typeof api==='undefined'
){
setTimeout(init,50);
return;
}

$('loanSearch').addEventListener(
'input',
AdminUI.debounce(
()=>loadLoans(1)
)
);

$('loanStatusFilter').addEventListener(
'change',
()=>loadLoans(1)
);
await loadLoans();
}

init();
</script>
@endpush