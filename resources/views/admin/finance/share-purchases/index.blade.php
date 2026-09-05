@extends('layouts.admin')

@section('title','Share Purchases')
@section('page_title','Share Purchases')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-pie-chart"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Share Purchases</h1>
                <p class="text-xs text-slate-500">Review and verify member share purchase requests.</p>
            </div>
        </div>

        <button
            type="button"
            onclick="loadSharePurchases()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total</p>
            <p id="totalCount" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-amber-600">Pending</p>
            <p id="pendingCount" class="mt-1 text-xl font-bold text-amber-600">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-emerald-600">Active</p>
            <p id="activeCount" class="mt-1 text-xl font-bold text-emerald-600">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-red-600">Rejected</p>
            <p id="rejectedCount" class="mt-1 text-xl font-bold text-red-600">0</p>
        </div>
    </div>

    {{-- Search / Filter --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Share Purchases</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by share no, member or reference.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[minmax(220px,280px)_180px_auto] lg:gap-0">
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search member, share no, reference..."
                        class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3 text-xs outline-none focus:border-indigo-400 lg:rounded-r-none">
                </div>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 bg-white px-3 text-xs outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="rejected">Rejected</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="transferred">Transferred</option>
                    <option value="retired">Retired</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1050px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Share</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Submitted</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="shareTable">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            Loading share purchases...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Share Details Modal --}}
<div id="shareDetailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Share Purchase Details</h2>
                    <p class="text-xs text-slate-500">
                        Review payment and member details.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeShareDetailsModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="shareDetailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div
            id="shareDetailsActions"
            class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
        </div>
    </div>
</div>

{{-- Reject Share Modal --}}
<div id="shareRejectModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-x-circle"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Reject Share Purchase</h2>
                    <p id="shareRejectDescription" class="text-xs text-slate-500"></p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeShareRejectModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="shareRejectForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="shareRejectId" type="hidden">

            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="shareRejectReason"
                        rows="4"
                        maxlength="1000"
                        class="app-input resize-none"
                        placeholder="Enter rejection reason..."
                        required></textarea>
                </div>

                <div
                    id="shareRejectError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeShareRejectModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back
                </button>

                <button
                    id="shareRejectButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    Reject Share
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>
@endsection

@push('scripts')
<script>
let shares=[];
let currentPage=1;
let lastPage=1;
let total=0;
let selectedShare=null;

const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('shareTable'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter')
};

async function loadSharePurchases(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading share purchases...',
            8
        );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value,
        page
    });

    try{
        const response=await api(
            `/api/member-shares?${query}`
        );

        const paginator=
            response.data??{};

        shares=
            paginator.data??[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??0
        );

        renderShareTable();
        updateSummary(
            response.summary??{}
        );

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadSharePurchases
        });
    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                8
            );
    }
}

