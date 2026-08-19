@extends('layouts.admin')

@section('title','Approval Management')
@section('page_title','Approval Management')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-check2-square text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">Approval Management</h1>
                <p class="text-sm text-slate-500">Review, approve and reject pending association requests.</p>
            </div>
        </div>

        <button type="button" onclick="refreshApprovals()" class="inline-flex w-fit items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise text-[11px]"></i>
            Refresh
        </button>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4 ">
            <p class="text-xs font-medium text-slate-500">Total</p>
            <p id="totalApprovals" class="mt-2 text-2xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <p class="text-xs font-medium text-amber-700">Pending</p>
            <p id="pendingApprovals" class="mt-2 text-2xl font-bold text-amber-600">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-xs font-medium text-emerald-700">Approved</p>
            <p id="approvedApprovals" class="mt-2 text-2xl font-bold text-emerald-600">0</p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <p class="text-xs font-medium text-red-700">Rejected</p>
            <p id="rejectedApprovals" class="mt-2 text-2xl font-bold text-red-600">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <p class="text-xs font-medium text-slate-600">Cancelled</p>
            <p id="cancelledApprovals" class="mt-2 text-2xl font-bold text-slate-600">0</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3 ">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-funnel text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Filter Approvals</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search and filter approval history</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">

                <div class="relative w-full sm:w-72">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input id="searchInput" type="text" placeholder="Search approvals..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button type="button" onclick="clearFilters()" class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
        <div class="overflow-x-auto">

            <table class="w-full min-w-[1100px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Request</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Module</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Requested By</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Date</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="approvalsTable">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">Loading approvals...</td>
                    </tr>
                </tbody>
            </table>

        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>

</div>

{{-- Detail Modal --}}
<div id="detailModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-file-earmark-check"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Approval Details</h2>
                    <p id="detailSubtitle" class="mt-1 text-xs text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeDetailModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="approvalDetail" class="p-5"></div>

        <div class="flex justify-end border-t border-slate-200 px-5 py-4">
            <button type="button" onclick="closeDetailModal()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Close
            </button>
        </div>

    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-md rounded-md bg-white">

        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Reject Request</h2>
                <p class="mt-1 text-xs text-slate-500">Provide a reason for rejection.</p>
            </div>

            <button type="button" onclick="closeRejectModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="p-5">
            <textarea id="rejectionReason" rows="4" class="app-input resize-none" placeholder="Enter rejection reason..."></textarea>

            <div id="rejectError" class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
            <button type="button" onclick="closeRejectModal()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">
                Cancel
            </button>

            <button id="rejectButton" type="button" onclick="submitRejection()" class="rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700">
                Reject
            </button>
        </div>

    </div>
</div>

<script>
let approvals=[];
let currentPage=1;
let lastPage=1;
let selectedApproval=null;
let searchTimer=null;

const canApprove=@json(auth()->user()->hasPermission('Approval.approve'));
const canReject=@json(auth()->user()->hasPermission('Approval.reject'));
const canCancel=@json(auth()->user()->hasPermission('Approval.update'));

async function loadApprovals(page=1){
    currentPage=page;

    const tbody=document.getElementById('approvalsTable');

    tbody.innerHTML=`
        <tr>
            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                Loading approvals...
            </td>
        </tr>
    `;

    try{
        const params=new URLSearchParams();
        const search=document.getElementById('searchInput').value.trim();
        const status=document.getElementById('statusFilter').value;

        if(search)params.set('search',search);
        if(status)params.set('status',status);
        params.set('page',page);

        const response=await api(`/api/approvals?${params.toString()}`);
        const paginator=response.data??{};

        approvals=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;

        renderApprovals();
        renderPagination();

    }catch(error){
        console.error(error);

        tbody.innerHTML=`
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-red-500">
                    Failed to load approvals.
                </td>
            </tr>
        `;
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/approvals/statistics');
        const stats=response.data??{};

        document.getElementById('totalApprovals').innerText=stats.total??0;
        document.getElementById('pendingApprovals').innerText=stats.pending??0;
        document.getElementById('approvedApprovals').innerText=stats.approved??0;
        document.getElementById('rejectedApprovals').innerText=stats.rejected??0;
        document.getElementById('cancelledApprovals').innerText=stats.cancelled??0;

    }catch(error){
        console.error(error);
    }
}

