@extends('layouts.admin')

@section('title','Share Purchases')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">
                Share Purchases
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Review member share purchase requests.
            </p>
        </div>

        <button
            type="button"
            onclick="loadSharePurchases()"
            class="inline-flex h-9 w-fit items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total</p>
            <p id="totalCount" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-xs text-amber-700">Pending</p>
            <p id="pendingCount" class="mt-2 text-xl font-bold text-amber-600">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-700">Active</p>
            <p id="activeCount" class="mt-2 text-xl font-bold text-emerald-600">0</p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/40 p-4">
            <p class="text-xs text-red-600">Rejected</p>
            <p id="rejectedCount" class="mt-2 text-xl font-bold text-red-600">0</p>
        </div>

    </div>

    <div class="rounded-md border border-slate-200 bg-white">

        <div class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[1fr_180px_100px]">
            <input
                id="shareSearch"
                type="text"
                placeholder="Search member, share no, reference..."
                class="h-9 rounded-md border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400">

            <select
                id="shareStatus"
                class="h-9 rounded-md border border-slate-300 bg-white px-3 text-xs outline-none focus:border-indigo-400">

                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="active">Active</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>

            <button
                type="button"
                onclick="applyShareFilter()"
                class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">
                Filter
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Share</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Method</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Submitted</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="sharePurchaseTable">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            id="sharePagination"
            class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
        </div>
    </div>
</div>

<div
    id="shareDetailsModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">

    <div class="w-full max-w-2xl overflow-hidden rounded-md bg-white shadow-xl">

        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Share Purchase Details
                </h3>
                <p class="text-xs text-slate-400">
                    Review payment and member details.
                </p>
            </div>

            <button
                type="button"
                onclick="closeShareDetails()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="shareDetails"
            class="grid gap-4 p-5 sm:grid-cols-2">
        </div>

        <div
            id="shareActions"
            class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
        </div>
    </div>
</div>

<div
    id="shareRejectModal"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/40 p-4">

    <div class="w-full max-w-md overflow-hidden rounded-md bg-white shadow-xl">

        <div class="border-b border-slate-200 px-5 py-4">
            <h3 class="text-base font-bold text-slate-800">
                Reject Share Purchase
            </h3>
        </div>

        <div class="p-5">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                Reason
            </label>

            <textarea
                id="shareRejectReason"
                rows="4"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm outline-none focus:border-red-400">
            </textarea>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
            <button
                type="button"
                onclick="closeShareReject()"
                class="h-9 rounded-md border border-slate-300 px-4 text-xs font-semibold text-slate-600">
                Cancel
            </button>

            <button
                type="button"
                onclick="confirmShareReject()"
                class="h-9 rounded-md bg-red-600 px-4 text-xs font-semibold text-white">
                Reject
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let sharePage=1;
let selectedAdminShare=null;

