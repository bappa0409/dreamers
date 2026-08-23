@extends('layouts.admin')

@section('title','Subscription Plans')
@section('page-title','Subscription Plans')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-card-checklist"></i>
            </div>

            <div>
                <h1 class="text-lg font-bold text-slate-800">
                    Subscription Plans
                </h1>

                <p class="mt-1 text-xs text-slate-500">
                    Manage monthly subscription amount and due date rules.
                </p>
            </div>
        </div>

        <button type="button"
            onclick="openPlanModal()"
            class="inline-flex h-9 w-fit cursor-pointer items-center gap-2 rounded-md bg-indigo-600 px-3 text-xs font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Plan
        </button>
    </div>

    <div class="rounded-md border border-slate-200 bg-white">
        <div class="grid gap-3 border-b border-slate-200 p-4 md:grid-cols-[1fr_180px_auto]">
            <div class="relative">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                <input id="searchInput"
                    type="text"
                    class="app-input !pl-9"
                    placeholder="Search plan...">
            </div>

            <select id="statusFilter" class="app-input">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <button type="button"
                onclick="clearFilters()"
                class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                Clear
            </button>
        </div>

        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Plan</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-slate-500">Amount</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Due Day</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Default</th>
                        <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase text-slate-500">Status</th>
                        <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase text-slate-500">Action</th>
                    </tr>
                </thead>

                <tbody id="planTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-xs text-slate-400">
                            Loading plans...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="planMobileGrid" class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 md:hidden"></div>
    </div>
</div>

<div id="planModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 id="planModalTitle" class="text-base font-bold text-slate-800">
                    Add Subscription Plan
                </h3>
                <p class="text-xs text-slate-400">
                    Configure monthly subscription rules.
                </p>
            </div>

            <button type="button"
                onclick="AdminUI.closeModal('planModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="planForm" novalidate>
            <div class="space-y-4 p-5">
                <div id="planError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-600">
                </div>

                <div>
                    <label class="form-label">
                        Plan Name <span class="text-red-500">*</span>
                    </label>

                    <input id="planName"
                        type="text"
                        maxlength="100"
                        class="app-input">

                    <p data-field-error="planName"
                        class="mt-1 hidden text-xs text-red-600"></p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">
                            Monthly Amount <span class="text-red-500">*</span>
                        </label>

                        <input id="planAmount"
                            type="number"
                            min="0.01"
                            step="0.01"
                            class="app-input">

                        <p data-field-error="planAmount"
                            class="mt-1 hidden text-xs text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">
                            Due Day <span class="text-red-500">*</span>
                        </label>

                        <input id="planDueDay"
                            type="number"
                            min="1"
                            max="31"
                            class="app-input">

                        <p data-field-error="planDueDay"
                            class="mt-1 hidden text-xs text-red-600"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                        <input id="planDefault"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                        <div>
                            <p class="text-xs font-semibold text-slate-700">
                                Default Plan
                            </p>

                            <p class="text-[10px] text-slate-400">
                                Used for automatic subscription assignment.
                            </p>
                        </div>
                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                        <input id="planActive"
                            type="checkbox"
                            checked
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                        <div>
                            <p class="text-xs font-semibold text-slate-700">
                                Active
                            </p>

                            <p class="text-[10px] text-slate-400">
                                Available for new subscriptions.
                            </p>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="form-label">
                        Description
                    </label>

                    <textarea id="planDescription"
                        rows="3"
                        maxlength="1000"
                        class="app-input resize-none"></textarea>

                    <p data-field-error="planDescription"
                        class="mt-1 hidden text-xs text-red-600"></p>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                    onclick="AdminUI.closeModal('planModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="savePlanButton"
                    type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Save Plan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));

let plans=[];
let editingPlan=null;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>`${currency}${Number(value||0).toLocaleString('en-BD',{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

async function loadPlans(){
    const body=$('planTableBody');
    const grid=$('planMobileGrid');

    body.innerHTML=AdminUI.loadingState(
        'Loading plans...',
        6
    );

    const params=new URLSearchParams();

    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);

    try{
        const response=await api(
            `/api/finance/subscription-plans?${params}`
        );

        plans=response.data||[];

        renderPlans();

    }catch(error){
        body.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            6
        );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-6 text-center text-xs text-red-600">
                ${esc(AdminUI.extractError(error))}
            </div>
        `;
    }
}

