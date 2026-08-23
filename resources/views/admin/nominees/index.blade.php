@extends('layouts.admin')

@section('title','Nominee Management')
@section('page_title','Nominee Management')

@section('content')
<div class="space-y-5">

{{-- Header --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
            <i class="bi bi-person-heart"></i>
        </div>
        <div>
            <h1 class="text-base font-bold text-slate-800">Nominee Management</h1>
            <p class="text-sm text-slate-500">Manage nominee information, allocations, identity and verification.</p>
        </div>
    </div>

    @if(auth()->user()->hasPermission('Nominee.create'))
    <button type="button" onclick="openNomineeModal()"
        class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
        <i class="bi bi-person-plus"></i>
        Add Nominee
    </button>
    @endif
</div>

{{-- Statistics --}}
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
    @php
        $stats=[
            [
                'id'=>'totalNominees',
                'label'=>'Total Nominees',
                'icon'=>'bi-people',
                'box'=>'border-slate-200 bg-white',
                'text'=>'text-slate-800',
                'iconbox'=>'bg-slate-100 text-slate-500'
            ],
            [
                'id'=>'activeNominees',
                'label'=>'Active',
                'icon'=>'bi-person-check',
                'box'=>'border-emerald-200 bg-emerald-50/40',
                'text'=>'text-emerald-700',
                'iconbox'=>'bg-emerald-100 text-emerald-600'
            ],
            [
                'id'=>'verifiedNominees',
                'label'=>'Verified',
                'icon'=>'bi-patch-check',
                'box'=>'border-indigo-200 bg-indigo-50/40',
                'text'=>'text-indigo-700',
                'iconbox'=>'bg-indigo-100 text-indigo-600'
            ],
            [
                'id'=>'pendingNominees',
                'label'=>'Pending Verification',
                'icon'=>'bi-hourglass-split',
                'box'=>'border-amber-200 bg-amber-50/40',
                'text'=>'text-amber-700',
                'iconbox'=>'bg-amber-100 text-amber-600'
            ]
        ];
    @endphp

    @foreach($stats as $stat)
    <div class="rounded-md border p-4 {{ $stat['box'] }}">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs text-slate-500">{{ $stat['label'] }}</p>
                <p id="{{ $stat['id'] }}" class="mt-2 text-xl font-bold {{ $stat['text'] }}">0</p>
            </div>
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $stat['iconbox'] }}">
                <i class="bi {{ $stat['icon'] }} text-sm"></i>
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
                <i class="bi bi-search text-sm"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-700">Search Nominees</p>
                <p class="hidden text-[11px] text-slate-400 sm:block">Search by nominee, member, phone or identity number.</p>
            </div>
        </div>

        <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[280px_160px_140px_auto] lg:gap-0">
            <div class="relative sm:col-span-2 lg:col-span-1">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input id="searchInput" type="text" placeholder="Search nominee..."
                    class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
            </div>

            <select id="verificationFilter"
                class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                <option value="">All Verification</option>
                <option value="unverified">Unverified</option>
                <option value="pending">Pending</option>
                <option value="verified">Verified</option>
                <option value="rejected">Rejected</option>
            </select>

            <select id="activeFilter"
                class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>

            <button type="button" onclick="clearFilters()"
                class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
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
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Nominee</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Relationship</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Allocation</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Priority</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Verification</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>

            <tbody id="nomineeTableBody" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">Loading nominees...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Mobile Cards --}}
<div id="nomineeMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
    <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
        Loading nominees...
    </div>
</div>