async function loadSharePurchases(page=sharePage){
    sharePage=page;

    const search=document
        .getElementById('shareSearch')
        .value
        .trim();

    const status=document
        .getElementById('shareStatus')
        .value;

    const params=new URLSearchParams({
        page:sharePage,
        per_page:20
    });

    if(search){
        params.set('search',search);
    }

    if(status){
        params.set('status',status);
    }

    const tbody=document.getElementById(
        'sharePurchaseTable'
    );

    tbody.innerHTML=`
        <tr>
            <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                Loading...
            </td>
        </tr>
    `;

    try{
        const response=await api(
            `/api/member-shares?${params.toString()}`
        );

        const paginator=response.data??{};
        const rows=paginator.data??[];

        renderAdminShares(rows);
        renderSharePagination(paginator);

        document.getElementById('totalCount').textContent=
            paginator.total??0;

        await loadShareCounts();
    }catch(error){
        tbody.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-red-500">
                    ${escapeAdminShare(error.message??'Failed to load shares.')}
                </td>
            </tr>
        `;
    }
}

async function loadShareCounts(){
    try{
        const [pending,active,rejected]=await Promise.all([
            api('/api/member-shares?status=pending&per_page=5'),
            api('/api/member-shares?status=active&per_page=5'),
            api('/api/member-shares?status=rejected&per_page=5')
        ]);

        document.getElementById('pendingCount').textContent=
            pending.data?.total??0;

        document.getElementById('activeCount').textContent=
            active.data?.total??0;

        document.getElementById('rejectedCount').textContent=
            rejected.data?.total??0;

    }catch(error){
        console.error(error);
    }
}

function applyShareFilter(){
    loadSharePurchases(1);
}

function renderAdminShares(rows){
    const tbody=document.getElementById(
        'sharePurchaseTable'
    );

    if(!rows.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                    No share purchases found.
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML=rows.map(share=>{
        const member=share.member??{};
        const user=member.user??{};

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">

                <td class="px-4 py-3">
                    <span class="font-mono text-xs font-semibold text-indigo-600">
                        ${escapeAdminShare(share.share_no??'-')}
                    </span>
                </td>

                <td class="px-4 py-3">
                    <div class="text-xs font-semibold text-slate-700">
                        ${escapeAdminShare(user.name??'-')}
                    </div>

                    <div class="mt-0.5 font-mono text-[10px] text-slate-400">
                        ${escapeAdminShare(member.member_code??'-')}
                    </div>
                </td>

                <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                    ৳${shareAdminMoney(share.purchase_amount)}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${shareAdminTitle(share.payment_method)}
                </td>

                <td class="max-w-[170px] px-4 py-3">
                    <p class="truncate text-xs text-slate-500">
                        ${escapeAdminShare(share.transaction_reference??'-')}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-slate-500">
                    ${shareAdminDate(share.created_at)}
                </td>

                <td class="px-4 py-3 text-center">
                    ${shareAdminBadge(share.status)}
                </td>

                <td class="px-4 py-3 text-right">
                    <button
                        type="button"
                        onclick="viewAdminShare(${Number(share.id)})"
                        class="inline-flex h-8 items-center rounded-md border border-slate-300 px-3 text-[11px] font-semibold text-slate-600 hover:bg-slate-50">
                        View
                    </button>
                </td>

            </tr>
        `;
    }).join('');
}

async function viewAdminShare(id){
    try{
        const response=await api(
            `/api/member-shares/${id}`
        );

        selectedAdminShare=response.data;

        renderAdminShareDetails();

        const modal=document.getElementById(
            'shareDetailsModal'
        );

        modal.classList.remove('hidden');
        modal.classList.add('flex');

    }catch(error){
        alert(error.message??'Failed to load share.');
    }
}

function renderAdminShareDetails(){
    const share=selectedAdminShare??{};
    const member=share.member??{};
    const user=member.user??{};

    document.getElementById(
        'shareDetails'
    ).innerHTML=`
        ${shareDetail('Share No',share.share_no)}
        ${shareDetail('Member',user.name)}
        ${shareDetail('Member Code',member.member_code)}
        ${shareDetail('Amount','৳'+shareAdminMoney(share.purchase_amount))}
        ${shareDetail('Payment Method',shareAdminTitle(share.payment_method))}
        ${shareDetail('Reference',share.transaction_reference??'-')}
        ${shareDetail('Status',shareAdminTitle(share.status))}
        ${shareDetail('Submitted',shareAdminDate(share.created_at))}
        ${shareDetail('Acquired',shareAdminDate(share.acquired_date))}
        ${shareDetail('Verified At',shareAdminDate(share.verified_at))}
        ${shareDetail('Note',share.verification_note??share.notes??'-')}
    `;

    const actions=document.getElementById(
        'shareActions'
    );

    if(share.status==='pending'){
        actions.innerHTML=`
            <button
                type="button"
                onclick="openShareReject()"
                class="h-9 rounded-md border border-red-200 bg-white px-4 text-xs font-semibold text-red-600 hover:bg-red-50">
                Reject
            </button>

            <button
                type="button"
                onclick="verifyAdminShare()"
                class="h-9 rounded-md bg-emerald-600 px-4 text-xs font-semibold text-white hover:bg-emerald-700">
                Verify
            </button>
        `;
    }else{
        actions.innerHTML=`
            <button
                type="button"
                onclick="closeShareDetails()"
                class="h-9 rounded-md border border-slate-300 px-4 text-xs font-semibold text-slate-600">
                Close
            </button>
        `;
    }
}

