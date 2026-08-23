@extends('layouts.member')

@section('title','Notifications')
@section('page-title','Notifications')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-bell-fill"></i>
            </div>

            <div>
                <h1 class="text-lg font-bold tracking-tight text-slate-800">
                    My Notifications
                </h1>

                <p class="mt-0.5 text-xs text-slate-500">
                    View payment, share, investment, notice, poll and other account updates.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                onclick="loadNotificationPage(1)"
                class="inline-flex h-9 items-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">

                <i class="bi bi-arrow-clockwise"></i>
                Refresh
            </button>

            <button
                id="pageMarkAllButton"
                type="button"
                onclick="markAllPageNotificationsRead()"
                class="inline-flex h-9 items-center gap-2 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">

                <i class="bi bi-check2-all"></i>
                Mark All Read
            </button>
        </div>
    </div>

    {{-- Info --}}
    <div class="flex gap-3 rounded-lg border border-sky-200 bg-sky-50/60 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
            <i class="bi bi-info-circle"></i>
        </div>

        <div>
            <p class="text-xs font-semibold text-sky-800">
                Notification Center
            </p>

            <p class="mt-0.5 text-[11px] leading-5 text-sky-700">
                Unread notifications are highlighted. Opening a notification automatically marks it as read.
            </p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Total
                    </p>

                    <p
                        id="notificationSummaryTotal"
                        class="mt-2 text-xl font-bold text-slate-800">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-bell"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">
                        Unread
                    </p>

                    <p
                        id="notificationSummaryUnread"
                        class="mt-2 text-xl font-bold text-amber-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-envelope-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                        Read
                    </p>

                    <p
                        id="notificationSummaryRead"
                        class="mt-2 text-xl font-bold text-emerald-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">
                        Today
                    </p>

                    <p
                        id="notificationSummaryToday"
                        class="mt-2 text-xl font-bold text-violet-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                    <i class="bi bi-calendar2-day"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-7">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="notificationPageSearch"
                        type="text"
                        placeholder="Search title or message..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Status
                </label>

                <select
                    id="notificationPageStatus"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">

                    <option value="">
                        All Notifications
                    </option>

                    <option value="unread">
                        Unread
                    </option>

                    <option value="read">
                        Read
                    </option>
                </select>
            </div>

            <div class="lg:col-span-2">
                <button
                    type="button"
                    onclick="clearNotificationPageFilters()"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">

                    Clear
                </button>
            </div>

        </div>
    </div>

    {{-- List --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="bi bi-bell-fill"></i>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Notification History
                </h2>

                <p class="text-[11px] text-slate-400">
                    All notifications sent to your account
                </p>
            </div>
        </div>

        <div
            id="notificationPageList"
            class="divide-y divide-slate-100">

            <div class="py-14 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                <p class="mt-3 text-xs text-slate-400">
                    Loading notifications...
                </p>
            </div>
        </div>

        <div id="notificationPagePagination"></div>
    </div>
</div>

{{-- Details Modal --}}
<div
    id="notificationDetailsModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

    <div class="flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">

            <div class="flex items-center gap-3">
                <div
                    id="notificationDetailsIcon"
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">

                    <i class="bi bi-bell"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Notification Details
                    </h2>

                    <p
                        id="notificationDetailsTime"
                        class="text-[11px] text-slate-400">
                        -
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeNotificationDetailsModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">

                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div
            id="notificationDetailsBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

            <button
                type="button"
                onclick="closeNotificationDetailsModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">

                Close
            </button>

            <a
                id="notificationDetailsAction"
                href="#"
                class="hidden h-9 items-center gap-1.5 rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700">

                <i class="bi bi-box-arrow-up-right"></i>

                Open
            </a>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
let notificationPageItems=[];
let notificationPageCurrent=1;
let notificationPageLast=1;
let notificationPageTotal=0;
let notificationSearchTimer=null;

const notificationPageSearch=
    document.getElementById(
        'notificationPageSearch'
    );

const notificationPageStatus=
    document.getElementById(
        'notificationPageStatus'
    );

async function loadNotificationPage(page=1){
    notificationPageCurrent=page;

    const list=
        document.getElementById(
            'notificationPageList'
        );

    list.innerHTML=`
        <div class="py-14 text-center">
            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

            <p class="mt-3 text-xs text-slate-400">
                Loading notifications...
            </p>
        </div>
    `;

    try{
        const params=
            new URLSearchParams({
                page:String(page),
                per_page:'15'
            });

        const search=
            notificationPageSearch
                .value
                .trim();

        const status=
            notificationPageStatus.value;

        if(search){
            params.set(
                'search',
                search
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        |
        | If your NotificationController already supports status=read/unread,
        | this will be handled server-side.
        |
        | If not, current page data is filtered client-side below.
        |
        */
        if(status){
            params.set(
                'status',
                status
            );
        }

        const response=
            await api(
                `/api/notifications?${params.toString()}`
            );

        const paginator=
            normalizeNotificationPaginator(
                response
            );

        notificationPageItems=
            paginator.items;

        notificationPageCurrent=
            paginator.currentPage;

        notificationPageLast=
            paginator.lastPage;

        notificationPageTotal=
            paginator.total;

        if(status){
            notificationPageItems=
                notificationPageItems.filter(
                    notification=>
                        status==='unread'
                            ?!notification.read_at
                            :Boolean(
                                notification.read_at
                            )
                );
        }

        updateNotificationPageSummary(
            response
        );

        renderNotificationPageItems();

        renderNotificationPagePagination();

    }catch(error){
        list.innerHTML=`
            <div class="py-14 text-center">

                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                    <i class="bi bi-exclamation-circle text-lg"></i>
                </div>

                <p class="mt-3 text-sm font-semibold text-red-600">
                    Failed to load notifications
                </p>

                <p class="mt-1 text-xs text-red-400">
                    ${escapeNotificationPageHtml(
                        extractNotificationPageError(
                            error
                        )
                    )}
                </p>

                <button
                    type="button"
                    onclick="loadNotificationPage(${notificationPageCurrent})"
                    class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50">

                    <i class="bi bi-arrow-clockwise"></i>

                    Try Again
                </button>

            </div>
        `;

        document.getElementById(
            'notificationPagePagination'
        ).innerHTML='';
    }
}

function normalizeNotificationPaginator(
    response
){
    const data=
        response.data??{};

    if(
        data&&
        typeof data==='object'&&
        !Array.isArray(data)&&
        Array.isArray(data.data)
    ){
        return{
            items:data.data,
            currentPage:Number(
                data.current_page??1
            ),
            lastPage:Number(
                data.last_page??1
            ),
            total:Number(
                data.total??
                data.data.length
            )
        };
    }

    if(Array.isArray(data)){
        return{
            items:data,
            currentPage:1,
            lastPage:1,
            total:data.length
        };
    }

    return{
        items:[],
        currentPage:1,
        lastPage:1,
        total:0
    };
}

async function updateNotificationPageSummary(
    response=null
){
    let unread=0;

    try{
        const countResponse=
            await api(
                '/api/notifications/unread-count'
            );

        unread=Number(
            countResponse.data?.count??
            countResponse.count??
            0
        );

    }catch{}

    const totalFromResponse=
        Number(
            response?.summary?.total??
            response?.data?.total??
            notificationPageTotal??
            0
        );

    const total=
        Math.max(
            totalFromResponse,
            unread
        );

    const read=
        Math.max(
            total-unread,
            0
        );

    /*
    |--------------------------------------------------------------------------
    | Today
    |--------------------------------------------------------------------------
    |
    | Uses today's records from the current response when API has no dedicated
    | summary. If controller later returns summary.today it is used instead.
    |
    */
    const todayFromSummary=
        response?.summary?.today;

    const today=
        todayFromSummary!==undefined
            ?Number(todayFromSummary)
            :notificationPageItems.filter(
                item=>
                    isNotificationToday(
                        item.created_at
                    )
            ).length;

    document.getElementById(
        'notificationSummaryTotal'
    ).textContent=
        total;

    document.getElementById(
        'notificationSummaryUnread'
    ).textContent=
        unread;

    document.getElementById(
        'notificationSummaryRead'
    ).textContent=
        read;

    document.getElementById(
        'notificationSummaryToday'
    ).textContent=
        today;

    const markAllButton=
        document.getElementById(
            'pageMarkAllButton'
        );

    markAllButton.disabled=
        unread<=0;

    syncNotificationPageBadges(
        unread
    );
}

function renderNotificationPageItems(){
    const list=
        document.getElementById(
            'notificationPageList'
        );

    if(!notificationPageItems.length){
        list.innerHTML=`
            <div class="py-16 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                    <i class="bi bi-bell text-xl"></i>
                </div>

                <p class="mt-4 text-sm font-semibold text-slate-700">
                    No notifications found
                </p>

                <p class="mt-1 text-xs text-slate-400">
                    No notifications match your current filters.
                </p>

            </div>
        `;

        return;
    }

    list.innerHTML=
        notificationPageItems
            .map(
                notification=>
                    renderNotificationPageRow(
                        notification
                    )
            )
            .join('');
}

function renderNotificationPageRow(
    notification
){
    const unread=
        !notification.read_at;

    const meta=
        notificationPageMeta(
            notification
        );

    const url=
        notificationPageUrl(
            notification
        );

    return`
        <article
            class="relative transition ${
                unread
                    ?'bg-indigo-50/30'
                    :'bg-white'
            } hover:bg-slate-50/70">

            ${
                unread
                    ?`
                        <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500"></div>
                    `
                    :''
            }

            <div class="flex gap-4 px-5 py-4">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${meta.className}">
                    <i class="bi ${meta.icon}"></i>
                </div>

                <div class="min-w-0 flex-1">

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="text-sm font-semibold ${
                                    unread
                                        ?'text-slate-900'
                                        :'text-slate-700'
                                }">

                                    ${escapeNotificationPageHtml(
                                        notification.title??
                                        notification.data?.title??
                                        'Notification'
                                    )}

                                </h3>

                                ${
                                    unread
                                        ?`
                                            <span class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wide text-indigo-600">
                                                New
                                            </span>
                                        `
                                        :''
                                }

                                <span class="rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-[9px] font-semibold text-slate-500">
                                    ${escapeNotificationPageHtml(
                                        meta.label
                                    )}
                                </span>

                            </div>

                            <p class="mt-1.5 max-w-4xl text-xs leading-5 text-slate-500">

                                ${escapeNotificationPageHtml(
                                    notification.message??
                                    notification.data?.message??
                                    ''
                                )}

                            </p>

                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate-400">

                                <span class="inline-flex items-center gap-1">

                                    <i class="bi bi-clock"></i>

                                    ${formatNotificationPageDateTime(
                                        notification.created_at
                                    )}

                                </span>

                                ${
                                    notification.read_at
                                        ?`
                                            <span class="inline-flex items-center gap-1">

                                                <i class="bi bi-check2-circle"></i>

                                                Read

                                            </span>
                                        `
                                        :`
                                            <span class="inline-flex items-center gap-1 text-indigo-500">

                                                <i class="bi bi-circle-fill text-[6px]"></i>

                                                Unread

                                            </span>
                                        `
                                }

                            </div>

                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-1.5">

                            <button
                                type="button"
                                onclick="openNotificationDetails('${escapeNotificationPageJs(notification.id)}')"
                                class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">

                                <i class="bi bi-eye"></i>

                                View
                            </button>

                            ${
                                url
                                    ?`
                                        <button
                                            type="button"
                                            onclick="goToNotificationTarget('${escapeNotificationPageJs(notification.id)}')"
                                            class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-sky-200 hover:bg-sky-50 hover:text-sky-700">

                                            <i class="bi bi-box-arrow-up-right"></i>

                                            Open
                                        </button>
                                    `
                                    :''
                            }

                            ${
                                unread
                                    ?`
                                        <button
                                            type="button"
                                            onclick="markPageNotificationRead('${escapeNotificationPageJs(notification.id)}')"
                                            title="Mark as read"
                                            class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">

                                            <i class="bi bi-check2"></i>

                                        </button>
                                    `
                                    :''
                            }

                        </div>

                    </div>

                </div>
            </div>
        </article>
    `;
}

function renderNotificationPagePagination(){
    const container=
        document.getElementById(
            'notificationPagePagination'
        );

    if(notificationPageLast<=1){
        container.innerHTML='';
        return;
    }

    const perPage=15;

    const from=
        notificationPageTotal===0
            ?0
            :(
                (
                    notificationPageCurrent-1
                )*perPage
            )+1;

    const to=
        Math.min(
            notificationPageCurrent*
            perPage,
            notificationPageTotal
        );

    container.innerHTML=`
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-[11px] text-slate-500">

                Showing

                <span class="font-semibold text-slate-700">
                    ${from}
                </span>

                –

                <span class="font-semibold text-slate-700">
                    ${to}
                </span>

                of

                <span class="font-semibold text-slate-700">
                    ${notificationPageTotal}
                </span>

                notifications

            </p>

            <div class="flex items-center gap-1">

                <button
                    type="button"
                    ${notificationPageCurrent<=1?'disabled':''}
                    onclick="loadNotificationPage(${notificationPageCurrent-1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    <i class="bi bi-chevron-left"></i>

                    Previous
                </button>

                <span class="px-3 text-[11px] font-medium text-slate-500">
                    ${notificationPageCurrent}
                    /
                    ${notificationPageLast}
                </span>

                <button
                    type="button"
                    ${notificationPageCurrent>=notificationPageLast?'disabled':''}
                    onclick="loadNotificationPage(${notificationPageCurrent+1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    Next

                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>

        </div>
    `;
}

window.openNotificationDetails=
async function(id){
    const local=
        notificationPageItems.find(
            item=>
                String(item.id)===
                String(id)
        );

    if(!local){
        if(window.Toast){
            Toast.error(
                'Notification not found.'
            );
        }

        return;
    }

    const modal=
        document.getElementById(
            'notificationDetailsModal'
        );

    modal.classList.remove(
        'hidden'
    );

    modal.classList.add(
        'flex'
    );

    document.body.classList.add(
        'overflow-hidden'
    );

    renderNotificationDetails(
        local
    );

    if(!local.read_at){
        try{
            const response=
                await api(
                    `/api/notifications/${id}/read`,
                    {
                        method:'PATCH'
                    }
                );

            local.read_at=
                response.data?.read_at??
                new Date().toISOString();

            renderNotificationDetails(
                local
            );

            await updateNotificationPageSummary();

            if(
                typeof window.loadNotifications===
                'function'
            ){
                window.loadNotifications();
            }

        }catch(error){
            console.error(error);
        }
    }

    renderNotificationPageItems();
};

function renderNotificationDetails(
    notification
){
    const meta=
        notificationPageMeta(
            notification
        );

    const title=
        notification.title??
        notification.data?.title??
        'Notification';

    const message=
        notification.message??
        notification.data?.message??
        '';

    const url=
        notificationPageUrl(
            notification
        );

    const icon=
        document.getElementById(
            'notificationDetailsIcon'
        );

    icon.className=
        `flex h-9 w-9 items-center justify-center rounded-lg ${meta.className}`;

    icon.innerHTML=
        `<i class="bi ${meta.icon}"></i>`;

    document.getElementById(
        'notificationDetailsTime'
    ).textContent=
        formatNotificationPageDateTime(
            notification.created_at
        );

    document.getElementById(
        'notificationDetailsBody'
    ).innerHTML=`
        <div class="space-y-5">

            <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-5">

                <div class="flex items-start gap-4">

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${meta.className}">
                        <i class="bi ${meta.icon} text-lg"></i>
                    </div>

                    <div class="min-w-0">

                        <div class="flex flex-wrap items-center gap-2">

                            <span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold text-slate-500">
                                ${escapeNotificationPageHtml(
                                    meta.label
                                )}
                            </span>

                            ${
                                notification.read_at
                                    ?`
                                        <span class="rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-600">
                                            Read
                                        </span>
                                    `
                                    :`
                                        <span class="rounded-md border border-indigo-200 bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-600">
                                            Unread
                                        </span>
                                    `
                            }

                        </div>

                        <h3 class="mt-3 text-base font-bold leading-6 text-slate-800">
                            ${escapeNotificationPageHtml(
                                title
                            )}
                        </h3>

                        <p class="mt-2 whitespace-pre-line break-words text-sm leading-7 text-slate-600">
                            ${escapeNotificationPageHtml(
                                message
                            )}
                        </p>

                    </div>

                </div>

            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                ${notificationInfoRow(
                    'Type',
                    meta.label
                )}

                ${notificationInfoRow(
                    'Received',
                    formatNotificationPageDateTime(
                        notification.created_at
                    )
                )}

                ${notificationInfoRow(
                    'Read At',
                    notification.read_at
                        ?formatNotificationPageDateTime(
                            notification.read_at
                        )
                        :'Not read yet'
                )}

            </div>

        </div>
    `;

    const action=
        document.getElementById(
            'notificationDetailsAction'
        );

    if(url){
        action.href=url;

        action.classList.remove(
            'hidden'
        );

        action.classList.add(
            'inline-flex'
        );

    }else{
        action.href='#';

        action.classList.add(
            'hidden'
        );

        action.classList.remove(
            'inline-flex'
        );
    }
}

function notificationInfoRow(
    label,
    value
){
    return`
        <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-3 last:border-0 sm:flex-row sm:items-center sm:justify-between">

            <span class="text-[11px] text-slate-500">
                ${escapeNotificationPageHtml(
                    label
                )}
            </span>

            <span class="text-xs font-semibold text-slate-700">
                ${escapeNotificationPageHtml(
                    value??'-'
                )}
            </span>

        </div>
    `;
}

window.closeNotificationDetailsModal=
function(){
    const modal=
        document.getElementById(
            'notificationDetailsModal'
        );

    modal.classList.add(
        'hidden'
    );

    modal.classList.remove(
        'flex'
    );

    document.body.classList.remove(
        'overflow-hidden'
    );
};

window.markPageNotificationRead=
async function(id){
    try{
        await api(
            `/api/notifications/${id}/read`,
            {
                method:'PATCH'
            }
        );

        const item=
            notificationPageItems.find(
                notification=>
                    String(
                        notification.id
                    )===String(id)
            );

        if(item){
            item.read_at=
                new Date().toISOString();
        }

        renderNotificationPageItems();

        await updateNotificationPageSummary();

        if(
            typeof window.loadNotifications===
            'function'
        ){
            window.loadNotifications();
        }

        if(window.Toast){
            Toast.success(
                'Notification marked as read.'
            );
        }

    }catch(error){
        if(window.Toast){
            Toast.error(
                extractNotificationPageError(
                    error
                )
            );
        }
    }
};

window.markAllPageNotificationsRead=
async function(){
    const button=
        document.getElementById(
            'pageMarkAllButton'
        );

    if(
        !button||
        button.disabled
    ){
        return;
    }

    const original=
        button.innerHTML;

    button.disabled=true;

    button.innerHTML=`
        <i class="bi bi-arrow-repeat animate-spin"></i>
        Updating...
    `;

    try{
        await api(
            '/api/notifications/read-all',
            {
                method:'PATCH'
            }
        );

        notificationPageItems
            .forEach(
                notification=>{
                    if(
                        !notification.read_at
                    ){
                        notification.read_at=
                            new Date()
                                .toISOString();
                    }
                }
            );

        renderNotificationPageItems();

        syncNotificationPageBadges(0);

        document.getElementById(
            'notificationSummaryUnread'
        ).textContent='0';

        const total=
            Number(
                document.getElementById(
                    'notificationSummaryTotal'
                ).textContent??
                0
            );

        document.getElementById(
            'notificationSummaryRead'
        ).textContent=
            total;

        if(
            typeof window.loadNotifications===
            'function'
        ){
            window.loadNotifications();
        }

        if(window.Toast){
            Toast.success(
                'All notifications marked as read.'
            );
        }

    }catch(error){
        if(window.Toast){
            Toast.error(
                extractNotificationPageError(
                    error
                )
            );
        }

    }finally{
        button.disabled=false;
        button.innerHTML=original;

        await updateNotificationPageSummary();
    }
};

window.goToNotificationTarget=
async function(id){
    const notification=
        notificationPageItems.find(
            item=>
                String(item.id)===
                String(id)
        );

    if(!notification){
        return;
    }

    try{
        if(!notification.read_at){
            await api(
                `/api/notifications/${id}/read`,
                {
                    method:'PATCH'
                }
            );
        }

    }catch(error){
        console.error(error);
    }

    const url=
        notificationPageUrl(
            notification
        );

    if(url){
        window.location.href=url;
    }
};

window.clearNotificationPageFilters=
function(){
    notificationPageSearch.value='';
    notificationPageStatus.value='';

    loadNotificationPage(1);
};

function notificationPageMeta(
    notification
){
    const value=
        String(
            notification.data?.type??
            notification.type??
            notification.title??
            ''
        ).toLowerCase();

    if(
        value.includes('subscription')||
        value.includes('payment')
    ){
        return{
            label:'Subscription',
            icon:'bi-credit-card',
            className:
                'bg-emerald-50 text-emerald-600'
        };
    }

    if(value.includes('share')){
        return{
            label:'Share',
            icon:'bi-layers-fill',
            className:
                'bg-violet-50 text-violet-600'
        };
    }

    if(value.includes('investment')){
        return{
            label:'Investment',
            icon:'bi-graph-up-arrow',
            className:
                'bg-indigo-50 text-indigo-600'
        };
    }

    if(value.includes('notice')){
        return{
            label:'Notice',
            icon:'bi-megaphone-fill',
            className:
                'bg-sky-50 text-sky-600'
        };
    }

    if(value.includes('poll')){
        return{
            label:'Poll',
            icon:'bi-bar-chart-fill',
            className:
                'bg-amber-50 text-amber-600'
        };
    }

    if(
        value.includes('security')||
        value.includes('login')||
        value.includes('password')
    ){
        return{
            label:'Security',
            icon:'bi-shield-check',
            className:
                'bg-red-50 text-red-600'
        };
    }

    if(value.includes('member')){
        return{
            label:'Membership',
            icon:'bi-person-check',
            className:
                'bg-cyan-50 text-cyan-600'
        };
    }

    return{
        label:'General',
        icon:'bi-bell',
        className:
            'bg-slate-100 text-slate-600'
    };
}

function notificationPageUrl(
    notification
){
    const raw=
        notification.url??
        notification.action_url??
        notification.data?.url??
        notification.data?.action_url??
        null;

    if(!raw){
        return null;
    }

    const value=
        String(raw).trim();

    if(
        value.startsWith('/')&&
        !value.startsWith('//')
    ){
        return value;
    }

    try{
        const url=
            new URL(
                value,
                window.location.origin
            );

        if(
            url.origin===
            window.location.origin
        ){
            return(
                url.pathname+
                url.search+
                url.hash
            );
        }

    }catch{}

    return null;
}

function syncNotificationPageBadges(
    count
){
    count=Number(
        count??0
    );

    const value=
        count>99
            ?'99+'
            :String(count);

    const topBadge=
        document.getElementById(
            'notificationBadge'
        );

    const sidebarBadge=
        document.getElementById(
            'sidebarNotificationBadge'
        );

    [topBadge,sidebarBadge]
        .filter(Boolean)
        .forEach(
            badge=>{
                badge.textContent=value;

                badge.classList.toggle(
                    'hidden',
                    count<=0
                );
            }
        );
}

function isNotificationToday(
    value
){
    if(!value){
        return false;
    }

    const date=
        new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return false;
    }

    const today=
        new Date();

    return(
        date.getFullYear()===
            today.getFullYear()&&
        date.getMonth()===
            today.getMonth()&&
        date.getDate()===
            today.getDate()
    );
}

function formatNotificationPageDateTime(
    value
){
    if(!value){
        return'-';
    }

    const date=
        new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return String(value);
    }

    return date.toLocaleString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric',
            hour:'2-digit',
            minute:'2-digit'
        }
    );
}