function renderShareTable(){
    if(!shares.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No share purchases found.',
                8
            );

        return;
    }

    el.table.innerHTML=shares.map(share=>{
        const member=share.member??{};
        const user=member.user??{};

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="px-4 py-3 font-mono text-sm font-semibold text-indigo-600">
                    ${AdminUI.escapeHtml(share.share_no??'—')}
                </td>

                <td class="px-4 py-3">
                    <p class="text-xs font-medium text-slate-700">
                        ${AdminUI.escapeHtml(user.name??'—')}
                    </p>

                    <p class="text-[10px] text-slate-400">
                        ${AdminUI.escapeHtml(member.member_code??'')}
                    </p>
                </td>

                <td class="px-4 py-3 text-right text-xs font-bold text-slate-800">
                    ${money(share.purchase_amount)}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${AdminUI.titleCase(share.payment_method)}
                </td>

                <td class="max-w-[170px] px-4 py-3">
                    <p class="truncate text-sm text-slate-500">
                        ${AdminUI.escapeHtml(share.transaction_reference??'—')}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-slate-500">
                    ${AdminUI.formatDate(share.created_at,true)}
                </td>

                <td class="px-4 py-3 text-center">
                    ${AdminUI.statusBadge(share.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <button
                            type="button"
                            onclick="viewShare(${Number(share.id)})"
                            class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-50 text-slate-500 hover:bg-slate-100"
                            title="View">
                            <i class="bi bi-eye text-sm"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateSummary(summary){
    document.getElementById('totalCount').textContent=
        AdminUI.formatNumber(summary.total??total);

    document.getElementById('pendingCount').textContent=
        AdminUI.formatNumber(summary.pending??0);

    document.getElementById('activeCount').textContent=
        AdminUI.formatNumber(summary.active??0);

    document.getElementById('rejectedCount').textContent=
        AdminUI.formatNumber(summary.rejected??0);
}

window.viewShare=async function(id){
    AdminUI.openModal(
        'shareDetailsModal'
    );

    document.getElementById(
        'shareDetailsActions'
    ).innerHTML='';

    const body=
        document.getElementById(
            'shareDetailsBody'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading details...'
        );

    try{
        const response=await api(
            `/api/member-shares/${id}`
        );

        selectedShare=
            response.data??{};

        renderShareDetails();
    }catch(error){
        body.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

function renderShareDetails(){
    const share=selectedShare??{};
    const member=share.member??{};
    const user=member.user??{};
    const entries=
        share.finance_transaction
            ?.entries??[];

    const body=
        document.getElementById(
            'shareDetailsBody'
        );

    const initials=(user.name??'?')
        .trim()
        .charAt(0)
        .toUpperCase();

    const methodIcons={
        cash:'bi-cash-stack',
        bank:'bi-bank',
        mobile_banking:'bi-phone',
        online:'bi-globe'
    };

    body.innerHTML=`
        <div class="mb-4 flex flex-col gap-4 rounded-md border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-layers text-lg"></i>
                </div>

                <div class="min-w-0">
                    <p class="truncate font-mono text-base font-bold text-slate-800">
                        ${AdminUI.escapeHtml(share.share_no??'—')}
                    </p>
                    <p class="text-xs text-slate-500">Share Purchase</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 sm:flex-col sm:items-end sm:justify-start">
                ${AdminUI.statusBadge(share.status)}
                <p class="text-xl font-bold text-emerald-600">
                    ${money(share.purchase_amount)}
                </p>
            </div>
        </div>

        <div class="mb-4 flex items-center gap-3 rounded-md border border-slate-200 bg-white p-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-bold text-indigo-600">
                ${AdminUI.escapeHtml(initials)}
            </div>

            <div class="min-w-0">
                <p class="truncate text-xs font-semibold text-slate-700">
                    ${AdminUI.escapeHtml(user.name??'—')}
                </p>
                <p class="text-[11px] text-slate-400">
                    ${AdminUI.escapeHtml(member.member_code??'—')}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            ${detail('Payment Method',AdminUI.titleCase(share.payment_method),methodIcons[share.payment_method]??'bi-credit-card')}
            ${detail('Reference',share.transaction_reference??'—','bi-hash')}
            ${detail('Submitted',AdminUI.formatDate(share.created_at,true),'bi-calendar-plus')}
            ${detail('Acquired',AdminUI.formatDate(share.acquired_date),'bi-calendar-check')}
            ${detail('Verified At',AdminUI.formatDate(share.verified_at,true),'bi-shield-check')}
            ${detail('Verified By',share.verifier?.name??'—','bi-person-check')}
        </div>

        ${
            entries.length
                ?`
                    <div class="mt-4">
                        <div class="mb-2 flex items-center gap-2">
                            <div class="flex h-7 w-7 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-journal-text text-sm"></i>
                            </div>

                            <p class="text-sm font-semibold text-slate-700">
                                Journal Entry
                            </p>
                        </div>

                        <div class="overflow-hidden overflow-x-auto rounded-md border border-slate-200">
                            <table class="w-full min-w-[500px] text-sm">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-xs text-left font-semibold text-slate-500">Account</th>
                                        <th class="px-3 py-2 text-xs text-right font-semibold text-slate-500">Debit</th>
                                        <th class="px-3 py-2 text-xs text-right font-semibold text-slate-500">Credit</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    ${entries.map(entry=>`
                                        <tr class="border-t border-slate-100 odd:bg-white even:bg-slate-50/60">
                                            <td class="px-3 py-2 text-xs text-slate-600">
                                                ${AdminUI.escapeHtml(entry.account?.code??'')}
                                                -
                                                ${AdminUI.escapeHtml(entry.account?.name??'')}
                                            </td>

                                            <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                                ${Number(entry.debit??0)>0?money(entry.debit):'—'}
                                            </td>

                                            <td class="px-3 py-2 text-xs text-right font-medium text-slate-700">
                                                ${Number(entry.credit??0)>0?money(entry.credit):'—'}
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `
                :''
        }

        ${
            share.verification_note??share.notes
                ?`
                    <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="mb-1 flex items-center gap-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            <i class="bi bi-sticky"></i>
                            Note
                        </p>

                        <p class="text-xs leading-5 text-slate-600">
                            ${AdminUI.escapeHtml(
                                share.verification_note??share.notes
                            )}
                        </p>
                    </div>
                `
                :''
        }
    `;

    const actions=
        document.getElementById(
            'shareDetailsActions'
        );

    if(canUpdate&&share.status==='pending'){
        actions.innerHTML=`
            <button
                type="button"
                onclick="closeShareDetailsModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>

            <button
                type="button"
                onclick="openShareRejectModal(${Number(share.id)})"
                class="cursor-pointer rounded-md border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                Reject
            </button>

            <button
                type="button"
                onclick="verifyShare(${Number(share.id)},'${escapeJs(share.share_no)}')"
                class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                Verify
            </button>
        `;
    }else{
        actions.innerHTML=`
            <button
                type="button"
                onclick="closeShareDetailsModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
            ${
                share.status==='active'
                    ?`<button
                        type="button"
                        onclick="downloadPdf('/api/member-shares/${Number(share.id)}/receipt','share-purchase-${AdminUI.escapeHtml(share.share_no??share.id)}.pdf')"
                        class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                        <i class="bi bi-file-earmark-pdf mr-1"></i>
                        Download PDF
                    </button>`
                    :''
            }
        `;
    }
}

window.closeShareDetailsModal=function(){
    AdminUI.closeModal(
        'shareDetailsModal'
    );
};

window.verifyShare=async function(id,shareNo){
    const confirmed=await AdminUI.confirm({
        title:'Verify Share Purchase?',
        subtitle:'This will post the accounting entry.',
        message:`Share purchase "${shareNo}" will be verified and activated.`,
        confirmText:'Verify',
        type:'success'
    });

    if(!confirmed){
        return;
    }

    try{
        const response=await api(
            `/api/member-shares/${id}/verify`,
            {
                method:'POST',
                body:JSON.stringify({
                    note:null
                })
            }
        );

        closeShareDetailsModal();

        Toast.success(
            response.message||
            'Share verified successfully.'
        );

        await loadSharePurchases(
            currentPage
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.openShareRejectModal=function(id){
    const share=selectedShare??{};

    document.getElementById(
        'shareRejectId'
    ).value=id;

    document.getElementById(
        'shareRejectReason'
    ).value='';

    document.getElementById(
        'shareRejectDescription'
    ).textContent=
        `${share.share_no??''} • ${money(share.purchase_amount)}`;

    AdminUI.clearError(
        'shareRejectError'
    );

    closeShareDetailsModal();

    AdminUI.openModal(
        'shareRejectModal'
    );
};

window.closeShareRejectModal=function(){
    AdminUI.closeModal(
        'shareRejectModal'
    );
};

document.getElementById(
    'shareRejectForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'shareRejectError'
        );

        const id=Number(
            document.getElementById(
                'shareRejectId'
            ).value
        );

        const note=
            document
                .getElementById(
                    'shareRejectReason'
                )
                .value
                .trim();

        if(!note){
            AdminUI.showError(
                'shareRejectError',
                'Rejection reason is required.'
            );

            return;
        }

        const button=
            document.getElementById(
                'shareRejectButton'
            );

        AdminUI.setLoading(
            button,
            'Rejecting...'
        );

        try{
            const response=await api(
                `/api/member-shares/${id}/reject`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        note
                    })
                }
            );

            closeShareRejectModal();

            Toast.success(
                response.message||
                'Share purchase rejected.'
            );

            await loadSharePurchases(
                currentPage
            );
        }catch(error){
            AdminUI.showError(
                'shareRejectError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

function detail(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 break-words text-sm font-medium text-slate-700">
                ${AdminUI.escapeHtml(
                    value??'—'
                )}
            </p>
        </div>
    `;
}

function money(value){
    return `${@json(setting('currency_symbol','৳'))}${Number(
        value??0
    ).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}

function escapeJs(value){
    return String(value??'')
        .replaceAll('\\','\\\\')
        .replaceAll("'","\\'")
        .replaceAll('\n',' ');
}

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';

    loadSharePurchases(1);
};

async function initSharePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initSharePage,
            50
        );

        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadSharePurchases(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadSharePurchases(1)
    );

    await loadSharePurchases();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initSharePage
    );
}else{
    initSharePage();
}
</script>
@endpush