function renderPlans(){
    const body=$('planTableBody');
    const grid=$('planMobileGrid');

    if(!plans.length){
        body.innerHTML=AdminUI.emptyState(
            'No plans found.',
            6
        );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs text-slate-400">
                No plans found.
            </div>
        `;

        return;
    }

    body.innerHTML=plans.map(plan=>`
        <tr class="hover:bg-slate-50">
            <td class="px-4 py-3">
                <p class="text-xs font-semibold text-slate-700">
                    ${esc(plan.name)}
                </p>

                <p class="mt-0.5 max-w-[260px] truncate text-[10px] text-slate-400">
                    ${esc(plan.description||'—')}
                </p>
            </td>

            <td class="px-4 py-3 text-right text-xs font-bold text-slate-700">
                ${money(plan.amount)}
            </td>

            <td class="px-4 py-3 text-xs text-slate-600">
                Day ${plan.due_day}
            </td>

            <td class="px-4 py-3">
                ${
                    plan.is_default
                        ?`<span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700">Default</span>`
                        :`<span class="text-xs text-slate-400">—</span>`
                }
            </td>

            <td class="px-4 py-3">
                ${AdminUI.statusBadge(
                    plan.is_active
                        ?'active'
                        :'inactive'
                )}
            </td>

            <td class="px-4 py-3">
                <div class="flex justify-end gap-1">
                    <button type="button"
                        onclick="editPlan(${plan.id})"
                        class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                        Edit
                    </button>

                    ${
                        !plan.is_default
                            ?`
                                <button type="button"
                                    onclick="deletePlan(${plan.id})"
                                    class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                                    Delete
                                </button>
                            `
                            :''
                    }
                </div>
            </td>
        </tr>
    `).join('');

    grid.innerHTML=plans.map(plan=>`
        <article class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-bold text-slate-700">
                        ${esc(plan.name)}
                    </p>

                    <p class="mt-1 text-lg font-bold text-indigo-600">
                        ${money(plan.amount)}
                    </p>
                </div>

                ${AdminUI.statusBadge(
                    plan.is_active
                        ?'active'
                        :'inactive'
                )}
            </div>

            <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">
                <span>Due: Day ${plan.due_day}</span>

                ${
                    plan.is_default
                        ?`<span class="rounded bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-700">Default</span>`
                        :''
                }
            </div>

            <p class="mt-3 line-clamp-2 text-xs text-slate-400">
                ${esc(plan.description||'No description')}
            </p>

            <div class="mt-4 flex justify-end gap-1 border-t border-slate-100 pt-3">
                <button type="button"
                    onclick="editPlan(${plan.id})"
                    class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700">
                    Edit
                </button>

                ${
                    !plan.is_default
                        ?`
                            <button type="button"
                                onclick="deletePlan(${plan.id})"
                                class="rounded-md border border-red-200 bg-red-50 px-2 py-1 text-[11px] font-semibold text-red-600">
                                Delete
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

    $('planForm').reset();
    $('planActive').checked=true;
    $('planDefault').checked=false;

    $('planModalTitle').textContent=
        'Add Subscription Plan';

    AdminUI.clearError('planError');
    AdminUI.clearFieldErrors('planForm');

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

    $('planName').value=plan.name||'';
    $('planAmount').value=plan.amount||'';
    $('planDueDay').value=plan.due_day||1;
    $('planDefault').checked=Boolean(plan.is_default);
    $('planActive').checked=Boolean(plan.is_active);
    $('planDescription').value=plan.description||'';

    $('planModalTitle').textContent=
        'Edit Subscription Plan';

    AdminUI.clearError('planError');
    AdminUI.clearFieldErrors('planForm');

    AdminUI.openModal('planModal');
};

$('planForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError('planError');
        AdminUI.clearFieldErrors('planForm');

        if(!AdminUI.validateRequired(
            'planForm',
            {
                planName:'Plan name is required.',
                planAmount:'Monthly amount is required.',
                planDueDay:'Due day is required.'
            }
        )){
            return;
        }

        const amount=Number(
            $('planAmount').value
        );

        const dueDay=Number(
            $('planDueDay').value
        );

        if(!Number.isFinite(amount)||amount<=0){
            AdminUI.showFieldError(
                'planAmount',
                'Monthly amount must be greater than zero.'
            );
            return;
        }

        if(
            !Number.isInteger(dueDay)||
            dueDay<1||
            dueDay>31
        ){
            AdminUI.showFieldError(
                'planDueDay',
                'Due day must be between 1 and 31.'
            );
            return;
        }

        if(
            $('planDefault').checked&&
            !$('planActive').checked
        ){
            AdminUI.showFieldError(
                'planActive',
                'Default plan must remain active.'
            );
            return;
        }

        const data={
            name:$('planName').value.trim(),
            amount,
            due_day:dueDay,
            is_default:$('planDefault').checked,
            is_active:$('planActive').checked,
            description:$('planDescription').value.trim()||null
        };

        const button=$('savePlanButton');

        AdminUI.setLoading(
            button,
            editingPlan
                ?'Updating...'
                :'Saving...'
        );

        try{
            await api(
                editingPlan
                    ?`/api/finance/subscription-plans/${editingPlan.id}`
                    :'/api/finance/subscription-plans',
                {
                    method:
                        editingPlan
                            ?'PUT'
                            :'POST',

                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal('planModal');

            Toast.success(
                editingPlan
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
                    is_default:'planDefault',
                    is_active:'planActive',
                    description:'planDescription'
                }
            )){
                AdminUI.showError(
                    'planError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(button);
        }
    }
);

window.deletePlan=function(id){
    AdminUI.deleteRequest(
        `/api/finance/subscription-plans/${id}`,
        {
            message:'Delete this unused subscription plan?',
            successMessage:'Subscription plan deleted successfully.',
            onSuccess:loadPlans
        }
    );
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('statusFilter').value='';
    loadPlans();
};

function init(){
    AdminUI.bindFieldValidation(
        'planForm'
    );

    $('searchInput').addEventListener(
        'input',
        AdminUI.debounce(
            loadPlans
        )
    );

    $('statusFilter').addEventListener(
        'change',
        loadPlans
    );

    loadPlans();
}

document.readyState==='loading'
    ?document.addEventListener(
        'DOMContentLoaded',
        init
    )
    :init();
</script>
@endpush