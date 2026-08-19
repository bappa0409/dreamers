@extends('layouts.admin')

@section('title','Poll Management')
@section('page_title','Poll Management')

@section('content')

<div class="space-y-5">

    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-ui-checks"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">Poll Management</h1>
                <p class="mt-1 text-sm text-slate-500">Create polls, manage voting periods and review results.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Poll.create'))
            <button type="button" onclick="openPollModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Create Poll
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Polls</p>
            <p id="totalPolls" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-xs text-emerald-600">Active</p>
            <p id="activePolls" class="mt-2 text-xl font-bold text-emerald-700">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-xs text-indigo-600">Upcoming</p>
            <p id="upcomingPolls" class="mt-2 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Ended</p>
            <p id="endedPolls" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="col-span-2 rounded-md border border-amber-200 bg-amber-50/40 p-4 xl:col-span-1">
            <p class="text-xs text-amber-600">Total Votes</p>
            <p id="totalVotes" class="mt-2 text-xl font-bold text-amber-700">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">Search Polls</p>
                <p class="hidden text-[11px] text-slate-400 sm:block">
                    Search by title or description
                </p>
            </div>
        </div>

        <div class="flex w-full items-center lg:w-auto">
            <div class="relative w-full lg:w-80">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                <input
                    id="searchInput"
                    type="text"
                    placeholder="Search polls..."
                    class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            <select
                id="stateFilter"
                class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400"
            >
                <option value="">All State</option>
                <option value="active">Active</option>
                <option value="upcoming">Upcoming</option>
                <option value="ended">Ended</option>
                <option value="inactive">Inactive</option>
            </select>

            <button
                type="button"
                onclick="clearFilters()"
                class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
            >
                <i class="bi bi-x-lg text-[10px]"></i>
                Clear
            </button>
        </div>
    </div>
</div>

    <div id="pollGrid" class="grid grid-cols-1 gap-3 lg:grid-cols-2 2xl:grid-cols-3"></div>

    <div id="paginationContainer"></div>
</div>

{{-- Poll Modal --}}
<div id="pollModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-5">
            <div>
                <h2 id="pollModalTitle" class="text-lg font-bold text-slate-800">Create Poll</h2>
                <p class="mt-1 text-xs text-slate-500">Create voting options and configure voting period.</p>
            </div>

            <button type="button" onclick="closePollModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="pollForm" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-5">

                <div>
                    <label class="form-label">Title *</label>
                    <input id="title" maxlength="255" class="app-input">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="description" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Start *</label>
                        <input id="startAt" type="text" class="app-input js-datetime-picker" placeholder="YYYY-MM-DD HH:MM" autocomplete="off">
                    </div>

                    <div>
                        <label class="form-label">End *</label>
                        <input id="endAt" type="text" class="app-input js-datetime-picker" placeholder="YYYY-MM-DD HH:MM" autocomplete="off">
                    </div>
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50 p-3">
                    <input id="isActive" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600">

                    <div>
                        <p class="text-xs font-semibold text-slate-700">Poll Active</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">Voting still follows the configured start and end time.</p>
                    </div>
                </label>

                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <label class="form-label mb-0">Poll Options *</label>

                        <button type="button" onclick="addOption()" class="cursor-pointer text-xs font-semibold text-indigo-600 hover:text-indigo-700">
                            <i class="bi bi-plus-lg mr-1"></i>
                            Add Option
                        </button>
                    </div>

                    <div id="optionContainer" class="space-y-2"></div>
                </div>

                <div id="pollError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closePollModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="savePollButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white">
                    Save Poll
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Result Modal --}}
<div id="resultModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3">
    <div class="app-modal-panel w-full max-w-2xl overflow-hidden rounded-md bg-white">

        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-5">
            <div>
                <h2 id="resultTitle" class="text-lg font-bold text-slate-800">Poll Results</h2>
                <p id="resultTotal" class="mt-1 text-xs text-slate-500"></p>
            </div>

            <button type="button" onclick="AdminUI.closeModal('resultModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="resultContent" class="space-y-3 p-5"></div>
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

async function loadPolls(page=1){
    currentPage=page;

    const query=AdminUI.query({
        search:el.search.value.trim(),
        state:el.stateFilter.value,
        page
    });

    try{
        const response=await api(`/api/polls?${query}`);
        const paginator=response.data??{};

        polls=paginator.data??[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;
        total=paginator.total??0;

        renderPolls();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadPolls
        });
    }catch(error){
        el.grid.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error)
        );
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
        console.error(error);
    }
}

