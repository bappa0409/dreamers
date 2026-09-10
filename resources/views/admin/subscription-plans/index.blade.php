@extends('layouts.admin')

@section('title','Subscription Plans')
@section('page_title','Subscription Plans')

@section('content')
<div class="space-y-3">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                <i class="bi bi-card-checklist"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Subscription Plans</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage the monthly amount, due day and late fine rules members are billed under.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
            <button
                type="button"
                onclick="openPlanModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-plus-lg text-[11px]"></i>
                Add Plan
            </button>
        @endif
    </div>

    {{-- Search Plans --}}
<div class="rounded-md border border-slate-200 bg-white p-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        {{-- Header --}}
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-base"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">
                    Search Plans
                </p>
                <p class="hidden text-[11px] text-slate-500 sm:block">
                    Search by plan name.
                </p>
            </div>
        </div>

        <div class="flex w-full items-center gap-2 lg:w-auto">

            {{-- Search --}}
            <div class="relative min-w-0 flex-1 lg:w-[280px]">
                <i
                    class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                <input id="searchInput" type="text" placeholder="Search plans..."
                    class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
            </div>

            {{-- Filter --}}
            <button type="button" onclick="toggleFilters()" id="filterButton"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 2xl:text-sm">
                <i class="bi bi-funnel text-xs"></i>
                <span>Filter</span>
                <i id="filterChevron" class="bi bi-chevron-down text-[10px]"></i>
            </button>

            {{-- Clear --}}
            <button type="button" onclick="clearFilters()"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 2xl:text-sm">
                <i class="bi bi-arrow-counterclockwise text-[10px]"></i>
                <span class="hidden sm:inline">Reset</span>
            </button>
        </div>
    </div>


    {{-- Filter Options --}}
    <div id="filterPanel" class="mt-3 hidden border-t border-slate-100 pt-3">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[180px_auto]">

            {{-- Status --}}
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">
                    Status
                </label>

                <select id="statusFilter"
                    class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

        </div>
    </div>
</div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full table-fixed text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[24%] px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Plan</th>
                        <th class="w-[13%] px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Amount</th>
                        <th class="w-[10%] px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Due Day</th>
                        <th class="w-[18%] px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Late Fine</th>
                        <th class="w-[10%] px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Default</th>
                        <th class="w-[10%] px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="w-[15%] px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="planTableBody">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-xs 2xl:text-sm text-center text-slate-500">
                            Loading plans...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="planMobileGrid" class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 md:hidden">
            <div class="col-span-full px-4 py-10 text-center text-sm text-slate-500">Loading plans...</div>
        </div>
    </div>
</div>