async function verifyAdminShare(){
    if(!selectedAdminShare){
        return;
    }

    if(!confirm(
        `Verify ${selectedAdminShare.share_no}?`
    )){
        return;
    }

    try{
        const response=await api(
            `/api/member-shares/${selectedAdminShare.id}/verify`,
            {
                method:'POST',
                body:JSON.stringify({
                    note:null
                })
            }
        );

        alert(
            response.message??
            'Share verified.'
        );

        closeShareDetails();

        await loadSharePurchases();

    }catch(error){
        alert(
            error?.data?.message??
            error.message??
            'Failed to verify share.'
        );
    }
}

function openShareReject(){
    document.getElementById(
        'shareRejectReason'
    ).value='';

    closeShareDetails();

    const modal=document.getElementById(
        'shareRejectModal'
    );

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeShareReject(){
    const modal=document.getElementById(
        'shareRejectModal'
    );

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function confirmShareReject(){
    if(!selectedAdminShare){
        return;
    }

    const note=document
        .getElementById('shareRejectReason')
        .value
        .trim();

    if(!note){
        alert('Rejection reason is required.');
        return;
    }

    try{
        const response=await api(
            `/api/member-shares/${selectedAdminShare.id}/reject`,
            {
                method:'POST',
                body:JSON.stringify({
                    note
                })
            }
        );

        alert(
            response.message??
            'Share rejected.'
        );

        closeShareReject();

        await loadSharePurchases();

    }catch(error){
        alert(
            error?.data?.message??
            error.message??
            'Failed to reject share.'
        );
    }
}

function closeShareDetails(){
    const modal=document.getElementById(
        'shareDetailsModal'
    );

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderSharePagination(data){
    const current=data.current_page??1;
    const last=data.last_page??1;

    document.getElementById(
        'sharePagination'
    ).innerHTML=`
        <span class="text-xs text-slate-400">
            Showing ${data.from??0}-${data.to??0}
            of ${data.total??0}
        </span>

        <div class="flex gap-2">
            <button
                type="button"
                ${current<=1?'disabled':''}
                onclick="loadSharePurchases(${current-1})"
                class="h-8 rounded-md border border-slate-300 px-3 text-xs font-semibold text-slate-600 disabled:opacity-40">
                Previous
            </button>

            <span class="flex h-8 items-center px-2 text-xs text-slate-500">
                ${current} / ${last}
            </span>

            <button
                type="button"
                ${current>=last?'disabled':''}
                onclick="loadSharePurchases(${current+1})"
                class="h-8 rounded-md border border-slate-300 px-3 text-xs font-semibold text-slate-600 disabled:opacity-40">
                Next
            </button>
        </div>
    `;
}

function shareDetail(label,value){
    return`
        <div class="rounded-md bg-slate-50 p-3">
            <div class="text-[10px] uppercase tracking-wide text-slate-400">
                ${escapeAdminShare(label)}
            </div>

            <div class="mt-1 text-sm font-semibold text-slate-700">
                ${escapeAdminShare(value??'-')}
            </div>
        </div>
    `;
}

function shareAdminBadge(status){
    const styles={
        pending:'bg-amber-50 text-amber-700',
        active:'bg-emerald-50 text-emerald-700',
        rejected:'bg-red-50 text-red-600',
        cancelled:'bg-slate-100 text-slate-600',
        transferred:'bg-indigo-50 text-indigo-700',
        retired:'bg-slate-100 text-slate-600'
    };

    return`
        <span class="rounded-md px-2 py-1 text-[10px] font-semibold ${
            styles[status]??
            'bg-slate-100 text-slate-600'
        }">
            ${escapeAdminShare(shareAdminTitle(status))}
        </span>
    `;
}

function shareAdminMoney(value){
    return Number(value??0).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    );
}

function shareAdminTitle(value){
    return String(value??'-')
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}

function shareAdminDate(value){
    if(!value){
        return'-';
    }

    const date=new Date(value);

    if(Number.isNaN(date.getTime())){
        return value;
    }

    return date.toLocaleString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric',
            hour:'2-digit',
            minute:'2-digit'
        }
    );
}

function escapeAdminShare(value){
    const div=document.createElement('div');
    div.textContent=String(value??'');
    return div.innerHTML;
}

document.addEventListener(
    'DOMContentLoaded',
    ()=>{
        document
            .getElementById('shareSearch')
            .addEventListener(
                'keydown',
                event=>{
                    if(event.key==='Enter'){
                        applyShareFilter();
                    }
                }
            );

        loadSharePurchases();
    }
);
</script>
@endpush