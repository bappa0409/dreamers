@extends('layouts.admin')

@section('title','Feedback & Support')
@section('page_title','Feedback & Support')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
<div class="flex items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>
<div>
<h1 class="text-base font-bold text-slate-800">Feedback & Support</h1>
<p class=" text-xs 2xl:text-sm text-slate-500">Manage member feedback, support requests, complaints, suggestions and service issues.</p>
</div>
</div>

@if(auth()->user()->hasPermission('FeedbackSupport.create'))
<button type="button" onclick="openCreateModal()" class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-lg"></i>
New Feedback / Support
</button>
@endif
</div>

{{-- Statistics --}}
<div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
@php
$stats=[
['total','Total','bi-collection','border-slate-200 bg-white','text-slate-800','bg-slate-100 text-slate-500'],
['open','Open','bi-inbox','border-sky-200 bg-sky-50/40','text-sky-700','bg-sky-100 text-sky-600'],
['urgent','Urgent','bi-exclamation-triangle','border-red-200 bg-red-50/40','text-red-700','bg-red-100 text-red-600'],
['resolved','Resolved','bi-patch-check','border-emerald-200 bg-emerald-50/40','text-emerald-700','bg-emerald-100 text-emerald-600'],
['closed','Closed','bi-check2-circle','border-indigo-200 bg-indigo-50/40','text-indigo-700','bg-indigo-100 text-indigo-600'],
];
@endphp

@foreach($stats as [$key,$label,$icon,$box,$text,$iconBox])
<div class="rounded-md border p-4 {{ $box }}">
<div class="flex items-start justify-between gap-3">
<div>
<p class=" text-xs 2xl:text-sm text-slate-500">{{ $label }}</p>
<p id="stat-{{ $key }}" class="mt-2 text-xl font-bold {{ $text }}">0</p>
</div>
<div class="flex h-8 w-8 items-center justify-center rounded-md {{ $iconBox }}">
<i class="bi {{ $icon }} text-base"></i>
</div>
</div>
</div>
@endforeach
</div>

{{-- Filters --}}
<div class="rounded-md border border-slate-200 bg-white p-3">
<div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
<div class="flex items-center gap-2">
<div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-search text-base"></i>
</div>
<div>
<p class="text-sm font-semibold text-slate-700">Search Feedback & Support</p>
<p class="hidden text-[11px] text-slate-400 sm:block">Search by ticket, member or subject.</p>
</div>
</div>

<div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:w-auto xl:grid-cols-[270px_155px_155px_145px_auto] xl:gap-0">
<div class="relative sm:col-span-2 xl:col-span-1">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
<input id="searchInput" type="text" placeholder="Search tickets..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 xl:rounded-r-none">
</div>

<select id="typeFilter" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none xl:rounded-none xl:border-l-0">
<option value="">All Types</option>
<option value="feedback">Feedback</option>
<option value="support_request">Support Request</option>
<option value="complaint">Complaint</option>
<option value="suggestion">Suggestion</option>
<option value="service_issue">Service Issue</option>
<option value="other">Other</option>
</select>

<select id="statusFilter" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none xl:rounded-none xl:border-l-0">
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

<select id="priorityFilter" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none xl:rounded-none xl:border-l-0">
<option value="">All Priority</option>
<option value="urgent">Urgent</option>
<option value="high">High</option>
<option value="normal">Normal</option>
<option value="low">Low</option>
</select>

<button type="button" onclick="clearFilters()" class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
<i class="bi bi-x-lg text-[10px]"></i>
Clear
</button>
</div>
</div>
</div>

{{-- Table + Pagination --}}
<div class="overflow-hidden rounded-md border border-slate-200 bg-white">
<div class="overflow-x-auto">
<table class="w-full min-w-[1080px] table-fixed text-base">
<thead class="border-b border-slate-200 bg-slate-50">
<tr>
<th class="w-[12%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Ticket</th>
<th class="w-[16%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Member</th>
<th class="w-[12%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Type</th>
<th class="w-[13%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Category</th>
<th class="w-[20%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Subject</th>
<th class="w-[9%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Priority</th>
<th class="w-[10%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
<th class="w-[8%] px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Action</th>
</tr>
</thead>

