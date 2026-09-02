@extends('layouts.member')

@section('title','Feedback & Support')
@section('page-title','Feedback & Support')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
<div class="flex items-center gap-4">
<div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
<i class="bi bi-headset"></i>
</div>

<div>
<h1 class="text-base font-semibold tracking-tight text-slate-800">Feedback & Support</h1>
<p class="mt-0.5 text-sm text-slate-500">Send feedback, request support, submit complaints or suggestions and track progress.</p>
</div>
</div>

<div class="flex flex-wrap gap-2">
<button id="refreshButton" type="button" onclick="loadTickets(currentPage)" class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 disabled:opacity-60">
<i id="refreshIcon" class="bi bi-arrow-clockwise"></i>
Refresh
</button>

<button type="button" onclick="openCreateModal()" class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-circle"></i>
New Feedback / Support
</button>
</div>
</div>

{{-- Information --}}
<div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
<div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
<i class="bi bi-info-circle"></i>
</div>

<div>
<p class="text-sm font-semibold text-indigo-800">Feedback & Support Centre</p>
<p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
Use this area for feedback, complaints, suggestions, service issues or assistance requests. You can add follow-ups and supporting attachments while a request remains open.
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
<p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">Open</p>
<p id="summaryOpen" class="mt-2 text-xl font-bold text-amber-700">0</p>
</div>
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
<i class="bi bi-inbox"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-red-600">Urgent</p>
<p id="summaryUrgent" class="mt-2 text-xl font-bold text-red-700">0</p>
</div>
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
<i class="bi bi-exclamation-triangle"></i>
</div>
</div>
</div>

<div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">Resolved / Closed</p>
<p id="summaryResolved" class="mt-2 text-xl font-bold text-emerald-700">0</p>
</div>
<div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
<i class="bi bi-check2-circle"></i>
</div>
</div>
</div>

</div>

{{-- Filters --}}
<div class="rounded-lg border border-slate-200 bg-white p-4">
<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12 xl:items-end">

<div class="xl:col-span-5">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Search</label>
<div class="relative">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="ticketSearch" type="text" placeholder="Ticket no, subject, category..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>
</div>

<div class="xl:col-span-2">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Type</label>
<select id="typeFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none">
<option value="">All Types</option>
<option value="feedback">Feedback</option>
<option value="support_request">Support Request</option>
<option value="complaint">Complaint</option>
<option value="suggestion">Suggestion</option>
<option value="service_issue">Service Issue</option>
<option value="other">Other</option>
</select>
</div>

<div class="xl:col-span-2">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Status</label>
<select id="statusFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none">
<option value="">All Status</option>
<option value="submitted">Submitted</option>
<option value="under_review">Under Review</option>
<option value="assigned">Assigned</option>
<option value="in_progress">In Progress</option>
<option value="resolved">Resolved</option>
<option value="closed">Closed</option>
<option value="rejected">Rejected</option>
<option value="cancelled">Cancelled</option>
</select>
</div>

<div class="xl:col-span-2">
<label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">Priority</label>
<select id="priorityFilter" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none">
<option value="">All Priority</option>
<option value="urgent">Urgent</option>
<option value="high">High</option>
<option value="normal">Normal</option>
<option value="low">Low</option>
</select>
</div>

<div class="xl:col-span-1">
<button type="button" onclick="clearFilters()" class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
Clear
</button>
</div>

</div>
</div>

{{-- Ticket Portfolio --}}
<div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

<div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>

<div>
<h2 class="text-base font-semibold text-slate-800">Support Requests</h2>
<p class="text-[11px] text-slate-400">Your feedback, complaint and support request history</p>
</div>
</div>

<div id="ticketList" class="grid gap-3 p-4 lg:grid-cols-2">
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-400">Loading requests...</p>
</div>
</div>

<div id="paginationContainer"></div>
</div>

</div>

{{-- Create Modal --}}
<div id="createModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>
<div>
<h2 class="text-base font-bold text-slate-800">New Feedback & Support</h2>
<p class="text-[11px] text-slate-400">Send a new request to the association.</p>
</div>
</div>

<button type="button" onclick="closeCreateModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="createForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
<div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

