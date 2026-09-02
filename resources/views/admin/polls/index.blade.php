@extends('layouts.admin')

@section('title','Poll Management')
@section('page_title','Poll Management')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-ui-checks"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Poll Management</h1>
                <p class="text-xs text-slate-500">Create polls, manage voting periods and review results.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Poll.create'))
            <button type="button" onclick="openPollModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Create Poll
            </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Polls</p>
            <p id="totalPolls" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-sm text-emerald-600">Active</p>
            <p id="activePolls" class="mt-2 text-xl font-bold text-emerald-700">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-sm text-indigo-600">Upcoming</p>
            <p id="upcomingPolls" class="mt-2 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Ended</p>
            <p id="endedPolls" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="col-span-2 rounded-md border border-amber-200 bg-amber-50/40 p-4 xl:col-span-1">
            <p class="text-sm text-amber-600">Total Votes</p>
            <p id="totalVotes" class="mt-2 text-xl font-bold text-amber-700">0</p>
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
                    <p class="text-sm font-semibold text-slate-700">Search Polls</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by title or description</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search polls..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="stateFilter" class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All State</option>
                    <option value="active">Active</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ended">Ended</option>
                    <option value="inactive">Inactive</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Poll Grid --}}
    <div id="pollGrid" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div class="col-span-full">
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-base text-slate-400">
                Loading polls...
            </div>
        </div>
    </div>

    <div id="paginationContainer"></div>
</div>

{{-- Poll Modal --}}
<div id="pollModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-ui-checks"></i>
                </div>
                <div>
                    <h2 id="pollModalTitle" class="text-base font-semibold text-slate-800">Create Poll</h2>
                    <p class="text-xs text-slate-500">Create voting options and configure voting period.</p>
                </div>
            </div>

            <button type="button" onclick="closePollModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="pollForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input id="title" maxlength="255" class="app-input" placeholder="Poll title">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="description" rows="3" class="app-input resize-none" placeholder="Optional description"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Start <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="startAt" type="text" class="app-input js-datetime-picker !pl-9" placeholder="Select date & time" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">End <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="endAt" type="text" class="app-input js-datetime-picker !pl-9" placeholder="Select date & time" autocomplete="off">
                        </div>
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                    <input id="isActive" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Poll Active</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">Voting still follows the configured start and end time.</p>
                    </div>
                </label>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="form-label mb-0">Poll Options <span class="text-red-500">*</span></label>

                        <button type="button" onclick="addOption()" class="cursor-pointer text-sm font-semibold text-indigo-600 transition hover:text-indigo-700">
                            <i class="bi bi-plus-lg mr-1"></i>
                            Add Option
                        </button>
                    </div>

                    <div id="optionContainer" class="space-y-2"></div>
                </div>

                <div id="pollError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button type="button" onclick="closePollModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button id="savePollButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    Save Poll
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Result Modal --}}
<div id="resultModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                    <i class="bi bi-bar-chart"></i>
                </div>
                <div>
                    <h2 id="resultTitle" class="text-base font-semibold text-slate-800">Poll Results</h2>
                    <p id="resultTotal" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('resultModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="resultContent" class="min-h-0 flex-1 space-y-3 overflow-y-auto p-5"></div>
    </div>
</div>

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>
@endsection

@push('scripts')
<script>
let polls=[];
let editingPoll=null;
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(auth()->user()->hasPermission('Poll.update'));
const canDelete=@json(auth()->user()->hasPermission('Poll.delete'));

const el={
    grid:document.getElementById('pollGrid'),
    search:document.getElementById('searchInput'),
    stateFilter:document.getElementById('stateFilter'),
    form:document.getElementById('pollForm'),
    title:document.getElementById('title'),
    description:document.getElementById('description'),
    startAt:document.getElementById('startAt'),
    endAt:document.getElementById('endAt'),
    isActive:document.getElementById('isActive'),
    options:document.getElementById('optionContainer'),
    saveButton:document.getElementById('savePollButton')
};

function pollLoadingState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.loadingState('Loading polls...')}
        </div>
    `;
}

function pollEmptyState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.emptyState('No polls found.')}
        </div>
    `;
}

function pollErrorState(error){
    el.grid.innerHTML=`
        <div class="col-span-full">
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        </div>
    `;
}

function setPickerDateTime(element,value){
    if(!element)return;

    if(!value){
        element.value='';
        if(element._flatpickr)element._flatpickr.clear();
        return;
    }

    const date=new Date(value);

    if(element._flatpickr)element._flatpickr.setDate(date,false);
    else element.value=value;
}

async function loadPolls(page=1){
    currentPage=page;
    pollLoadingState();

    const query=AdminUI.query({
        search:el.search.value.trim(),
        state:el.stateFilter.value,
        page
    });

    try{
        const response=await api(`/api/polls?${query}`);
        const paginator=response.data??{};

        polls=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??polls.length);

        renderPolls();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadPolls
        });
    }catch(error){
        pollErrorState(error);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadPolls
        });
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/polls/statistics');
        const stats=response.data??{};

        document.getElementById('totalPolls').innerText=stats.total??0;
        document.getElementById('activePolls').innerText=stats.active??0;
        document.getElementById('upcomingPolls').innerText=stats.upcoming??0;
        document.getElementById('endedPolls').innerText=stats.ended??0;
        document.getElementById('totalVotes').innerText=stats.total_votes??0;
    }catch(error){
        console.error('Poll statistics load failed:',error);
    }
}