function renderApprovals(){
    const tbody=document.getElementById('approvalsTable');

    if(!approvals.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    No approval requests found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=approvals.map(approval=>{
        const pending=approval.status==='pending';

        const approveButton=canApprove&&pending?`
            <button onclick="approveRequest(${approval.id})" class="rounded-md bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100">
                Approve
            </button>
        `:'';

        const rejectButton=canReject&&pending?`
            <button onclick="openRejectModal(${approval.id})" class="rounded-md bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                Reject
            </button>
        `:'';

        const cancelButton=canCancel&&pending?`
            <button onclick="cancelRequest(${approval.id})" class="rounded-md bg-slate-100 px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 hover:bg-slate-200">
                Cancel
            </button>
        `:'';

        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50">

                <td class="px-5 py-4">
                    <div class="font-semibold text-slate-800">
                        ${escapeHtml(approval.action??'Request')}
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        #${approval.id}
                    </div>
                </td>

                <td class="px-5 py-4">
                    <span class="rounded-md bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700">
                        ${escapeHtml(approval.module??'General')}
                    </span>
                </td>

                <td class="px-5 py-4">
                    <div class="text-sm font-medium text-slate-700">
                        ${escapeHtml(approval.requester?.name??'System')}
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        ${escapeHtml(approval.requester?.email??'')}
                    </div>
                </td>

                <td class="px-5 py-4 text-xs text-slate-500">
                    ${formatDateTime(approval.created_at)}
                </td>

                <td class="px-5 py-4">
                    ${statusBadge(approval.status)}
                </td>

                <td class="px-5 py-4">
                    <div class="flex flex-wrap justify-end gap-1.5">

                        <button onclick="viewApproval(${approval.id})" class="rounded-md bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">
                            View
                        </button>

                        ${approveButton}
                        ${rejectButton}
                        ${cancelButton}

                    </div>
                </td>

            </tr>
        `;
    }).join('');
}

async function viewApproval(id){
    try{
        const response=await api(`/api/approvals/${id}`);
        selectedApproval=response.data;

        document.getElementById('detailSubtitle').innerText=
            `${selectedApproval.module??'General'} • #${selectedApproval.id}`;

        document.getElementById('approvalDetail').innerHTML=buildApprovalDetail(selectedApproval);

        const modal=document.getElementById('detailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

    }catch(error){
        alert(extractError(error));
    }
}

function buildApprovalDetail(approval){
    return `
        <div class="space-y-4">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                ${detailItem('Module',approval.module)}
                ${detailItem('Action',approval.action)}
                ${detailItem('Status',statusBadge(approval.status),true)}
                ${detailItem('Requested By',approval.requester?.name??'System')}
                ${detailItem('Requested At',formatDateTime(approval.created_at))}
                ${detailItem('Processed At',formatDateTime(
                    approval.approved_at||
                    approval.rejected_at||
                    approval.cancelled_at
                ))}

            </div>

            ${approval.request_note?detailBlock('Request Note',approval.request_note):''}
            ${approval.rejection_reason?detailBlock('Rejection Reason',approval.rejection_reason,'red'):''}
            ${approval.cancellation_reason?detailBlock('Cancellation Reason',approval.cancellation_reason):''}

        </div>
    `;
}

function detailItem(label,value,raw=false){
    return `
        <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
            <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${escapeHtml(label)}
            </div>

            <div class="mt-1 text-sm font-medium text-slate-700">
                ${raw?(value??'N/A'):escapeHtml(value??'N/A')}
            </div>
        </div>
    `;
}

function detailBlock(label,value,color='slate'){
    return `
        <div class="rounded-md border border-${color}-200 bg-${color}-50 p-4">
            <p class="text-xs font-semibold text-${color}-600">
                ${escapeHtml(label)}
            </p>

            <p class="mt-2 whitespace-pre-line text-sm text-slate-700">
                ${escapeHtml(value)}
            </p>
        </div>
    `;
}

function closeDetailModal(){
    const modal=document.getElementById('detailModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    selectedApproval=null;
}

async function approveRequest(id){
    if(!confirm('Approve this request?'))return;

    try{
        await api(`/api/approvals/${id}/approve`,{
            method:'POST',
            body:JSON.stringify({})
        });

        await refreshApprovals();

    }catch(error){
        alert(extractError(error));
    }
}

function openRejectModal(id){
    selectedApproval=approvals.find(item=>Number(item.id)===Number(id));

    document.getElementById('rejectionReason').value='';
    document.getElementById('rejectError').classList.add('hidden');

    const modal=document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal(){
    const modal=document.getElementById('rejectModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    selectedApproval=null;
}

async function submitRejection(){
    if(!selectedApproval)return;

    const reason=document.getElementById('rejectionReason').value.trim();

    if(!reason){
        showRejectError('Rejection reason is required.');
        return;
    }

    const button=document.getElementById('rejectButton');
    button.disabled=true;
    button.innerText='Rejecting...';

    try{
        await api(`/api/approvals/${selectedApproval.id}/reject`,{
            method:'POST',
            body:JSON.stringify({reason})
        });

        closeRejectModal();
        await refreshApprovals();

    }catch(error){
        showRejectError(extractError(error));

    }finally{
        button.disabled=false;
        button.innerText='Reject';
    }
}

async function cancelRequest(id){
    const reason=prompt('Cancellation reason (optional):');

    if(reason===null)return;

    try{
        await api(`/api/approvals/${id}/cancel`,{
            method:'POST',
            body:JSON.stringify({
                reason:reason.trim()||null
            })
        });

        await refreshApprovals();

    }catch(error){
        alert(extractError(error));
    }
}

async function refreshApprovals(){
    await Promise.all([
        loadApprovals(currentPage),
        loadStatistics()
    ]);
}

function renderPagination(){
    const container=document.getElementById('paginationContainer');

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    container.innerHTML=`
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-500">
                Page ${currentPage} of ${lastPage}
            </span>

            <div class="flex gap-2">

                <button
                    ${currentPage<=1?'disabled':''}
                    onclick="loadApprovals(${currentPage-1})"
                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 disabled:opacity-40"
                >
                    Previous
                </button>

                <button
                    ${currentPage>=lastPage?'disabled':''}
                    onclick="loadApprovals(${currentPage+1})"
                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 disabled:opacity-40"
                >
                    Next
                </button>

            </div>
        </div>
    `;
}

function statusBadge(status){
    const styles={
        pending:'bg-amber-50 text-amber-700',
        approved:'bg-emerald-50 text-emerald-700',
        rejected:'bg-red-50 text-red-700',
        cancelled:'bg-slate-100 text-slate-600'
    };

    const label=status
        ?status.charAt(0).toUpperCase()+status.slice(1)
        :'Unknown';

    return `
        <span class="rounded-full px-2.5 py-1 text-xs font-semibold ${styles[status]??'bg-slate-100 text-slate-600'}">
            ${escapeHtml(label)}
        </span>
    `;
}

function showRejectError(message){
    const box=document.getElementById('rejectError');
    box.innerText=message;
    box.classList.remove('hidden');
}

function clearFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('statusFilter').value='';
    loadApprovals(1);
}

function formatDateTime(value){
    if(!value)return 'N/A';

    return new Date(value).toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function extractError(error){
    if(error.data?.errors){
        const errors=Object.values(error.data.errors).flat();
        if(errors.length)return errors.join(' ');
    }

    return error.data?.message||error.message||'Something went wrong.';
}

function escapeHtml(value){
    if(value===null||value===undefined)return '';

    return String(value)
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");
}

document.getElementById('searchInput').addEventListener('input',function(){
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>loadApprovals(1),350);
});

document.getElementById('statusFilter').addEventListener('change',()=>{
    loadApprovals(1);
});

document.addEventListener('DOMContentLoaded',function(){
    Promise.all([
        loadApprovals(),
        loadStatistics()
    ]);
});
</script>

@endsection