<div id="createError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<div class="grid gap-4 sm:grid-cols-2">

<div>
<label class="form-label">Category <span class="text-red-500">*</span></label>
<select id="categoryId" class="app-input w-full">
<option value="">Select Category</option>
</select>
<p data-field-error="categoryId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Type <span class="text-red-500">*</span></label>
<select id="ticketType" class="app-input w-full">
<option value="">Select Type</option>
<option value="feedback">Feedback</option>
<option value="support_request">Support Request</option>
<option value="complaint">Complaint</option>
<option value="suggestion">Suggestion</option>
<option value="service_issue">Service Issue</option>
<option value="other">Other</option>
</select>
<p data-field-error="ticketType" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Priority <span class="text-red-500">*</span></label>
<select id="ticketPriority" class="app-input w-full">
<option value="normal">Normal</option>
<option value="low">Low</option>
<option value="high">High</option>
<option value="urgent">Urgent</option>
</select>
<p data-field-error="ticketPriority" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex items-end">
<label class="flex h-10 w-full items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600">
<input id="isConfidential" type="checkbox">
<i class="bi bi-lock text-slate-400"></i>
Confidential request
</label>
</div>

</div>

<div>
<label class="form-label">Subject <span class="text-red-500">*</span></label>
<input id="subject" type="text" maxlength="200" class="app-input w-full" placeholder="Briefly describe the issue">
<p data-field-error="subject" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Description <span class="text-red-500">*</span></label>
<textarea id="description" rows="6" maxlength="20000" class="app-input w-full resize-none" placeholder="Explain your feedback, complaint or support request..."></textarea>
<p data-field-error="description" class="mt-1 hidden text-sm text-red-600"></p>
</div>

</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeCreateModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">Cancel</button>
<button id="createButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white disabled:opacity-60">Submit Request</button>
</div>
</form>

</div>
</div>

{{-- Follow-up Modal --}}
<div id="followUpModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h2 class="text-base font-bold text-slate-800">Add Follow-up</h2>
<p id="followUpSubtitle" class="text-[11px] text-slate-400"></p>
</div>

<button type="button" onclick="closeFollowUpModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="followUpForm" novalidate data-js-validation="1">
<input id="followUpTicketId" type="hidden">

<div class="p-5">
<div id="followUpError" class="mb-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<label class="form-label">Follow-up Message <span class="text-red-500">*</span></label>
<textarea id="followUpMessage" rows="5" maxlength="10000" class="app-input w-full resize-none" placeholder="Add more information or ask for an update..."></textarea>
<p data-field-error="followUpMessage" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeFollowUpModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">Cancel</button>
<button id="followUpButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white disabled:opacity-60">Add Follow-up</button>
</div>
</form>

</div>
</div>

{{-- Attachment Modal --}}
<div id="attachmentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div>
<h2 class="text-base font-bold text-slate-800">Add Attachment</h2>
<p id="attachmentSubtitle" class="text-[11px] text-slate-400"></p>
</div>

<button type="button" onclick="closeAttachmentModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<form id="attachmentForm" novalidate data-js-validation="1">
<input id="attachmentTicketId" type="hidden">

<div class="p-5">
<div id="attachmentError" class="mb-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

<label class="form-label">File <span class="text-red-500">*</span></label>

<input id="attachmentFile" type="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" class="block w-full rounded-md border border-slate-300 bg-white p-2 text-sm text-slate-600" data-validation-required-message="Please select an attachment." data-validation-file-message="PDF, JPG, PNG, WEBP, DOC or DOCX files are allowed.">

<p class="mt-1 text-[10px] text-slate-400">PDF, image, DOC or DOCX.</p>
<p data-field-error="attachmentFile" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeAttachmentModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">Cancel</button>
<button id="attachmentButton" type="submit" class="h-9 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white disabled:opacity-60">Upload Attachment</button>
</div>
</form>

</div>
</div>

{{-- Details Modal --}}
<div id="detailsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">
<div class="flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

<div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>
<div>
<h2 id="detailsTitle" class="text-base font-bold text-slate-800">Feedback & Support</h2>
<p id="detailsTicketNo" class="font-mono text-[10px] text-slate-400"></p>
</div>
</div>

