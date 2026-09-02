@extends('layouts.admin')

@section('title','Approval Management')
@section('page_title','Approval Management')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
<div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
    <div class="flex items-start gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
            <i class="bi bi-check2-square text-base"></i>
        </div>

        <div>
            <h1 class="text-base font-bold tracking-tight text-slate-800">
                Approval Management
            </h1>

            <p class="text-xs text-slate-500">
                Review, approve and reject pending association requests.
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        @if(auth()->user()->hasPermission('Approval.update'))
            <a
                href="{{ route('admin.approval-workflows') }}"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                <i class="bi bi-diagram-3 text-[11px]"></i>
                Workflow Settings
            </a>
        @endif

        <button
            type="button"
            onclick="refreshApprovals()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
        >
            <i class="bi bi-arrow-clockwise text-[11px]"></i>
            Refresh
        </button>
    </div>
</div>


    {{-- =========================================================
    STATISTICS
    ========================================================== --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-sm font-medium text-slate-500">
                Total
            </p>

            <p
                id="totalApprovals"
                class="mt-2 text-2xl font-bold text-slate-800"
            >
                0
            </p>
        </div>


        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <p class="text-sm font-medium text-amber-700">
                Pending
            </p>

            <p
                id="pendingApprovals"
                class="mt-2 text-2xl font-bold text-amber-600"
            >
                0
            </p>
        </div>


        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-sm font-medium text-emerald-700">
                Approved
            </p>

            <p
                id="approvedApprovals"
                class="mt-2 text-2xl font-bold text-emerald-600"
            >
                0
            </p>
        </div>


        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <p class="text-sm font-medium text-red-700">
                Rejected
            </p>

            <p
                id="rejectedApprovals"
                class="mt-2 text-2xl font-bold text-red-600"
            >
                0
            </p>
        </div>


        <div class="col-span-2 rounded-md border border-slate-200 bg-slate-50 p-4 lg:col-span-1">
            <p class="text-sm font-medium text-slate-600">
                Cancelled
            </p>

            <p
                id="cancelledApprovals"
                class="mt-2 text-2xl font-bold text-slate-600"
            >
                0
            </p>
        </div>

    </div>


    {{-- =========================================================
    FILTERS
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-funnel text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Filter Approvals
                    </p>

                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search and filter approval history
                    </p>
                </div>
            </div>


            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">

                <div class="relative w-full sm:min-w-[240px] lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search approvals..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none"
                    >
                </div>


                <select
                    id="statusFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0"
                >
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                </select>


                <label
                    class="flex h-9 shrink-0 cursor-pointer items-center gap-2 border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 lg:border-l-0"
                    title="যাদের turn এখনো আসেনি, সেই approval request গুলো লুকিয়ে রাখবে — শুধু যার approve করার পালা এখন, তার কাছেই দেখাবে।"
                >
                    <input
                        id="myPendingToggle"
                        type="checkbox"
                        checked
                        class="h-3.5 w-3.5 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                    >
                    My Turn Only
                </label>


                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0"
                >
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
    TABLE
    ========================================================== --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[13%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Request
                        </th>

                        <th class="w-[11%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Module
                        </th>

                        <th class="w-[17%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Subject
                        </th>

                        <th class="w-[16%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Requested By
                        </th>

                        <th class="w-[14%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Date
                        </th>

                        <th class="w-[10%] px-3 py-3 text-left text-sm font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="w-[19%] px-3 py-3 text-right text-sm font-semibold text-slate-600">
                            Action
                        </th>
                    </tr>
                </thead>


                <tbody id="approvalsTable">
                    <tr>
                        <td
                            colspan="7"
                            class="px-6 py-12 text-center text-slate-500"
                        >
                            Loading approvals...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>


        <div
            id="paginationContainer"
            class="border-t border-slate-200 px-4 py-3"
        ></div>

    </div>

</div>


{{-- =============================================================
DETAIL MODAL
============================================================= --}}
<div
    id="detailModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-file-earmark-check"></i>
                </div>

                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-800">
                        Approval Details
                    </h2>

                    <p
                        id="detailSubtitle"
                        class="mt-1 truncate text-sm text-slate-500"
                    ></p>
                </div>

            </div>


            <button
                type="button"
                onclick="closeDetailModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div
            id="approvalDetail"
            class="min-h-0 flex-1 overflow-y-auto p-5"
        ></div>


        <div class="flex shrink-0 justify-end border-t border-slate-200 px-5 py-4">
            <button
                type="button"
                onclick="closeDetailModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                Close
            </button>
        </div>

    </div>
</div>


{{-- =============================================================
REJECT MODAL
============================================================= --}}
<div
    id="rejectModal"
    class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3"
>
    <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">

            <div>
                <h2 class="text-base font-semibold text-slate-800">
                    Reject Request
                </h2>

                <p class="text-xs text-slate-500">
                    Provide a reason for rejection.
                </p>
            </div>


            <button
                type="button"
                onclick="closeRejectModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div class="p-5">

            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                Rejection Reason *
            </label>

            <textarea
                id="rejectionReason"
                rows="4"
                class="app-input resize-none"
                placeholder="Enter rejection reason..."
            ></textarea>


            <div
                id="rejectError"
                class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"
            ></div>

        </div>


        <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">

            <button
                type="button"
                onclick="closeRejectModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600"
            >
                Cancel
            </button>

            <button
                id="rejectButton"
                type="button"
                onclick="submitRejection()"
                class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
            >
                Reject
            </button>

        </div>

    </div>
</div>


{{-- =============================================================
CANCEL MODAL
============================================================= --}}
<div
    id="cancelModal"
    class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3"
>
    <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">

            <div>
                <h2 class="text-base font-semibold text-slate-800">
                    Cancel Request
                </h2>

                <p class="text-xs text-slate-500">
                    You may provide an optional cancellation reason.
                </p>
            </div>


            <button
                type="button"
                onclick="closeCancelModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <div class="p-5">

            <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                Cancellation Reason
            </label>

            <textarea
                id="cancellationReason"
                rows="4"
                maxlength="2000"
                class="app-input resize-none"
                placeholder="Enter cancellation reason (optional)..."
            ></textarea>


            <div
                id="cancelError"
                class="mt-3 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"
            ></div>

        </div>


        <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">

            <button
                type="button"
                onclick="closeCancelModal()"
                class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600"
            >
                Back
            </button>

            <button
                id="cancelButton"
                type="button"
                onclick="submitCancellation()"
                class="cursor-pointer rounded-md bg-slate-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800"
            >
                Cancel Request
            </button>

        </div>

    </div>
</div>


<script>
let approvals=[];
let currentPage=1;
let lastPage=1;
let totalApprovalsCount=0;
let selectedApproval=null;

const canApprove=@json(
    auth()->user()->hasPermission('Approval.approve')
);

const canReject=@json(
    auth()->user()->hasPermission('Approval.reject')
);

const canCancel=@json(
    auth()->user()->hasPermission('Approval.update')
);

const currentUserId=@json(
    auth()->user()->id
);

let myPendingOnly=true;

const el={
    table:document.getElementById('approvalsTable'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    myPendingToggle:document.getElementById('myPendingToggle'),
    pagination:document.getElementById('paginationContainer')
};


/*
|--------------------------------------------------------------------------
| Turn Helpers
|--------------------------------------------------------------------------
|
| An approval moves through its steps one at a time (current_step).
| A step's approver should only see/act on the request once every
| earlier step has been approved and it becomes the current step.
*/

function currentStepOf(approval){
    if(!Array.isArray(approval.steps)){
        return null;
    }

    return approval.steps.find(
        step=>
            Number(step.step_no)===
            Number(approval.current_step)
    )??null;
}

function isMyTurn(approval){
    if(approval.status!=='pending'){
        return false;
    }

    const step=currentStepOf(approval);

    if(!step){
        return false;
    }

    return(
        step.status==='pending'&&
        Number(step.approver_user_id)===
        Number(currentUserId)
    );
}


/*
|--------------------------------------------------------------------------
| Load Approvals
|--------------------------------------------------------------------------
*/

async function loadApprovals(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading approvals...',
            7
        );

    const query=
        AdminUI.query({
            search:el.search.value.trim(),
            status:el.statusFilter.value,
            my_pending:myPendingOnly?'1':'',
            page
        });

    try{
        const response=
            await api(
                `/api/approvals?${query}`
            );

        const paginator=
            response.data??{};

        approvals=
            Array.isArray(paginator.data)
                ?paginator.data
                :[];

        currentPage=
            Number(
                paginator.current_page??1
            );

        lastPage=
            Number(
                paginator.last_page??1
            );

        totalApprovalsCount=
            Number(
                paginator.total??0
            );

        renderApprovals();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total:totalApprovalsCount,
            onPageChange:loadApprovals
        });

    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                7
            );

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadApprovals
        });
    }
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

async function loadStatistics(){
    try{
        const response=
            await api(
                '/api/approvals/statistics'
            );

        const stats=
            response.data??{};

        document.getElementById(
            'totalApprovals'
        ).innerText=
            stats.total??0;

        document.getElementById(
            'pendingApprovals'
        ).innerText=
            stats.pending??0;

        document.getElementById(
            'approvedApprovals'
        ).innerText=
            stats.approved??0;

        document.getElementById(
            'rejectedApprovals'
        ).innerText=
            stats.rejected??0;

        document.getElementById(
            'cancelledApprovals'
        ).innerText=
            stats.cancelled??0;

    }catch(error){
        console.error(
            'Approval statistics load failed:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Render Approvals
|--------------------------------------------------------------------------
*/

function renderApprovals(){
    if(!approvals.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No approval requests found.',
                7
            );

        return;
    }


    el.table.innerHTML=
        approvals.map(approval=>{

            const pending=
                approval.status==='pending';


            return `
                <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50">

                    <td class="min-w-0 overflow-hidden px-3 py-4">

                        <p
                            class="truncate text-sm font-semibold capitalize text-slate-800"
                            title="${AdminUI.escapeHtml(approval.action??'Request')}"
                        >
                            ${AdminUI.escapeHtml(
                                approval.action??'Request'
                            )}
                        </p>

                        <p class="mt-1 text-[10px] text-slate-400">
                            #${approval.id}
                        </p>

                    </td>


                    <td class="min-w-0 overflow-hidden px-3 py-4">

                        <span
                            class="block truncate rounded-md bg-indigo-50 px-2 py-1 text-center text-[10px] font-semibold text-indigo-700"
                            title="${AdminUI.escapeHtml(approval.module??'General')}"
                        >
                            ${AdminUI.escapeHtml(
                                approval.module??'General'
                            )}
                        </span>

                    </td>


                    <td class="min-w-0 overflow-hidden px-3 py-4">

                        <p
                            class="truncate text-sm font-semibold text-slate-700"
                            title="${AdminUI.escapeHtml(approvableLabel(approval))}"
                        >
                            ${AdminUI.escapeHtml(
                                approvableLabel(approval)
                            )}
                        </p>

                    </td>


                    <td class="min-w-0 overflow-hidden px-3 py-4">

                        <p
                            class="truncate text-sm font-semibold text-slate-700"
                            title="${AdminUI.escapeHtml(approval.requester?.name??'System')}"
                        >
                            ${AdminUI.escapeHtml(
                                approval.requester?.name??
                                'System'
                            )}
                        </p>

                        ${
                            approval.requester?.email
                                ?`
                                    <p
                                        class="mt-1 truncate text-[10px] text-slate-400"
                                        title="${AdminUI.escapeHtml(approval.requester.email)}"
                                    >
                                        ${AdminUI.escapeHtml(
                                            approval.requester.email
                                        )}
                                    </p>
                                `
                                :''
                        }

                    </td>


                    <td class="min-w-0 overflow-hidden px-3 py-4">

                        <p
                            class="truncate whitespace-nowrap text-[11px] text-slate-500"
                            title="${AdminUI.escapeHtml(AdminUI.formatDate(approval.created_at,true))}"
                        >
                            ${AdminUI.formatDate(
                                approval.created_at,
                                true
                            )}
                        </p>

                    </td>


                    <td class="overflow-hidden px-3 py-4">
                        ${statusBadge(
                            approval.status
                        )}

                        ${stepHint(approval)}
                    </td>


                    <td class="px-3 py-4">

                        <div class="flex items-center justify-end gap-1">

                            <button
                                type="button"
                                onclick="viewApproval(${approval.id})"
                                title="View Details"
                                class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                            >
                                <i class="bi bi-eye text-sm"></i>
                            </button>


                            ${
                                canApprove&&pending&&isMyTurn(approval)
                                    ?`
                                        <button
                                            type="button"
                                            onclick="approveRequest(${approval.id},this)"
                                            title="Approve"
                                            class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                        >
                                            <i class="bi bi-check-lg text-sm"></i>
                                        </button>
                                    `
                                    :''
                            }


                            ${
                                canReject&&pending&&isMyTurn(approval)
                                    ?`
                                        <button
                                            type="button"
                                            onclick="openRejectModal(${approval.id})"
                                            title="Reject"
                                            class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                        >
                                            <i class="bi bi-x-lg text-sm"></i>
                                        </button>
                                    `
                                    :''
                            }


                            ${
                                canCancel&&pending
                                    ?`
                                        <button
                                            type="button"
                                            onclick="openCancelModal(${approval.id})"
                                            title="Cancel Request"
                                            class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-slate-100 text-slate-600 transition hover:bg-slate-200"
                                        >
                                            <i class="bi bi-slash-circle text-sm"></i>
                                        </button>
                                    `
                                    :''
                            }

                        </div>

                    </td>

                </tr>
            `;
        }).join('');
}


/*
|--------------------------------------------------------------------------
| Approvable Subject Label
|--------------------------------------------------------------------------
*/

function approvableLabel(approval){
    const approvable=
        approval.approvable??null;

    if(!approvable){
        return '—';
    }

    return(
        approvable.user?.name??
        approvable.member?.user?.name??
        approvable.name??
        approvable.title??
        '—'
    );
}


/*
|--------------------------------------------------------------------------
| Status Badge
|--------------------------------------------------------------------------
*/

function statusBadge(status){
    const styles={
        pending:
            'bg-amber-50 text-amber-700',

        approved:
            'bg-emerald-50 text-emerald-700',

        rejected:
            'bg-red-50 text-red-700',

        cancelled:
            'bg-slate-100 text-slate-600'
    };


    return `
        <span
            class="inline-flex max-w-full rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${
                styles[status]??
                'bg-slate-100 text-slate-600'
            }"
        >
            <span class="truncate">
                ${AdminUI.escapeHtml(
                    status??
                    'unknown'
                )}
            </span>
        </span>
    `;
}


/*
|--------------------------------------------------------------------------
| Step Hint (which approver's turn it currently is)
|--------------------------------------------------------------------------
*/

function stepHint(approval){
    if(approval.status!=='pending'){
        return '';
    }

    const step=currentStepOf(approval);

    if(!step){
        return '';
    }

    const label=
        isMyTurn(approval)
            ?'Your turn'
            :(
                step.approver?.name??
                'Awaiting approver'
            );

    return `
        <p
            class="mt-1 truncate text-[10px] ${
                isMyTurn(approval)
                    ?'font-semibold text-emerald-600'
                    :'text-slate-400'
            }"
            title="Step ${step.step_no} of ${approval.total_steps??'?'}"
        >
            Step ${step.step_no}/${approval.total_steps??'?'} · ${AdminUI.escapeHtml(label)}
        </p>
    `;
}


/*
|--------------------------------------------------------------------------
| View Approval
|--------------------------------------------------------------------------
*/

async function viewApproval(id){
    try{
        const response=
            await api(
                `/api/approvals/${id}`
            );

        selectedApproval=
            response.data??response;


        document.getElementById(
            'detailSubtitle'
        ).innerText=
            `${
                selectedApproval.module??
                'General'
            } • #${
                selectedApproval.id
            }`;


        document.getElementById(
            'approvalDetail'
        ).innerHTML=
            buildApprovalDetail(
                selectedApproval
            );


        AdminUI.openModal(
            'detailModal'
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}


/*
|--------------------------------------------------------------------------
| Approval Detail
|--------------------------------------------------------------------------
*/

function buildApprovalDetail(approval){
    return `
        <div class="space-y-4">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                ${detailItem(
                    'Module',
                    approval.module??
                    'General'
                )}

                ${detailItem(
                    'Action',
                    approval.action??
                    'Request'
                )}

                ${detailItem(
                    'Subject',
                    approvableLabel(approval)
                )}

                ${detailItem(
                    'Status',
                    statusBadge(
                        approval.status
                    ),
                    true
                )}

                ${detailItem(
                    'Requested By',
                    approval.requester?.name??
                    'System'
                )}

                ${detailItem(
                    'Requested At',
                    AdminUI.formatDate(
                        approval.created_at,
                        true
                    )
                )}

                ${detailItem(
                    'Processed At',
                    formatProcessedDate(
                        approval
                    )
                )}

            </div>


            ${
                approval.request_note
                    ?detailBlock(
                        'Request Note',
                        approval.request_note
                    )
                    :''
            }


            ${
                approval.rejection_reason
                    ?detailBlock(
                        'Rejection Reason',
                        approval.rejection_reason,
                        'red'
                    )
                    :''
            }


            ${
                approval.cancellation_reason
                    ?detailBlock(
                        'Cancellation Reason',
                        approval.cancellation_reason,
                        'slate'
                    )
                    :''
            }


            ${stepsBlock(approval)}

        </div>
    `;
}


function stepsBlock(approval){
    if(!Array.isArray(approval.steps)||!approval.steps.length){
        return '';
    }

    const badges={
        approved:'bg-emerald-50 text-emerald-700',
        rejected:'bg-red-50 text-red-700',
        pending:'bg-amber-50 text-amber-700'
    };

    const rows=approval.steps
        .slice()
        .sort((a,b)=>a.step_no-b.step_no)
        .map(step=>{
            const isCurrent=
                approval.status==='pending'&&
                Number(step.step_no)===
                Number(approval.current_step);

            return `
                <div class="flex items-center justify-between gap-3 rounded-md border ${
                    isCurrent
                        ?'border-indigo-200 bg-indigo-50/60'
                        :'border-slate-200 bg-white'
                } px-3 py-2">

                    <div class="min-w-0">
                        <p class="truncate text-xs font-semibold text-slate-700">
                            Step ${step.step_no}: ${AdminUI.escapeHtml(
                                step.approver?.name??'Unassigned'
                            )}
                        </p>

                        ${
                            step.acted_at
                                ?`
                                    <p class="mt-0.5 text-[10px] text-slate-400">
                                        ${AdminUI.formatDate(step.acted_at,true)}
                                    </p>
                                `
                                :''
                        }
                    </div>

                    <span class="shrink-0 rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${
                        badges[step.status]??'bg-slate-100 text-slate-600'
                    }">
                        ${AdminUI.escapeHtml(step.status)}
                    </span>

                </div>
            `;
        })
        .join('');

    return `
        <div>
            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                Approval Steps
            </p>

            <div class="space-y-2">
                ${rows}
            </div>
        </div>
    `;
}


function formatProcessedDate(approval){
    const date=
        approval.approved_at||
        approval.rejected_at||
        approval.cancelled_at;

    return date
        ?AdminUI.formatDate(
            date,
            true
        )
        :'N/A';
}


function detailItem(
    label,
    value,
    raw=false
){
    return `
        <div class="min-w-0 rounded-md border border-slate-200 bg-slate-50 p-2.5">

            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <div class="mt-1 break-words text-sm font-medium text-slate-700">
                ${
                    raw
                        ?value??'N/A'
                        :AdminUI.escapeHtml(
                            value??'N/A'
                        )
                }
            </div>

        </div>
    `;
}


function detailBlock(
    label,
    value,
    color='slate'
){
    const styles={
        red:
            'border-red-200 bg-red-50',

        slate:
            'border-slate-200 bg-slate-50'
    };

    const titleStyles={
        red:'text-red-600',
        slate:'text-slate-600'
    };

    return `
        <div class="rounded-md border ${
            styles[color]??
            styles.slate
        } p-3">

            <p class="text-xs font-semibold ${
                titleStyles[color]??
                titleStyles.slate
            }">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1.5 whitespace-pre-line break-words text-sm text-slate-700">
                ${AdminUI.escapeHtml(value)}
            </p>

        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Detail Modal
|--------------------------------------------------------------------------
*/

function closeDetailModal(){
    AdminUI.closeModal(
        'detailModal'
    );

    selectedApproval=null;
}


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

async function approveRequest(id,button){
    const confirmed=
        await AdminUI.confirm({
            title:'Approve Request?',
            message:'Approve this request?',
            confirmText:'Approve',
            type:'success'
        });

    if(!confirmed){
        return;
    }

    if(button){
        AdminUI.setLoading(
            button,
            ''
        );
    }

    try{
        await api(
            `/api/approvals/${id}/approve`,
            {
                method:'POST',

                body:JSON.stringify({})
            }
        );

        Toast.success(
            'Approval request approved successfully.'
        );

        await refreshApprovals();

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );

    }finally{
        if(button){
            AdminUI.resetLoading(
                button
            );
        }
    }
}


/*
|--------------------------------------------------------------------------
| Reject Modal
|--------------------------------------------------------------------------
*/

function openRejectModal(id){
    selectedApproval=
        approvals.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!selectedApproval){
        Toast.error(
            'Approval request not found.'
        );

        return;
    }


    document.getElementById(
        'rejectionReason'
    ).value='';


    AdminUI.clearError(
        'rejectError'
    );


    AdminUI.openModal(
        'rejectModal'
    );
}


function closeRejectModal(){
    AdminUI.closeModal(
        'rejectModal'
    );

    selectedApproval=null;
}


/*
|--------------------------------------------------------------------------
| Submit Rejection
|--------------------------------------------------------------------------
*/

async function submitRejection(){
    if(!selectedApproval){
        return;
    }


    const reason=
        document.getElementById(
            'rejectionReason'
        ).value.trim();


    AdminUI.clearError(
        'rejectError'
    );


    if(!reason){
        AdminUI.showError(
            'rejectError',
            'Rejection reason is required.'
        );

        return;
    }


    const button=
        document.getElementById(
            'rejectButton'
        );


    AdminUI.setLoading(
        button,
        'Rejecting...'
    );


    try{
        await api(
            `/api/approvals/${selectedApproval.id}/reject`,
            {
                method:'POST',

                body:JSON.stringify({
                    reason
                })
            }
        );


        closeRejectModal();


        Toast.success(
            'Approval request rejected successfully.'
        );


        await refreshApprovals();

    }catch(error){
        AdminUI.showError(
            'rejectError',
            AdminUI.extractError(error)
        );

    }finally{
        AdminUI.resetLoading(
            button
        );
    }
}


/*
|--------------------------------------------------------------------------
| Cancel Modal
|--------------------------------------------------------------------------
*/

function openCancelModal(id){
    selectedApproval=
        approvals.find(
            item=>
                Number(item.id)===
                Number(id)
        );


    if(!selectedApproval){
        Toast.error(
            'Approval request not found.'
        );

        return;
    }


    document.getElementById(
        'cancellationReason'
    ).value='';


    AdminUI.clearError(
        'cancelError'
    );


    AdminUI.openModal(
        'cancelModal'
    );
}


function closeCancelModal(){
    AdminUI.closeModal(
        'cancelModal'
    );

    selectedApproval=null;
}


/*
|--------------------------------------------------------------------------
| Submit Cancellation
|--------------------------------------------------------------------------
*/

async function submitCancellation(){
    if(!selectedApproval){
        return;
    }


    const reason=
        document.getElementById(
            'cancellationReason'
        ).value.trim();


    const button=
        document.getElementById(
            'cancelButton'
        );


    AdminUI.clearError(
        'cancelError'
    );


    AdminUI.setLoading(
        button,
        'Cancelling...'
    );


    try{
        await api(
            `/api/approvals/${selectedApproval.id}/cancel`,
            {
                method:'POST',

                body:JSON.stringify({
                    reason:
                        reason||null
                })
            }
        );


        closeCancelModal();


        Toast.success(
            'Approval request cancelled successfully.'
        );


        await refreshApprovals();

    }catch(error){
        AdminUI.showError(
            'cancelError',
            AdminUI.extractError(error)
        );

    }finally{
        AdminUI.resetLoading(
            button
        );
    }
}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

async function refreshApprovals(){
    await Promise.all([
        loadApprovals(
            currentPage
        ),
        loadStatistics()
    ]);
}


/*
|--------------------------------------------------------------------------
| Refresh Button
|--------------------------------------------------------------------------
*/

async function refreshApprovalsWithToast(){
    await refreshApprovals();

    Toast.success(
        'Approval data refreshed.'
    );
}


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

function clearFilters(){
    el.search.value='';
    el.statusFilter.value='';

    myPendingOnly=true;
    el.myPendingToggle.checked=true;

    loadApprovals(1);
}


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initApprovalPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initApprovalPage,
            50
        );

        return;
    }


    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadApprovals(1)
        )
    );


    el.statusFilter.addEventListener(
        'change',
        ()=>{
            const value=el.statusFilter.value;

            if(value&&value!=='pending'&&myPendingOnly){
                myPendingOnly=false;
                el.myPendingToggle.checked=false;
            }

            loadApprovals(1);
        }
    );


    el.myPendingToggle.addEventListener(
        'change',
        ()=>{
            myPendingOnly=
                el.myPendingToggle.checked;

            loadApprovals(1);
        }
    );


    await Promise.all([
        loadApprovals(),
        loadStatistics()
    ]);
}


if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initApprovalPage
    );
}else{
    initApprovalPage();
}
</script>

@endsection