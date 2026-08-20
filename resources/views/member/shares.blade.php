@extends('layouts.member')

@section('title','My Shares')
@section('page-title','My Shares')

@section('content')
@php
    $currency=setting('currency_symbol','৳');
@endphp

<div class="space-y-5">

    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">
                My Shares
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                View your association shares and submit new share purchases.
            </p>
        </div>

        <div class="flex gap-2">
            <button
                type="button"
                onclick="loadShares()"
                class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>

            <button
                type="button"
                onclick="openPurchaseModal()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-plus-circle"></i>
                Buy Share
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">
                Total Shares
            </p>

            <p
                id="totalShares"
                class="mt-2 text-xl font-bold text-slate-800">
                0
            </p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-700">
                Active Shares
            </p>

            <p
                id="activeShares"
                class="mt-2 text-xl font-bold text-emerald-600">
                0
            </p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-xs text-amber-700">
                Pending Purchases
            </p>

            <p
                id="pendingShares"
                class="mt-2 text-xl font-bold text-amber-600">
                0
            </p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-xs text-indigo-700">
                Active Share Value
            </p>

            <p
                id="activeShareValue"
                class="mt-2 text-xl font-bold text-indigo-600">
                {{ $currency }}0.00
            </p>
        </div>

    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-800">
                Share History
            </h2>

            <p class="mt-0.5 text-[11px] text-slate-400">
                Your active and pending association shares.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Share No.
                        </th>

                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Amount
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Payment Method
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Reference
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Acquired
                        </th>

                        <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Note
                        </th>
                    </tr>
                </thead>

                <tbody id="sharesTable">
                    <tr>
                        <td
                            colspan="7"
                            class="px-4 py-10 text-center text-slate-400">
                            Loading shares...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>
    </div>

</div>

{{-- Purchase Modal --}}
<div
    id="purchaseModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4 backdrop-blur-[1px]">

    <div class="w-full max-w-lg overflow-hidden rounded-md border border-slate-200 bg-white shadow-xl">

        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">

            <div>
                <h3 class="text-base font-bold text-slate-800">
                    Buy New Share
                </h3>

                <p class="mt-0.5 text-xs text-slate-400">
                    Purchase will remain pending until verified.
                </p>
            </div>

            <button
                type="button"
                onclick="closePurchaseModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <form id="purchaseForm">

            <div class="space-y-4 p-5">

                <div class="rounded-md border border-indigo-100 bg-indigo-50/50 p-4">

                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-indigo-700">
                            Default Share Value
                        </span>

                        <span
                            id="defaultShareValue"
                            class="text-lg font-bold text-indigo-700">
                            {{ $currency }}0.00
                        </span>
                    </div>

                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Purchase Amount
                    </label>

                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">
                            {{ $currency }}
                        </span>

                        <input
                            id="purchaseAmount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            required
                            class="h-10 w-full rounded-md border border-slate-300 bg-white pl-8 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Payment Method
                    </label>

                    <select
                        id="sharePaymentMethod"
                        required
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-indigo-400">

                        <option value="cash">
                            Cash
                        </option>

                        <option value="bank">
                            Bank
                        </option>

                        <option value="mobile_banking">
                            Mobile Banking
                        </option>

                        <option value="online">
                            Online
                        </option>

                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Transaction Reference
                    </label>

                    <input
                        id="shareReference"
                        type="text"
                        maxlength="255"
                        placeholder="Txn ID / bank reference"
                        class="h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none focus:border-indigo-400">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-600">
                        Notes
                    </label>

                    <textarea
                        id="shareNotes"
                        rows="3"
                        maxlength="3000"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus:border-indigo-400">
                    </textarea>
                </div>

                <div
                    id="shareError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                </div>

            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">

                <button
                    type="button"
                    onclick="closePurchaseModal()"
                    class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="purchaseButton"
                    type="submit"
                    class="h-9 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    Submit Purchase
                </button>

            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const shareCurrency=@json($currency);

let shareData=[];
let shareSettings={};

function shareMoney(value){
    return shareCurrency+
        Number(value??0).toLocaleString(
            'en-US',
            {
                minimumFractionDigits:2,
                maximumFractionDigits:2
            }
        );
}