function renderPolls(){
    if(!polls.length){
        pollEmptyState();
        return;
    }

    el.grid.innerHTML=polls.map(item=>`
        <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:border-slate-300">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-base font-bold text-slate-800" title="${AdminUI.escapeHtml(item.title??'')}">
                            ${AdminUI.escapeHtml(item.title??'Untitled Poll')}
                        </h3>

                        <p class="mt-1 truncate text-[11px] text-slate-400">
                            ${AdminUI.formatDate(item.start_at,true)} → ${AdminUI.formatDate(item.end_at,true)}
                        </p>
                    </div>

                    <div class="shrink-0">
                        ${AdminUI.statusBadge(item.state)}
                    </div>
                </div>

                ${item.description?`
                    <p class="mt-3 line-clamp-2 break-words text-sm leading-5 text-slate-500">
                        ${AdminUI.escapeHtml(item.description)}
                    </p>
                `:''}

                <div class="mt-4 space-y-1.5">
                    ${(item.options??[]).slice(0,4).map(option=>`
                        <div class="truncate rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-600" title="${AdminUI.escapeHtml(option.option_text??'')}">
                            ${AdminUI.escapeHtml(option.option_text??'')}
                        </div>
                    `).join('')}

                    ${(item.options??[]).length>4?`
                        <div class="px-1 text-[10px] font-medium text-indigo-500">
                            +${item.options.length-4} more option(s)
                        </div>
                    `:''}
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    <span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-600">
                        ${item.votes_count??0} Votes
                    </span>

                    <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] text-slate-500">
                        ${(item.options??[]).length} Options
                    </span>
                </div>
            </div>

            <div class="mt-auto flex justify-end gap-1 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                <button type="button" onclick="showResults(${item.id})" title="Results" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-sky-50 text-sky-700 transition hover:bg-sky-100">
                    <i class="bi bi-bar-chart text-sm"></i>
                </button>

                ${canUpdate?`
                    <button type="button" onclick="togglePoll(${item.id})" title="${item.is_active?'Deactivate':'Activate'}" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700 transition hover:bg-amber-100">
                        <i class="bi ${item.is_active?'bi-pause-circle':'bi-play-circle'} text-sm"></i>
                    </button>

                    <button type="button" onclick="editPoll(${item.id})" title="Edit" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                        <i class="bi bi-pencil-square text-sm"></i>
                    </button>
                `:''}

                ${canDelete?`
                    <button type="button" onclick="deletePoll(${item.id})" title="Delete" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100">
                        <i class="bi bi-trash text-sm"></i>
                    </button>
                `:''}
            </div>
        </article>
    `).join('');
}

window.addOption=function(value=''){
    const row=document.createElement('div');

    row.className='flex items-center gap-2';

    row.innerHTML=`
        <input type="text" maxlength="255" value="${AdminUI.escapeHtml(value)}" class="poll-option app-input min-w-0 flex-1" placeholder="Option">

        <button type="button" title="Remove Option" class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100">
            <i class="bi bi-trash text-sm"></i>
        </button>
    `;

    row.querySelector('button').addEventListener('click',()=>{
        if(el.options.children.length<=2){
            Toast.warning('A poll requires at least two options.');
            return;
        }

        row.remove();
    });

    el.options.appendChild(row);
};

window.openPollModal=function(item=null){
    editingPoll=item;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('pollError');

    el.options.innerHTML='';

    document.getElementById('pollModalTitle').innerText=
        item?'Edit Poll':'Create Poll';

    el.saveButton.innerText=
        item?'Update Poll':'Save Poll';

    if(item){
        el.title.value=item.title??'';
        el.description.value=item.description??'';
        el.isActive.checked=Boolean(item.is_active);

        setPickerDateTime(el.startAt,item.start_at);
        setPickerDateTime(el.endAt,item.end_at);

        (item.options??[]).forEach(option=>{
            addOption(option.option_text);
        });
    }else{
        el.isActive.checked=true;

        setPickerDateTime(el.startAt,null);
        setPickerDateTime(el.endAt,null);

        addOption();
        addOption();
    }

    AdminUI.openModal('pollModal');
};

window.closePollModal=function(){
    AdminUI.closeModal('pollModal');
    editingPoll=null;
};