function extractNotificationPageError(
    error
){
    if(error?.data?.errors){
        const errors=
            Object.values(
                error.data.errors
            ).flat();

        if(errors.length){
            return errors[0];
        }
    }

    return error?.data?.message??
        error?.message??
        'Something went wrong.';
}

function escapeNotificationPageHtml(
    value
){
    const div=
        document.createElement(
            'div'
        );

    div.textContent=
        String(value??'');

    return div.innerHTML;
}

function escapeNotificationPageJs(
    value
){
    return String(value??'')
        .replaceAll('\\','\\\\')
        .replaceAll("'","\\'");
}

notificationPageSearch.addEventListener(
    'input',
    ()=>{
        clearTimeout(
            notificationSearchTimer
        );

        notificationSearchTimer=
            setTimeout(
                ()=>loadNotificationPage(1),
                350
            );
    }
);

notificationPageStatus.addEventListener(
    'change',
    ()=>loadNotificationPage(1)
);

document.getElementById(
    'notificationDetailsModal'
).addEventListener(
    'click',
    event=>{
        if(
            event.target===
            event.currentTarget
        ){
            closeNotificationDetailsModal();
        }
    }
);

document.addEventListener(
    'keydown',
    event=>{
        if(event.key==='Escape'){
            closeNotificationDetailsModal();
        }
    }
);

async function initNotificationPage(){
    if(
        typeof window.api===
        'undefined'
    ){
        setTimeout(
            initNotificationPage,
            50
        );

        return;
    }

    await loadNotificationPage(1);
}

if(
    document.readyState===
    'loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        initNotificationPage
    );
}else{
    initNotificationPage();
}
</script>

@endpush