<div id="paginationWrap" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- Add/Edit Modal --}}
<div id="nomineeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

    <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-person-heart"></i>
            </div>
            <div>
                <h3 id="nomineeModalTitle" class="text-lg font-bold text-slate-800">Add Nominee</h3>
                <p id="nomineeModalSubtitle" class="text-xs text-slate-500">Add nominee information for a member.</p>
            </div>
        </div>

        <button type="button" onclick="AdminUI.closeModal('nomineeModal')" class="app-modal-close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form id="nomineeForm" class="flex min-h-0 flex-1 flex-col" novalidate>
        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">

            <div id="nomineeError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

            {{-- Member --}}
            <div id="memberSection" class="rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-person"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Member</h4>
                        <p class="text-[11px] text-slate-400">Select the member for this nominee.</p>
                    </div>
                </div>

                <label class="form-label">Member <span class="text-red-500">*</span></label>
                <select id="memberId" class="app-input w-full">
                    <option value="">Select Member</option>
                </select>
                <p data-field-error="memberId" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

            {{-- Personal --}}
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                        <i class="bi bi-person-vcard"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Personal Information</h4>
                        <p class="text-[11px] text-slate-400">Basic nominee information.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Nominee Name <span class="text-red-500">*</span></label>
                        <input id="nomineeName" type="text" maxlength="150" class="app-input w-full" placeholder="Full name">
                        <p data-field-error="nomineeName" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Relationship <span class="text-red-500">*</span></label>
                        <input id="relationship" type="text" maxlength="80" class="app-input w-full" placeholder="Spouse, Son, Daughter...">
                        <p data-field-error="relationship" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Phone</label>
                        <input id="phone" type="text" maxlength="30" class="app-input w-full" placeholder="Phone number">
                        <p data-field-error="phone" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Date of Birth</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input id="dateOfBirth" type="text"
                                class="app-input js-date-picker w-full !pl-9"
                                placeholder="Select date"
                                autocomplete="off">
                        </div>
                        <p data-field-error="dateOfBirth" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Address</label>
                        <textarea id="address" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Address"></textarea>
                        <p data-field-error="address" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                </div>
            </div>

            {{-- Identity --}}
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                        <i class="bi bi-card-heading"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Identity Information</h4>
                        <p class="text-[11px] text-slate-400">Identity details used for verification.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Identity Type</label>
                        <select id="identityType" class="app-input w-full">
                            <option value="">Select Identity Type</option>
                            <option value="nid">National ID (NID)</option>
                            <option value="birth_certificate">Birth Certificate</option>
                            <option value="passport">Passport</option>
                            <option value="other">Other</option>
                        </select>
                        <p data-field-error="identityType" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Identity Number</label>
                        <input id="identityNumber" type="text" maxlength="100" class="app-input w-full" placeholder="Identity number">
                        <p data-field-error="identityNumber" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                </div>
            </div>

            {{-- Allocation --}}
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                        <i class="bi bi-pie-chart"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Allocation & Priority</h4>
                        <p class="text-[11px] text-slate-400">Combined active nominee allocation cannot exceed 100%.</p>
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Allocation Percentage <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input id="allocationPercentage" type="number" min="0.01" max="100" step="0.01" class="app-input w-full !pr-9" placeholder="0.00">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">%</span>
                        </div>
                        <p data-field-error="allocationPercentage" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Priority <span class="text-red-500">*</span></label>
                        <input id="priority" type="number" min="1" max="999" value="1" class="app-input w-full">
                        <p data-field-error="priority" class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                </div>

                <div class="mt-4 rounded-md border border-sky-200 bg-sky-50 px-3 py-2.5">
                    <div class="flex gap-2">
                        <i class="bi bi-info-circle mt-0.5 text-sky-600"></i>
                        <p class="text-[11px] leading-4 text-sky-700">Multiple nominees are allowed, but total allocation of active nominees for one member cannot exceed 100%.</p>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="form-label">Notes</label>
                <textarea id="notes" rows="3" maxlength="3000" class="app-input w-full resize-none" placeholder="Optional notes..."></textarea>
                <p data-field-error="notes" class="mt-1 hidden text-xs text-red-600"></p>
            </div>

        </div>

        <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
            <button type="button" onclick="AdminUI.closeModal('nomineeModal')"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </button>

            <button id="saveNomineeButton" type="submit"
                class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                Save Nominee
            </button>
        </div>
    </form>
</div>
</div>