<button type="button" onclick="closeDetailsModal()" class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
<i class="bi bi-x-lg"></i>
</button>
</div>

<div id="detailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

<div class="flex justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">
<button type="button" onclick="closeDetailsModal()" class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-600">Close</button>
</div>

</div>
</div>

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>
@endsection

@push('scripts')
<script>
const API='/api/member/feedback-support';

let supportData={
categories:[],
tickets:[]
};

let currentPage=1;
let lastPage=1;
let total=0;

const $=id=>document.getElementById(id);

function typeLabel(type){
return{
feedback:'Feedback',
support_request:'Support Request',
complaint:'Complaint',
suggestion:'Suggestion',
service_issue:'Service Issue',
other:'Other'
}[type]??titleCase(type);
}

function statusBadge(status){
const map={
submitted:['Submitted','bg-sky-50 text-sky-700','bi-send'],
under_review:['Under Review','bg-indigo-50 text-indigo-700','bi-search'],
assigned:['Assigned','bg-violet-50 text-violet-700','bi-person-check'],
in_progress:['In Progress','bg-amber-50 text-amber-700','bi-arrow-repeat'],
resolved:['Resolved','bg-emerald-50 text-emerald-700','bi-patch-check'],
closed:['Closed','bg-slate-100 text-slate-600','bi-check2-circle'],
rejected:['Rejected','bg-red-50 text-red-700','bi-x-circle'],
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

function priorityBadge(priority){
const map={
urgent:'bg-red-50 text-red-700',
high:'bg-orange-50 text-orange-700',
normal:'bg-sky-50 text-sky-700',
low:'bg-slate-100 text-slate-500'
};

return`
<span class="inline-flex rounded-full px-2 py-1 text-[9px] font-semibold uppercase ${map[priority]??map.normal}">
${escapeHtml(priority)}
</span>`;
}

async function loadTickets(page=1){
currentPage=page;

const button=$('refreshButton');
const icon=$('refreshIcon');

button.disabled=true;
icon.classList.add('animate-spin');

$('ticketList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>
<p class="mt-3 text-sm text-slate-400">Loading requests...</p>
</div>`;

try{
const params=new URLSearchParams({
page,
per_page:10
});

const search=$('ticketSearch').value.trim();
const type=$('typeFilter').value;
const status=$('statusFilter').value;
const priority=$('priorityFilter').value;

if(search)params.set('search',search);
if(type)params.set('type',type);
if(status)params.set('status',status);
if(priority)params.set('priority',priority);

const response=await api(`${API}?${params.toString()}`);
const payload=response.data??{};

supportData.categories=Array.isArray(payload.categories)
?payload.categories
:[];

const ticketsPayload=payload.tickets??[];

if(Array.isArray(ticketsPayload)){
supportData.tickets=ticketsPayload;
currentPage=1;
lastPage=1;
total=ticketsPayload.length;
}else{
supportData.tickets=Array.isArray(ticketsPayload.data)
?ticketsPayload.data
:[];

currentPage=Number(ticketsPayload.current_page??1);
lastPage=Number(ticketsPayload.last_page??1);
total=Number(ticketsPayload.total??supportData.tickets.length);
}

renderCategoryOptions();
renderTickets();
renderSummary(payload.summary??null);
renderPagination(ticketsPayload);

}catch(error){
$('ticketList').innerHTML=`
<div class="col-span-full py-12 text-center">
<div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-50 text-red-500">
<i class="bi bi-exclamation-circle"></i>
</div>
<p class="mt-3 text-base font-semibold text-red-600">Failed to load requests</p>
<p class="mt-1 text-sm text-red-400">${escapeHtml(AdminUI.extractError(error))}</p>
</div>`;

$('paginationContainer').innerHTML='';
}finally{
button.disabled=false;
icon.classList.remove('animate-spin');
}
}

function renderCategoryOptions(){
$('categoryId').innerHTML=
'<option value="">Select Category</option>'+
supportData.categories.map(category=>`
<option value="${category.id}">
${escapeHtml(category.name)}
</option>
`).join('');
}

function renderSummary(summary){
if(summary){
$('summaryTotal').textContent=Number(summary.total??0);
$('summaryOpen').textContent=Number(summary.open??0);
$('summaryUrgent').textContent=Number(summary.urgent??0);
$('summaryResolved').textContent=Number(
summary.resolved_closed??
(Number(summary.resolved??0)+Number(summary.closed??0))
);
return;
}

const tickets=supportData.tickets;

$('summaryTotal').textContent=total||tickets.length;

$('summaryOpen').textContent=tickets.filter(ticket=>
!['resolved','closed','cancelled','rejected'].includes(ticket.status)
).length;

$('summaryUrgent').textContent=tickets.filter(ticket=>
ticket.priority==='urgent'
).length;

$('summaryResolved').textContent=tickets.filter(ticket=>
['resolved','closed'].includes(ticket.status)
).length;
}

function renderTickets(){
const tickets=supportData.tickets;

if(!tickets.length){
$('ticketList').innerHTML=`
<div class="col-span-full py-14 text-center">
<div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
<i class="bi bi-headset text-xl"></i>
</div>
<p class="mt-4 text-base font-semibold text-slate-700">No Feedback & Support Requests</p>
<p class="mt-1 text-sm text-slate-400">No requests match the current filters.</p>
</div>`;
return;
}

$('ticketList').innerHTML=tickets.map(ticket=>`
<article class="overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-indigo-200 hover:shadow-sm">

<div class="border-b border-slate-100 p-4">
<div class="flex items-start justify-between gap-3">

<div class="flex min-w-0 items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${typeIconColor(ticket.type)}">
<i class="bi ${typeIcon(ticket.type)}"></i>
</div>

<div class="min-w-0">
<div class="flex flex-wrap items-center gap-2">
<p class="font-mono text-sm font-semibold text-indigo-600">
${escapeHtml(ticket.ticket_no)}
</p>

${ticket.is_confidential?`
<span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[9px] font-semibold uppercase text-red-600">
<i class="bi bi-lock-fill"></i>
Confidential
</span>`:''}
</div>

<p class="mt-1 truncate text-[11px] text-slate-400">
${escapeHtml(typeLabel(ticket.type))}
${ticket.category?.name?' • '+escapeHtml(ticket.category.name):''}
</p>
</div>
</div>

${statusBadge(ticket.status)}
</div>
</div>

<div class="space-y-3 p-4">

<div class="flex items-start justify-between gap-3">
<div class="min-w-0">
<h3 class="text-base font-bold text-slate-800">
${escapeHtml(ticket.subject)}
</h3>

<p class="mt-1 line-clamp-3 text-[11px] leading-5 text-slate-600">
${escapeHtml(ticket.description)}
</p>
</div>

${priorityBadge(ticket.priority)}
</div>

<div class="grid grid-cols-2 gap-2">
${miniBox(
'Created',
dateText(ticket.created_at)
)}
${miniBox(
'Updates',
String((ticket.updates??[]).length)
)}
</div>

${ticket.resolution?`
<div class="rounded-md border border-emerald-200 bg-emerald-50 p-3">
<p class="text-[10px] font-semibold uppercase text-emerald-600">
Resolution
</p>
<p class="mt-1 line-clamp-3 text-[11px] leading-5 text-emerald-800">
${escapeHtml(ticket.resolution)}
</p>
</div>`:''}

</div>

<div class="flex flex-wrap gap-2 border-t border-slate-100 bg-slate-50/50 px-4 py-3">

<button type="button" onclick="openDetails(${Number(ticket.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-300 bg-white px-2.5 text-[11px] font-semibold text-slate-600 hover:border-indigo-200 hover:text-indigo-700">
<i class="bi bi-eye"></i>
View
</button>

${!['resolved','closed','cancelled','rejected'].includes(ticket.status)?`
<button type="button" onclick="openFollowUpModal(${Number(ticket.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-sky-200 bg-sky-50 px-2.5 text-[11px] font-semibold text-sky-700 hover:bg-sky-100">
<i class="bi bi-chat-dots"></i>
Follow-up
</button>

<button type="button" onclick="openAttachmentModal(${Number(ticket.id)})" class="inline-flex h-8 items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
<i class="bi bi-paperclip"></i>
Attachment
</button>`:''}

${ticket.status==='submitted'?`
<button type="button" onclick="cancelTicket(${Number(ticket.id)})" class="ml-auto inline-flex h-8 items-center gap-1.5 rounded-md border border-red-200 bg-white px-2.5 text-[11px] font-semibold text-red-600 hover:bg-red-50">
<i class="bi bi-x-circle"></i>
Cancel
</button>`:''}

</div>
</article>
`).join('');
}

function renderPagination(meta){
const container=$('paginationContainer');

if(Array.isArray(meta)||lastPage<=1){
container.innerHTML='';
return;
}

const perPage=Number(meta.per_page??10);

const from=Number(
meta.from??
((currentPage-1)*perPage)+1
);

const to=Number(
meta.to??
Math.min(currentPage*perPage,total)
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
onclick="loadTickets(${currentPage-1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 disabled:opacity-40">
<i class="bi bi-chevron-left"></i>
Previous
</button>

<span class="px-3 text-[11px] font-medium text-slate-500">
${currentPage} / ${lastPage}
</span>

<button type="button"
${currentPage>=lastPage?'disabled':''}
onclick="loadTickets(${currentPage+1})"
class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 disabled:opacity-40">
Next
<i class="bi bi-chevron-right"></i>
</button>
</div>

</div>`;
}

window.openCreateModal=function(){
$('createForm').reset();

AdminUI.clearError('createError');
AdminUI.clearFieldErrors('createForm');

$('ticketPriority').value='normal';

openModal('createModal');
};

window.closeCreateModal=function(){
closeModal('createModal');
};

$('createForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('createError');
AdminUI.clearFieldErrors('createForm');

if(!AdminUI.validateForm('createForm',{
categoryId:'Please select a category.',
ticketType:'Please select a type.',
ticketPriority:'Priority is required.',
subject:'Subject is required.',
description:'Description is required.'
})){
return;
}

const button=$('createButton');
AdminUI.setLoading(button,'Submitting...');

try{
const response=await api(API,{
method:'POST',
body:JSON.stringify({
feedback_support_category_id:Number($('categoryId').value),
type:$('ticketType').value,
subject:$('subject').value.trim(),
description:$('description').value.trim(),
priority:$('ticketPriority').value,
is_confidential:$('isConfidential').checked
})
});

closeCreateModal();

Toast.success(
response.message??
'Feedback & Support request submitted successfully.'
);

await loadTickets(1);

}catch(error){
if(!AdminUI.showValidationErrors('createForm',error,{
feedback_support_category_id:'categoryId',
type:'ticketType',
subject:'subject',
description:'description',
priority:'ticketPriority',
is_confidential:'isConfidential'
})){
AdminUI.showError(
'createError',
AdminUI.extractError(error)
);
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openFollowUpModal=function(id){
const ticket=findTicket(id);

if(!ticket)return;

$('followUpForm').reset();

AdminUI.clearError('followUpError');
AdminUI.clearFieldErrors('followUpForm');

$('followUpTicketId').value=id;
$('followUpSubtitle').textContent=`${ticket.ticket_no} • ${ticket.subject}`;

openModal('followUpModal');
};

window.closeFollowUpModal=function(){
closeModal('followUpModal');
};

$('followUpForm').addEventListener('submit',async event=>{
event.preventDefault();

const id=Number($('followUpTicketId').value);

AdminUI.clearError('followUpError');
AdminUI.clearFieldErrors('followUpForm');

if(!AdminUI.validateForm('followUpForm',{
followUpMessage:'Follow-up message is required.'
})){
return;
}

const button=$('followUpButton');
AdminUI.setLoading(button,'Saving...');

try{
const response=await api(`${API}/${id}/follow-up`,{
method:'POST',
body:JSON.stringify({
message:$('followUpMessage').value.trim()
})
});

closeFollowUpModal();

Toast.success(
response.message??
'Follow-up added successfully.'
);

await loadTickets(currentPage);

}catch(error){
if(!AdminUI.showValidationErrors('followUpForm',error,{
message:'followUpMessage'
})){
AdminUI.showError(
'followUpError',
AdminUI.extractError(error)
);
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openAttachmentModal=function(id){
const ticket=findTicket(id);

if(!ticket)return;

$('attachmentForm').reset();

AdminUI.clearError('attachmentError');
AdminUI.clearFieldErrors('attachmentForm');

$('attachmentTicketId').value=id;
$('attachmentSubtitle').textContent=`${ticket.ticket_no} • ${ticket.subject}`;

openModal('attachmentModal');
};

window.closeAttachmentModal=function(){
closeModal('attachmentModal');
};

$('attachmentForm').addEventListener('submit',async event=>{
event.preventDefault();

const id=Number($('attachmentTicketId').value);
const file=$('attachmentFile').files?.[0];

AdminUI.clearError('attachmentError');
AdminUI.clearFieldErrors('attachmentForm');

const form=new FormData();
form.append('file',file);

const button=$('attachmentButton');
AdminUI.setLoading(button,'Uploading...');

try{
const response=await api(
`${API}/${id}/attachments`,
{
method:'POST',
body:form
}
);

closeAttachmentModal();

Toast.success(
response.message??
'Attachment uploaded successfully.'
);

await loadTickets(currentPage);

}catch(error){
if(!AdminUI.showValidationErrors('attachmentForm',error,{
file:'attachmentFile'
})){
AdminUI.showError(
'attachmentError',
AdminUI.extractError(error)
);
}
}finally{
AdminUI.resetLoading(button);
}
});

window.cancelTicket=function(id){
const ticket=findTicket(id);

if(!ticket)return;

AdminUI.request(
`${API}/${id}/cancel`,
{
method:'POST',
data:{},
confirmation:{
title:'Cancel Feedback & Support Request?',
message:`Cancel ${ticket.ticket_no}?`,
confirmText:'Cancel Request',
type:'danger'
},
successMessage:'Feedback & Support request cancelled.',
onSuccess:async()=>{
await loadTickets(currentPage);
}
}
);
};

window.openDetails=function(id){
const ticket=findTicket(id);

if(!ticket)return;

$('detailsTitle').textContent=ticket.subject;
$('detailsTicketNo').textContent=ticket.ticket_no;

$('detailsBody').innerHTML=`
<div class="space-y-5">

<div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
${summaryBox('Type',typeLabel(ticket.type))}
${summaryBox('Priority',titleCase(ticket.priority))}
${summaryBox('Status',titleCase(ticket.status))}
${summaryBox('Created',dateText(ticket.created_at))}
</div>

<div class="overflow-hidden rounded-lg border border-slate-200">
<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-base font-semibold text-slate-800">Request Information</h3>
</div>

<div class="grid sm:grid-cols-2">
${detailItem('Ticket Number',ticket.ticket_no)}
${detailItem('Category',ticket.category?.name??'—')}
${detailItem('Type',typeLabel(ticket.type))}
${detailItem('Priority',priorityBadge(ticket.priority),true)}
${detailItem('Status',statusBadge(ticket.status),true)}
${detailItem('Confidential',ticket.is_confidential?'Yes':'No')}
${detailItem('Created',dateTimeText(ticket.created_at))}
${detailItem('Resolved',dateTimeText(ticket.resolved_at))}
</div>
</div>

<div class="rounded-lg border border-slate-200 p-4">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Description</p>
<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">
${escapeHtml(ticket.description)}
</p>
</div>

${ticket.resolution?`
<div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
<p class="text-sm font-semibold text-emerald-700">
<i class="bi bi-check2-circle mr-1"></i>
Resolution
</p>
<p class="mt-2 whitespace-pre-line text-sm leading-5 text-emerald-800">
${escapeHtml(ticket.resolution)}
</p>
</div>`:''}

${renderUpdates(ticket.updates??[])}

${renderAttachments(ticket.attachments??[])}

</div>`;

openModal('detailsModal');
};

function renderUpdates(updates){
if(!updates.length)return'';

return`
<div class="overflow-hidden rounded-lg border border-slate-200">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-base font-semibold text-slate-800">Conversation & Updates</h3>
<p class="text-[11px] text-slate-400">${updates.length} update${updates.length===1?'':'s'}</p>
</div>

<div class="space-y-2 p-4">
${updates.map(update=>{
const memberFollowUp=update.type==='member_follow_up';

return`
<div class="rounded-md border ${memberFollowUp?'border-sky-200 bg-sky-50/40':'border-indigo-200 bg-indigo-50/30'} p-3">

<div class="flex items-start justify-between gap-3">
<span class="text-[10px] font-semibold uppercase ${memberFollowUp?'text-sky-600':'text-indigo-600'}">
${memberFollowUp?'Your Follow-up':'Support Response'}
</span>

<span class="text-[9px] text-slate-400">
${dateTimeText(update.created_at)}
</span>
</div>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">
${escapeHtml(update.message)}
</p>

</div>`;
}).join('')}
</div>

</div>`;
}

function renderAttachments(attachments){
if(!attachments.length)return'';

return`
<div class="overflow-hidden rounded-lg border border-slate-200">

<div class="border-b border-slate-200 px-5 py-4">
<h3 class="text-base font-semibold text-slate-800">Attachments</h3>
<p class="text-[11px] text-slate-400">${attachments.length} uploaded file${attachments.length===1?'':'s'}</p>
</div>

<div class="divide-y divide-slate-100">

${attachments.map(file=>`
<div class="flex items-center gap-3 px-4 py-3">

<div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-paperclip"></i>
</div>

<div class="min-w-0 flex-1">
<p class="truncate text-sm font-semibold text-slate-700">
${escapeHtml(file.original_name??file.file_name??'Attachment')}
</p>
</div>

<a href="/api/member/feedback-support/attachments/${Number(file.id)}" target="_blank" rel="noopener" class="inline-flex h-8 items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 text-[10px] font-semibold text-indigo-600">
<i class="bi bi-box-arrow-up-right"></i>
View
</a>

</div>`).join('')}

</div>
</div>`;
}

window.closeDetailsModal=function(){
closeModal('detailsModal');
};

function findTicket(id){
const ticket=supportData.tickets.find(
item=>Number(item.id)===Number(id)
);

if(!ticket){
Toast.error('Feedback & Support request not found.');
return null;
}

return ticket;
}

function miniBox(label,value){
return`
<div class="rounded-md bg-slate-50 p-3">
<p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400">${escapeHtml(label)}</p>
<p class="mt-1 truncate text-sm font-bold text-slate-700">${escapeHtml(value)}</p>
</div>`;
}

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

function typeIcon(type){
return{
feedback:'bi-chat-heart',
support_request:'bi-headset',
complaint:'bi-exclamation-circle',
suggestion:'bi-lightbulb',
service_issue:'bi-tools',
other:'bi-chat-square-text'
}[type]??'bi-headset';
}

function typeIconColor(type){
return{
feedback:'bg-pink-50 text-pink-600',
support_request:'bg-indigo-50 text-indigo-600',
complaint:'bg-red-50 text-red-600',
suggestion:'bg-amber-50 text-amber-600',
service_issue:'bg-sky-50 text-sky-600',
other:'bg-slate-100 text-slate-600'
}[type]??'bg-indigo-50 text-indigo-600';
}

function titleCase(value){
return String(value??'')
.replaceAll('_',' ')
.replace(/\b\w/g,char=>char.toUpperCase());
}

function dateText(value){
if(!value)return'—';

const date=new Date(value);

if(Number.isNaN(date.getTime()))return String(value);

return date.toLocaleDateString('en-GB',{
day:'2-digit',
month:'short',
year:'numeric'
});
}

function dateTimeText(value){
if(!value)return'—';

const date=new Date(value);

if(Number.isNaN(date.getTime()))return String(value);

return date.toLocaleString('en-GB',{
day:'2-digit',
month:'short',
year:'numeric',
hour:'2-digit',
minute:'2-digit'
});
}

window.clearFilters=function(){
$('ticketSearch').value='';
$('typeFilter').value='';
$('statusFilter').value='';
$('priorityFilter').value='';

loadTickets(1);
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

$('ticketSearch').addEventListener(
'input',
AdminUI.debounce(()=>loadTickets(1))
);

[
'typeFilter',
'statusFilter',
'priorityFilter'
].forEach(id=>{
$(id).addEventListener(
'change',
()=>loadTickets(1)
);
});

await loadTickets();
}

init();
</script>
@endpush