async function loadShares(){
    const table=document.getElementById(
        'sharesTable'
    );

    table.innerHTML=`
        <tr>
            <td colspan="7" class="px-4 py-10 text-center text-slate-400">
                Loading shares...
            </td>
        </tr>
    `;

    try{
        const response=await api(
            '/api/member/shares'
        );

        const data=response.data??{};

        const summary=data.summary??{};

        shareData=Array.isArray(data.shares)
            ?data.shares
            :[];

        shareSettings=data.settings??{};

        document.getElementById(
            'totalShares'
        ).textContent=
            Number(
                summary.total_shares??0
            );

        document.getElementById(
            'activeShares'
        ).textContent=
            Number(
                summary.active_shares??0
            );

        document.getElementById(
            'pendingShares'
        ).textContent=
            Number(
                summary.pending_shares??0
            );

        document.getElementById(
            'activeShareValue'
        ).textContent=
            shareMoney(
                summary.active_share_value
            );

        document.getElementById(
            'defaultShareValue'
        ).textContent=
            shareMoney(
                shareSettings.default_share_value
            );

        renderShares();

    }catch(error){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="px-4 py-10 text-center text-red-500">
                    ${escapeShareHtml(
                        error?.data?.message??
                        error?.message??
                        'Failed to load shares.'
                    )}
                </td>
            </tr>
        `;
    }
}

function renderShares(){
    const table=document.getElementById(
        'sharesTable'
    );

    if(!shareData.length){
        table.innerHTML=`
            <tr>
                <td colspan="7" class="px-4 py-10 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-50 text-slate-400">
                        <i class="bi bi-layers"></i>
                    </div>

                    <p class="mt-3 text-xs font-semibold text-slate-600">
                        No shares found
                    </p>
                </td>
            </tr>
        `;

        return;
    }

    table.innerHTML=
        shareData.map(share=>`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">

                <td class="px-4 py-3">
                    <span class="font-mono text-xs font-semibold text-indigo-600">
                        ${escapeShareHtml(
                            share.share_no??'-'
                        )}
                    </span>
                </td>

                <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                    ${shareMoney(
                        share.purchase_amount
                    )}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${titleShare(
                        share.payment_method??'-'
                    )}
                </td>

                <td class="max-w-[180px] px-4 py-3">
                    <p class="truncate text-xs text-slate-500">
                        ${escapeShareHtml(
                            share.transaction_reference??'-'
                        )}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-slate-500">
                    ${
                        share.acquired_date
                            ?formatShareDate(
                                share.acquired_date
                            )
                            :'Pending'
                    }
                </td>

                <td class="px-4 py-3 text-center">
                    ${shareStatusBadge(
                        share.status
                    )}
                </td>

                <td class="max-w-[220px] px-4 py-3">
                    <p class="truncate text-xs text-slate-500">
                        ${escapeShareHtml(
                            share.verification_note??
                            share.notes??
                            '-'
                        )}
                    </p>
                </td>

            </tr>
        `).join('');
}

function openPurchaseModal(){
    const amount=Number(
        shareSettings.default_share_value??0
    );

    document.getElementById(
        'purchaseAmount'
    ).value=
        amount>0
            ?amount.toFixed(2)
            :'';

    document.getElementById(
        'sharePaymentMethod'
    ).value='cash';

    document.getElementById(
        'shareReference'
    ).value='';

    document.getElementById(
        'shareNotes'
    ).value='';

    document.getElementById(
        'shareError'
    ).classList.add('hidden');

    const modal=
        document.getElementById(
            'purchaseModal'
        );

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePurchaseModal(){
    const modal=
        document.getElementById(
            'purchaseModal'
        );

    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.getElementById(
    'purchaseForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const amount=Number(
            document.getElementById(
                'purchaseAmount'
            ).value
        );

        const paymentMethod=
            document.getElementById(
                'sharePaymentMethod'
            ).value;

        const reference=
            document.getElementById(
                'shareReference'
            ).value.trim();

        const notes=
            document.getElementById(
                'shareNotes'
            ).value.trim();

        const errorBox=
            document.getElementById(
                'shareError'
            );

        errorBox.classList.add(
            'hidden'
        );

        if(
            !Number.isFinite(amount)||
            amount<=0
        ){
            errorBox.textContent=
                'Enter a valid share amount.';

            errorBox.classList.remove(
                'hidden'
            );

            return;
        }

        const minimum=Number(
            shareSettings
                .minimum_share_purchase_amount??0
        );

        if(
            minimum>0&&
            amount<minimum
        ){
            errorBox.textContent=
                `Minimum share purchase amount is ${shareMoney(minimum)}.`;

            errorBox.classList.remove(
                'hidden'
            );

            return;
        }

        const button=
            document.getElementById(
                'purchaseButton'
            );

        button.disabled=true;
        button.textContent='Submitting...';

        try{
            const response=await api(
                '/api/member/shares',
                {
                    method:'POST',
                    body:JSON.stringify({
                        purchase_amount:amount,
                        payment_method:paymentMethod,
                        transaction_reference:
                            reference||null,
                        notes:notes||null
                    })
                }
            );

            Toast.success(
                response.message??
                'Share purchase submitted.'
            );

            closePurchaseModal();

            await loadShares();

        }catch(error){
            errorBox.textContent=
                error?.data?.message??
                error?.message??
                'Failed to submit share purchase.';

            errorBox.classList.remove(
                'hidden'
            );

        }finally{
            button.disabled=false;
            button.textContent=
                'Submit Purchase';
        }
    }
);

function shareStatusBadge(status){
    const classes={
        active:
            'bg-emerald-50 text-emerald-700',

        pending:
            'bg-amber-50 text-amber-700',

        rejected:
            'bg-red-50 text-red-600',

        cancelled:
            'bg-slate-100 text-slate-600',

        transferred:
            'bg-indigo-50 text-indigo-700',

        retired:
            'bg-slate-100 text-slate-600'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${
            classes[status]??
            'bg-slate-100 text-slate-600'
        }">
            ${escapeShareHtml(
                titleShare(status)
            )}
        </span>
    `;
}

function titleShare(value){
    return String(value??'-')
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            c=>c.toUpperCase()
        );
}

function formatShareDate(value){
    if(!value){
        return'-';
    }

    const date=new Date(
        String(value).length===10
            ?`${value}T00:00:00`
            :value
    );

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return value;
    }

    return date.toLocaleDateString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric'
        }
    );
}

function escapeShareHtml(value){
    const div=
        document.createElement(
            'div'
        );

    div.textContent=
        String(value??'');

    return div.innerHTML;
}

document.addEventListener(
    'DOMContentLoaded',
    loadShares
);
</script>
@endpush