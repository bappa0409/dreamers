@extends('layouts.admin')

@section('title','Audit Logs')
@section('page_title','Audit Logs')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white py-4 px-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-clock-history text-base"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">
                    Audit Logs
                </h1>

                <p class="text-xs text-slate-500">
                    A record of who did what, when — across the system.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="refreshLogs()"
            class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            <i class="bi bi-arrow-clockwise text-[12px]"></i>
            Refresh
        </button>
    </div>


    {{-- =========================================================
    FILTERS
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            {{-- Search --}}
            <div class="lg:col-span-3">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Description or user..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >
                </div>
            </div>

            {{-- User --}}
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    User
                </label>

                <select
                    id="userFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All users</option>
                </select>
            </div>

            {{-- Module --}}
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Module
                </label>

                <select
                    id="moduleFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All modules</option>
                </select>
            </div>

            {{-- Action --}}
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Action
                </label>

                <select
                    id="actionFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-2.5 text-xs text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
                    <option value="">All actions</option>
                </select>
            </div>

            {{-- Date Range --}}
            <div class="lg:col-span-2">
                <label class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    Date Range
                </label>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date range"
                        autocomplete="off"
                    >
                </div>
            </div>

            {{-- Clear --}}
            <div class="lg:col-span-1">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3 text-xs font-semibold text-indigo-600 transition hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100"
                >
                    <i class="bi bi-x-circle me-1"></i>
                    Clear
                </button>
            </div>

        </div>
    </div>


    {{-- =========================================================
    TABLE
    ========================================================== --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-left text-base">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[13%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Date & Time
                        </th>

                        <th class="w-[15%] px-3 py-3 text-sm font-semibold text-slate-600">
                            User
                        </th>

                        <th class="w-[10%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Action
                        </th>

                        <th class="w-[11%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Module
                        </th>

                        <th class="w-[29%] px-3 py-3 text-sm font-semibold text-slate-600">
                            Description
                        </th>

                        <th class="w-[14%] px-3 py-3 text-sm font-semibold text-slate-600">
                            IP Address
                        </th>

                        <th class="w-[8%] px-3 py-3 text-right text-xs font-semibold text-slate-600">
                            Details
                        </th>
                    </tr>
                </thead>


                <tbody id="logsTableBody">
                    <tr>
                        <td
                            colspan="7"
                            class="px-5 py-10 text-center text-base text-slate-400"
                        >
                            Loading audit logs...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>


        <div
            id="logsPagination"
            class="border-t border-slate-200 px-4 py-3"
        ></div>

    </div>

</div>


{{-- =============================================================
DETAIL MODAL
============================================================= --}}
<div
    id="logDetailModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">

            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-file-text"></i>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Log Details
                    </h2>

                    <p
                        id="logDetailSubtitle"
                        class="mt-1 truncate text-sm text-slate-500"
                    ></p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('logDetailModal')"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>


        <div class="min-h-0 flex-1 overflow-y-auto p-5">

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        User
                    </p>

                    <p
                        id="detailUser"
                        class="mt-1 text-sm font-semibold text-slate-700"
                    >
                        —
                    </p>
                </div>

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Date & Time
                    </p>

                    <p
                        id="detailDate"
                        class="mt-1 text-sm font-semibold text-slate-700"
                    >
                        —
                    </p>
                </div>

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        IP Address
                    </p>

                    <p
                        id="detailIp"
                        class="mt-1 text-sm font-semibold text-slate-700"
                    >
                        —
                    </p>
                </div>

                <div class="rounded-md bg-slate-50 p-3">
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Subject
                    </p>

                    <p
                        id="detailSubject"
                        class="mt-1 break-words text-sm font-semibold text-slate-700"
                    >
                        —
                    </p>
                </div>

            </div>


            <div class="mt-4 rounded-md border border-slate-200 p-4">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                    Description
                </p>

                <p
                    id="detailDescription"
                    class="mt-2 whitespace-pre-wrap break-words text-sm leading-5 text-slate-600"
                >
                    —
                </p>
            </div>


            <div class="mt-4 rounded-md border border-slate-200 p-4">
                <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                    User Agent
                </p>

                <p
                    id="detailUserAgent"
                    class="mt-2 break-words text-[11px] leading-5 text-slate-500"
                >
                    —
                </p>
            </div>


            <div
                id="detailChangesWrap"
                class="mt-4 hidden"
            >
                <div class="mb-2 flex items-center gap-2">
                    <i class="bi bi-arrow-left-right text-sm text-indigo-500"></i>

                    <p class="text-sm font-semibold text-slate-700">
                        Changes
                    </p>
                </div>

                <div class="overflow-hidden rounded-md border border-slate-200">
                    <table class="w-full table-fixed text-left text-sm">

                        <thead class="bg-slate-50">
                            <tr>
                                <th class="w-[25%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Field
                                </th>

                                <th class="w-[37.5%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Before
                                </th>

                                <th class="w-[37.5%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    After
                                </th>
                            </tr>
                        </thead>

                        <tbody id="detailChangesBody"></tbody>

                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded',function(){

const tableBody=document.getElementById('logsTableBody');
const searchInput=document.getElementById('searchInput');
const userFilter=document.getElementById('userFilter');
const moduleFilter=document.getElementById('moduleFilter');
const actionFilter=document.getElementById('actionFilter');
const dateRangeFilter=document.getElementById('dateRangeFilter');

let currentPage=1;
let lastPage=1;
let total=0;


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

function formatRawDate(date){
    if(!date){
        return '';
    }

    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}


function getDateRange(){
    if(dateRangeFilter?._flatpickr){
        const dates=
            dateRangeFilter._flatpickr.selectedDates??[];

        return {
            from:dates[0]
                ?formatRawDate(dates[0])
                :'',

            to:dates[1]
                ?formatRawDate(dates[1])
                :''
        };
    }

    const value=
        dateRangeFilter?.value?.trim()??'';

    if(!value){
        return {
            from:'',
            to:''
        };
    }

    const [from='',to='']=
        value.split(' to ');

    return {
        from,
        to
    };
}


/*
|--------------------------------------------------------------------------
| Load Filter Options
|--------------------------------------------------------------------------
*/

async function loadFilterOptions(){
    try{
        const response=
            await api(
                '/api/activity-logs/filters'
            );

        const data=
            response.data??response??{};

        userFilter.innerHTML=
            '<option value="">All users</option>';

        (data.users??[]).forEach(user=>{
            const option=
                document.createElement('option');

            option.value=user.id;
            option.textContent=user.name;

            userFilter.appendChild(option);
        });


        moduleFilter.innerHTML=
            '<option value="">All modules</option>';

        (data.modules??[]).forEach(module=>{
            const option=
                document.createElement('option');

            option.value=module;
            option.textContent=
                titleCase(module);

            moduleFilter.appendChild(option);
        });


        actionFilter.innerHTML=
            '<option value="">All actions</option>';

        (data.actions??[]).forEach(action=>{
            const option=
                document.createElement('option');

            option.value=action;
            option.textContent=
                titleCase(action);

            actionFilter.appendChild(option);
        });

    }catch(error){
        console.error(
            'Activity log filter load failed:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Load Logs
|--------------------------------------------------------------------------
*/

async function loadLogs(page=1){
    currentPage=page;

    const range=
        getDateRange();

    const query=
        AdminUI.query({
            page,
            search:searchInput.value.trim(),
            user_id:userFilter.value,
            module:moduleFilter.value,
            action:actionFilter.value,
            from:range.from,
            to:range.to
        });


    tableBody.innerHTML=
        AdminUI.loadingState(
            'Loading audit logs...',
            7
        );


    try{
        const response=
            await api(
                `/api/activity-logs?${query}`
            );


        const paginator=
            response.data?.data
                ?response.data
                :response;


        const logs=
            paginator.data??[];


        currentPage=
            Number(
                paginator.current_page??1
            );

        lastPage=
            Number(
                paginator.last_page??1
            );

        total=
            Number(
                paginator.total??0
            );


        renderTable(logs);


        AdminUI.renderPagination({
            container:'logsPagination',
            currentPage,
            lastPage,
            total,
            onPageChange:loadLogs
        });

    }catch(error){
        tableBody.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                7
            );


        AdminUI.renderPagination({
            container:'logsPagination',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadLogs
        });
    }
}


/*
|--------------------------------------------------------------------------
| Render Table
|--------------------------------------------------------------------------
*/

function renderTable(logs){
    if(!logs.length){
        tableBody.innerHTML=
            AdminUI.emptyState(
                'No activity found for the selected filters.',
                7
            );

        return;
    }


    tableBody.innerHTML=
        logs.map(log=>`
            <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50/60">

                <td class="overflow-hidden px-3 py-3">
                    <p
                        class="truncate whitespace-nowrap text-sm text-slate-500"
                        title="${AdminUI.escapeHtml(AdminUI.formatDate(log.created_at,true))}"
                    >
                        ${AdminUI.formatDate(
                            log.created_at,
                            true
                        )}
                    </p>
                </td>


                <td class="min-w-0 overflow-hidden px-3 py-3">
                    <div class="min-w-0">

                        <p
                            class="truncate text-xs font-semibold text-slate-700"
                            title="${AdminUI.escapeHtml(log.user?.name||'System')}"
                        >
                            ${AdminUI.escapeHtml(
                                log.user?.name||
                                'System'
                            )}
                        </p>

                        ${
                            log.user?.email
                                ?`
                                    <p
                                        class="truncate text-[10px] text-slate-400"
                                        title="${AdminUI.escapeHtml(log.user.email)}"
                                    >
                                        ${AdminUI.escapeHtml(
                                            log.user.email
                                        )}
                                    </p>
                                `
                                :''
                        }

                    </div>
                </td>


                <td class="overflow-hidden px-3 py-3">
                    ${actionBadge(log.action)}
                </td>


                <td class="overflow-hidden px-3 py-3">
                    <span
                        class="block truncate rounded-md bg-slate-100 px-2 py-1 text-[10px] font-medium text-slate-600"
                        title="${AdminUI.escapeHtml(titleCase(log.module||'—'))}"
                    >
                        ${AdminUI.escapeHtml(
                            titleCase(
                                log.module||
                                '—'
                            )
                        )}
                    </span>
                </td>


                <td class="min-w-0 overflow-hidden px-3 py-3">
                    <p
                        class="truncate text-xs text-slate-600"
                        title="${AdminUI.escapeHtml(log.description||'')}"
                    >
                        ${AdminUI.escapeHtml(
                            log.description||
                            '—'
                        )}
                    </p>
                </td>


                <td class="overflow-hidden px-3 py-3">
                    <p
                        class="truncate font-mono text-[11px] text-slate-500"
                        title="${AdminUI.escapeHtml(log.ip_address||'—')}"
                    >
                        ${AdminUI.escapeHtml(
                            log.ip_address||
                            '—'
                        )}
                    </p>
                </td>


                <td class="px-3 py-3">
                    <div class="flex justify-end">
                        <button
                            type="button"
                            onclick="viewLogDetail(${log.id})"
                            title="View details"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                        >
                            <i class="bi bi-eye text-sm"></i>
                        </button>
                    </div>
                </td>

            </tr>
        `).join('');
}


/*
|--------------------------------------------------------------------------
| Action Badge
|--------------------------------------------------------------------------
*/

function actionBadge(action){
    const map={
        created:{
            className:'bg-emerald-50 text-emerald-700',
            icon:'bi-plus-circle'
        },

        updated:{
            className:'bg-amber-50 text-amber-700',
            icon:'bi-pencil'
        },

        deleted:{
            className:'bg-red-50 text-red-700',
            icon:'bi-trash3'
        },

        login:{
            className:'bg-sky-50 text-sky-700',
            icon:'bi-box-arrow-in-right'
        },

        logout:{
            className:'bg-slate-100 text-slate-600',
            icon:'bi-box-arrow-right'
        },

        login_failed:{
            className:'bg-red-50 text-red-700',
            icon:'bi-exclamation-triangle'
        },

        approved:{
            className:'bg-emerald-50 text-emerald-700',
            icon:'bi-check2-circle'
        },

        rejected:{
            className:'bg-red-50 text-red-700',
            icon:'bi-x-circle'
        },

        sent:{
            className:'bg-indigo-50 text-indigo-700',
            icon:'bi-send'
        },

        uploaded:{
            className:'bg-violet-50 text-violet-700',
            icon:'bi-upload'
        }
    };


    const config=
        map[action]??{
            className:'bg-slate-100 text-slate-600',
            icon:'bi-dot'
        };


    return `
        <span
            class="inline-flex max-w-full items-center gap-1 rounded-md px-2 py-1 text-[10px] font-semibold ${config.className}"
            title="${AdminUI.escapeHtml(titleCase(action))}"
        >
            <i class="bi ${config.icon} shrink-0 text-[10px]"></i>

            <span class="truncate">
                ${AdminUI.escapeHtml(
                    titleCase(action)
                )}
            </span>
        </span>
    `;
}


/*
|--------------------------------------------------------------------------
| Detail Modal
|--------------------------------------------------------------------------
*/

window.viewLogDetail=
async function(id){
    resetDetailModal();

    AdminUI.openModal(
        'logDetailModal'
    );

    try{
        const response=
            await api(
                `/api/activity-logs/${id}`
            );

        const log=
            response.data??response;


        document.getElementById(
            'logDetailSubtitle'
        ).textContent=
            `${
                titleCase(
                    log.action||
                    ''
                )
            } • ${
                titleCase(
                    log.module||
                    ''
                )
            }`;


        document.getElementById(
            'detailUser'
        ).textContent=
            log.user?.name||
            'System';


        document.getElementById(
            'detailDate'
        ).textContent=
            AdminUI.formatDate(
                log.created_at,
                true
            );


        document.getElementById(
            'detailIp'
        ).textContent=
            log.ip_address||
            '—';


        document.getElementById(
            'detailDescription'
        ).textContent=
            log.description||
            '—';


        document.getElementById(
            'detailSubject'
        ).textContent=
            log.subject_type
                ?`${String(log.subject_type).split('\\').pop()} #${log.subject_id??''}`
                :'—';


        document.getElementById(
            'detailUserAgent'
        ).textContent=
            log.user_agent||
            '—';


        renderChanges(
            log.old_values,
            log.new_values
        );

    }catch(error){
        document.getElementById(
            'detailDescription'
        ).textContent=
            AdminUI.extractError(
                error
            );
    }
};


function resetDetailModal(){
    document.getElementById(
        'logDetailSubtitle'
    ).textContent=
        'Loading...';

    document.getElementById(
        'detailUser'
    ).textContent=
        'Loading...';

    document.getElementById(
        'detailDate'
    ).textContent=
        '—';

    document.getElementById(
        'detailIp'
    ).textContent=
        '—';

    document.getElementById(
        'detailSubject'
    ).textContent=
        '—';

    document.getElementById(
        'detailDescription'
    ).textContent=
        '—';

    document.getElementById(
        'detailUserAgent'
    ).textContent=
        '—';

    document.getElementById(
        'detailChangesWrap'
    ).classList.add(
        'hidden'
    );

    document.getElementById(
        'detailChangesBody'
    ).innerHTML='';
}


/*
|--------------------------------------------------------------------------
| Render Changes
|--------------------------------------------------------------------------
*/

function renderChanges(
    oldValues,
    newValues
){
    const wrap=
        document.getElementById(
            'detailChangesWrap'
        );

    const body=
        document.getElementById(
            'detailChangesBody'
        );


    const oldData=
        normalizeObject(
            oldValues
        );

    const newData=
        normalizeObject(
            newValues
        );


    const keys=
        new Set([
            ...Object.keys(
                oldData
            ),

            ...Object.keys(
                newData
            )
        ]);


    if(!keys.size){
        wrap.classList.add(
            'hidden'
        );

        body.innerHTML='';

        return;
    }


    body.innerHTML=
        [...keys].map(key=>{
            const before=
                oldData[key];

            const after=
                newData[key];

            return `
                <tr class="border-b border-slate-100 last:border-0">

                    <td class="overflow-hidden px-3 py-2">
                        <p
                            class="truncate font-medium text-slate-600"
                            title="${AdminUI.escapeHtml(titleCase(key))}"
                        >
                            ${AdminUI.escapeHtml(
                                titleCase(key)
                            )}
                        </p>
                    </td>

                    <td class="overflow-hidden px-3 py-2 text-red-600">
                        <div class="break-words">
                            ${formatValue(before)}
                        </div>
                    </td>

                    <td class="overflow-hidden px-3 py-2 text-emerald-700">
                        <div class="break-words">
                            ${formatValue(after)}
                        </div>
                    </td>

                </tr>
            `;
        }).join('');


    wrap.classList.remove(
        'hidden'
    );
}


/*
|--------------------------------------------------------------------------
| Normalize Object
|--------------------------------------------------------------------------
*/

function normalizeObject(value){
    if(!value){
        return {};
    }

    if(
        typeof value==='object'&&
        !Array.isArray(value)
    ){
        return value;
    }

    if(typeof value==='string'){
        try{
            const parsed=
                JSON.parse(value);

            return (
                parsed&&
                typeof parsed==='object'&&
                !Array.isArray(parsed)
            )
                ?parsed
                :{};

        }catch{
            return {};
        }
    }

    return {};
}


/*
|--------------------------------------------------------------------------
| Format Value
|--------------------------------------------------------------------------
*/

function formatValue(value){
    if(
        value===null||
        value===undefined||
        value===''
    ){
        return `
            <span class="text-slate-300">
                —
            </span>
        `;
    }

    if(typeof value==='boolean'){
        return value
            ?'Yes'
            :'No';
    }

    if(typeof value==='object'){
        return AdminUI.escapeHtml(
            JSON.stringify(value)
        );
    }

    return AdminUI.escapeHtml(
        String(value)
    );
}


/*
|--------------------------------------------------------------------------
| Helper
|--------------------------------------------------------------------------
*/

function titleCase(value){
    return String(value??'')
        .replace(/_/g,' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

window.clearFilters=
function(){
    searchInput.value='';
    userFilter.value='';
    moduleFilter.value='';
    actionFilter.value='';

    if(dateRangeFilter?._flatpickr){
        dateRangeFilter
            ._flatpickr
            .clear();
    }else if(dateRangeFilter){
        dateRangeFilter.value='';
    }

    loadLogs(1);
};


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

window.refreshLogs=
async function(){
    await loadLogs(
        currentPage
    );

    if(
        typeof window.Toast!=='undefined'
    ){
        Toast.success(
            'Audit logs refreshed.'
        );
    }
};


/*
|--------------------------------------------------------------------------
| Filter Events
|--------------------------------------------------------------------------
*/

function registerFilters(){
    searchInput.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadLogs(1)
        )
    );


    [
        userFilter,
        moduleFilter,
        actionFilter
    ].forEach(element=>{
        element.addEventListener(
            'change',
            ()=>loadLogs(1)
        );
    });


    if(dateRangeFilter?._flatpickr){
        dateRangeFilter
            ._flatpickr
            .config
            .onClose
            .push(
                selectedDates=>{
                    if(
                        selectedDates.length===0||
                        selectedDates.length===2
                    ){
                        loadLogs(1);
                    }
                }
            );
    }else{
        dateRangeFilter?.addEventListener(
            'change',
            ()=>loadLogs(1)
        );
    }
}


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initAuditLogsPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initAuditLogsPage,
            50
        );

        return;
    }


    if(
        typeof window.initDatePickers==='function'
    ){
        window.initDatePickers();
    }


    registerFilters();


    await Promise.all([
        loadFilterOptions(),
        loadLogs()
    ]);
}


initAuditLogsPage();

});
</script>

@endpush