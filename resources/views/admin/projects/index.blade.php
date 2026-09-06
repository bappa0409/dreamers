@extends('layouts.admin')

@section('title','Project Management')
@section('page_title','Project Management')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-kanban"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Project Management</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage projects, budgets, progress and member participation.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Project.create'))
            <button type="button" onclick="openProjectModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Add Project
            </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class=" text-xs 2xl:text-sm text-slate-500">Projects</p>
            <p id="totalProjects" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-sm text-indigo-600">Budget</p>
            <p id="totalBudget" class="mt-2 truncate text-lg font-bold text-indigo-700">{{ setting('currency_symbol','৳') }}0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-sm text-amber-600">Actual Cost</p>
            <p id="actualCost" class="mt-2 truncate text-lg font-bold text-amber-700">{{ setting('currency_symbol','৳') }}0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-sm text-emerald-600">Completed</p>
            <p id="completedCount" class="mt-2 text-xl font-bold text-emerald-700">0</p>
        </div>

        <div class="col-span-2 rounded-md border border-slate-200 bg-white p-4 xl:col-span-1">
            <p class=" text-xs 2xl:text-sm text-slate-500">Avg. Progress</p>
            <p id="averageProgress" class="mt-2 text-xl font-bold text-slate-800">0%</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Projects</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by code, name or location</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">
                <div class="relative w-full sm:min-w-[220px] lg:w-72">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search projects..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3  text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="planned">Planned</option>
                    <option value="active">Active</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <div class="relative min-w-0 lg:w-[200px]">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="dateRangeFilter" type="text" placeholder="Start date range" autocomplete="off" class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm font-medium text-slate-600 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                </div>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div id="projectGrid" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div class="col-span-full">
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-base text-slate-400">Loading projects...</div>
        </div>
    </div>

    <div id="paginationContainer"></div>
</div>

{{-- Project Modal --}}
<div id="projectModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-kanban"></i>
                </div>
                <div>
                    <h2 id="projectModalTitle" class="text-sm font-semibold text-slate-800">Add Project</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Project details, financials and progress.</p>
                </div>
            </div>

            <button type="button" onclick="closeProjectModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="projectForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Project Name <span class="text-red-500">*</span></label>
                    <input id="name" maxlength="255" class="app-input" placeholder="Project name">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="description" rows="3" class="app-input resize-none" placeholder="Project description"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <label class="form-label">Location</label>
                        <input id="location" maxlength="255" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Budget</label>
                        <input id="budget" type="number" min="0" step="0.01" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Actual Cost</label>
                        <input id="actualCostInput" type="number" min="0" step="0.01" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Start Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="startDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Expected End Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="expectedEndDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Actual End Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="actualEndDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Progress (%)</label>
                        <input id="progress" type="number" min="0" max="100" value="0" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select id="status" class="app-input">
                            <option value="planned">Planned</option>
                            <option value="active">Active</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea id="notes" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div id="projectError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button type="button" onclick="closeProjectModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button id="saveProjectButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Save Project</button>
            </div>
        </form>
    </div>
</div>

{{-- Member Modal --}}
<div id="memberModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-800">Assign Member</h2>
                    <p id="memberProjectInfo" class="mt-1 truncate text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeMemberModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="memberForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <input id="memberProjectId" type="hidden">

                <div>
                    <label class="form-label">Member <span class="text-red-500">*</span></label>
                    <select id="memberId" class="app-input">
                        <option value="">Select Member</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Contribution</label>
                        <input id="memberContribution" type="number" min="0" step="0.01" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Role</label>
                        <input id="memberRole" maxlength="100" placeholder="Investor, Coordinator..." class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Joined Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="joinedDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select id="memberStatus" class="app-input">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div id="memberError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeMemberModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button id="saveMemberButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Assign Member</button>
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
let projects=[];
let editingProject=null;
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(auth()->user()->hasPermission('Project.update'));
const canDelete=@json(auth()->user()->hasPermission('Project.delete'));
const currency=@json(setting('currency_symbol','৳'));