{{-- Plan Modal --}}
<div id="planModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                    <i class="bi bi-card-checklist"></i>
                </div>

                <div>
                    <h2 id="planModalTitle" class="text-sm font-semibold text-slate-800">Add Subscription Plan</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Configure the monthly amount and due date rules.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('planModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="planForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-card-checklist"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Plan Information</h3>
                            <p class="text-[11px] text-slate-500">Enter the subscription plan details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="form-label">Plan Name <span class="text-red-500">*</span></label>

                            <input id="planName" type="text" maxlength="100" class="app-input">

                            <p data-field-error="planName" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Monthly Amount <span class="text-red-500">*</span></label>

                            <input id="planAmount" type="number" min="0.01" step="0.01" class="app-input">

                            <p data-field-error="planAmount" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Due Day <span class="text-red-500">*</span></label>

                            <input id="planDueDay" type="number" min="1" max="31" class="app-input">

                            <p class="mt-1 text-[10px] text-slate-500">Day of month the payment is due (1&ndash;31).</p>

                            <p data-field-error="planDueDay" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Description</label>

                        <textarea id="planDescription" rows="3" maxlength="1000" class="app-input resize-none"></textarea>

                        <p data-field-error="planDescription" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 p-4">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Late Fine Rules</h3>
                            <p class="text-[11px] text-slate-500">
                                Applied automatically when a payment is overdue.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Fine Type</label>

                            <select id="planFineType" class="app-input cursor-pointer">
                                <option value="none">No Fine</option>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        <div id="planFineValueField">
                            <label class="form-label">Fine Value</label>

                            <input id="planFineValue" type="number" min="0" step="0.01" class="app-input">

                            <p data-field-error="planFineValue" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Grace Days</label>

                            <input id="planGraceDays" type="number" min="0" max="365" class="app-input">

                            <p class="mt-1 text-[10px] text-slate-500">Days after due date before a fine applies.</p>

                            <p data-field-error="planGraceDays" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-toggles"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Plan Settings</h3>
                            <p class="text-[11px] text-slate-500">Control availability and default assignment.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <input id="planDefault" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                            <div>
                                <p class="text-xs 2xl:text-sm font-semibold text-slate-700">Default Plan</p>
                                <p class="text-[10px] text-slate-500">Used for automatic subscription assignment.</p>
                            </div>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <input id="planActive" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                            <div>
                                <p class="text-xs 2xl:text-sm font-semibold text-slate-700">Active</p>
                                <p class="text-[10px] text-slate-500">Available for new subscriptions.</p>
                            </div>
                        </label>
                    </div>
                </section>

                <div id="planError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('planModal')"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    <i class="bi bi-x-lg"></i>
                    Close
                </button>

                <button
                    id="savePlanButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                >
                    <i class="bi bi-check2-circle"></i>
                    <span id="savePlanButtonLabel">Save Plan</span>
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

<script>
const currency=@json(setting('currency_symbol','৳'));

const canEditPlan=@json(auth()->user()->hasPermission('Finance.update'));
const canDeletePlan=@json(auth()->user()->hasPermission('Finance.delete'));

let plans=[];
let editingPlan=null;

const el={
    tableBody:document.getElementById('planTableBody'),
    mobileGrid:document.getElementById('planMobileGrid'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('planForm'),
    saveButton:document.getElementById('savePlanButton'),
    saveButtonLabel:document.getElementById('savePlanButtonLabel'),
    fineType:document.getElementById('planFineType'),
    fineValueField:document.getElementById('planFineValueField')
};

const money=value=>`${currency}${Number(value||0).toLocaleString('en-US',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

function fineLabel(plan){
    if(!plan.fine_type||plan.fine_type==='none'){
        return`<span class="text-xs 2xl:text-sm text-slate-500">No fine</span>`;
    }

    const value=plan.fine_type==='percentage'
        ?`${Number(plan.fine_value||0)}%`
        :money(plan.fine_value);

    return`
        <span class="text-xs 2xl:text-sm font-semibold text-slate-700">${value}</span>
        <span class="block text-[10px] text-slate-500">
            after ${Number(plan.grace_days||0)} grace day${Number(plan.grace_days||0)===1?'':'s'}
        </span>
    `;
}

function toggleFineValueVisibility(){
    el.fineValueField.classList.toggle(
        'hidden',
        el.fineType.value==='none'
    );
}

el.fineType.addEventListener(
    'change',
    toggleFineValueVisibility
);

async function loadPlans(){
    el.tableBody.innerHTML=AdminUI.loadingState('Loading plans...',7);

    el.mobileGrid.innerHTML=`
        <div class="col-span-full px-4 py-10 text-center text-sm text-slate-500">Loading plans...</div>
    `;

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value
    });

    try{
        const response=await api(`/api/finance/subscription-plans?${query}`);

        plans=Array.isArray(response.data)
            ?response.data
            :[];

        renderPlans();
    }catch(error){
        el.tableBody.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            7
        );

        el.mobileGrid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-6 text-center text-sm text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        `;
    }
}