<tbody id="ticketTable">
<tr>
<td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">Loading Feedback & Support...</td>
</tr>
</tbody>
</table>
</div>

<div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
</div>
</div>

{{-- Create Modal --}}
<div id="createModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

<div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>
<div>
<h3 class="text-sm font-semibold text-slate-800">New Feedback & Support</h3>
<p class=" text-xs 2xl:text-sm text-slate-500">Create a request on behalf of a member.</p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('createModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="createForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
<div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

<div id="createError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div class="rounded-md border border-slate-200 p-4">
<div class="grid gap-4 md:grid-cols-2">

<div>
<label class="form-label">Member <span class="text-red-500">*</span></label>
<select id="memberId" class="app-input w-full">
<option value="">Select Member</option>
</select>
<p data-field-error="memberId" class="mt-1 hidden text-sm text-red-600"></p>
</div>

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

</div>
</div>

<div class="rounded-md border border-slate-200 p-4">
<div>
<label class="form-label">Subject <span class="text-red-500">*</span></label>
<input id="subject" type="text" maxlength="200" class="app-input w-full" placeholder="Brief subject">
<p data-field-error="subject" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="mt-4">
<label class="form-label">Description <span class="text-red-500">*</span></label>
<textarea id="description" rows="5" maxlength="20000" class="app-input w-full resize-none" placeholder="Describe the feedback or support request..."></textarea>
<p data-field-error="description" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<label class="mt-4 flex items-center gap-2 text-sm font-medium text-slate-600">
<input id="isConfidential" type="checkbox">
<span>Mark as confidential</span>
</label>
</div>

</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('createModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="createButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">Create Request</button>
</div>
</form>
</div>
</div>

{{-- Manage Modal --}}
<div id="manageModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-md bg-white">

<div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-headset"></i>
</div>
<div>
<h3 id="manageTitle" class="text-sm font-semibold text-slate-800">Feedback & Support</h3>
<p id="manageSubtitle" class=" text-xs 2xl:text-sm text-slate-500"></p>
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
<div id="ticketDetails" class="mt-3 grid grid-cols-2 gap-3"></div>
</section>

<section class="rounded-md border border-slate-200 p-4">
<h4 class="text-sm font-semibold text-slate-800">Description</h4>
<div id="ticketDescription" class="mt-3 whitespace-pre-line rounded-md bg-slate-50 p-3 text-sm leading-5 text-slate-600"></div>

<div id="resolutionWrap" class="mt-3 hidden rounded-md border border-emerald-200 bg-emerald-50 p-3">
<p class="text-[10px] font-semibold uppercase tracking-wide text-emerald-600">Resolution</p>
<p id="resolutionText" class="mt-1 whitespace-pre-line text-sm text-emerald-800"></p>
</div>
</section>
</div>

<section class="rounded-md border border-slate-200 p-4">
<div class="mb-3 flex items-center justify-between">
<div>
<h4 class="text-sm font-semibold text-slate-800">Conversation & Updates</h4>
<p class="text-[11px] text-slate-400">Support responses, member follow-ups and internal notes.</p>
</div>
</div>

<div id="updateList" class="space-y-2"></div>
</section>

</div>

<div id="manageActions" class="flex flex-wrap justify-end gap-2 border-t border-slate-200 px-5 py-4"></div>
</div>
</div>

{{-- Assign Modal --}}
<div id="assignModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

<div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Assign Request</h3>
<p class=" text-xs 2xl:text-sm text-slate-500">Assign this request to an active user.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('assignModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="assignForm" novalidate data-js-validation="1">
<div class="space-y-4 p-5">
<div id="assignError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<div>
<label class="form-label">Assign To <span class="text-red-500">*</span></label>
<select id="assignedTo" class="app-input w-full">
<option value="">Select User</option>
</select>
<p data-field-error="assignedTo" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div>
<label class="form-label">Assignment Note</label>
<textarea id="assignmentNote" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('assignModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="assignButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Assign</button>
</div>
</form>
</div>
</div>

{{-- Note / Response Modal --}}
<div id="messageModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

<div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 id="messageModalTitle" class="text-base font-bold text-slate-800">Add Response</h3>
<p id="messageModalSubtitle" class=" text-xs 2xl:text-sm text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('messageModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="messageForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="messageError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<label class="form-label">Message <span class="text-red-500">*</span></label>
<textarea id="messageText" rows="5" maxlength="10000" class="app-input w-full resize-none"></textarea>
<p data-field-error="messageText" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('messageModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="messageButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save</button>
</div>
</form>
</div>
</div>

{{-- Resolve Modal --}}
<div id="resolveModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

<div class="flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Resolve Request</h3>
<p class=" text-xs 2xl:text-sm text-slate-500">Provide the final resolution for the member.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('resolveModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="resolveForm" novalidate data-js-validation="1">
<div class="p-5">
<div id="resolveError" class="mb-3 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

<label class="form-label">Resolution <span class="text-red-500">*</span></label>
<textarea id="resolution" rows="5" maxlength="10000" class="app-input w-full resize-none"></textarea>
<p data-field-error="resolution" class="mt-1 hidden text-sm text-red-600"></p>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('resolveModal')" class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold">Cancel</button>
<button id="resolveButton" type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Resolve Request</button>
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
const API='/api/feedback-support';

const permissions={
review:@json(auth()->user()->hasPermission('FeedbackSupport.review')),
assign:@json(auth()->user()->hasPermission('FeedbackSupport.assign')),
resolve:@json(auth()->user()->hasPermission('FeedbackSupport.resolve'))
};

let tickets=[];
let optionsData={categories:[],members:[],users:[]};
let currentTicket=null;
let currentPage=1;
let lastPage=1;
let total=0;
let messageMode=null;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const date=value=>value?AdminUI.formatDate(value):'—';

function typeLabel(type){
return{
feedback:'Feedback',
support_request:'Support Request',
complaint:'Complaint',
suggestion:'Suggestion',
service_issue:'Service Issue',
other:'Other'
}[type]??type;
}

function priorityBadge(priority){
const map={
urgent:'bg-red-50 text-red-700',
high:'bg-orange-50 text-orange-700',
normal:'bg-sky-50 text-sky-700',
low:'bg-slate-100 text-slate-500'
};

return`<span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[priority]??'bg-slate-100 text-slate-500'}">${esc(priority)}</span>`;
}

function statusBadge(status){
const map={
submitted:['Submitted','bg-sky-50 text-sky-700','bi-send'],
under_review:['Under Review','bg-indigo-50 text-indigo-700','bi-search'],
assigned:['Assigned','bg-violet-50 text-violet-700','bi-person-check'],
in_progress:['In Progress','bg-amber-50 text-amber-700','bi-arrow-repeat'],
resolved:['Resolved','bg-emerald-50 text-emerald-700','bi-patch-check'],
closed:['Closed','bg-slate-100 text-slate-600','bi-check2-circle'],
rejected:['Rejected','bg-red-50 text-red-600','bi-x-circle'],
cancelled:['Cancelled','bg-slate-100 text-slate-500','bi-slash-circle']
};

const item=map[status]??[status,'bg-slate-100 text-slate-500','bi-circle'];

return`
<span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${item[1]}">
<i class="bi ${item[2]}"></i>
${esc(item[0])}
</span>`;
}

async function loadOptions(){
try{
const response=await api(`${API}/options`);
optionsData=response.data??{};

$('memberId').innerHTML=
'<option value="">Select Member</option>'+
(optionsData.members??[]).map(member=>`
<option value="${member.id}">
${esc(member.user?.name??'N/A')} (${esc(member.member_code??'')})
</option>`).join('');

$('categoryId').innerHTML=
'<option value="">Select Category</option>'+
(optionsData.categories??[]).map(category=>`
<option value="${category.id}">${esc(category.name)}</option>
`).join('');

$('assignedTo').innerHTML=
'<option value="">Select User</option>'+
(optionsData.users??[]).map(user=>`
<option value="${user.id}">${esc(user.name)}${user.email?` (${esc(user.email)})`:''}</option>
`).join('');

}catch(error){
Toast.error(AdminUI.extractError(error));
}
}

async function loadStatistics(){
try{
const response=await api(`${API}/statistics`);

Object.entries(response.data??{}).forEach(([key,value])=>{
const element=$(`stat-${key}`);
if(element)element.textContent=Number(value??0);
});
}catch(error){
console.error(error);
}
}

async function loadTickets(page=1){
currentPage=page;

$('ticketTable').innerHTML=AdminUI.loadingState('Loading Feedback & Support...',8);

const query=AdminUI.query({
search:$('searchInput').value.trim(),
type:$('typeFilter').value,
status:$('statusFilter').value,
priority:$('priorityFilter').value,
page,
per_page:15
});

try{
const response=await api(`${API}?${query}`);
const paginator=response.data??{};

tickets=Array.isArray(paginator.data)?paginator.data:[];

currentPage=Number(paginator.current_page??1);
lastPage=Number(paginator.last_page??1);
total=Number(paginator.total??tickets.length);

renderTickets();

AdminUI.renderPagination({
container:'paginationContainer',
currentPage,
lastPage,
total,
onPageChange:loadTickets
});

}catch(error){
$('ticketTable').innerHTML=AdminUI.emptyState(
AdminUI.extractError(error),
8
);
}
}

function renderTickets(){
if(!tickets.length){
$('ticketTable').innerHTML=AdminUI.emptyState('No Feedback & Support requests found.',8);
return;
}

$('ticketTable').innerHTML=tickets.map(ticket=>`
<tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">

<td class="px-4 py-3">
<div class="flex items-center gap-1.5">
<span class="font-mono text-sm font-semibold text-indigo-600">${esc(ticket.ticket_no)}</span>
${ticket.is_confidential?'<i class="bi bi-lock-fill text-[10px] text-red-500" title="Confidential"></i>':''}
</div>
<div class="mt-0.5 text-[10px] text-slate-400">${date(ticket.created_at)}</div>
</td>

<td class="px-4 py-3">
<div class="truncate  text-xs 2xl:text-sm font-semibold text-slate-700">${esc(ticket.member?.user?.name??'N/A')}</div>
<div class="mt-0.5 truncate text-[10px] font-medium text-indigo-500">${esc(ticket.member?.member_code??'')}</div>
</td>

<td class="px-4 py-3">
<span class="text-sm font-medium text-slate-600">${esc(typeLabel(ticket.type))}</span>
</td>

<td class="px-4 py-3">
<span class="text-sm text-slate-600">${esc(ticket.category?.name??'—')}</span>
</td>

<td class="px-4 py-3">
<div class="max-w-[240px] truncate text-sm font-semibold text-slate-700">${esc(ticket.subject)}</div>
${ticket.assignee?`<div class="truncate text-[10px] text-slate-400">Assigned: ${esc(ticket.assignee.name)}</div>`:''}
</td>

<td class="px-4 py-3">
${priorityBadge(ticket.priority)}
</td>

<td class="px-4 py-3">
${statusBadge(ticket.status)}
</td>

<td class="px-4 py-3 text-right">
<button type="button" onclick="openManage(${ticket.id})" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100" title="Manage">
<i class="bi bi-eye text-sm"></i>
</button>
</td>

</tr>
`).join('');
}

window.openCreateModal=function(){
$('createForm').reset();

AdminUI.clearError('createError');
AdminUI.clearFieldErrors('createForm');

$('ticketPriority').value='normal';

AdminUI.openModal('createModal');
};

$('createForm').addEventListener('submit',async event=>{
event.preventDefault();

AdminUI.clearError('createError');
AdminUI.clearFieldErrors('createForm');

if(!AdminUI.validateForm('createForm',{
memberId:'Please select a member.',
categoryId:'Please select a category.',
ticketType:'Please select a request type.',
ticketPriority:'Priority is required.',
subject:'Subject is required.',
description:'Description is required.'
}))return;

const payload={
member_id:Number($('memberId').value),
feedback_support_category_id:Number($('categoryId').value),
type:$('ticketType').value,
priority:$('ticketPriority').value,
subject:$('subject').value.trim(),
description:$('description').value.trim(),
is_confidential:$('isConfidential').checked
};

const button=$('createButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(API,{
method:'POST',
body:JSON.stringify(payload)
});

AdminUI.closeModal('createModal');
Toast.success('Feedback & Support request created successfully.');

await Promise.all([
loadTickets(1),
loadStatistics()
]);

}catch(error){
if(!AdminUI.showValidationErrors('createForm',error,{
member_id:'memberId',
feedback_support_category_id:'categoryId',
type:'ticketType',
priority:'ticketPriority',
subject:'subject',
description:'description',
is_confidential:'isConfidential'
})){
AdminUI.showError('createError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openManage=async function(id){
AdminUI.clearError('manageError');

try{
const response=await api(`${API}/${id}`);
currentTicket=response.data;

renderManage(currentTicket);
AdminUI.openModal('manageModal');

}catch(error){
Toast.error(AdminUI.extractError(error));
}
};

function renderManage(ticket){
$('manageTitle').textContent=`${ticket.ticket_no} — ${ticket.subject}`;
$('manageSubtitle').textContent=
`${typeLabel(ticket.type)} • ${ticket.category?.name??''} • ${ticket.status.replaceAll('_',' ')}`;

$('summaryCards').innerHTML=[
['Priority',ticket.priority,'bg-red-50','text-red-700'],
['Status',ticket.status.replaceAll('_',' '),'bg-indigo-50','text-indigo-700'],
['Assigned To',ticket.assignee?.name??'Unassigned','bg-sky-50','text-sky-700'],
['Created',date(ticket.created_at),'bg-slate-50','text-slate-700']
].map(([label,value,bg,text])=>`
<div class="rounded-md ${bg} p-3">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${esc(label)}</p>
<p class="mt-1 truncate text-sm font-bold capitalize ${text}">${esc(value)}</p>
</div>
`).join('');

$('ticketDetails').innerHTML=[
['Member',ticket.member?.user?.name??'—'],
['Member Code',ticket.member?.member_code??'—'],
['Type',typeLabel(ticket.type)],
['Category',ticket.category?.name??'—'],
['Priority',ticket.priority],
['Status',ticket.status.replaceAll('_',' ')],
['Assigned To',ticket.assignee?.name??'Unassigned'],
['Assigned At',date(ticket.assigned_at)],
['Created At',date(ticket.created_at)],
['Confidential',ticket.is_confidential?'Yes':'No']
].map(([label,value])=>`
<div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
<p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">${esc(label)}</p>
<p class="mt-1 break-words text-sm font-semibold capitalize text-slate-700">${esc(value)}</p>
</div>
`).join('');

$('ticketDescription').textContent=ticket.description??'—';

$('resolutionWrap').classList.toggle('hidden',!ticket.resolution);
$('resolutionText').textContent=ticket.resolution??'';

renderUpdates(ticket.updates??[]);
renderActions(ticket);
}

function renderUpdates(updates){
if(!updates.length){
$('updateList').innerHTML=`
<div class="rounded-md bg-slate-50 p-5 text-center text-sm text-slate-400">
No updates yet.
</div>`;
return;
}

$('updateList').innerHTML=updates.map(update=>{
const isInternal=update.type==='internal_note';
const isMember=update.type==='member_follow_up';

return`
<div class="rounded-md border ${isInternal?'border-amber-200 bg-amber-50/40':isMember?'border-sky-200 bg-sky-50/40':'border-slate-200 bg-slate-50/50'} p-3">

<div class="flex items-start justify-between gap-3">
<div>
<span class="text-[10px] font-semibold uppercase ${isInternal?'text-amber-600':isMember?'text-sky-600':'text-indigo-600'}">
${isInternal?'Internal Note':isMember?'Member Follow-up':'Support Response'}
</span>

${update.user?.name?`
<span class="ml-1 text-[10px] text-slate-400">• ${esc(update.user.name)}</span>
`:''}
</div>

<span class="text-[9px] text-slate-400">${date(update.created_at)}</span>
</div>

<p class="mt-2 whitespace-pre-line text-sm leading-5 text-slate-600">${esc(update.message)}</p>
</div>`;
}).join('');
}

function renderActions(ticket){
const actions=[];

if(permissions.review&&ticket.status==='submitted'){
actions.push(actionButton('Start Review','bi-search','indigo','startReview()'));
}

if(permissions.assign&&['submitted','under_review','assigned','in_progress'].includes(ticket.status)){
actions.push(actionButton(
ticket.assigned_to?'Reassign':'Assign',
'bi-person-check',
'slate',
'openAssignModal()'
));
}

if(permissions.review&&ticket.status==='assigned'){
actions.push(actionButton('Start Progress','bi-play-circle','indigo','startProgress()'));
}

if(permissions.review&&!['closed','cancelled','rejected'].includes(ticket.status)){
actions.push(actionButton('Internal Note','bi-journal-text','slate',`openMessageModal('internal')`));
actions.push(actionButton('Support Response','bi-chat-dots','indigo',`openMessageModal('response')`));
}

if(permissions.resolve&&['assigned','in_progress'].includes(ticket.status)){
actions.push(actionButton('Resolve','bi-check2-circle','emerald','openResolveModal()'));
}

if(permissions.resolve&&ticket.status==='resolved'){
actions.push(actionButton('Close Request','bi-check2-square','emerald','closeRequest()'));
}

$('manageActions').innerHTML=actions.length
?actions.join('')
:'<span class="text-xs 2xl:text-sm text-slate-400">No further action available.</span>';
}

function actionButton(label,icon,color,handler){
const styles={
indigo:'border-indigo-600 bg-indigo-600 text-white hover:bg-indigo-700',
emerald:'border-emerald-600 bg-emerald-600 text-white hover:bg-emerald-700',
red:'border-red-200 bg-white text-red-600 hover:bg-red-50',
slate:'border-slate-300 bg-white text-slate-600 hover:bg-slate-50'
};

return`
<button type="button" onclick="${handler}" class="inline-flex items-center gap-1.5 rounded-md border px-3 py-2 text-xs 2xl:text-sm font-semibold transition ${styles[color]}">
<i class="bi ${icon}"></i>
${label}
</button>`;
}

window.startReview=function(){
if(!currentTicket)return;

AdminUI.request(`${API}/${currentTicket.id}/review`,{
method:'POST',
data:{},
confirmation:{
title:'Start Review?',
message:`Move ${currentTicket.ticket_no} to Under Review?`,
confirmText:'Start Review',
type:'primary'
},
successMessage:'Request moved to review.',
onSuccess:refreshCurrentTicket
});
};

window.openAssignModal=function(){
if(!currentTicket)return;

$('assignForm').reset();

AdminUI.clearError('assignError');
AdminUI.clearFieldErrors('assignForm');

if(currentTicket.assigned_to){
$('assignedTo').value=currentTicket.assigned_to;
}

AdminUI.openModal('assignModal');
};

$('assignForm').addEventListener('submit',async event=>{
event.preventDefault();

if(!currentTicket)return;

AdminUI.clearError('assignError');
AdminUI.clearFieldErrors('assignForm');

if(!AdminUI.validateForm('assignForm',{
assignedTo:'Please select a user.'
}))return;

const button=$('assignButton');
AdminUI.setLoading(button,'Assigning...');

try{
await api(`${API}/${currentTicket.id}/assign`,{
method:'POST',
body:JSON.stringify({
assigned_to:Number($('assignedTo').value),
note:$('assignmentNote').value.trim()||null
})
});

AdminUI.closeModal('assignModal');
Toast.success('Feedback & Support request assigned.');

await refreshCurrentTicket();

}catch(error){
if(!AdminUI.showValidationErrors('assignForm',error,{
assigned_to:'assignedTo',
note:'assignmentNote'
})){
AdminUI.showError('assignError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.startProgress=function(){
if(!currentTicket)return;

AdminUI.request(`${API}/${currentTicket.id}/progress`,{
method:'POST',
data:{},
confirmation:{
title:'Start Progress?',
message:`Mark ${currentTicket.ticket_no} as In Progress?`,
confirmText:'Start Progress',
type:'primary'
},
successMessage:'Request marked as in progress.',
onSuccess:refreshCurrentTicket
});
};

window.openMessageModal=function(mode){
messageMode=mode;

$('messageForm').reset();

AdminUI.clearError('messageError');
AdminUI.clearFieldErrors('messageForm');

if(mode==='internal'){
$('messageModalTitle').textContent='Add Internal Note';
$('messageModalSubtitle').textContent='Internal notes are intended for administrative use.';
$('messageButton').textContent='Add Internal Note';
}else{
$('messageModalTitle').textContent='Send Support Response';
$('messageModalSubtitle').textContent='This response will be visible to the member.';
$('messageButton').textContent='Send Response';
}

AdminUI.openModal('messageModal');
};

$('messageForm').addEventListener('submit',async event=>{
event.preventDefault();

if(!currentTicket||!messageMode)return;

AdminUI.clearError('messageError');
AdminUI.clearFieldErrors('messageForm');

if(!AdminUI.validateForm('messageForm',{
messageText:'Message is required.'
}))return;

const endpoint=messageMode==='internal'
?`${API}/${currentTicket.id}/internal-note`
:`${API}/${currentTicket.id}/response`;

const button=$('messageButton');

AdminUI.setLoading(
button,
messageMode==='internal'?'Saving...':'Sending...'
);

try{
await api(endpoint,{
method:'POST',
body:JSON.stringify({
message:$('messageText').value.trim()
})
});

AdminUI.closeModal('messageModal');

Toast.success(
messageMode==='internal'
?'Internal note added successfully.'
:'Support response sent successfully.'
);

await refreshCurrentTicket();

}catch(error){
if(!AdminUI.showValidationErrors('messageForm',error,{
message:'messageText'
})){
AdminUI.showError('messageError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openResolveModal=function(){
$('resolveForm').reset();

AdminUI.clearError('resolveError');
AdminUI.clearFieldErrors('resolveForm');

AdminUI.openModal('resolveModal');
};

$('resolveForm').addEventListener('submit',event=>{
event.preventDefault();

if(!currentTicket)return;

AdminUI.clearError('resolveError');
AdminUI.clearFieldErrors('resolveForm');

if(!AdminUI.validateForm('resolveForm',{
resolution:'Resolution is required.'
}))return;

const resolution=$('resolution').value.trim();

AdminUI.request(`${API}/${currentTicket.id}/resolve`,{
method:'POST',
data:{resolution},
confirmation:{
title:'Resolve Request?',
message:`Resolve ${currentTicket.ticket_no}? The resolution will be visible to the member.`,
confirmText:'Resolve Request',
type:'success'
},
successMessage:'Feedback & Support request resolved.',
onSuccess:async()=>{
AdminUI.closeModal('resolveModal');
await refreshCurrentTicket();
},
onError:error=>{
if(!AdminUI.showValidationErrors('resolveForm',error,{
resolution:'resolution'
})){
AdminUI.showError('resolveError',AdminUI.extractError(error));
}
}
});
});

window.closeRequest=function(){
if(!currentTicket)return;

AdminUI.request(`${API}/${currentTicket.id}/close`,{
method:'POST',
data:{},
confirmation:{
title:'Close Request?',
message:`Close ${currentTicket.ticket_no}? This indicates that the support process is complete.`,
confirmText:'Close Request',
type:'success'
},
successMessage:'Feedback & Support request closed.',
onSuccess:refreshCurrentTicket
});
};

async function refreshCurrentTicket(){
if(!currentTicket)return;

const id=currentTicket.id;

await Promise.all([
loadStatistics(),
loadTickets(currentPage)
]);

try{
const response=await api(`${API}/${id}`);
currentTicket=response.data;
renderManage(currentTicket);
}catch(error){
AdminUI.closeModal('manageModal');
}
}

window.clearFilters=function(){
$('searchInput').value='';
$('typeFilter').value='';
$('statusFilter').value='';
$('priorityFilter').value='';

loadTickets(1);
};

async function init(){
if(typeof AdminUI==='undefined'||typeof api==='undefined'){
setTimeout(init,50);
return;
}

$('searchInput').addEventListener(
'input',
AdminUI.debounce(()=>loadTickets(1))
);

['typeFilter','statusFilter','priorityFilter'].forEach(id=>{
$(id).addEventListener('change',()=>loadTickets(1));
});
await Promise.all([
loadOptions(),
loadStatistics(),
loadTickets()
]);
}

init();
</script>
@endpush