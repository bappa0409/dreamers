@extends('layouts.member')

@section('title','Notices')
@section('page-title','Notices')

@section('content')

<div class="space-y-3">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-megaphone"></i>
            </div>

            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">
                    Association Notices
                </h1>

                <p class="mt-0.5 text-sm text-slate-500">
                    View announcements, events and important association updates.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="loadNotices(1)"
            class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    {{-- Information --}}
    <div class="flex gap-3 rounded-lg border border-indigo-200 bg-indigo-50/50 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
            <i class="bi bi-info-circle"></i>
        </div>

        <div>
            <p class="text-sm font-semibold text-indigo-800">
                Association Communication
            </p>

            <p class="mt-0.5 text-[11px] leading-5 text-indigo-700">
                Published notices, announcements, events and urgent updates from the association will appear here.
            </p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-500">
                        Total Notices
                    </p>

                    <p id="summaryTotal" class="text-xl font-bold text-slate-800">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-megaphone"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">
                        Announcements
                    </p>

                    <p id="summaryAnnouncements" class="mt-2 text-xl font-bold text-sky-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <i class="bi bi-broadcast"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-amber-600">
                        Events
                    </p>

                    <p id="summaryEvents" class="text-xl font-bold text-amber-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class="bi bi-calendar-event"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-red-200 bg-red-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-red-600">
                        High / Urgent
                    </p>

                    <p id="summaryImportant" class="mt-2 text-xl font-bold text-red-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-red-600">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-5">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search notice title or content..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Type
                </label>

                <select
                    id="typeFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">

                    <option value="">
                        All Types
                    </option>

                    <option value="notice">
                        Notice
                    </option>

                    <option value="announcement">
                        Announcement
                    </option>

                    <option value="event">
                        Event
                    </option>

                    <option value="urgent">
                        Urgent
                    </option>

                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Priority
                </label>

                <select
                    id="priorityFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">

                    <option value="">
                        All Priorities
                    </option>

                    <option value="low">
                        Low
                    </option>

                    <option value="normal">
                        Normal
                    </option>

                    <option value="high">
                        High
                    </option>

                    <option value="urgent">
                        Urgent
                    </option>

                </select>
            </div>

            <div class="lg:col-span-1">
                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Clear
                </button>
            </div>

        </div>
    </div>

    {{-- Notice Portfolio --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="bi bi-megaphone"></i>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Published Notices
                </h2>

                <p class="text-[11px] text-slate-500">
                    Latest association announcements and updates
                </p>
            </div>
        </div>

        <div
            id="noticeContainer"
            class="grid grid-cols-1 gap-4 p-5 xl:grid-cols-2">

            <div class="col-span-full py-14 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                <p class="mt-3 text-sm text-slate-500">
                    Loading notices...
                </p>
            </div>

        </div>

        <div id="paginationContainer"></div>
    </div>

</div>

{{-- Notice Details Modal --}}
<div
    id="noticeModal"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[1px]">

    <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-megaphone"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Notice Details
                    </h2>

                    <p class="text-[11px] text-slate-500">
                        Association notice information
                    </p>
                </div>

            </div>

            <button
                type="button"
                onclick="closeNoticeModal()"
                class="flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-600">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div
            id="noticeModalBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">

            <button
                type="button"
                onclick="closeNoticeModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>

        </div>

    </div>
</div>

@endsection

@push('scripts')

<script>
let notices=[];
let currentPage=1;
let lastPage=1;
let total=0;
let searchTimer=null;

const searchInput=
    document.getElementById(
        'searchInput'
    );

const typeFilter=
    document.getElementById(
        'typeFilter'
    );

const priorityFilter=
    document.getElementById(
        'priorityFilter'
    );

async function loadNotices(page=1){
    currentPage=page;

    const container=
        document.getElementById(
            'noticeContainer'
        );

    container.innerHTML=`
        <div class="col-span-full py-14 text-center">

            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

            <p class="mt-3 text-sm text-slate-500">
                Loading notices...
            </p>

        </div>
    `;

    try{
        const params=
            new URLSearchParams({
                page:String(page),
                per_page:'10'
            });

        const search=
            searchInput.value.trim();

        const type=
            typeFilter.value;

        const priority=
            priorityFilter.value;

        if(search){
            params.set(
                'search',
                search
            );
        }

        if(type){
            params.set(
                'type',
                type
            );
        }

        if(priority){
            params.set(
                'priority',
                priority
            );
        }

        const response=
            await api(
                `/api/member/notices?${params.toString()}`
            );

        const paginator=
            response.data??{};

        notices=
            Array.isArray(
                paginator.data
            )
                ?paginator.data
                :[];

        currentPage=
            Number(
                paginator.current_page??
                1
            );

        lastPage=
            Number(
                paginator.last_page??
                1
            );

        total=
            Number(
                paginator.total??
                0
            );

        updateNoticeSummary();

        renderNotices();

        renderNoticePagination();

    }catch(error){
        container.innerHTML=`
            <div class="col-span-full py-14 text-center">

                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                    <i class="bi bi-exclamation-circle text-lg"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-red-600">
                    Failed to load notices
                </p>

                <p class="mt-1 text-sm text-red-400">
                    ${escapeValue(
                        extractError(
                            error
                        )
                    )}
                </p>

                <button
                    type="button"
                    onclick="loadNotices(${currentPage})"
                    class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">

                    <i class="bi bi-arrow-clockwise"></i>

                    Try Again
                </button>

            </div>
        `;

        document.getElementById(
            'paginationContainer'
        ).innerHTML='';
    }
}

function updateNoticeSummary(){
    document.getElementById(
        'summaryTotal'
    ).textContent=
        total;

    document.getElementById(
        'summaryAnnouncements'
    ).textContent=
        notices.filter(
            item=>
                item.type===
                'announcement'
        ).length;

    document.getElementById(
        'summaryEvents'
    ).textContent=
        notices.filter(
            item=>
                item.type===
                'event'
        ).length;

    document.getElementById(
        'summaryImportant'
    ).textContent=
        notices.filter(
            item=>
                item.priority===
                    'high'||
                item.priority===
                    'urgent'
        ).length;
}

function renderNotices(){
    const container=
        document.getElementById(
            'noticeContainer'
        );

    if(!notices.length){
        container.innerHTML=`
            <div class="col-span-full py-16 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                    <i class="bi bi-megaphone text-xl"></i>
                </div>

                <p class="mt-4 text-base font-semibold text-slate-700">
                    No notices found
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    No published notices match your current filters.
                </p>

            </div>
        `;

        return;
    }

    container.innerHTML=
        notices.map(
            item=>{
                const body=
                    stripHtml(
                        item.content??
                        ''
                    );

                const priority=
                    item.priority??
                    'normal';

                const type=
                    item.type??
                    'notice';

                return`
                    <article
                        id="notice-${Number(item.id)}"
                        class="group relative flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition duration-200 hover:border-slate-300 hover:shadow-sm">

                        ${
                            priority==='urgent'
                                ?`
                                    <div class="absolute inset-y-0 left-0 w-1 bg-red-500"></div>
                                `
                                :priority==='high'
                                    ?`
                                        <div class="absolute inset-y-0 left-0 w-1 bg-amber-500"></div>
                                    `
                                    :''
                        }

                        <div class="flex-1 p-5">

                            <div class="mb-4 flex items-start justify-between gap-4">

                                <div class="flex min-w-0 items-start gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${noticeIconStyle(priority)}">
                                        <i class="${noticeIcon(type)}"></i>
                                    </div>

                                    <div class="min-w-0">

                                        <div class="mb-2 flex flex-wrap items-center gap-1.5">
                                            ${typeBadge(type)}
                                            ${priorityBadge(priority)}
                                        </div>

                                        <h3 class="text-base font-bold leading-6 text-slate-800">
                                            ${escapeValue(
                                                item.title??
                                                'Untitled Notice'
                                            )}
                                        </h3>

                                    </div>

                                </div>

                            </div>

                            <p class="line-clamp-3 whitespace-pre-line text-sm leading-6 text-slate-500">
                                ${escapeValue(body)}
                            </p>

                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/50 px-5 py-3">

                            <div class="min-w-0">

                                <p class="flex items-center gap-1.5 text-[10px] text-slate-500">
                                    <i class="bi bi-calendar3"></i>

                                    ${publishedDate(
                                        item
                                    )}
                                </p>

                                ${
                                    item.creator?.name
                                        ?`
                                            <p class="mt-1 flex items-center gap-1.5 truncate text-[10px] text-slate-500">

                                                <i class="bi bi-person"></i>

                                                By ${escapeValue(
                                                    item.creator.name
                                                )}

                                            </p>
                                        `
                                        :''
                                }

                            </div>

                            <button
                                type="button"
                                onclick="openNoticeModal(${Number(item.id)})"
                                class="inline-flex h-8 shrink-0 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">

                                <i class="bi bi-eye"></i>

                                View

                            </button>
                            

                        </div>

                    </article>
                `;
            }
        ).join('');

    scrollToNoticeFromHash();
}

function renderNoticePagination(){
    const container=
        document.getElementById(
            'paginationContainer'
        );

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    const from=
        total===0
            ?0
            :(
                (
                    currentPage-1
                )*10
            )+1;

    const to=
        Math.min(
            currentPage*10,
            total
        );

    container.innerHTML=`
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/40 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

            <p class="text-[11px] text-slate-500">

                Showing

                <span class="font-semibold text-xs 2xl:text-sm text-slate-700">
                    ${from}
                </span>

                –

                <span class="font-semibold text-xs 2xl:text-sm text-slate-700">
                    ${to}
                </span>

                of

                <span class="font-semibold text-xs 2xl:text-sm text-slate-700">
                    ${total}
                </span>

                notices

            </p>

            <div class="flex items-center gap-1">

                <button
                    type="button"
                    ${currentPage<=1?'disabled':''}
                    onclick="loadNotices(${currentPage-1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    <i class="bi bi-chevron-left"></i>

                    Previous
                </button>

                <span class="px-3 text-[11px] font-medium text-slate-500">
                    ${currentPage} / ${lastPage}
                </span>

                <button
                    type="button"
                    ${currentPage>=lastPage?'disabled':''}
                    onclick="loadNotices(${currentPage+1})"
                    class="inline-flex h-8 items-center gap-1 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                    Next

                    <i class="bi bi-chevron-right"></i>
                </button>

            </div>

        </div>
    `;
}

window.openNoticeModal=function(id){
    const item=
        notices.find(
            notice=>
                Number(
                    notice.id
                )===
                Number(id)
        );

    if(!item){
        Toast.error(
            'Notice not found.'
        );

        return;
    }

    const attachmentUrl=
        item.attachment
            ?`/storage/${String(
                item.attachment
            ).replace(/^\/+/,'')}`
            :null;

    const priority=
        item.priority??
        'normal';

    const type=
        item.type??
        'notice';

    document.getElementById(
        'noticeModalBody'
    ).innerHTML=`
        <div class="space-y-3">

            <div class="rounded-lg ${modalHeroStyle(priority)} p-5">

                <div class="flex items-start justify-between gap-4">

                    <div class="min-w-0">

                        <div class="mb-3 flex flex-wrap items-center gap-2">
                            ${typeBadge(type)}
                            ${priorityBadge(priority)}
                        </div>

                        <h3 class="text-lg font-bold leading-7 text-slate-800">
                            ${escapeValue(
                                item.title??
                                'Untitled Notice'
                            )}
                        </h3>

                        <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-slate-500">

                            <span class="inline-flex items-center gap-1.5">
                                <i class="bi bi-calendar3"></i>

                                ${publishedDate(
                                    item
                                )}
                            </span>

                            ${
                                item.creator?.name
                                    ?`
                                        <span class="inline-flex items-center gap-1.5">

                                            <i class="bi bi-person"></i>

                                            ${escapeValue(
                                                item.creator.name
                                            )}

                                        </span>
                                    `
                                    :''
                            }

                        </div>

                    </div>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg ${noticeIconStyle(priority)}">
                        <i class="${noticeIcon(type)} text-lg"></i>
                    </div>

                </div>

            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

                <div class="border-b border-slate-200 px-5 py-4">

                    <h3 class="text-sm font-semibold text-slate-800">
                        Notice Content
                    </h3>

                    <p class="text-[11px] text-slate-500">
                        Full published announcement
                    </p>

                </div>

                <div class="p-5">

                    <div class="whitespace-pre-line break-words text-base leading-7 text-slate-600">
                        ${escapeValue(
                            stripHtml(
                                item.content??
                                ''
                            )
                        )}
                    </div>

                </div>

            </div>

            ${
                attachmentUrl
                    ?`
                        <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-4">

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                                <div class="flex min-w-0 items-center gap-3">

                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm">
                                        <i class="bi bi-paperclip"></i>
                                    </div>

                                    <div class="min-w-0">

                                        <p class="text-sm font-semibold text-slate-700">
                                            Attachment
                                        </p>

                                        <p class="truncate text-[10px] text-slate-500">
                                            ${escapeValue(
                                                fileName(
                                                    item.attachment
                                                )
                                            )}
                                        </p>

                                    </div>

                                </div>

                                <a
                                    href="${escapeAttribute(attachmentUrl)}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3 text-[11px] font-semibold text-white transition hover:bg-indigo-700">

                                    <i class="bi bi-box-arrow-up-right"></i>

                                    Open Attachment

                                </a>

                            </div>

                        </div>
                    `
                    :''
            }

        </div>
    `;

    const modal=
        document.getElementById(
            'noticeModal'
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
};

window.closeNoticeModal=function(){
    const modal=
        document.getElementById(
            'noticeModal'
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

function typeBadge(type){
    const styles={
        notice:
            'border-slate-200 bg-slate-100 text-slate-600',

        announcement:
            'border-indigo-200 bg-indigo-50 text-indigo-700',

        event:
            'border-sky-200 bg-sky-50 text-sky-700',

        urgent:
            'border-red-200 bg-red-50 text-red-700'
    };

    return`
        <span class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold ${
            styles[type]??
            styles.notice
        }">

            ${escapeValue(
                titleCase(
                    type??
                    'notice'
                )
            )}

        </span>
    `;
}

function priorityBadge(priority){
    const styles={
        low:
            'border-slate-200 bg-slate-50 text-slate-500',

        normal:
            'border-emerald-200 bg-emerald-50 text-emerald-700',

        high:
            'border-amber-200 bg-amber-50 text-amber-700',

        urgent:
            'border-red-200 bg-red-50 text-red-700'
    };

    return`
        <span class="inline-flex rounded-md border px-2 py-1 text-[10px] font-semibold ${
            styles[priority]??
            styles.normal
        }">

            ${escapeValue(
                titleCase(
                    priority??
                    'normal'
                )
            )}

        </span>
    `;
}

function noticeIcon(type){
    const icons={
        notice:
            'bi bi-megaphone',

        announcement:
            'bi bi-broadcast',

        event:
            'bi bi-calendar-event',

        urgent:
            'bi bi-exclamation-triangle'
    };

    return icons[type]??
        icons.notice;
}

function noticeIconStyle(priority){
    const styles={
        low:
            'bg-slate-100 text-slate-500',

        normal:
            'bg-indigo-50 text-indigo-600',

        high:
            'bg-amber-100 text-amber-600',

        urgent:
            'bg-red-100 text-red-600'
    };

    return styles[priority]??
        styles.normal;
}

function modalHeroStyle(priority){
    const styles={
        low:
            'border border-slate-200 bg-slate-50',

        normal:
            'border border-indigo-100 bg-indigo-50/40',

        high:
            'border border-amber-200 bg-amber-50/50',

        urgent:
            'border border-red-200 bg-red-50/50'
    };

    return styles[priority]??
        styles.normal;
}

function publishedDate(item){
    const value=
        item.publish_at??
        item.created_at;

    if(!value){
        return'No publish date';
    }

    const date=
        new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return'—';
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

function stripHtml(value){
    const element=
        document.createElement(
            'div'
        );

    element.innerHTML=
        String(
            value??
            ''
        );

    return element.textContent||
        element.innerText||
        '';
}

function fileName(path){
    return String(
        path??
        ''
    )
        .split('/')
        .pop()||
        'Attachment';
}

function titleCase(value){
    return String(
        value??
        ''
    )
        .replaceAll(
            '_',
            ' '
        )
        .replace(
            /\b\w/g,
            char=>
                char.toUpperCase()
        );
}

function extractError(error){
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

function escapeValue(value){
    const div=
        document.createElement(
            'div'
        );

    div.textContent=
        String(
            value??
            ''
        );

    return div.innerHTML;
}

function escapeAttribute(value){
    return String(
        value??
        ''
    )
        .replaceAll(
            '&',
            '&amp;'
        )
        .replaceAll(
            '"',
            '&quot;'
        )
        .replaceAll(
            "'",
            '&#039;'
        )
        .replaceAll(
            '<',
            '&lt;'
        )
        .replaceAll(
            '>',
            '&gt;'
        );
}

function scrollToNoticeFromHash(){
    const match=
        window.location.hash.match(
            /^#notice-(\d+)$/
        );

    if(!match){
        return;
    }

    const id=
        Number(
            match[1]
        );

    const element=
        document.getElementById(
            `notice-${id}`
        );

    if(!element){
        return;
    }

    setTimeout(
        ()=>{
            element.scrollIntoView({
                behavior:'smooth',
                block:'center'
            });

            element.classList.add(
                'ring-2',
                'ring-indigo-200'
            );

            setTimeout(
                ()=>{
                    element.classList.remove(
                        'ring-2',
                        'ring-indigo-200'
                    );
                },
                2500
            );
        },
        100
    );
}

window.clearFilters=function(){
    searchInput.value='';
    typeFilter.value='';
    priorityFilter.value='';

    loadNotices(1);
};

searchInput.addEventListener(
    'input',
    ()=>{
        clearTimeout(
            searchTimer
        );

        searchTimer=
            setTimeout(
                ()=>loadNotices(1),
                400
            );
    }
);

typeFilter.addEventListener(
    'change',
    ()=>loadNotices(1)
);

priorityFilter.addEventListener(
    'change',
    ()=>loadNotices(1)
);

document.getElementById(
    'noticeModal'
).addEventListener(
    'click',
    event=>{
        if(
            event.target===
            event.currentTarget
        ){
            closeNoticeModal();
        }
    }
);

document.addEventListener(
    'keydown',
    event=>{
        if(event.key==='Escape'){
            closeNoticeModal();
        }
    }
);

async function initNoticePage(){
    if(
        typeof window.api===
            'undefined'
    ){
        setTimeout(
            initNoticePage,
            50
        );

        return;
    }

    await loadNotices(1);
}

if(
    document.readyState===
    'loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        initNoticePage
    );
}else{
    initNoticePage();
}
</script>

@endpush