function renderPlans(){
    if(!plans.length){
        el.tableBody.innerHTML=AdminUI.emptyState(
            'No subscription plans found.',
            7
        );

        el.mobileGrid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs 2xl:text-sm text-slate-500">
                No subscription plans found.
            </div>
        `;

        return;
    }

    el.tableBody.innerHTML=plans.map(plan=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="min-w-0 px-4 py-3">
                <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-800" title="${AdminUI.escapeHtml(plan.name??'')}">
                    ${AdminUI.escapeHtml(plan.name??'N/A')}
                </p>

                <p class="mt-1 max-w-[220px] truncate text-[10px] text-slate-500">
                    ${AdminUI.escapeHtml(plan.description||'No description')}
                </p>
            </td>

            <td class="px-4 py-3 text-right text-xs 2xl:text-sm font-bold text-slate-700">
                ${money(plan.amount)}
            </td>

            <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
                Day ${plan.due_day}
            </td>

            <td class="px-4 py-3">
                ${fineLabel(plan)}
            </td>

            <td class="px-4 py-3">
                ${
                    plan.is_default
                        ?`<span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700">Default</span>`
                        :`<span class="text-xs 2xl:text-sm text-slate-500">&mdash;</span>`
                }
            </td>

            <td class="px-4 py-3">
                ${AdminUI.statusBadge(plan.is_active?'active':'inactive')}
            </td>

            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                    ${
                        canEditPlan
                            ?`
                                <button
                                    type="button"
                                    onclick="editPlan(${plan.id})"
                                    title="Edit Plan"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                >
                                    <i class="bi bi-pencil-square text-sm"></i>
                                </button>
                            `
                            :''
                    }

                    ${
                        canDeletePlan&&!plan.is_default
                            ?`
                                <button
                                    type="button"
                                    onclick="deletePlan(${plan.id})"
                                    title="Delete Plan"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                >
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            `
                            :''
                    }
                </div>
            </td>
        </tr>
    `).join('');

    el.mobileGrid.innerHTML=plans.map(plan=>`
        <article class="rounded-md border border-slate-200 bg-white px-5 py-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-base font-bold text-slate-700" title="${AdminUI.escapeHtml(plan.name??'')}">
                        ${AdminUI.escapeHtml(plan.name??'N/A')}
                    </p>

                    <p class="mt-1 text-lg font-bold text-indigo-600">
                        ${money(plan.amount)}
                    </p>
                </div>

                ${AdminUI.statusBadge(plan.is_active?'active':'inactive')}
            </div>

            <div class="mt-3 flex flex-wrap items-center gap-2 text-sm text-slate-500">
                <span>Due: Day ${plan.due_day}</span>

                ${
                    plan.is_default
                        ?`<span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Default</span>`
                        :''
                }
            </div>

            <div class="mt-2 text-sm text-slate-500">
                ${fineLabel(plan)}
            </div>

            <p class="mt-3 line-clamp-2 text-sm text-slate-500">
                ${AdminUI.escapeHtml(plan.description||'No description')}
            </p>

            <div class="mt-4 flex justify-end gap-1 border-t border-slate-100 pt-3">
                ${
                    canEditPlan
                        ?`
                            <button
                                type="button"
                                onclick="editPlan(${plan.id})"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600"
                            >
                                <i class="bi bi-pencil-square text-sm"></i>
                            </button>
                        `
                        :''
                }

                ${
                    canDeletePlan&&!plan.is_default
                        ?`
                            <button
                                type="button"
                                onclick="deletePlan(${plan.id})"
                                class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600"
                            >
                                <i class="bi bi-trash text-sm"></i>
                            </button>
                        `
                        :''
                }
            </div>
        </article>
    `).join('');
}

window.openPlanModal=function(){
    editingPlan=null;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('planError');
    AdminUI.clearFieldErrors('planForm');

    document.getElementById('planModalTitle').textContent='Add Subscription Plan';
    el.saveButtonLabel.textContent='Save Plan';

    document.getElementById('planFineType').value='none';
    document.getElementById('planGraceDays').value=0;
    document.getElementById('planActive').checked=true;
    document.getElementById('planDefault').checked=false;

    toggleFineValueVisibility();

    AdminUI.openModal('planModal');
};

window.editPlan=function(id){
    const plan=plans.find(
        item=>Number(item.id)===Number(id)
    );

    if(!plan){
        Toast.error('Plan not found.');
        return;
    }

    editingPlan=plan;

    AdminUI.clearError('planError');
    AdminUI.clearFieldErrors('planForm');

    document.getElementById('planName').value=plan.name||'';
    document.getElementById('planAmount').value=plan.amount||'';
    document.getElementById('planDueDay').value=plan.due_day||1;
    document.getElementById('planFineType').value=plan.fine_type||'none';
    document.getElementById('planFineValue').value=plan.fine_value||0;
    document.getElementById('planGraceDays').value=plan.grace_days||0;
    document.getElementById('planDefault').checked=Boolean(plan.is_default);
    document.getElementById('planActive').checked=Boolean(plan.is_active);
    document.getElementById('planDescription').value=plan.description||'';

    document.getElementById('planModalTitle').textContent='Edit Subscription Plan';
    el.saveButtonLabel.textContent='Update Plan';

    toggleFineValueVisibility();

    AdminUI.openModal('planModal');
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('planError');
    AdminUI.clearFieldErrors('planForm');

    if(!AdminUI.validateForm(
        'planForm',
        {
            planName:'Plan name is required.',
            planAmount:'Monthly amount is required.',
            planDueDay:'Due day is required.'
        }
    )){
        return;
    }

    const amount=Number(document.getElementById('planAmount').value);
    const dueDay=Number(document.getElementById('planDueDay').value);
    const fineType=document.getElementById('planFineType').value;
    const fineValue=Number(document.getElementById('planFineValue').value||0);
    const graceDays=Number(document.getElementById('planGraceDays').value||0);
    const isDefault=document.getElementById('planDefault').checked;
    const isActive=document.getElementById('planActive').checked;

    if(!Number.isFinite(amount)||amount<=0){
        AdminUI.showFieldError('planAmount','Monthly amount must be greater than zero.');
        return;
    }

    if(!Number.isInteger(dueDay)||dueDay<1||dueDay>31){
        AdminUI.showFieldError('planDueDay','Due day must be between 1 and 31.');
        return;
    }

    if(fineType!=='none'&&fineValue<=0){
        AdminUI.showFieldError('planFineValue','Fine value must be greater than zero when a fine is enabled.');
        return;
    }

    if(fineType==='percentage'&&fineValue>100){
        AdminUI.showFieldError('planFineValue','Percentage fine cannot exceed 100%.');
        return;
    }

    if(!Number.isInteger(graceDays)||graceDays<0||graceDays>365){
        AdminUI.showFieldError('planGraceDays','Grace days must be between 0 and 365.');
        return;
    }

    if(isDefault&&!isActive){
        AdminUI.showFieldError('planActive','Default plan must remain active.');
        return;
    }

    const data={
        name:document.getElementById('planName').value.trim(),
        amount,
        due_day:dueDay,
        fine_type:fineType,
        fine_value:fineType==='none'?0:fineValue,
        grace_days:graceDays,
        is_default:isDefault,
        is_active:isActive,
        description:document.getElementById('planDescription').value.trim()||null
    };

    AdminUI.setLoading(
        el.saveButton,
        editingPlan?'Updating...':'Saving...'
    );

    try{
        const editing=Boolean(editingPlan);

        await api(
            editing
                ?`/api/finance/subscription-plans/${editingPlan.id}`
                :'/api/finance/subscription-plans',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('planModal');

        Toast.success(
            editing
                ?'Subscription plan updated successfully.'
                :'Subscription plan created successfully.'
        );

        await loadPlans();
    }catch(error){
        if(!AdminUI.showValidationErrors(
            'planForm',
            error,
            {
                name:'planName',
                amount:'planAmount',
                due_day:'planDueDay',
                fine_type:'planFineType',
                fine_value:'planFineValue',
                grace_days:'planGraceDays',
                is_default:'planDefault',
                is_active:'planActive',
                description:'planDescription'
            }
        )){
            AdminUI.showError('planError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

window.deletePlan=function(id){
    AdminUI.deleteRequest(
        `/api/finance/subscription-plans/${id}`,
        {
            title:'Delete Plan?',
            message:'This subscription plan will be permanently deleted. Plans already assigned to members cannot be deleted \u2014 deactivate them instead.',
            confirmText:'Delete',
            successMessage:'Subscription plan deleted successfully.',
            onSuccess:loadPlans
        }
    );
};

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';
    loadPlans();
};

async function initSubscriptionPlansPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initSubscriptionPlansPage,50);
        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadPlans()
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadPlans()
    );

    await loadPlans();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initSubscriptionPlansPage
    );
}else{
    initSubscriptionPlansPage();
}
</script>
@endsection