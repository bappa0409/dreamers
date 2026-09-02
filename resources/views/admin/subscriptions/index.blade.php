@extends('layouts.admin')

@section('title','Monthly Subscriptions')
@section('page-title','Monthly Subscriptions')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <h1 class="text-base font-semibold text-slate-800">Monthly Subscriptions</h1>
                <p class="mt-1 text-sm text-slate-500">Manage member subscriptions and monthly dues.</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openBulkModal()"
                class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                <i class="bi bi-stack"></i>
                Generate Monthly Dues
            </button>

            <button type="button" onclick="openSubscriptionModal()"
                class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-md bg-indigo-600 px-3 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Assign Subscription
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-[11px] text-slate-400">Total Due</p>
            <p id="statDue" class="mt-1 text-xl font-bold text-slate-800">{{ setting('currency_symbol','৳') }}0.00</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-[11px] text-emerald-600">Paid</p>
            <p id="statPaid" class="mt-1 text-xl font-bold text-emerald-700">{{ setting('currency_symbol','৳') }}0.00</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <p class="text-[11px] text-amber-600">Outstanding</p>
            <p id="statOutstanding" class="mt-1 text-xl font-bold text-amber-700">{{ setting('currency_symbol','৳') }}0.00</p>
        </div>

        <div class="rounded-md border border-sky-200 bg-sky-50/50 p-4">
            <p class="text-[11px] text-sky-600">Records</p>
            <p id="statRecords" class="mt-1 text-xl font-bold text-sky-700">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white">
        <div class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="md:col-span-2">
                <label class="form-label">Search</label>
                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="filterSearch"
                        type="text"
                        class="app-input !pl-9"
                        placeholder="Member name, code, email...">
                </div>
            </div>

            <div>
                <label class="form-label">Status</label>
                <select id="filterStatus" class="app-input">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div>
                <label class="form-label">Year</label>
                <select id="filterYear" class="app-input"></select>
            </div>

            <div>
                <label class="form-label">Month</label>
                <select id="filterMonth" class="app-input"></select>
            </div>
        </div>

        <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Member</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Plan</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Start</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Due</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody id="subscriptionTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-400">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="subscriptionMobileGrid" class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 lg:hidden"></div>

        <div id="paginationWrap" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Assign Subscription Modal --}}
