@extends('layouts.admin')

@section('title','Committee & Election')
@section('page_title','Committee & Election')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
<div class="flex items-start gap-3">
<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-person-badge"></i>
</div>
<div>
<h1 class="text-base font-bold text-slate-800">Committee & Election</h1>
<p class="text-sm text-slate-500">Manage committees, terms, positions, office bearers and elections.</p>
</div>
</div>

<div class="flex flex-wrap gap-2">
<button type="button" onclick="openPositionModal()" class="inline-flex items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
<i class="bi bi-diagram-3"></i>
Add Position
</button>

@if(auth()->user()->hasPermission('Committee.create'))
<button type="button" onclick="openCommitteeModal()" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
<i class="bi bi-plus-lg"></i>
New Committee
</button>
@endif
</div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
@php
$stats=[
['committees','Committees','bi-diagram-3','border-slate-200 bg-white','text-slate-800','bg-slate-100 text-slate-500'],
['active_terms','Active Terms','bi-calendar-check','border-emerald-200 bg-emerald-50/40','text-emerald-700','bg-emerald-100 text-emerald-600'],
['active_members','Office Bearers','bi-people','border-indigo-200 bg-indigo-50/40','text-indigo-700','bg-indigo-100 text-indigo-600'],
['elections','Elections','bi-check2-square','border-amber-200 bg-amber-50/40','text-amber-700','bg-amber-100 text-amber-600'],
];
@endphp

@foreach($stats as [$key,$label,$icon,$box,$text,$iconBox])
<div class="rounded-md border p-4 {{ $box }}">
<div class="flex items-start justify-between gap-3">
<div>
<p class="text-xs text-slate-500">{{ $label }}</p>
<p id="stat-{{ $key }}" class="mt-2 text-xl font-bold {{ $text }}">0</p>
</div>
<div class="flex h-8 w-8 items-center justify-center rounded-md {{ $iconBox }}">
<i class="bi {{ $icon }} text-sm"></i>
</div>
</div>
</div>
@endforeach
</div>

{{-- Filters --}}
<div class="rounded-md border border-slate-200 bg-white p-3">
<div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
<div class="flex items-center gap-2">
<div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-search text-sm"></i>
</div>
<div>
<p class="text-sm font-semibold text-slate-700">Search Committees</p>
<p class="hidden text-[11px] text-slate-400 sm:block">Search by committee name or type.</p>
</div>
</div>

<div class="flex w-full items-center lg:w-auto">
<div class="relative w-full lg:w-80">
<i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
<input id="searchInput" type="text" placeholder="Search committees..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
</div>

<select id="statusFilter" class="h-9 border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
<option value="">All Status</option>
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>

<button type="button" onclick="clearFilters()" class="inline-flex h-9 items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
<i class="bi bi-x-lg text-[10px]"></i>
Clear
</button>
</div>
</div>
</div>

{{-- Table + pagination same card --}}
<div class="overflow-hidden rounded-md border border-slate-200 bg-white">
<div class="overflow-x-auto">
<table class="w-full min-w-[900px] table-fixed text-sm">
<thead class="border-b border-slate-200 bg-slate-50">
<tr>
<th class="w-[27%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Committee</th>
<th class="w-[13%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
<th class="w-[20%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Current Term</th>
<th class="w-[12%] px-4 py-3 text-center text-xs font-semibold text-slate-600">Terms</th>
<th class="w-[12%] px-4 py-3 text-center text-xs font-semibold text-slate-600">Members</th>
<th class="w-[8%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
<th class="w-[8%] px-4 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
</tr>
</thead>
<tbody id="committeeTable">
<tr><td colspan="7" class="px-4 py-10 text-center text-slate-400">Loading committees...</td></tr>
</tbody>
</table>
</div>

<div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
</div>
</div>