const el={
    grid:document.getElementById('projectGrid'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    dateRange:document.getElementById('dateRangeFilter'),
    form:document.getElementById('projectForm'),
    name:document.getElementById('name'),
    description:document.getElementById('description'),
    location:document.getElementById('location'),
    budget:document.getElementById('budget'),
    actualCost:document.getElementById('actualCostInput'),
    startDate:document.getElementById('startDate'),
    expectedEndDate:document.getElementById('expectedEndDate'),
    actualEndDate:document.getElementById('actualEndDate'),
    progress:document.getElementById('progress'),
    status:document.getElementById('status'),
    notes:document.getElementById('notes'),
    saveButton:document.getElementById('saveProjectButton')
};

function money(value){
    return currency+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function setPickerDate(element,value){
    if(!element)return;

    const date=value?String(value).substring(0,10):'';
    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(date,false)
            :element._flatpickr.clear();
    }
}

function rawDate(date){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function selectedDateRange(){
    if(!el.dateRange?._flatpickr){
        return{from:'',to:''};
    }

    const dates=el.dateRange._flatpickr.selectedDates??[];

    return{
        from:dates[0]?rawDate(dates[0]):'',
        to:dates[1]?rawDate(dates[1]):''
    };
}

function projectLoadingState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.loadingState('Loading projects...')}
        </div>
    `;
}

function projectEmptyState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.emptyState('No projects found.')}
        </div>
    `;
}

function projectErrorState(error){
    el.grid.innerHTML=`
        <div class="col-span-full">
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        </div>
    `;
}

async function loadProjects(page=1){
    currentPage=page;
    projectLoadingState();

    const range=selectedDateRange();
    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        from:range.from,
        to:range.to,
        page
    });

    try{
        const response=await api(`/api/projects?${query}`);
        const paginator=response.data??{};

        projects=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??projects.length);

        renderProjects();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadProjects
        });
    }catch(error){
        projectErrorState(error);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadProjects
        });
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/projects/statistics');
        const stats=response.data??{};

        document.getElementById('totalProjects').innerText=stats.total??0;
        document.getElementById('totalBudget').innerText=money(stats.total_budget);
        document.getElementById('actualCost').innerText=money(stats.actual_cost);
        document.getElementById('completedCount').innerText=stats.completed??0;
        document.getElementById('averageProgress').innerText=`${Number(stats.average_progress??0).toFixed(1)}%`;
    }catch(error){
        console.error('Project statistics load failed:',error);
    }
}

function renderProjects(){
    if(!projects.length){
        projectEmptyState();
        return;
    }

    el.grid.innerHTML=projects.map(item=>{
        const progress=Math.max(0,Math.min(Number(item.progress??0),100));

        return `
            <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:border-slate-300">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-indigo-600" title="${AdminUI.escapeHtml(item.project_code??'')}">
                                ${AdminUI.escapeHtml(item.project_code??'—')}
                            </p>

                            <h3 class="mt-1 truncate text-base font-bold text-slate-800" title="${AdminUI.escapeHtml(item.name??'')}">
                                ${AdminUI.escapeHtml(item.name??'Untitled Project')}
                            </h3>

                            <p class="mt-1 truncate text-[11px] text-slate-400" title="${AdminUI.escapeHtml(item.location??'Location not specified')}">
                                <i class="bi bi-geo-alt mr-1"></i>
                                ${AdminUI.escapeHtml(item.location??'Location not specified')}
                            </p>
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(item.status)}
                        </div>
                    </div>

                    ${item.description?`
                        <p class="mt-3 line-clamp-2 break-words text-sm leading-5 text-slate-500">
                            ${AdminUI.escapeHtml(item.description)}
                        </p>
                    `:''}

                    <div class="mt-4">
                        <div class="mb-1 flex items-center justify-between text-[10px]">
                            <span class="text-slate-400">Progress</span>
                            <span class="font-semibold text-slate-600">${progress}%</span>
                        </div>

                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-indigo-500 transition-all" style="width:${progress}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="min-w-0 rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] text-slate-400">Budget</p>
                            <p class="mt-1 truncate text-sm font-bold text-slate-700" title="${AdminUI.escapeHtml(money(item.budget))}">
                                ${money(item.budget)}
                            </p>
                        </div>

                        <div class="min-w-0 rounded-md bg-amber-50 p-3">
                            <p class="text-[10px] text-amber-500">Actual Cost</p>
                            <p class="mt-1 truncate text-sm font-bold text-amber-700" title="${AdminUI.escapeHtml(money(item.actual_cost))}">
                                ${money(item.actual_cost)}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1.5">
                        <span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] text-indigo-600">
                            ${item.members_count??0} Member${Number(item.members_count??0)===1?'':'s'}
                        </span>

                        <span class="max-w-full truncate rounded-md bg-emerald-50 px-2 py-1 text-[10px] text-emerald-700" title="${AdminUI.escapeHtml(money(item.total_contribution??0))}">
                            ${money(item.total_contribution??0)} Contribution
                        </span>

                        ${item.start_date?`
                            <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] text-slate-500">
                                ${AdminUI.formatDate(item.start_date)}
                            </span>
                        `:''}
                    </div>
                </div>

                <div class="mt-auto flex justify-end gap-1 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${canUpdate&&item.status!=='cancelled'?`
                        <button type="button" onclick="openMemberModal(${item.id})" title="Assign Member" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100">
                            <i class="bi bi-person-plus text-sm"></i>
                        </button>
                    `:''}

                    ${canUpdate?`
                        <button type="button" onclick="editProject(${item.id})" title="Edit Project" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                            <i class="bi bi-pencil-square text-sm"></i>
                        </button>
                    `:''}

                    ${canDelete?`
                        <button type="button" onclick="deleteProject(${item.id})" title="Delete Project" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100">
                            <i class="bi bi-trash text-sm"></i>
                        </button>
                    `:''}
                </div>
            </article>
        `;
    }).join('');
}