{{-- Verification Modal --}}
<div id="verificationModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
<div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">

    <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                <i class="bi bi-patch-check"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Nominee Verification</h3>
                <p id="verificationSubtitle" class="text-xs text-slate-500"></p>
            </div>
        </div>

        <button type="button" onclick="AdminUI.closeModal('verificationModal')" class="app-modal-close">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
        <input id="verificationNomineeId" type="hidden">

        <div id="verificationError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>

        <div id="verificationDetails" class="grid grid-cols-2 gap-3"></div>

        <div>
            <label class="form-label">Verification Note</label>
            <textarea id="verificationNote" rows="4" maxlength="3000" class="app-input w-full resize-none" placeholder="Optional note for verification or required reason for rejection..."></textarea>
            <p data-field-error="verificationNote" class="mt-1 hidden text-xs text-red-600"></p>
        </div>
    </div>

    @if(auth()->user()->hasPermission('Nominee.verify'))
    <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
        <button type="button" onclick="rejectNominee()"
            class="cursor-pointer rounded-md border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">
            <i class="bi bi-x-circle mr-1"></i>
            Reject
        </button>

        <button id="verifyNomineeButton" type="button" onclick="verifyNominee()"
            class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 disabled:opacity-60">
            <i class="bi bi-check2-circle mr-1"></i>
            Verify
        </button>
    </div>
    @endif
</div>
</div>
@endsection

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>

@push('scripts')
<script>
const API='/api/nominees';
const canUpdate=@json(auth()->user()->hasPermission('Nominee.update'));
const canVerify=@json(auth()->user()->hasPermission('Nominee.verify'));
const canDelete=@json(auth()->user()->hasPermission('Nominee.delete'));

let nominees=[];
let memberOptions=[];
let editingNominee=null;
let currentPage=1;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');
const date=value=>value?AdminUI.formatDate(value):'—';

const nomineeFieldMap={
    member_id:'memberId',
    name:'nomineeName',
    relationship:'relationship',
    phone:'phone',
    date_of_birth:'dateOfBirth',
    identity_type:'identityType',
    identity_number:'identityNumber',
    allocation_percentage:'allocationPercentage',
    priority:'priority',
    address:'address',
    notes:'notes'
};