window.editPoll=function(id){
    const item=polls.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Poll not found.');
        return;
    }

    openPollModal(item);
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('pollError');

    const options=[...document.querySelectorAll('.poll-option')]
        .map(input=>input.value.trim())
        .filter(Boolean);

    const title=el.title.value.trim();
    const startAt=el.startAt.value;
    const endAt=el.endAt.value;

    if(!title){
        AdminUI.showError('pollError','Poll title is required.');
        return;
    }

    if(!startAt){
        AdminUI.showError('pollError','Poll start time is required.');
        return;
    }

    if(!endAt){
        AdminUI.showError('pollError','Poll end time is required.');
        return;
    }

    if(new Date(endAt)<=new Date(startAt)){
        AdminUI.showError('pollError','Poll end time must be after start time.');
        return;
    }

    if(options.length<2){
        AdminUI.showError('pollError','At least two poll options are required.');
        return;
    }

    const uniqueOptions=new Set(
        options.map(option=>option.toLowerCase())
    );

    if(uniqueOptions.size!==options.length){
        AdminUI.showError('pollError','Duplicate poll options are not allowed.');
        return;
    }

    const data={
        title,
        description:el.description.value.trim()||null,
        start_at:startAt,
        end_at:endAt,
        is_active:el.isActive.checked,
        options
    };

    AdminUI.setLoading(
        el.saveButton,
        editingPoll?'Updating...':'Saving...'
    );

    try{
        const editing=Boolean(editingPoll);

        await api(
            editing
                ?`/api/polls/${editingPoll.id}`
                :'/api/polls',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closePollModal();

        Toast.success(
            editing
                ?'Poll updated successfully.'
                :'Poll created successfully.'
        );

        await Promise.all([
            loadPolls(editing?currentPage:1),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'pollError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

/*
|--------------------------------------------------------------------------
| Activate / Deactivate
|--------------------------------------------------------------------------
| Always uses global confirmation modal.
*/
window.togglePoll=async function(id){
    const item=polls.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Poll not found.');
        return;
    }

    const activating=!item.is_active;

    const confirmed=await AdminUI.confirm({
        title:activating?'Activate Poll?':'Deactivate Poll?',
        message:activating
            ?'This poll will become active. Voting will still follow the configured start and end time.'
            :'This poll will be deactivated and members will no longer be able to vote.',
        confirmText:activating?'Activate':'Deactivate',
        cancelText:'Cancel',
        type:activating?'primary':'warning'
    });

    if(!confirmed)return;

    try{
        await api(`/api/polls/${id}/toggle`,{
            method:'PATCH',
            body:JSON.stringify({})
        });

        Toast.success(
            activating
                ?'Poll activated successfully.'
                :'Poll deactivated successfully.'
        );

        await Promise.all([
            loadPolls(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.showResults=async function(id){
    try{
        const response=await api(`/api/polls/${id}/results`);
        const data=response.data??{};
        const results=Array.isArray(data.results)?data.results:[];

        document.getElementById('resultTitle').innerText=
            data.poll?.title??'Poll Results';

        document.getElementById('resultTotal').innerText=
            `${data.total_votes??0} total votes`;

        const container=
            document.getElementById('resultContent');

        if(!results.length){
            container.innerHTML=
                AdminUI.emptyState('No votes recorded yet.');
        }else{
            container.innerHTML=
                results.map(result=>{
                    const votes=Number(
                        result.votes??
                        result.votes_count??
                        0
                    );

                    const totalVotes=
                        Number(data.total_votes??0);

                    const percentage=Number(
                        result.percentage??
                        (
                            totalVotes>0
                                ?votes/totalVotes*100
                                :0
                        )
                    );

                    return `
                        <div class="rounded-md border border-slate-200 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <span class="min-w-0 truncate text-sm font-semibold text-slate-700" title="${AdminUI.escapeHtml(result.option_text??'')}">
                                    ${AdminUI.escapeHtml(result.option_text??'')}
                                </span>

                                <span class="shrink-0 text-sm font-bold text-indigo-600">
                                    ${votes} • ${percentage.toFixed(2)}%
                                </span>
                            </div>

                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-indigo-500" style="width:${Math.max(0,Math.min(percentage,100))}%"></div>
                            </div>
                        </div>
                    `;
                }).join('');
        }

        AdminUI.openModal('resultModal');
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
| AdminUI.deleteRequest uses the global confirmation modal.
*/
window.deletePoll=function(id){
    const item=polls.find(item=>Number(item.id)===Number(id));

    if(!item){
        Toast.error('Poll not found.');
        return;
    }

    AdminUI.deleteRequest(`/api/polls/${id}`,{
        title:'Delete Poll?',
        message:'This poll and all existing votes will be permanently deleted. This action cannot be undone.',
        confirmText:'Delete',
        successMessage:'Poll deleted successfully.',
        onSuccess:async()=>{
            if(polls.length===1&&currentPage>1){
                currentPage--;
            }

            await Promise.all([
                loadPolls(currentPage),
                loadStatistics()
            ]);
        }
    });
};

window.clearFilters=function(){
    el.search.value='';
    el.stateFilter.value='';

    loadPolls(1);
};

async function initPollPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initPollPage,50);
        return;
    }

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(()=>loadPolls(1))
    );

    el.stateFilter.addEventListener(
        'change',
        ()=>loadPolls(1)
    );

    pollLoadingState();

    await Promise.all([
        loadPolls(),
        loadStatistics()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initPollPage
    );
}else{
    initPollPage();
}
</script>
@endpush