window.openProjectModal=function(item=null){
    editingProject=item;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('projectError');

    document.getElementById('projectModalTitle').innerText=item?'Edit Project':'Add Project';
    el.saveButton.innerText=item?'Update Project':'Save Project';

    if(item){
        el.name.value=item.name??'';
        el.description.value=item.description??'';
        el.location.value=item.location??'';
        el.budget.value=item.budget??0;
        el.actualCost.value=item.actual_cost??0;
        el.progress.value=item.progress??0;
        el.status.value=item.status??'planned';
        el.notes.value=item.notes??'';

        setPickerDate(el.startDate,item.start_date);
        setPickerDate(el.expectedEndDate,item.expected_end_date);
        setPickerDate(el.actualEndDate,item.actual_end_date);
    }else{
        el.progress.value=0;
        el.status.value='planned';

        setPickerDate(el.startDate,null);
        setPickerDate(el.expectedEndDate,null);
        setPickerDate(el.actualEndDate,null);
    }

    AdminUI.openModal('projectModal');
};

window.closeProjectModal=function(){
    AdminUI.closeModal('projectModal');
    editingProject=null;
};

window.editProject=function(id){
    const item=projects.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Project not found.');
        return;
    }

    openProjectModal(item);
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('projectError');

    const name=el.name.value.trim();
    const budget=Number(el.budget.value||0);
    const actualCost=Number(el.actualCost.value||0);
    const progress=Number(el.progress.value||0);

    if(!name){
        AdminUI.showError('projectError','Project name is required.');
        return;
    }

    if(budget<0){
        AdminUI.showError('projectError','Budget cannot be negative.');
        return;
    }

    if(actualCost<0){
        AdminUI.showError('projectError','Actual cost cannot be negative.');
        return;
    }

    if(progress<0||progress>100){
        AdminUI.showError('projectError','Progress must be between 0 and 100.');
        return;
    }

    if(
        el.startDate.value&&
        el.expectedEndDate.value&&
        new Date(el.expectedEndDate.value)<new Date(el.startDate.value)
    ){
        AdminUI.showError('projectError','Expected end date cannot be before start date.');
        return;
    }

    if(
        el.startDate.value&&
        el.actualEndDate.value&&
        new Date(el.actualEndDate.value)<new Date(el.startDate.value)
    ){
        AdminUI.showError('projectError','Actual end date cannot be before start date.');
        return;
    }

    const data={
        name,
        description:el.description.value.trim()||null,
        location:el.location.value.trim()||null,
        budget,
        actual_cost:actualCost,
        start_date:el.startDate.value||null,
        expected_end_date:el.expectedEndDate.value||null,
        actual_end_date:el.actualEndDate.value||null,
        progress,
        status:el.status.value,
        notes:el.notes.value.trim()||null
    };

    AdminUI.setLoading(el.saveButton,editingProject?'Updating...':'Saving...');

    try{
        const editing=Boolean(editingProject);

        await api(
            editing?`/api/projects/${editingProject.id}`:'/api/projects',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closeProjectModal();

        Toast.success(
            editing
                ?'Project updated successfully.'
                :'Project created successfully.'
        );

        await Promise.all([
            loadProjects(editing?currentPage:1),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError('projectError',AdminUI.extractError(error));
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

async function loadMembers(){
    const select=document.getElementById('memberId');

    select.innerHTML='<option value="">Loading members...</option>';
    select.disabled=true;

    try{
        const response=await api('/api/projects/members');
        const members=response.data??[];

        select.innerHTML='<option value="">Select Member</option>';

        members.forEach(member=>{
            const option=document.createElement('option');

            option.value=member.id;
            option.textContent=`${member.member_code} - ${member.user?.name??'Member'}`;

            select.appendChild(option);
        });
    }catch(error){
        select.innerHTML='<option value="">Unable to load members</option>';
        Toast.error('Unable to load members.');
    }finally{
        select.disabled=false;
    }
}

window.openMemberModal=async function(id){
    const item=projects.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Project not found.');
        return;
    }

    const form=document.getElementById('memberForm');

    AdminUI.resetForm(form);
    AdminUI.clearError('memberError');

    document.getElementById('memberProjectId').value=item.id;
    document.getElementById('memberProjectInfo').innerText=`${item.project_code} • ${item.name}`;
    document.getElementById('memberStatus').value='active';

    setPickerDate(
        document.getElementById('joinedDate'),
        new Date().toISOString().slice(0,10)
    );

    AdminUI.openModal('memberModal');

    await loadMembers();
};

window.closeMemberModal=function(){
    AdminUI.closeModal('memberModal');
};

document.getElementById('memberForm').addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('memberError');

    const id=document.getElementById('memberProjectId').value;
    const memberId=Number(document.getElementById('memberId').value);
    const contribution=Number(document.getElementById('memberContribution').value||0);
    const button=document.getElementById('saveMemberButton');

    if(!memberId){
        AdminUI.showError('memberError','Please select a member.');
        return;
    }

    if(contribution<0){
        AdminUI.showError('memberError','Contribution cannot be negative.');
        return;
    }

    const data={
        member_id:memberId,
        contribution,
        role:document.getElementById('memberRole').value.trim()||null,
        joined_date:document.getElementById('joinedDate').value||null,
        status:document.getElementById('memberStatus').value
    };

    AdminUI.setLoading(button,'Assigning...');

    try{
        await api(`/api/projects/${id}/members`,{
            method:'POST',
            body:JSON.stringify(data)
        });

        closeMemberModal();
        Toast.success('Member assigned successfully.');

        await Promise.all([
            loadProjects(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError('memberError',AdminUI.extractError(error));
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteProject=function(id){
    const item=projects.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Project not found.');
        return;
    }

    AdminUI.deleteRequest(`/api/projects/${id}`,{
        title:'Delete Project?',
        message:'This project and its related member assignments will be permanently deleted. This action cannot be undone.',
        confirmText:'Delete',
        successMessage:'Project deleted successfully.',
        onSuccess:async()=>{
            if(projects.length===1&&currentPage>1)currentPage--;

            await Promise.all([
                loadProjects(currentPage),
                loadStatistics()
            ]);
        }
    });
};

window.clearFilters=function(){
    el.search.value='';
    el.statusFilter.value='';

    if(el.dateRange?._flatpickr){
        el.dateRange._flatpickr.clear();
    }else if(el.dateRange){
        el.dateRange.value='';
    }

    loadProjects(1);
};

function registerDateRange(){
    if(!el.dateRange?._flatpickr)return;

    el.dateRange._flatpickr.config.onClose.push(selectedDates=>{
        if(selectedDates.length===0||selectedDates.length===2){
            loadProjects(1);
        }
    });
}

async function initProjectPage(){
    if(typeof window.AdminUI==='undefined'||typeof window.api==='undefined'){
        setTimeout(initProjectPage,50);
        return;
    }

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(()=>loadProjects(1))
    );

    el.statusFilter.addEventListener(
        'change',
        ()=>loadProjects(1)
    );

    registerDateRange();

    await Promise.all([
        loadProjects(),
        loadStatistics()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initProjectPage);
}else{
    initProjectPage();
}
</script>
@endpush