function setDate(id,value){
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

function verificationBadge(status){
    const map={
        verified:['Verified','bg-emerald-50 text-emerald-700','bi-patch-check-fill'],
        pending:['Pending','bg-amber-50 text-amber-700','bi-hourglass-split'],
        rejected:['Rejected','bg-red-50 text-red-600','bi-x-circle'],
        unverified:['Unverified','bg-slate-100 text-slate-500','bi-question-circle']
    };

    const item=map[status]??map.unverified;

    return`
        <span class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${item[1]}">
            <i class="bi ${item[2]}"></i>
            ${item[0]}
        </span>
    `;
}

function activeBadge(active){
    return active
        ?'<span class="inline-flex rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Active</span>'
        :'<span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">Inactive</span>';
}

function identityLabel(type){
    return{
        nid:'National ID',
        birth_certificate:'Birth Certificate',
        passport:'Passport',
        other:'Other'
    }[type]??'—';
}

async function loadOptions(){
    try{
        const response=await api(`${API}/options`);

        memberOptions=
            response.data?.members??[];

        $('memberId').innerHTML=
            '<option value="">Select Member</option>'+
            memberOptions.map(member=>`
                <option value="${member.id}">
                    ${esc(member.user?.name??'N/A')} (${esc(member.member_code??'')})
                </option>
            `).join('');

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadStatistics(){
    try{
        const response=await api(
            `${API}/statistics`
        );

        const data=response.data??{};

        $('totalNominees').textContent=
            data.total??0;

        $('activeNominees').textContent=
            data.active??0;

        $('verifiedNominees').textContent=
            data.verified??0;

        $('pendingNominees').textContent=
            data.pending??0;

    }catch(error){
        console.error(error);
    }
}

async function loadNominees(page=1){
    currentPage=page;

    const tbody=$('nomineeTableBody');
    const grid=$('nomineeMobileGrid');

    if(tbody){
        tbody.innerHTML=
            AdminUI.loadingState(
                'Loading nominees...',
                8
            );
    }

    if(grid){
        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                <div class="flex items-center justify-center gap-2">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                    Loading nominees...
                </div>
            </div>
        `;
    }

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const verification=$('verificationFilter').value;
    const active=$('activeFilter').value;

    if(search){
        params.set('search',search);
    }

    if(verification){
        params.set(
            'verification_status',
            verification
        );
    }

    if(active!==''){
        params.set('active',active);
    }

    try{
        const response=await api(
            `${API}?${params.toString()}`
        );

        const paginator=response.data??{};

        nominees=paginator.data??[];

        renderNominees();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadNominees
        );

    }catch(error){
        const message=
            AdminUI.extractError(error);

        if(tbody){
            tbody.innerHTML=
                AdminUI.emptyState(
                    message,
                    8
                );
        }

        if(grid){
            grid.innerHTML=`
                <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-sm text-red-600">
                    ${esc(message)}
                </div>
            `;
        }
    }
}

function nomineeActions(nominee){
    const actions=[];

    if(canVerify){
        actions.push(`
            <button type="button"
                onclick="openVerificationModal(${nominee.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                <i class="bi bi-patch-check"></i>
                Verify
            </button>
        `);
    }

    if(canUpdate){
        actions.push(`
            <button type="button"
                onclick="editNominee(${nominee.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <i class="bi bi-pencil"></i>
                Edit
            </button>
        `);

        actions.push(`
            <button type="button"
                onclick="toggleNominee(${nominee.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border ${nominee.is_active?'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100':'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'} px-2.5 py-1.5 text-[11px] font-semibold transition">
                <i class="bi ${nominee.is_active?'bi-pause-circle':'bi-play-circle'}"></i>
                ${nominee.is_active?'Deactivate':'Activate'}
            </button>
        `);
    }

    if(canDelete){
        actions.push(`
            <button type="button"
                onclick="deleteNominee(${nominee.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50">
                <i class="bi bi-trash3"></i>
                Delete
            </button>
        `);
    }

    return actions.join('');
}

function renderNominees(){
    const tbody=$('nomineeTableBody');
    const grid=$('nomineeMobileGrid');

    if(!nominees.length){
        if(tbody){
            tbody.innerHTML=
                AdminUI.emptyState(
                    'No nominees found.',
                    8
                );
        }

        if(grid){
            grid.innerHTML=`
                <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <i class="bi bi-person-heart"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-600">No nominees found</p>
                    <p class="mt-1 text-xs text-slate-400">Try changing the search or filters.</p>
                </div>
            `;
        }

        return;
    }

    if(tbody){
        tbody.innerHTML=nominees.map(nominee=>`
            <tr class="transition hover:bg-slate-50/70">

                <td class="px-4 py-3">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-700">
                            ${esc(nominee.member?.user?.name??'N/A')}
                        </div>
                        <div class="mt-0.5 text-[11px] font-medium text-indigo-600">
                            ${esc(nominee.member?.member_code??'')}
                        </div>
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-person-heart"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="font-semibold text-slate-700">
                                ${esc(nominee.name)}
                            </div>

                            <div class="mt-0.5 max-w-[180px] truncate text-xs text-slate-400">
                                ${esc(nominee.phone||'No phone')}
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-4 py-3 text-slate-500">
                    ${esc(nominee.relationship)}
                </td>

                <td class="px-4 py-3 text-center">
                    <span class="inline-flex rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700">
                        ${Number(nominee.allocation_percentage||0).toFixed(2)}%
                    </span>
                </td>

                <td class="px-4 py-3 text-center">
                    <span class="inline-flex min-w-7 items-center justify-center rounded-md bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">
                        ${nominee.priority??1}
                    </span>
                </td>

                <td class="px-4 py-3">
                    ${verificationBadge(
                        nominee.verification_status
                    )}
                </td>

                <td class="px-4 py-3">
                    ${activeBadge(
                        nominee.is_active
                    )}
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-1">
                        ${nomineeActions(nominee)}
                    </div>
                </td>
            </tr>
        `).join('');
    }

    if(grid){
        grid.innerHTML=nominees.map(nominee=>`
            <article class="overflow-hidden rounded-md border border-slate-200 bg-white">

                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">

                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-person-heart"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-700">
                                    ${esc(nominee.name)}
                                </p>

                                <p class="mt-0.5 truncate text-[11px] text-slate-400">
                                    ${esc(nominee.relationship)}
                                </p>
                            </div>
                        </div>

                        ${verificationBadge(
                            nominee.verification_status
                        )}
                    </div>
                </div>

                <div class="space-y-3 p-4">

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Allocation
                            </p>

                            <p class="mt-1 text-sm font-bold text-indigo-700">
                                ${Number(
                                    nominee.allocation_percentage||0
                                ).toFixed(2)}%
                            </p>
                        </div>

                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Priority
                            </p>

                            <p class="mt-1 text-sm font-bold text-slate-700">
                                ${nominee.priority??1}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-2 border-t border-slate-100 pt-3 text-xs">

                        <div>
                            <p class="text-[10px] text-slate-400">Member</p>
                            <p class="mt-0.5 truncate font-medium text-slate-600">
                                ${esc(
                                    nominee.member?.user?.name??
                                    'N/A'
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Member Code</p>
                            <p class="mt-0.5 truncate font-medium text-indigo-600">
                                ${esc(
                                    nominee.member?.member_code??
                                    '—'
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Date of Birth</p>
                            <p class="mt-0.5 font-medium text-slate-600">
                                ${date(
                                    nominee.date_of_birth
                                )}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Status</p>
                            <div class="mt-0.5">
                                ${activeBadge(
                                    nominee.is_active
                                )}
                            </div>
                        </div>

                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${nomineeActions(nominee)}
                </div>
            </article>
        `).join('');
    }
}

window.openNomineeModal=function(){
    editingNominee=null;

    $('nomineeForm').reset();

    AdminUI.clearError(
        'nomineeError'
    );

    AdminUI.clearFieldErrors(
        'nomineeForm'
    );

    $('nomineeModalTitle')
        .textContent='Add Nominee';

    $('nomineeModalSubtitle')
        .textContent=
            'Add nominee information for a member.';

    $('memberSection')
        .classList.remove('hidden');

    $('memberId').disabled=false;
    $('priority').value=1;

    AdminUI.openModal(
        'nomineeModal'
    );

    window.initDatePickers?.();

    setDate(
        'dateOfBirth',
        ''
    );
};

window.editNominee=async function(id){
    try{
        const response=await api(
            `${API}/${id}`
        );

        const nominee=response.data;

        editingNominee=nominee;

        $('nomineeForm').reset();

        AdminUI.clearError(
            'nomineeError'
        );

        AdminUI.clearFieldErrors(
            'nomineeForm'
        );

        $('nomineeModalTitle')
            .textContent='Edit Nominee';

        $('nomineeModalSubtitle')
            .textContent=
                'Update nominee information and allocation.';

        $('memberSection')
            .classList.add('hidden');

        $('nomineeName').value=
            nominee.name||'';

        $('relationship').value=
            nominee.relationship||'';

        $('phone').value=
            nominee.phone||'';

        $('identityType').value=
            nominee.identity_type||'';

        $('identityNumber').value=
            nominee.identity_number||'';

        $('allocationPercentage').value=
            nominee.allocation_percentage||'';

        $('priority').value=
            nominee.priority||1;

        $('address').value=
            nominee.address||'';

        $('notes').value=
            nominee.notes||'';

        AdminUI.openModal(
            'nomineeModal'
        );

        window.initDatePickers?.();

        setDate(
            'dateOfBirth',
            nominee.date_of_birth
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

$('nomineeForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'nomineeError'
        );

        AdminUI.clearFieldErrors(
            'nomineeForm'
        );

        const required={
            nomineeName:
                'Nominee name is required.',
            relationship:
                'Relationship is required.',
            allocationPercentage:
                'Allocation percentage is required.',
            priority:
                'Priority is required.'
        };

        if(!editingNominee){
            required.memberId=
                'Please select a member.';
        }

        if(
            !AdminUI.validateRequired(
                'nomineeForm',
                required
            )
        ){
            return;
        }

        const allocation=
            Number(
                $('allocationPercentage').value
            );

        if(
            !Number.isFinite(allocation)||
            allocation<=0||
            allocation>100
        ){
            AdminUI.showFieldError(
                'allocationPercentage',
                'Allocation must be greater than 0 and not exceed 100%.'
            );

            return;
        }

        const priority=
            Number(
                $('priority').value
            );

        if(
            !Number.isInteger(priority)||
            priority<1
        ){
            AdminUI.showFieldError(
                'priority',
                'Priority must be at least 1.'
            );

            return;
        }

        const identityType=
            $('identityType').value;

        const identityNumber=
            $('identityNumber').value.trim();

        if(
            identityType&&
            !identityNumber
        ){
            AdminUI.showFieldError(
                'identityNumber',
                'Identity number is required when identity type is selected.'
            );

            return;
        }

        if(
            identityNumber&&
            !identityType
        ){
            AdminUI.showFieldError(
                'identityType',
                'Please select an identity type.'
            );

            return;
        }

        const data={
            name:
                $('nomineeName')
                    .value.trim(),

            relationship:
                $('relationship')
                    .value.trim(),

            phone:
                $('phone')
                    .value.trim()||null,

            date_of_birth:
                $('dateOfBirth')
                    .value||null,

            identity_type:
                identityType||null,

            identity_number:
                identityNumber||null,

            allocation_percentage:
                allocation,

            priority,

            address:
                $('address')
                    .value.trim()||null,

            notes:
                $('notes')
                    .value.trim()||null
        };

        if(!editingNominee){
            data.member_id=
                Number(
                    $('memberId').value
                );
        }

        const button=
            $('saveNomineeButton');

        AdminUI.setLoading(
            button,
            editingNominee
                ?'Updating...'
                :'Saving...'
        );

        try{
            await api(
                editingNominee
                    ?`${API}/${editingNominee.id}`
                    :API,
                {
                    method:
                        editingNominee
                            ?'PUT'
                            :'POST',

                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal(
                'nomineeModal'
            );

            Toast.success(
                editingNominee
                    ?'Nominee updated successfully.'
                    :'Nominee created successfully.'
            );

            await Promise.all([
                loadNominees(
                    editingNominee
                        ?currentPage
                        :1
                ),
                loadStatistics()
            ]);

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
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.openVerificationModal=async function(id){
    try{
        const response=await api(
            `${API}/${id}`
        );

        const nominee=response.data;

        AdminUI.clearError(
            'verificationError'
        );

        $('verificationNote').value='';

        $('verificationNomineeId').value=
            nominee.id;

        $('verificationSubtitle')
            .textContent=
            `${nominee.name} • ${nominee.relationship}`;

        $('verificationDetails').innerHTML=`
            ${detailBox(
                'Verification',
                nominee.verification_status
                    ?nominee.verification_status.replaceAll('_',' ')
                    :'Unverified'
            )}

            ${detailBox(
                'Identity Type',
                identityLabel(
                    nominee.identity_type
                )
            )}

            ${detailBox(
                'Identity Number',
                nominee.identity_number||'—'
            )}

            ${detailBox(
                'Date of Birth',
                nominee.date_of_birth
                    ?date(nominee.date_of_birth)
                    :'—'
            )}

            ${detailBox(
                'Allocation',
                `${Number(
                    nominee.allocation_percentage||0
                ).toFixed(2)}%`
            )}

            ${detailBox(
                'Priority',
                String(
                    nominee.priority||1
                )
            )}
        `;

        AdminUI.openModal(
            'verificationModal'
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

function detailBox(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-slate-50/60 p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${esc(label)}
            </p>

            <p class="mt-1 break-words text-xs font-semibold capitalize text-slate-700">
                ${esc(value)}
            </p>
        </div>
    `;
}

window.verifyNominee=async function(){
    const id=
        $('verificationNomineeId').value;

    if(!id)return;

    AdminUI.clearError(
        'verificationError'
    );

    const button=
        $('verifyNomineeButton');

    AdminUI.setLoading(
        button,
        'Verifying...'
    );

    try{
        await api(
            `${API}/${id}/verify`,
            {
                method:'POST',

                body:JSON.stringify({
                    verification_note:
                        $('verificationNote')
                            .value.trim()||null
                })
            }
        );

        AdminUI.closeModal(
            'verificationModal'
        );

        Toast.success(
            'Nominee verified successfully.'
        );

        await Promise.all([
            loadNominees(currentPage),
            loadStatistics()
        ]);

    }catch(error){
        AdminUI.showError(
            'verificationError',
            AdminUI.extractError(error)
        );

    }finally{
        AdminUI.resetLoading(
            button
        );
    }
};

window.rejectNominee=function(){
    const id=
        $('verificationNomineeId').value;

    const note=
        $('verificationNote')
            .value.trim();

    if(!note){
        AdminUI.showFieldError(
            'verificationNote',
            'Rejection reason is required.'
        );

        return;
    }

    AdminUI.request(
        `${API}/${id}/reject`,
        {
            method:'POST',

            data:{
                rejection_reason:note
            },

            confirmation:{
                title:'Reject Nominee Verification?',
                message:
                    'Are you sure you want to reject this nominee verification?',
                confirmText:'Reject Nominee',
                type:'danger'
            },

            successMessage:
                'Nominee verification rejected.',

            onSuccess:async()=>{
                AdminUI.closeModal(
                    'verificationModal'
                );

                await Promise.all([
                    loadNominees(
                        currentPage
                    ),
                    loadStatistics()
                ]);
            }
        }
    );
};

window.toggleNominee=function(id){
    const nominee=
        nominees.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!nominee){
        Toast.error(
            'Nominee not found.'
        );

        return;
    }

    const activating=
        !Boolean(
            nominee.is_active
        );

    AdminUI.request(
        `${API}/${id}/active`,
        {
            method:'PATCH',

            data:{
                is_active:
                    activating
            },

            confirmation:{
                title:
                    activating
                        ?'Activate Nominee?'
                        :'Deactivate Nominee?',

                message:
                    activating
                        ?`Activate ${nominee.name}?`
                        :`Deactivate ${nominee.name}? The nominee will no longer be included in active allocation calculations.`,

                confirmText:
                    activating
                        ?'Activate'
                        :'Deactivate',

                type:
                    activating
                        ?'success'
                        :'danger'
            },

            successMessage:
                activating
                    ?'Nominee activated successfully.'
                    :'Nominee deactivated successfully.',

            onSuccess:async()=>
                Promise.all([
                    loadNominees(
                        currentPage
                    ),
                    loadStatistics()
                ])
        }
    );
};

window.deleteNominee=function(id){
    const nominee=
        nominees.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!nominee){
        Toast.error(
            'Nominee not found.'
        );

        return;
    }

    AdminUI.deleteRequest(
        `${API}/${id}`,
        {
            message:
                `Delete nominee ${nominee.name}? This action cannot be undone.`,

            successMessage:
                'Nominee deleted successfully.',

            onSuccess:async()=>
                Promise.all([
                    loadNominees(
                        currentPage
                    ),
                    loadStatistics()
                ])
        }
    );
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('verificationFilter').value='';
    $('activeFilter').value='';

    loadNominees(1);
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

    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadNominees(1)
        )
    );

    $('verificationFilter')
        .addEventListener(
            'change',
            ()=>loadNominees(1)
        );

    $('activeFilter')
        .addEventListener(
            'change',
            ()=>loadNominees(1)
        );

    AdminUI.bindFieldValidation(
        'nomineeForm'
    );

    window.initDatePickers?.();

    await Promise.all([
        loadOptions(),
        loadNominees(),
        loadStatistics()
    ]);
}

init();
</script>
@endpush