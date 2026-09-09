@extends('layouts.admin')

@section('title','Approval Workflows')
@section('page_title','Approval Workflows')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-diagram-3"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Approval Workflows</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Configure sequential approval steps for system modules.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('admin.approvals') }}"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                <i class="bi bi-arrow-left text-[11px]"></i>
                Back to Approval Requests
            </a>

            @if(auth()->user()->hasPermission('Approval.update'))
                <button type="button" onclick="openWorkflowModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="bi bi-plus-lg text-[11px]"></i>
                    Add Workflow
                </button>
            @endif
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Workflows</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by module or action</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search workflows..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer border border-slate-300 bg-white px-3  text-xs 2xl:text-sm text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All Status</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3  text-xs 2xl:text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <table class="w-full table-fixed text-sm">
            <thead class="border-b border-slate-200 bg-slate-50">
                <tr>
                    <th class="w-[18%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Module</th>
                    <th class="w-[14%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Action</th>
                    <th class="w-[40%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Approval Steps</th>
                    <th class="w-[12%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                    <th class="w-[16%] px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                </tr>
            </thead>

            <tbody id="workflowTable">
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-base text-slate-400">Loading workflows...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="workflowModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-diagram-3"></i>
                </div>
                <div>
                    <h2 id="workflowModalTitle" class="text-sm font-semibold text-slate-800">Add Workflow</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Select module, action and up to 3 approvers.</p>
                </div>
            </div>

            <button type="button" onclick="closeWorkflowModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="workflowForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-diagram-3"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Workflow Trigger</h3>
                            <p class="text-[11px] text-slate-400">Which module and action this workflow applies to.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Module *</label>
                            <select id="module" class="app-input cursor-pointer">
                                <option value="">Select Module</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Action *</label>
                            <select id="action" class="app-input cursor-pointer">
                                <option value="">Select Action</option>
                            </select>
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                            <i class="bi bi-list-ol"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Approval Steps</h3>
                            <p class="text-[11px] text-slate-400">Step 1 is required. Step 2 and Step 3 are optional.</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-[70px_1fr] items-center gap-3">
                            <div class="text-sm font-semibold text-slate-600">Step 1</div>
                            <select id="approver1" class="approver-select app-input cursor-pointer">
                                <option value="">Select Approver</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-[70px_1fr] items-center gap-3">
                            <div class="text-sm font-semibold text-slate-600">Step 2</div>
                            <select id="approver2" class="approver-select app-input cursor-pointer">
                                <option value="">Optional</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-[70px_1fr] items-center gap-3">
                            <div class="text-sm font-semibold text-slate-600">Step 3</div>
                            <select id="approver3" class="approver-select app-input cursor-pointer">
                                <option value="">Optional</option>
                            </select>
                        </div>
                    </div>
                </section>

                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-md border border-slate-200 p-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-toggle2-on"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Active Workflow</p>
                            <p class="mt-0.5 text-[10px] text-slate-400">New requests will use this workflow.</p>
                        </div>
                    </div>

                    <input id="isActive" type="checkbox" checked class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600">
                </label>

                <div id="workflowError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeWorkflowModal()" class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>

                <button id="saveWorkflowButton" type="submit" class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    <span class="workflow-save-label">Save Workflow</span>
                </button>
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
let workflows=[];
let users=[];
let editingWorkflow=null;
let wiredModuleActions={};

const canUpdate=@json(auth()->user()->hasPermission('Approval.update'));

const el={
    table:document.getElementById('workflowTable'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('workflowForm'),
    module:document.getElementById('module'),
    action:document.getElementById('action'),
    approver1:document.getElementById('approver1'),
    approver2:document.getElementById('approver2'),
    approver3:document.getElementById('approver3'),
    isActive:document.getElementById('isActive'),
    saveButton:document.getElementById('saveWorkflowButton')
};

async function loadWorkflows(){
    el.table.innerHTML=AdminUI.loadingState('Loading workflows...',5);

    try{
        const response=await api('/api/approval-workflows');
        workflows=Array.isArray(response.data)?response.data:[];
        wiredModuleActions=response.wired_module_actions??{};
        renderModuleOptions();
        renderWorkflows();
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),5);
    }
}

function renderModuleOptions(){
    const current=el.module.value;

    el.module.innerHTML='<option value="">Select Module</option>';

    Object.keys(wiredModuleActions).forEach(module=>{
        const option=document.createElement('option');
        option.value=module;
        option.textContent=module;
        el.module.appendChild(option);
    });

    el.module.value=current;
    renderActionOptions(el.module.value);
}