<div id="subscriptionModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Assign Subscription</h3>
                <p class="text-sm text-slate-400">Assign an active subscription plan to a member.</p>
            </div>

            <button type="button" onclick="AdminUI.closeModal('subscriptionModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="subscriptionForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <div id="subscriptionError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"></div>

                <div>
                    <label class="form-label">Member <span class="text-red-500">*</span></label>
                    <select id="memberId" class="app-input">
                        <option value="">Select Member</option>
                    </select>
                    <p data-field-error="memberId" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="form-label">Plan <span class="text-red-500">*</span></label>
                    <select id="planId" class="app-input">
                        <option value="">Select Plan</option>
                    </select>
                    <p data-field-error="planId" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                        <input id="startDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off">
                    </div>
                    <p data-field-error="startDate" class="mt-1 hidden text-sm text-red-600"></p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('subscriptionModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="saveSubscriptionButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Bulk Generate Modal --}}
<div id="bulkModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Generate Monthly Dues</h3>
                <p class="text-sm text-slate-400">Generate dues for all active member subscriptions.</p>
            </div>

            <button type="button" onclick="AdminUI.closeModal('bulkModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="bulkForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <div id="bulkError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"></div>

                <div>
                    <label class="form-label">Year <span class="text-red-500">*</span></label>
                    <select id="bulkYear" class="app-input"></select>
                </div>

                <div>
                    <label class="form-label">Month <span class="text-red-500">*</span></label>
                    <select id="bulkMonth" class="app-input"></select>
                </div>

                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                    Existing dues for the selected month will not be duplicated.
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('bulkModal')"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="generateBulkButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Generate
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

let currentPage=1;
let subscriptions=[];
let members=[];
let plans=[];

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>`${currency}${Number(value||0).toLocaleString('en-BD',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

function monthName(month){
    return new Date(
        2000,
        Number(month)-1,
        1
    ).toLocaleDateString('en-US',{
        month:'short'
    });
}

function initPeriodOptions(){
    const year=Number(new Date().getFullYear());

    const years=Array.from(
        {length:11},
        (_,i)=>year-5+i
    );

    const yearHtml=years.map(y=>`
        <option value="${y}" ${y===year?'selected':''}>${y}</option>
    `).join('');

    ['filterYear','bulkYear'].forEach(id=>{
        $(id).innerHTML=yearHtml;
    });

    const month=new Date().getMonth()+1;

    const monthHtml=Array.from(
        {length:12},
        (_,i)=>i+1
    ).map(m=>`
        <option value="${m}" ${m===month?'selected':''}>
            ${monthName(m)}
        </option>
    `).join('');

    ['filterMonth','bulkMonth'].forEach(id=>{
        $(id).innerHTML=monthHtml;
    });
}

async function loadOptions(){
    try{
        const [memberResponse,planResponse]=await Promise.all([
            api('/api/finance/subscriptions/members'),
            api('/api/finance/subscription-plans')
        ]);

        members=memberResponse.data||[];
        plans=(planResponse.data||[]).filter(
            plan=>plan.is_active
        );

        $('memberId').innerHTML=
            `<option value="">Select Member</option>`+
            members.map(member=>`
                <option value="${member.id}">
                    ${esc(member.member_code)} - ${esc(member.user?.name||'')}
                </option>
            `).join('');

        $('planId').innerHTML=
            `<option value="">Select Plan</option>`+
            plans.map(plan=>`
                <option value="${plan.id}">
                    ${esc(plan.name)} (${money(plan.amount)})
                </option>
            `).join('');

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadSummary(){
    const params=new URLSearchParams({
        year:$('filterYear').value,
        month:$('filterMonth').value
    });

    try{
        const response=await api(
            `/api/finance/subscriptions/summary?${params}`
        );

        const data=response.data||{};

        $('statDue').textContent=
            money(data.total_due);

        $('statPaid').textContent=
            money(data.total_paid);

        $('statOutstanding').textContent=
            money(data.outstanding);

        $('statRecords').textContent=
            data.total_records||0;

    }catch(error){
        console.error(error);
    }
}

async function loadSubscriptions(page=1){
    currentPage=page;

    const body=$('subscriptionTableBody');
    const grid=$('subscriptionMobileGrid');

    body.innerHTML=
        AdminUI.loadingState(
            'Loading subscriptions...',
            6
        );

    grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
            Loading subscriptions...
        </div>
    `;

    const params=new URLSearchParams({
        page:String(page),
        year:$('filterYear').value,
        month:$('filterMonth').value
    });

    const search=$('filterSearch').value.trim();
    const status=$('filterStatus').value;

    if(search){
        params.set('search',search);
    }

    if(status){
        params.set('status',status);
    }

    try{
        const response=await api(
            `/api/finance/subscriptions?${params}`
        );

        const paginator=response.data||{};

        subscriptions=paginator.data||[];

        renderSubscriptions();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadSubscriptions
        );

    }catch(error){
        body.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                6
            );
    }
}

