@extends('layouts.admin')

@section('title','Audit Logs')
@section('page_title','Audit Logs')

@section('content')

<div class="space-y-3">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div
        class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white py-4 px-5 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                <i class="bi bi-clock-history text-base"></i>
            </div>

            <div>
                <h1 class="text-sm 2xl:text-base font-bold tracking-tight text-slate-800">
                    Audit Logs
                </h1>

                <p class=" text-xs 2xl:text-sm text-slate-500">
                    A record of who did what, when — across the system.
                </p>
            </div>
        </div>

        <button type="button" onclick="refreshLogs()"
            class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise text-[12px]"></i>
            Refresh
        </button>
    </div>


    {{-- =========================================================
    FILTERS
    ========================================================== --}}

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            {{-- Header --}}
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Activity Log
                    </p>
                    <p class="hidden text-[11px] text-slate-500 sm:block">
                        Search by description or user.
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center gap-2 lg:w-auto">

                {{-- Search --}}
                <div class="relative min-w-0 flex-1 lg:w-[280px]">
                    <i
                        class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                    <input id="searchInput" type="text" placeholder="Description or user..."
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
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                {{-- User --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        User
                    </label>

                    <select id="userFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All users</option>
                    </select>
                </div>

                {{-- Module --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Module
                    </label>

                    <select id="moduleFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All modules</option>
                    </select>
                </div>

                {{-- Action --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Action
                    </label>

                    <select id="actionFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All actions</option>
                    </select>
                </div>

                {{-- Date Range --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Date Range
                    </label>

                    <div class="relative">
                        <i
                            class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                        <input id="dateRangeFilter" type="text"
                            class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm"
                            placeholder="Select date range" autocomplete="off">
                    </div>
                </div>

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
                        <th class="w-[13%] px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-600">
                            Date & Time
                        </th>

                        <th class="w-[15%] px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-600">
                            User
                        </th>

                        <th class="w-[16%] px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-600">
                            Action / Module
                        </th>

                        <th class="w-[34%] px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-600">
                            Description
                        </th>

                        <th class="w-[14%] px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-600">
                            IP Address
                        </th>

                        <th class="w-[8%] px-3 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">
                            Details
                        </th>
                    </tr>
                </thead>


                <tbody id="logsTableBody">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-xs 2xl:text-sm text-slate-500">
                            Loading audit logs...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>


        <div id="logsPagination" class="border-t border-slate-200 px-4 py-3"></div>

    </div>

</div>


{{-- =============================================================
DETAIL MODAL
============================================================= --}}
<div id="logDetailModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">

            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                    <i class="bi bi-file-text"></i>
                </div>

                <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-800">
                        Log Details
                    </h2>

                    <p id="logDetailSubtitle" class="mt-1 truncate text-xs 2xl:text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('logDetailModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>


        <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">

            {{-- Activity Overview --}}
            <section class="rounded-lg border border-slate-200 bg-white p-5 transition">

                {{-- Header --}}
                <div class="mb-5 flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                        <i class="bi bi-person-vcard text-base"></i>
                    </div>

                    <div>
                        <h3 class="text-base font-bold tracking-tight text-slate-800">
                            Activity Overview
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500 2xl:text-sm">
                            Who performed this action, when and from where.
                        </p>
                    </div>
                </div>

                {{-- Activity Information --}}
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">

                    {{-- User --}}
                    <div class="rounded-md border border-slate-200 bg-slate-50/70 p-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bi bi-person text-sm text-slate-500"></i>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                                    User
                                </span>
                            </div>
                        </div>

                        <p id="detailUser" class="mt-2 break-words text-xs 2xl:text-sm font-semibold text-slate-700">
                            —
                        </p>
                    </div>

                    {{-- Date & Time --}}
                    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bi bi-calendar3 text-sm text-indigo-400"></i>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-indigo-500">
                                    Date & Time
                                </span>
                            </div>
                        </div>

                        <p id="detailDate" class="mt-2 break-words text-xs 2xl:text-sm font-semibold text-indigo-700">
                            —
                        </p>
                    </div>

                    {{-- IP Address --}}
                    <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bi bi-globe2 text-sm text-emerald-400"></i>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-emerald-600">
                                    IP Address
                                </span>
                            </div>
                        </div>

                        <p id="detailIp" class="mt-2 break-words text-xs 2xl:text-sm font-semibold text-emerald-700">
                            —
                        </p>
                    </div>

                    {{-- Subject --}}
                    <div class="rounded-md border border-violet-200 bg-violet-50/50 p-3.5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bi bi-file-earmark-text text-sm text-violet-400"></i>
                                <span class="text-[11px] font-medium uppercase tracking-wide text-violet-600">
                                    Subject
                                </span>
                            </div>
                        </div>

                        <p id="detailSubject"
                            class="mt-2 break-words text-xs 2xl:text-sm font-semibold text-violet-700">
                            —
                        </p>
                    </div>

                </div>

                {{-- Description --}}
                <div class="mt-3 rounded-md border border-slate-200 bg-white p-3.5">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-card-text text-sm text-slate-500"></i>
                        <span class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                            Description
                        </span>
                    </div>

                    <p id="detailDescription"
                        class="mt-2 whitespace-pre-wrap break-words text-sm leading-5 text-slate-600">
                        —
                    </p>
                </div>

                {{-- User Agent --}}
                <div class="mt-3 rounded-md border border-slate-200 bg-slate-50/70 p-3.5">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-pc-display text-sm text-slate-500"></i>
                        <span class="text-[11px] font-medium uppercase tracking-wide text-slate-500">
                            User Agent
                        </span>
                    </div>

                    <p id="detailUserAgent" class="mt-2 break-words text-[11px] leading-5 text-slate-500">
                        —
                    </p>
                </div>

            </section>

            {{-- Changes --}}
            <section id="detailChangesWrap" class="hidden rounded-md border border-slate-200 bg-white p-4">
                <div class="mb-4 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Changes</h3>
                        <p class="text-[11px] text-slate-500">Field-level changes recorded for this action.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-md border border-slate-200">
                    <table class="w-full table-fixed text-left text-xs 2xl:text-sm">

                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="w-[25%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Field
                                </th>

                                <th
                                    class="w-[37.5%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    Before
                                </th>

                                <th
                                    class="w-[37.5%] px-3 py-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                    After
                                </th>
                            </tr>
                        </thead>

                        <tbody id="detailChangesBody"></tbody>

                    </table>
                </div>
            </section>

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
            6
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
                6
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
                6
            );

        return;
    }


    tableBody.innerHTML=
        logs.map(log=>`
            <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50/60">

                <td class="overflow-hidden px-3 py-3">
                    ${(()=>{
                        const {datePart,timePart}=
                            splitDateTime(log.created_at);

                        return `
                            <p class="truncate text-xs 2xl:text-sm font-medium text-slate-700" title="${AdminUI.escapeHtml(datePart)} ${AdminUI.escapeHtml(timePart)}">
                                ${AdminUI.escapeHtml(datePart)}
                            </p>
                            <p class="truncate text-[10px] text-slate-500">
                                ${AdminUI.escapeHtml(timePart)}
                            </p>
                        `;
                    })()}
                </td>


                <td class="min-w-0 overflow-hidden px-3 py-3">
                    <div class="min-w-0">

                        <p
                            class="truncate text-xs 2xl:text-sm font-semibold text-slate-700"
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
                                        class="truncate text-[10px] text-slate-500"
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
                    <div class="flex flex-col items-start gap-1">
                        ${actionBadge(log.action)}

                        <span
                            class="block max-w-full truncate rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600"
                            title="${AdminUI.escapeHtml(titleCase(log.module||'—'))}"
                        >
                            ${AdminUI.escapeHtml(
                                titleCase(
                                    log.module||
                                    '—'
                                )
                            )}
                        </span>
                    </div>
                </td>


                <td class="min-w-0 overflow-hidden px-3 py-3">
                    <p
                        class="truncate text-xs 2xl:text-sm text-slate-600"
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
                            class="truncate text-xs 2xl:text-sm font-medium text-slate-600"
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
| Split Date & Time
|--------------------------------------------------------------------------
*/

function splitDateTime(value){
    const datePart=
        AdminUI.formatDate(value);

    const full=
        AdminUI.formatDate(
            value,
            true
        );

    const timePart=
        full
            .slice(datePart.length)
            .trim();

    return {
        datePart,
        timePart
    };
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