function renderActionOptions(module){
    const current=el.action.value;
    const actions=wiredModuleActions[module]??[];

    el.action.innerHTML='<option value="">Select Action</option>';

    actions.forEach(action=>{
        const option=document.createElement('option');
        option.value=action;
        option.textContent=AdminUI.titleCase(action);
        el.action.appendChild(option);
    });

    el.action.value=actions.includes(current)?current:'';
}

el.module.addEventListener('change',()=>renderActionOptions(el.module.value));

async function loadUsers(){
    try{
        const response=await api(
            '/api/approval-workflows/approvers'
        );

        users=Array.isArray(response.data)
            ?response.data
            :[];

        renderApproverOptions();
    }catch(error){
        users=[];
        renderApproverOptions();
    }
}

function filteredWorkflows(){
    const search=el.search.value.trim().toLowerCase();
    const status=el.status.value;

    return workflows.filter(workflow=>{
        const matchesSearch=!search||`${workflow.module} ${workflow.action}`.toLowerCase().includes(search);
        const matchesStatus=status===''||String(Number(Boolean(workflow.is_active)))===status;
        return matchesSearch&&matchesStatus;
    });
}

function renderWorkflows(){
    const rows=filteredWorkflows();

    if(!rows.length){
        el.table.innerHTML=AdminUI.emptyState('No approval workflows found.',5);
        return;
    }

    el.table.innerHTML=rows.map(workflow=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="px-4 py-3">
                <p class="truncate  text-xs 2xl:text-sm font-semibold text-slate-800">
                    ${AdminUI.escapeHtml(workflow.module)}
                </p>
            </td>

            <td class="px-4 py-3">
                <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600">
                    ${AdminUI.escapeHtml(AdminUI.titleCase(workflow.action))}
                </span>
            </td>

            <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-1">
                    ${(workflow.steps??[]).map(step=>`
                        <span class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700">
                            <span class="text-indigo-400">${step.step_no}.</span>
                            ${AdminUI.escapeHtml(step.approver?.name??'Unknown')}
                        </span>
                    `).join('')}
                </div>
            </td>

            <td class="px-4 py-3">
                ${
                    workflow.is_active
                        ?'<span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Active</span>'
                        :'<span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">Inactive</span>'
                }
            </td>

            <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-1">
                    ${
                        canUpdate
                            ?`
                                <button type="button" onclick="editWorkflow(${workflow.id})" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 hover:bg-indigo-100" title="Edit">
                                    <i class="bi bi-pencil-square text-sm"></i>
                                </button>

                                <button type="button" onclick="toggleWorkflow(${workflow.id})" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md ${workflow.is_active?'bg-amber-50 text-amber-700 hover:bg-amber-100':'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'}" title="${workflow.is_active?'Deactivate':'Activate'}">
                                    <i class="bi ${workflow.is_active?'bi-pause-circle':'bi-play-circle'} text-sm"></i>
                                </button>

                                <button type="button" onclick="deleteWorkflow(${workflow.id})" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100" title="Delete">
                                    <i class="bi bi-trash text-sm"></i>
                                </button>
                            `
                            :''
                    }
                </div>
            </td>
        </tr>
    `).join('');
}

function renderApproverOptions(){
    [el.approver1,el.approver2,el.approver3].forEach((select,index)=>{
        const current=select.value;

        select.innerHTML=index===0
            ?'<option value="">Select Approver</option>'
            :'<option value="">Optional</option>';

        users.forEach(user=>{
            const option=document.createElement('option');
            option.value=user.id;
            option.textContent=user.email?`${user.name} (${user.email})`:user.name;
            select.appendChild(option);
        });

        select.value=current;
    });
}

window.openWorkflowModal=function(workflow=null){
    editingWorkflow=workflow;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('workflowError');
    renderActionOptions(''); // নতুন — stale action list খালি করে দেয়

    document.getElementById('workflowModalTitle').innerText=
        workflow?'Edit Workflow':'Add Workflow';

    el.saveButton.querySelector('.workflow-save-label').textContent=
        workflow?'Update Workflow':'Save Workflow';

    el.isActive.checked=true;

    if(workflow){
        el.module.value=workflow.module??'';
        renderActionOptions(workflow.module??''); // নতুন
        el.action.value=workflow.action??'';
        el.isActive.checked=Boolean(workflow.is_active);

        const steps=workflow.steps??[];

        el.approver1.value=steps[0]?.approver_user_id??'';
        el.approver2.value=steps[1]?.approver_user_id??'';
        el.approver3.value=steps[2]?.approver_user_id??'';
    }

    AdminUI.openModal('workflowModal');
};

window.closeWorkflowModal=function(){
    AdminUI.closeModal('workflowModal');
    editingWorkflow=null;
};

window.editWorkflow=function(id){
    const workflow=workflows.find(item=>Number(item.id)===Number(id));

    if(!workflow){
        Toast.error('Workflow not found.');
        return;
    }

    openWorkflowModal(workflow);
};

function selectedApprovers(){
    return[
        el.approver1.value,
        el.approver2.value,
        el.approver3.value
    ].filter(Boolean).map(Number);
}

function validateApprovers(ids){
    if(!ids.length){
        return 'Step 1 approver is required.';
    }

    if(!el.approver1.value){
        return 'Step 1 approver is required.';
    }

    if(el.approver3.value&&!el.approver2.value){
        return 'Step 2 must be selected before Step 3.';
    }

    if(new Set(ids).size!==ids.length){
        return 'The same user cannot be selected for multiple approval steps.';
    }

    return null;
}

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('workflowError');

    if(!el.module.value){
        AdminUI.showError('workflowError','Module is required.');
        return;
    }

    if(!el.action.value){
        AdminUI.showError('workflowError','Action is required.');
        return;
    }

    const approvers=selectedApprovers();
    const approverError=validateApprovers(approvers);

    if(approverError){
        AdminUI.showError('workflowError',approverError);
        return;
    }

    const data={
        module:el.module.value,
        action:el.action.value,
        is_active:el.isActive.checked,
        approvers
    };

    const editing=Boolean(editingWorkflow);

    AdminUI.setLoading(
        el.saveButton,
        editing?'Updating...':'Saving...'
    );

    try{
        await api(
            editing
                ?`/api/approval-workflows/${editingWorkflow.id}`
                :'/api/approval-workflows',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closeWorkflowModal();

        Toast.success(
            editing
                ?'Approval workflow updated successfully.'
                :'Approval workflow created successfully.'
        );

        await loadWorkflows();
    }catch(error){
        AdminUI.showError(
            'workflowError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

window.toggleWorkflow=function(id){
    const workflow=workflows.find(item=>Number(item.id)===Number(id));

    if(!workflow){
        Toast.error('Workflow not found.');
        return;
    }

    const action=workflow.is_active?'Deactivate':'Activate';

    AdminUI.confirm({
        title:`${action} Workflow?`,
        message:workflow.is_active
            ?'New approval requests will no longer use this workflow.'
            :'New approval requests will use this workflow.',
        confirmText:action,
        onConfirm:async()=>{
            try{
                await api(
                    `/api/approval-workflows/${id}/toggle`,
                    {
                        method:'PATCH',
                        body:JSON.stringify({})
                    }
                );

                Toast.success(
                    workflow.is_active
                        ?'Workflow deactivated successfully.'
                        :'Workflow activated successfully.'
                );

                await loadWorkflows();
            }catch(error){
                Toast.error(AdminUI.extractError(error));
            }
        }
    });
};

window.deleteWorkflow=function(id){
    const workflow=workflows.find(item=>Number(item.id)===Number(id));

    if(!workflow){
        Toast.error('Workflow not found.');
        return;
    }

    AdminUI.deleteRequest(
        `/api/approval-workflows/${id}`,
        {
            title:'Delete Workflow?',
            message:`Delete ${workflow.module} / ${AdminUI.titleCase(workflow.action)} approval workflow? Existing approval requests will not be affected.`,
            confirmText:'Delete',
            successMessage:'Approval workflow deleted successfully.',
            onSuccess:loadWorkflows
        }
    );
};

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';
    renderWorkflows();
};

function enforceSequentialSteps(){
    if(!el.approver1.value){
        el.approver2.value='';
        el.approver3.value='';
        el.approver2.disabled=true;
        el.approver3.disabled=true;
        return;
    }

    el.approver2.disabled=false;

    if(!el.approver2.value){
        el.approver3.value='';
        el.approver3.disabled=true;
    }else{
        el.approver3.disabled=false;
    }
}

[el.approver1,el.approver2,el.approver3].forEach(select=>{
    select.addEventListener('change',enforceSequentialSteps);
});

async function initWorkflowPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initWorkflowPage,50);
        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(renderWorkflows)
    );

    el.status.addEventListener(
        'change',
        renderWorkflows
    );

    await Promise.all([
        loadUsers(),
        loadWorkflows()
    ]);

    enforceSequentialSteps();
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initWorkflowPage);
}else{
    initWorkflowPage();
}
</script>
@endpush