function renderSubscriptions(){
    const body=$('subscriptionTableBody');
    const grid=$('subscriptionMobileGrid');

    if(!subscriptions.length){
        body.innerHTML=
            AdminUI.emptyState(
                'No subscriptions found.',
                6
            );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                No subscriptions found.
            </div>
        `;

        return;
    }

    body.innerHTML=subscriptions.map(subscription=>{
        const member=subscription.member||{};
        const user=member.user||{};
        const plan=subscription.plan||{};
        const due=subscription.dues?.[0]||null;

        return`
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <p class="text-sm font-semibold text-slate-700">
                        ${esc(user.name||'—')}
                    </p>

                    <p class="mt-0.5 font-mono text-[10px] text-slate-400">
                        ${esc(member.member_code||'—')}
                    </p>
                </td>

                <td class="px-4 py-3">
                    <p class="text-sm font-semibold text-slate-600">
                        ${esc(plan.name||'—')}
                    </p>

                    <p class="text-[10px] text-slate-400">
                        ${money(plan.amount||0)}
                    </p>
                </td>

                <td class="px-4 py-3 text-sm text-slate-500">
                    ${AdminUI.formatDate(subscription.start_date)}
                </td>

                <td class="px-4 py-3">
                    ${AdminUI.statusBadge(
                        subscription.is_active
                            ?'active'
                            :'inactive'
                    )}
                </td>

                <td class="px-4 py-3">
                    ${
                        due
                            ?`
                                <p class="text-sm font-semibold text-slate-700">
                                    ${money(due.amount)}
                                </p>

                                <div class="mt-1">
                                    ${AdminUI.statusBadge(due.status)}
                                </div>
                            `
                            :`
                                <span class="text-sm text-slate-400">
                                    Not generated
                                </span>
                            `
                    }
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        ${
                            !due&&subscription.is_active
                                ?`
                                    <button type="button"
                                        onclick="generateDue(${subscription.id})"
                                        class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                                        Generate Due
                                    </button>
                                `
                                :''
                        }

                        ${
                            subscription.is_active
                                ?`
                                    <button type="button"
                                        onclick="deactivateSubscription(${subscription.id})"
                                        class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                                        Deactivate
                                    </button>
                                `
                                :''
                        }
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    grid.innerHTML=subscriptions.map(subscription=>{
        const member=subscription.member||{};
        const user=member.user||{};
        const plan=subscription.plan||{};
        const due=subscription.dues?.[0]||null;

        return`
            <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-base font-bold text-slate-700">
                                ${esc(user.name||'—')}
                            </p>

                            <p class="mt-0.5 font-mono text-[10px] text-slate-400">
                                ${esc(member.member_code||'—')}
                            </p>
                        </div>

                        ${AdminUI.statusBadge(
                            subscription.is_active
                                ?'active'
                                :'inactive'
                        )}
                    </div>
                </div>

                <div class="space-y-3 p-4">
                    <div>
                        <p class="text-[10px] text-slate-400">Plan</p>
                        <p class="mt-1 text-base font-semibold text-slate-600">
                            ${esc(plan.name||'—')}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[10px] text-slate-400">Monthly</p>
                            <p class="mt-1 text-base font-semibold text-slate-700">
                                ${money(plan.amount||0)}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Start</p>
                            <p class="mt-1 text-sm font-medium text-slate-600">
                                ${AdminUI.formatDate(subscription.start_date)}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-md bg-slate-50 p-3">
                        <p class="text-[10px] text-slate-400">
                            Selected Month Due
                        </p>

                        ${
                            due
                                ?`
                                    <div class="mt-1 flex items-center justify-between">
                                        <span class="font-bold text-slate-700">
                                            ${money(due.amount)}
                                        </span>

                                        ${AdminUI.statusBadge(due.status)}
                                    </div>
                                `
                                :`
                                    <p class="mt-1 text-sm text-slate-400">
                                        Not generated
                                    </p>
                                `
                        }
                    </div>
                </div>

                <div class="flex justify-end gap-1 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${
                        !due&&subscription.is_active
                            ?`
                                <button type="button"
                                    onclick="generateDue(${subscription.id})"
                                    class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700">
                                    Generate Due
                                </button>
                            `
                            :''
                    }

                    ${
                        subscription.is_active
                            ?`
                                <button type="button"
                                    onclick="deactivateSubscription(${subscription.id})"
                                    class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600">
                                    Deactivate
                                </button>
                            `
                            :''
                    }
                </div>
            </article>
        `;
    }).join('');
}

window.openSubscriptionModal=function(){
    $('subscriptionForm').reset();

    AdminUI.clearError('subscriptionError');
    AdminUI.clearFieldErrors('subscriptionForm');

    AdminUI.openModal(
        'subscriptionModal'
    );

    window.initDatePickers?.();

    const today=new Date()
        .toISOString()
        .slice(0,10);

    $('startDate')._flatpickr
        ?.setDate(today,false);
};

window.openBulkModal=function(){
    AdminUI.clearError('bulkError');

    $('bulkYear').value=
        $('filterYear').value;

    $('bulkMonth').value=
        $('filterMonth').value;

    AdminUI.openModal('bulkModal');
};

$('subscriptionForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError('subscriptionError');
        AdminUI.clearFieldErrors('subscriptionForm');

        if(!AdminUI.validateForm(
            'subscriptionForm',
            {
                memberId:'Member is required.',
                planId:'Subscription plan is required.',
                startDate:'Start date is required.'
            }
        )){
            return;
        }

        const button=$('saveSubscriptionButton');

        AdminUI.setLoading(
            button,
            'Assigning...'
        );

        try{
            await api(
                '/api/finance/subscriptions',
                {
                    method:'POST',
                    body:JSON.stringify({
                        member_id:Number(
                            $('memberId').value
                        ),
                        subscription_plan_id:Number(
                            $('planId').value
                        ),
                        start_date:$('startDate').value
                    })
                }
            );

            AdminUI.closeModal(
                'subscriptionModal'
            );

            Toast.success(
                'Subscription assigned successfully.'
            );

            await Promise.all([
                loadSubscriptions(1),
                loadSummary()
            ]);

        }catch(error){
            if(!AdminUI.showValidationErrors(
                'subscriptionForm',
                error,
                {
                    member_id:'memberId',
                    subscription_plan_id:'planId',
                    start_date:'startDate'
                }
            )){
                AdminUI.showError(
                    'subscriptionError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(button);
        }
    }
);

$('bulkForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const button=$('generateBulkButton');

        AdminUI.setLoading(
            button,
            'Generating...'
        );

        try{
            const response=await api(
                '/api/finance/subscriptions/generate-bulk',
                {
                    method:'POST',
                    body:JSON.stringify({
                        year:Number(
                            $('bulkYear').value
                        ),
                        month:Number(
                            $('bulkMonth').value
                        )
                    })
                }
            );

            AdminUI.closeModal('bulkModal');

            const data=response.data||{};

            Toast.success(
                `${data.created||0} due generated, ${data.existing||0} already existed.`
            );

            await Promise.all([
                loadSubscriptions(1),
                loadSummary()
            ]);

        }catch(error){
            AdminUI.showError(
                'bulkError',
                AdminUI.extractError(error)
            );

        }finally{
            AdminUI.resetLoading(button);
        }
    }
);

window.generateDue=function(id){
    AdminUI.request(
        `/api/finance/subscriptions/${id}/generate-due`,
        {
            method:'POST',
            data:{
                year:Number(
                    $('filterYear').value
                ),
                month:Number(
                    $('filterMonth').value
                )
            },

            confirmation:{
                title:'Generate Monthly Due?',
                message:'Generate due for this member for the selected month?',
                confirmText:'Generate',
                type:'info'
            },

            successMessage:
                'Monthly due generated successfully.',

            onSuccess:async()=>{
                await Promise.all([
                    loadSubscriptions(currentPage),
                    loadSummary()
                ]);
            }
        }
    );
};

window.deactivateSubscription=function(id){
    AdminUI.request(
        `/api/finance/subscriptions/${id}/deactivate`,
        {
            method:'POST',
            data:{},

            confirmation:{
                title:'Deactivate Subscription?',
                message:'This member will stop receiving future monthly dues from this subscription.',
                confirmText:'Deactivate',
                type:'danger'
            },

            successMessage:
                'Subscription deactivated successfully.',

            onSuccess:async()=>{
                await loadSubscriptions(
                    currentPage
                );
            }
        }
    );
};

function reload(){
    Promise.all([
        loadSubscriptions(1),
        loadSummary()
    ]);
}

function init(){
    initPeriodOptions();

    window.initDatePickers?.();
$('filterSearch').addEventListener(
        'input',
        AdminUI.debounce(reload)
    );

    $('filterStatus').addEventListener(
        'change',
        reload
    );

    $('filterYear').addEventListener(
        'change',
        reload
    );

    $('filterMonth').addEventListener(
        'change',
        reload
    );

    loadOptions();

    reload();
}

document.readyState==='loading'
    ?document.addEventListener(
        'DOMContentLoaded',
        init
    )
    :init();
</script>
@endpush