function renderPolls(){
    if(!polls.length){
        el.grid.innerHTML=AdminUI.emptyState('No polls found.');
        return;
    }

    el.grid.innerHTML=polls.map(item=>`
        <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-bold text-slate-800">
                            ${AdminUI.escapeHtml(item.title)}
                        </h3>

                        <p class="mt-1 text-[11px] text-slate-400">
                            ${AdminUI.formatDate(item.start_at,true)}
                            →
                            ${AdminUI.formatDate(item.end_at,true)}
                        </p>
                    </div>

                    ${AdminUI.statusBadge(item.state)}
                </div>

                ${item.description?`
                    <p class="mt-3 line-clamp-2 text-xs leading-5 text-slate-500">
                        ${AdminUI.escapeHtml(item.description)}
                    </p>
                `:''}

                <div class="mt-4 space-y-1.5">
                    ${(item.options??[]).slice(0,4).map(option=>`
                        <div class="rounded-md bg-slate-50 px-3 py-2 text-xs text-slate-600">
                            ${AdminUI.escapeHtml(option.option_text)}
                        </div>
                    `).join('')}
                </div>

                <div class="mt-3 flex gap-1.5">
                    <span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-600">
                        ${item.votes_count??0} Votes
                    </span>

                    <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] text-slate-500">
                        ${(item.options??[]).length} Options
                    </span>
                </div>
            </div>

            <div class="mt-auto flex justify-end gap-1 border-t bg-slate-50/50 px-4 py-3">
                <button type="button" onclick="showResults(${item.id})" title="Results" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-sky-50 text-sky-700">
                    <i class="bi bi-bar-chart text-xs"></i>
                </button>

                ${canUpdate?`
                    <button type="button" onclick="togglePoll(${item.id})" title="${item.is_active?'Deactivate':'Activate'}" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700">
                        <i class="bi ${item.is_active?'bi-pause-circle':'bi-play-circle'} text-xs"></i>
                    </button>

                    <button type="button" onclick="editPoll(${item.id})" title="Edit" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-pencil-square text-xs"></i>
                    </button>
                `:''}

                ${canDelete?`
                    <button type="button" onclick="deletePoll(${item.id})" title="Delete" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600">
                        <i class="bi bi-trash text-xs"></i>
                    </button>
                `:''}
            </div>
        </article>
    `).join('');
}

function addOption(value=''){
    const row=document.createElement('div');

    row.className='flex items-center gap-2';

    row.innerHTML=`
        <input type="text" maxlength="255" value="${AdminUI.escapeHtml(value)}" class="poll-option app-input flex-1" placeholder="Option">

        <button type="button" class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100">
            <i class="bi bi-trash text-xs"></i>
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
}

function openPollModal(item=null){
    editingPoll=item;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('pollError');

    el.options.innerHTML='';

    document.getElementById('pollModalTitle').innerText=
        item?'Edit Poll':'Create Poll';

    el.saveButton.innerText=
        item?'Update Poll':'Save Poll';

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    if(item){
        el.title.value=item.title??'';
        el.description.value=item.description??'';
        el.isActive.checked=Boolean(item.is_active);

        if(el.startAt._flatpickr){
            el.startAt._flatpickr.setDate(item.start_at,false);
        }else{
            el.startAt.value=item.start_at??'';
        }

        if(el.endAt._flatpickr){
            el.endAt._flatpickr.setDate(item.end_at,false);
        }else{
            el.endAt.value=item.end_at??'';
        }

        (item.options??[]).forEach(option=>{
            addOption(option.option_text);
        });
    }else{
        el.isActive.checked=true;
        addOption();
        addOption();
    }

    AdminUI.openModal('pollModal');

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }
}

function closePollModal(){
    AdminUI.closeModal('pollModal');
    editingPoll=null;
}

function editPoll(id){
    const item=polls.find(
        item=>Number(item.id)===Number(id)
    );

    if(item){
        openPollModal(item);
    }
}

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('pollError');

    const options=[
        ...document.querySelectorAll('.poll-option')
    ]
    .map(input=>input.value.trim())
    .filter(Boolean);

    if(options.length<2){
        AdminUI.showError(
            'pollError',
            'At least two poll options are required.'
        );

        return;
    }

    const data={
        title:el.title.value.trim(),
        description:el.description.value.trim()||null,
        start_at:el.startAt.value,
        end_at:el.endAt.value,
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

function togglePoll(id){
    AdminUI.request(
        `/api/polls/${id}/toggle`,
        {
            method:'PATCH',
            successMessage:'Poll status updated.',
            onSuccess:async()=>{
                await Promise.all([
                    loadPolls(currentPage),
                    loadStatistics()
                ]);
            }
        }
    );
}

async function showResults(id){
    try{
        const response=await api(`/api/polls/${id}/results`);
        const data=response.data??{};

        document.getElementById('resultTitle').innerText=
            data.poll?.title??'Poll Results';

        document.getElementById('resultTotal').innerText=
            `${data.total_votes??0} total votes`;

        const container=document.getElementById('resultContent');

        container.innerHTML=(data.results??[]).map(result=>`
            <div>
                <div class="mb-1 flex items-center justify-between gap-3">
                    <span class="truncate text-xs font-semibold text-slate-700">
                        ${AdminUI.escapeHtml(result.option_text)}
                    </span>

                    <span class="shrink-0 text-xs font-bold text-indigo-600">
                        ${result.votes} • ${Number(result.percentage).toFixed(2)}%
                    </span>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-indigo-500" style="width:${Math.min(Number(result.percentage),100)}%"></div>
                </div>
            </div>
        `).join('');

        AdminUI.openModal('resultModal');
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
}

function deletePoll(id){
    AdminUI.deleteRequest(
        `/api/polls/${id}`,
        {
            message:'Delete this poll permanently?',
            successMessage:'Poll deleted successfully.',
            onSuccess:async()=>{
                await Promise.all([
                    loadPolls(currentPage),
                    loadStatistics()
                ]);
            }
        }
    );
}

function clearFilters(){
    el.search.value='';
    el.stateFilter.value='';
    loadPolls(1);
}

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

    el.grid.innerHTML=
        AdminUI.loadingState('Loading polls...');

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

@endsection