{{-- Committee Modal --}}
<div id="committeeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-diagram-3"></i>
</div>
<div>
<h3 class="text-base font-bold text-slate-800">Create Committee</h3>
<p class="text-xs text-slate-500">Create a new association committee.</p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('committeeModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="committeeForm" novalidate>
<div class="space-y-4 p-5">
<div id="committeeError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Committee Name <span class="text-red-500">*</span></label>
<input id="committeeName" class="app-input w-full" maxlength="150">
<p data-field-error="committeeName" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Type <span class="text-red-500">*</span></label>
<select id="committeeType" class="app-input w-full">
<option value="executive">Executive</option>
<option value="advisory">Advisory</option>
<option value="special">Special</option>
<option value="other">Other</option>
</select>
<p data-field-error="committeeType" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Description</label>
<textarea id="committeeDescription" rows="3" maxlength="3000" class="app-input w-full resize-none"></textarea>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('committeeModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Cancel</button>
<button id="committeeButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">Create Committee</button>
</div>
</form>
</div>
</div>

{{-- Position Modal --}}
<div id="positionModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Add Committee Position</h3>
<p class="text-xs text-slate-500">Create a reusable committee position.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('positionModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="positionForm" novalidate>
<div class="space-y-4 p-5">
<div id="positionError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div class="grid gap-4 sm:grid-cols-2">
<div>
<label class="form-label">Code <span class="text-red-500">*</span></label>
<input id="positionCode" class="app-input w-full" placeholder="ASSISTANT_SECRETARY">
<p data-field-error="positionCode" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Name <span class="text-red-500">*</span></label>
<input id="positionName" class="app-input w-full">
<p data-field-error="positionName" class="mt-1 hidden text-xs text-red-600"></p>
</div>
</div>

<div>
<label class="form-label">Sort Order</label>
<input id="positionOrder" type="number" value="100" min="0" class="app-input w-full">
</div>

<label class="flex items-center gap-2 text-xs font-medium text-slate-600">
<input id="positionExclusive" type="checkbox" checked>
Exclusive position
</label>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('positionModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="positionButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Save Position</button>
</div>
</form>
</div>
</div>

{{-- Detail Modal --}}
<div id="detailModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
<div class="app-modal-panel flex max-h-[94vh] w-full max-w-6xl flex-col overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div class="flex items-center gap-3">
<div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
<i class="bi bi-person-badge"></i>
</div>
<div>
<h3 id="detailTitle" class="text-lg font-bold text-slate-800">Committee</h3>
<p id="detailSub" class="text-xs text-slate-500"></p>
</div>
</div>
<button type="button" onclick="AdminUI.closeModal('detailModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<div id="detailBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>
</div>
</div>

{{-- Term Modal --}}
<div id="termModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Add Committee Term</h3>
<p class="text-xs text-slate-500">Create a new committee tenure.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('termModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="termForm" novalidate>
<div class="space-y-4 p-5">
<div id="termError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Term Name <span class="text-red-500">*</span></label>
<input id="termName" class="app-input w-full" placeholder="2026-2028">
<p data-field-error="termName" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div class="grid gap-4 sm:grid-cols-2">
<div>
<label class="form-label">Start Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
<input id="termStartDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="termStartDate" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">End Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
<input id="termEndDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="termEndDate" class="mt-1 hidden text-xs text-red-600"></p>
</div>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('termModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="termButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Create Term</button>
</div>
</form>
</div>
</div>

{{-- Assign Member Modal --}}
<div id="assignMemberModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Assign Committee Member</h3>
<p class="text-xs text-slate-500">Assign an eligible member to a committee position.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('assignMemberModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="assignMemberForm" novalidate>
<div class="space-y-4 p-5">
<div id="assignMemberError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Member <span class="text-red-500">*</span></label>
<select id="assignMemberId" class="app-input w-full"><option value="">Select Member</option></select>
<p data-field-error="assignMemberId" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Position <span class="text-red-500">*</span></label>
<select id="assignPositionId" class="app-input w-full"><option value="">Select Position</option></select>
<p data-field-error="assignPositionId" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Appointment Method <span class="text-red-500">*</span></label>
<select id="appointmentMethod" class="app-input w-full">
<option value="appointed">Appointed</option>
<option value="replacement">Replacement</option>
<option value="elected">Elected</option>
</select>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('assignMemberModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="assignMemberButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Assign Member</button>
</div>
</form>
</div>
</div>

{{-- Election Modal --}}
<div id="electionModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Create Election</h3>
<p class="text-xs text-slate-500">Create an election under the selected committee term.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('electionModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="electionForm" novalidate>
<div class="space-y-4 p-5">
<div id="electionError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Election Title <span class="text-red-500">*</span></label>
<input id="electionTitle" class="app-input w-full" placeholder="Executive Committee Election 2026">
<p data-field-error="electionTitle" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Election Date <span class="text-red-500">*</span></label>
<div class="relative">
<i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
<input id="electionDate" type="text" class="app-input js-date-picker w-full !pl-9" placeholder="Select date" autocomplete="off">
</div>
<p data-field-error="electionDate" class="mt-1 hidden text-xs text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('electionModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="electionButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Create Election</button>
</div>
</form>
</div>
</div>

{{-- Candidate Modal --}}
<div id="candidateModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Add Election Candidate</h3>
<p class="text-xs text-slate-500">Nominate a member for a committee position.</p>
</div>
<button type="button" onclick="AdminUI.closeModal('candidateModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="candidateForm" novalidate>
<div class="space-y-4 p-5">
<div id="candidateError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Member <span class="text-red-500">*</span></label>
<select id="candidateMemberId" class="app-input w-full"><option value="">Select Member</option></select>
<p data-field-error="candidateMemberId" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<div>
<label class="form-label">Position <span class="text-red-500">*</span></label>
<select id="candidatePositionId" class="app-input w-full"><option value="">Select Position</option></select>
<p data-field-error="candidatePositionId" class="mt-1 hidden text-xs text-red-600"></p>
</div>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('candidateModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="candidateButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Add Candidate</button>
</div>
</form>
</div>
</div>

{{-- Result Modal --}}
<div id="resultModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
<div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">
<div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
<div>
<h3 class="text-base font-bold text-slate-800">Candidate Result</h3>
<p id="resultSubtitle" class="text-xs text-slate-500"></p>
</div>
<button type="button" onclick="AdminUI.closeModal('resultModal')" class="app-modal-close"><i class="bi bi-x-lg"></i></button>
</div>

<form id="resultForm" novalidate>
<div class="space-y-4 p-5">
<div id="resultError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

<div>
<label class="form-label">Votes <span class="text-red-500">*</span></label>
<input id="candidateVotes" type="number" min="0" step="1" class="app-input w-full">
<p data-field-error="candidateVotes" class="mt-1 hidden text-xs text-red-600"></p>
</div>

<label class="flex items-center gap-2 text-xs font-medium text-slate-600">
<input id="candidateElected" type="checkbox">
Mark candidate as elected
</label>
</div>

<div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
<button type="button" onclick="AdminUI.closeModal('resultModal')" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold">Cancel</button>
<button id="resultButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">Save Result</button>
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
const API='/api/committees';

let committees=[];
let activeCommittee=null;
let optionsData={members:[],positions:[]};
let activeTermId=null;
let activeElectionId=null;
let activeCandidate=null;
let currentPage=1;
let lastPage=1;
let total=0;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const date=value=>value?AdminUI.formatDate(value):'—';

function statusBadge(active){
return active
?'<span class="inline-flex rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Active</span>'
:'<span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">Inactive</span>';
}

function termStatusBadge(status){
const map={
draft:'bg-slate-100 text-slate-600',
active:'bg-emerald-50 text-emerald-700',
completed:'bg-indigo-50 text-indigo-700'
};
return`<span class="rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[status]??'bg-slate-100 text-slate-600'}">${esc(status)}</span>`;
}

function electionBadge(status){
const map={
draft:'bg-slate-100 text-slate-600',
nomination_open:'bg-amber-50 text-amber-700',
voting:'bg-sky-50 text-sky-700',
completed:'bg-emerald-50 text-emerald-700'
};
return`<span class="rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[status]??'bg-slate-100 text-slate-600'}">${esc(String(status).replaceAll('_',' '))}</span>`;
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

async function loadOptions(){
try{
const response=await api(`${API}/options`);
optionsData=response.data??{members:[],positions:[]};

const memberOptions='<option value="">Select Member</option>'+
(optionsData.members??[]).map(m=>`<option value="${m.id}">${esc(m.user?.name??'N/A')} (${esc(m.member_code??'')})</option>`).join('');

const positionOptions='<option value="">Select Position</option>'+
(optionsData.positions??[]).map(p=>`<option value="${p.id}">${esc(p.name)}</option>`).join('');

$('assignMemberId').innerHTML=memberOptions;
$('candidateMemberId').innerHTML=memberOptions;
$('assignPositionId').innerHTML=positionOptions;
$('candidatePositionId').innerHTML=positionOptions;
}catch(error){
Toast.error(AdminUI.extractError(error));
}
}

async function loadCommittees(page=1){
currentPage=page;

$('committeeTable').innerHTML=AdminUI.loadingState('Loading committees...',7);

const query=AdminUI.query({
search:$('searchInput').value.trim(),
status:$('statusFilter').value,
page
});

try{
const response=await api(`${API}?${query}`);
const paginator=response.data??{};

committees=Array.isArray(paginator.data)?paginator.data:[];

currentPage=Number(paginator.current_page??1);
lastPage=Number(paginator.last_page??1);
total=Number(paginator.total??committees.length);

renderCommittees();

AdminUI.renderPagination({
container:'paginationContainer',
currentPage,
lastPage,
total,
onPageChange:loadCommittees
});
}catch(error){
$('committeeTable').innerHTML=AdminUI.emptyState(AdminUI.extractError(error),7);
}
}

function renderCommittees(){
if(!committees.length){
$('committeeTable').innerHTML=AdminUI.emptyState('No committees found.',7);
return;
}

$('committeeTable').innerHTML=committees.map(c=>{
const term=c.active_term;

return`
<tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
<td class="px-4 py-3">
<div class="font-semibold text-slate-800">${esc(c.name)}</div>
<div class="mt-0.5 truncate text-[11px] text-slate-400">${esc(c.description??'')}</div>
</td>

<td class="px-4 py-3 text-xs capitalize text-slate-600">${esc(c.type)}</td>

<td class="px-4 py-3">
${term?`
<div class="text-xs font-semibold text-slate-700">${esc(term.name)}</div>
<div class="mt-0.5 text-[10px] text-slate-400">${date(term.start_date)} — ${date(term.end_date)}</div>
`:'<span class="text-xs text-slate-400">No active term</span>'}
</td>

<td class="px-4 py-3 text-center">
<span class="inline-flex min-w-7 items-center justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">${Number(c.terms_count??0)}</span>
</td>

<td class="px-4 py-3 text-center">
<span class="inline-flex min-w-7 items-center justify-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">${Number(term?.active_members_count??0)}</span>
</td>

<td class="px-4 py-3">${statusBadge(c.is_active)}</td>

<td class="px-4 py-3 text-right">
<button type="button" onclick="openCommittee(${c.id})" class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100" title="Manage">
<i class="bi bi-eye text-xs"></i>
</button>
</td>
</tr>`;
}).join('');
}

window.openCommittee=async function(id){
try{
const response=await api(`${API}/${id}`);
activeCommittee=response.data;

$('detailTitle').textContent=activeCommittee.name;
$('detailSub').textContent=activeCommittee.type;

renderDetail();
AdminUI.openModal('detailModal');
}catch(error){
Toast.error(AdminUI.extractError(error));
}
};

function renderDetail(){
const terms=activeCommittee?.terms??[];

$('detailBody').innerHTML=`
<div class="mb-4 flex justify-end">
<button type="button" onclick="openTermModal()" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">
<i class="bi bi-plus-lg"></i>
Add Term
</button>
</div>

<div class="space-y-4">
${terms.length?terms.map(renderTerm).join(''):`
<div class="rounded-md border border-dashed border-slate-300 p-10 text-center text-sm text-slate-400">
No committee term created yet.
</div>`}
</div>`;
}

function renderTerm(term){
return`
<section class="overflow-hidden rounded-md border border-slate-200">
<div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
<div>
<div class="flex items-center gap-2">
<h4 class="text-sm font-bold text-slate-700">${esc(term.name)}</h4>
${termStatusBadge(term.status)}
</div>
<p class="mt-1 text-[11px] text-slate-400">${date(term.start_date)} — ${date(term.end_date)}</p>
</div>

<div class="flex flex-wrap gap-1.5">
${term.status==='draft'?`
<button onclick="activateTerm(${term.id})" class="mini-action">Activate</button>`:''}

${term.status==='active'?`
<button onclick="openAssignMemberModal(${term.id})" class="mini-action">Add Member</button>
<button onclick="openElectionModal(${term.id})" class="mini-action">Election</button>
<button onclick="completeTerm(${term.id})" class="mini-danger">Complete</button>`:''}
</div>
</div>

<div class="space-y-5 p-4">

<div>
<div class="mb-2 flex items-center justify-between">
<h5 class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Committee Members</h5>
<span class="text-[10px] text-slate-400">${(term.members??[]).length} member(s)</span>
</div>
${renderMembers(term)}
</div>

<div>
<div class="mb-2 flex items-center justify-between">
<h5 class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Elections</h5>
<span class="text-[10px] text-slate-400">${(term.elections??[]).length} election(s)</span>
</div>
${renderElections(term)}
</div>

</div>
</section>`;
}

function renderMembers(term){
const members=term.members??[];

if(!members.length){
return'<div class="rounded-md bg-slate-50 p-4 text-center text-xs text-slate-400">No committee members assigned.</div>';
}

return`
<div class="overflow-x-auto rounded-md border border-slate-200">
<table class="w-full min-w-[700px] text-xs">
<thead class="border-b border-slate-200 bg-slate-50">
<tr>
<th class="px-3 py-2 text-left">Position</th>
<th class="px-3 py-2 text-left">Member</th>
<th class="px-3 py-2 text-left">Method</th>
<th class="px-3 py-2 text-left">Status</th>
<th class="px-3 py-2 text-right">Action</th>
</tr>
</thead>
<tbody>
${members.map(m=>`
<tr class="border-b border-slate-100 last:border-0">
<td class="px-3 py-2 font-semibold text-slate-700">${esc(m.position?.name??'')}</td>
<td class="px-3 py-2">
<span class="font-medium text-slate-700">${esc(m.member?.user?.name??'')}</span>
<span class="ml-1 text-[10px] text-indigo-500">${esc(m.member?.member_code??'')}</span>
</td>
<td class="px-3 py-2 capitalize text-slate-500">${esc(m.appointment_method)}</td>
<td class="px-3 py-2 capitalize text-slate-500">${esc(m.status)}</td>
<td class="px-3 py-2 text-right">
${m.status==='active'?`
<button onclick="endMember(${m.id},'resigned')" class="text-[11px] font-semibold text-amber-600">Resign</button>
<button onclick="endMember(${m.id},'removed')" class="ml-2 text-[11px] font-semibold text-red-600">Remove</button>
`:''}
</td>
</tr>`).join('')}
</tbody>
</table>
</div>`;
}

function renderElections(term){
const elections=term.elections??[];

if(!elections.length){
return'<div class="rounded-md bg-slate-50 p-4 text-center text-xs text-slate-400">No elections created.</div>';
}

return elections.map(e=>`
<div class="mb-2 rounded-md border border-slate-200 p-3 last:mb-0">
<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
<div>
<div class="flex items-center gap-2">
<p class="text-xs font-semibold text-slate-700">${esc(e.title)}</p>
${electionBadge(e.status)}
</div>
<p class="mt-1 text-[10px] text-slate-400">${date(e.election_date)}</p>
</div>

<div class="flex flex-wrap gap-1.5">
${e.status==='draft'?`
<button onclick="changeElectionStatus(${e.id},'nomination_open')" class="mini-action">Open Nomination</button>`:''}

${e.status==='nomination_open'?`
<button onclick="openCandidateModal(${e.id})" class="mini-action">Add Candidate</button>
<button onclick="changeElectionStatus(${e.id},'voting')" class="mini-action">Start Voting</button>`:''}

${e.status==='voting'?`
<button onclick="changeElectionStatus(${e.id},'completed')" class="mini-action">Complete Election</button>`:''}
</div>
</div>

${(e.candidates??[]).length?`
<div class="mt-3 space-y-1.5">
${e.candidates.map(c=>`
<div class="flex items-center justify-between gap-3 rounded-md bg-slate-50 px-3 py-2">
<div class="min-w-0 text-xs">
<span class="font-semibold text-slate-700">${esc(c.position?.name??'')}</span>
<span class="text-slate-400"> — </span>
<span class="text-slate-600">${esc(c.member?.user?.name??'')}</span>
</div>

${e.status==='voting'?`
<button onclick='openResultModal(${JSON.stringify(c)})' class="shrink-0 text-[11px] font-semibold text-indigo-600">Result</button>
`:`
<span class="shrink-0 text-[10px] text-slate-400">${Number(c.votes??0)} votes · ${esc(c.status??'')}</span>
`}
</div>`).join('')}
</div>`:''}
</div>
`).join('');
}

window.openCommitteeModal=function(){
$('committeeForm').reset();
AdminUI.clearError('committeeError');
AdminUI.clearFieldErrors('committeeForm');
AdminUI.openModal('committeeModal');
};

$('committeeForm').addEventListener('submit',async e=>{
e.preventDefault();

AdminUI.clearError('committeeError');
AdminUI.clearFieldErrors('committeeForm');

if(!AdminUI.validateRequired('committeeForm',{
committeeName:'Committee name is required.',
committeeType:'Committee type is required.'
}))return;

const button=$('committeeButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(API,{
method:'POST',
body:JSON.stringify({
name:$('committeeName').value.trim(),
type:$('committeeType').value,
description:$('committeeDescription').value.trim()||null
})
});

AdminUI.closeModal('committeeModal');
Toast.success('Committee created successfully.');

await Promise.all([
loadCommittees(1),
loadStatistics()
]);
}catch(error){
if(!AdminUI.showValidationErrors('committeeForm',error,{
name:'committeeName',
type:'committeeType',
description:'committeeDescription'
})){
AdminUI.showError('committeeError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openPositionModal=function(){
$('positionForm').reset();
$('positionOrder').value=100;
$('positionExclusive').checked=true;

AdminUI.clearError('positionError');
AdminUI.clearFieldErrors('positionForm');
AdminUI.openModal('positionModal');
};

$('positionForm').addEventListener('submit',async e=>{
e.preventDefault();

AdminUI.clearError('positionError');
AdminUI.clearFieldErrors('positionForm');

if(!AdminUI.validateRequired('positionForm',{
positionCode:'Position code is required.',
positionName:'Position name is required.'
}))return;

const button=$('positionButton');
AdminUI.setLoading(button,'Saving...');

try{
await api(`${API}/positions`,{
method:'POST',
body:JSON.stringify({
code:$('positionCode').value.trim(),
name:$('positionName').value.trim(),
sort_order:Number($('positionOrder').value||0),
is_exclusive:$('positionExclusive').checked
})
});

AdminUI.closeModal('positionModal');
Toast.success('Committee position created.');

await loadOptions();
}catch(error){
if(!AdminUI.showValidationErrors('positionForm',error,{
code:'positionCode',
name:'positionName',
sort_order:'positionOrder'
})){
AdminUI.showError('positionError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openTermModal=function(){
if(!activeCommittee)return;

$('termForm').reset();
AdminUI.clearError('termError');
AdminUI.clearFieldErrors('termForm');

AdminUI.openModal('termModal');
window.initDatePickers?.();
};

$('termForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!activeCommittee)return;

AdminUI.clearError('termError');
AdminUI.clearFieldErrors('termForm');

if(!AdminUI.validateRequired('termForm',{
termName:'Term name is required.',
termStartDate:'Start date is required.',
termEndDate:'End date is required.'
}))return;

if($('termEndDate').value<$('termStartDate').value){
AdminUI.showFieldError('termEndDate','End date must be after start date.');
return;
}

const button=$('termButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(`${API}/${activeCommittee.id}/terms`,{
method:'POST',
body:JSON.stringify({
name:$('termName').value.trim(),
start_date:$('termStartDate').value,
end_date:$('termEndDate').value
})
});

AdminUI.closeModal('termModal');
Toast.success('Committee term created.');

await refreshCommittee();
}catch(error){
if(!AdminUI.showValidationErrors('termForm',error,{
name:'termName',
start_date:'termStartDate',
end_date:'termEndDate'
})){
AdminUI.showError('termError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.activateTerm=function(id){
AdminUI.request(`${API}/terms/${id}/activate`,{
method:'POST',
data:{},
confirmation:{
title:'Activate Committee Term?',
message:'Activate this committee term? Any existing active term may be affected.',
confirmText:'Activate Term',
type:'success'
},
successMessage:'Committee term activated.',
onSuccess:refreshCommittee
});
};

window.completeTerm=function(id){
AdminUI.request(`${API}/terms/${id}/complete`,{
method:'POST',
data:{},
confirmation:{
title:'Complete Committee Term?',
message:'Complete this term? Active committee memberships under this term will also be completed.',
confirmText:'Complete Term',
type:'danger'
},
successMessage:'Committee term completed.',
onSuccess:refreshCommittee
});
};

window.openAssignMemberModal=function(termId){
activeTermId=termId;
$('assignMemberForm').reset();

AdminUI.clearError('assignMemberError');
AdminUI.clearFieldErrors('assignMemberForm');
AdminUI.openModal('assignMemberModal');
};

$('assignMemberForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!activeTermId)return;

if(!AdminUI.validateRequired('assignMemberForm',{
assignMemberId:'Please select a member.',
assignPositionId:'Please select a position.'
}))return;

const button=$('assignMemberButton');
AdminUI.setLoading(button,'Assigning...');

try{
await api(`${API}/terms/${activeTermId}/members`,{
method:'POST',
body:JSON.stringify({
member_id:Number($('assignMemberId').value),
committee_position_id:Number($('assignPositionId').value),
appointment_method:$('appointmentMethod').value
})
});

AdminUI.closeModal('assignMemberModal');
Toast.success('Committee member assigned.');
await refreshCommittee();
}catch(error){
if(!AdminUI.showValidationErrors('assignMemberForm',error,{
member_id:'assignMemberId',
committee_position_id:'assignPositionId',
appointment_method:'appointmentMethod'
})){
AdminUI.showError('assignMemberError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.endMember=function(id,status){
AdminUI.request(`${API}/memberships/${id}`,{
method:'PUT',
data:{status},
confirmation:{
title:status==='resigned'?'Mark Member Resigned?':'Remove Committee Member?',
message:status==='resigned'
?'Mark this committee membership as resigned?'
:'Remove this member from the committee position?',
confirmText:status==='resigned'?'Confirm Resignation':'Remove Member',
type:'danger'
},
successMessage:status==='resigned'?'Committee member marked resigned.':'Committee member removed.',
onSuccess:refreshCommittee
});
};

window.openElectionModal=function(termId){
activeTermId=termId;

$('electionForm').reset();
AdminUI.clearError('electionError');
AdminUI.clearFieldErrors('electionForm');

AdminUI.openModal('electionModal');
window.initDatePickers?.();
};

$('electionForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!activeTermId)return;

if(!AdminUI.validateRequired('electionForm',{
electionTitle:'Election title is required.',
electionDate:'Election date is required.'
}))return;

const button=$('electionButton');
AdminUI.setLoading(button,'Creating...');

try{
await api(`${API}/terms/${activeTermId}/elections`,{
method:'POST',
body:JSON.stringify({
title:$('electionTitle').value.trim(),
election_date:$('electionDate').value
})
});

AdminUI.closeModal('electionModal');
Toast.success('Election created successfully.');
await refreshCommittee();
}catch(error){
if(!AdminUI.showValidationErrors('electionForm',error,{
title:'electionTitle',
election_date:'electionDate'
})){
AdminUI.showError('electionError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.changeElectionStatus=function(id,status){
const labels={
nomination_open:['Open Nomination?','Open Nomination','Open nomination for this election?','success'],
voting:['Start Voting?','Start Voting','Start voting for this election?','success'],
completed:['Complete Election?','Complete Election','Complete this election and finalize the voting stage?','danger']
};

const item=labels[status]??['Change Election Status?','Confirm','Change election status?','primary'];

AdminUI.request(`${API}/elections/${id}/status`,{
method:'PUT',
data:{status},
confirmation:{
title:item[0],
message:item[2],
confirmText:item[1],
type:item[3]
},
successMessage:'Election status updated.',
onSuccess:refreshCommittee
});
};

window.openCandidateModal=function(electionId){
activeElectionId=electionId;

$('candidateForm').reset();
AdminUI.clearError('candidateError');
AdminUI.clearFieldErrors('candidateForm');
AdminUI.openModal('candidateModal');
};

$('candidateForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!activeElectionId)return;

if(!AdminUI.validateRequired('candidateForm',{
candidateMemberId:'Please select a member.',
candidatePositionId:'Please select a position.'
}))return;

const button=$('candidateButton');
AdminUI.setLoading(button,'Adding...');

try{
await api(`${API}/elections/${activeElectionId}/candidates`,{
method:'POST',
body:JSON.stringify({
member_id:Number($('candidateMemberId').value),
committee_position_id:Number($('candidatePositionId').value)
})
});

AdminUI.closeModal('candidateModal');
Toast.success('Election candidate added.');
await refreshCommittee();
}catch(error){
if(!AdminUI.showValidationErrors('candidateForm',error,{
member_id:'candidateMemberId',
committee_position_id:'candidatePositionId'
})){
AdminUI.showError('candidateError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

window.openResultModal=function(candidate){
activeCandidate=candidate;

$('resultForm').reset();
AdminUI.clearError('resultError');
AdminUI.clearFieldErrors('resultForm');

$('candidateVotes').value=Number(candidate.votes??0);
$('candidateElected').checked=candidate.status==='elected';

$('resultSubtitle').textContent=
`${candidate.member?.user?.name??'Candidate'} • ${candidate.position?.name??''}`;

AdminUI.openModal('resultModal');
};

$('resultForm').addEventListener('submit',async e=>{
e.preventDefault();
if(!activeCandidate)return;

if(!AdminUI.validateRequired('resultForm',{
candidateVotes:'Number of votes is required.'
}))return;

const votes=Number($('candidateVotes').value);

if(!Number.isInteger(votes)||votes<0){
AdminUI.showFieldError('candidateVotes','Votes must be zero or a positive whole number.');
return;
}

const button=$('resultButton');
AdminUI.setLoading(button,'Saving...');

try{
await api(`${API}/candidates/${activeCandidate.id}/result`,{
method:'PUT',
body:JSON.stringify({
votes,
elected:$('candidateElected').checked
})
});

AdminUI.closeModal('resultModal');
Toast.success('Candidate result updated.');
await refreshCommittee();
}catch(error){
if(!AdminUI.showValidationErrors('resultForm',error,{
votes:'candidateVotes'
})){
AdminUI.showError('resultError',AdminUI.extractError(error));
}
}finally{
AdminUI.resetLoading(button);
}
});

async function refreshCommittee(){
if(!activeCommittee)return;

const id=activeCommittee.id;

await Promise.all([
loadStatistics(),
loadCommittees(currentPage)
]);

try{
const response=await api(`${API}/${id}`);
activeCommittee=response.data;
renderDetail();
}catch(error){
AdminUI.closeModal('detailModal');
Toast.error(AdminUI.extractError(error));
}
}

window.clearFilters=function(){
$('searchInput').value='';
$('statusFilter').value='';
loadCommittees(1);
};

async function init(){
if(typeof AdminUI==='undefined'||typeof api==='undefined'){
setTimeout(init,50);
return;
}

$('searchInput').addEventListener(
'input',
AdminUI.debounce(()=>loadCommittees(1))
);

$('statusFilter').addEventListener(
'change',
()=>loadCommittees(1)
);

[
'committeeForm',
'positionForm',
'termForm',
'assignMemberForm',
'electionForm',
'candidateForm',
'resultForm'
].forEach(id=>AdminUI.bindFieldValidation(id));

window.initDatePickers?.();

await Promise.all([
loadOptions(),
loadStatistics(),
loadCommittees()
]);
}

init();
</script>

<style>
.mini-action{padding:.35rem .6rem;border:1px solid #cbd5e1;border-radius:.375rem;background:#fff;font-size:.7rem;font-weight:600;color:#475569;transition:.15s}
.mini-action:hover{background:#f8fafc}
.mini-danger{padding:.35rem .6rem;border:1px solid #fecaca;border-radius:.375rem;background:#fff;font-size:.7rem;font-weight:600;color:#dc2626;transition:.15s}
.mini-danger:hover{background:#fef2f2}
</style>
@endpush