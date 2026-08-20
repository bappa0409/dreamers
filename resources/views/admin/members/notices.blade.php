@extends('layouts.admin')

@section('title','Notices')
@section('page_title','Notices')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white px-5 py-4">

        <div class="flex items-center gap-3">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-megaphone"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    Association Notices
                </h1>

                <p class="text-sm text-slate-500">
                    Latest announcements and important updates.
                </p>
            </div>

        </div>

    </div>


    {{-- =========================================================
    NOTICE LIST
    ========================================================== --}}
    <div id="noticeList" class="space-y-3">

        <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
            Loading notices...
        </div>

    </div>


    {{-- Pagination --}}
    <div id="noticePagination"></div>

</div>

@endsection


@push('scripts')

<script>
let currentPage=1;
let lastPage=1;
let total=0;

const noticeList=
    document.getElementById(
        'noticeList'
    );


/*
|--------------------------------------------------------------------------
| Load Notices
|--------------------------------------------------------------------------
*/

async function loadNotices(page=1){
    currentPage=page;

    noticeList.innerHTML=
        AdminUI.loadingState(
            'Loading notices...'
        );


    try{
        const query=
            AdminUI.query({
                published_only:1,
                page
            });


        const response=
            await api(
                `/api/notices?${query}`
            );


        /*
        |--------------------------------------------------------------------------
        | API:
        |
        | {
        |   success: true,
        |   data: {
        |       data: [...],
        |       current_page: ...
        |   }
        | }
        |--------------------------------------------------------------------------
        */

        const paginator=
            response.data??{};


        const notices=
            Array.isArray(
                paginator.data
            )
                ?paginator.data
                :[];


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


        renderNotices(
            notices
        );


        AdminUI.renderPagination({
            container:'noticePagination',
            currentPage,
            lastPage,
            total,
            onPageChange:loadNotices
        });

    }catch(error){
        noticeList.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-sm text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;


        AdminUI.renderPagination({
            container:'noticePagination',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadNotices
        });
    }
}


/*
|--------------------------------------------------------------------------
| Render Notices
|--------------------------------------------------------------------------
*/

function renderNotices(notices){
    if(!notices.length){
        noticeList.innerHTML=
            AdminUI.emptyState(
                'No notices available.'
            );

        return;
    }


    noticeList.innerHTML=
        notices.map(item=>`

            <article
                id="notice-${item.id}"
                class="overflow-hidden rounded-md border border-slate-200 bg-white"
            >

                <div class="p-5">

                    {{-- Header --}}
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">

                        <div class="min-w-0 flex-1">

                            <div class="mb-2 flex flex-wrap items-center gap-2">

                                ${noticeTypeBadge(
                                    item.type
                                )}

                                ${priorityBadge(
                                    item.priority
                                )}

                            </div>


                            <h2
                                class="break-words text-lg font-bold text-slate-800"
                            >
                                ${AdminUI.escapeHtml(
                                    item.title??
                                    'Untitled Notice'
                                )}
                            </h2>


                            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate-400">

                                ${
                                    item.creator?.name
                                        ?`
                                            <span>
                                                <i class="bi bi-person me-1"></i>

                                                ${AdminUI.escapeHtml(
                                                    item.creator.name
                                                )}
                                            </span>
                                        `
                                        :''
                                }


                                ${
                                    item.publish_at
                                        ?`
                                            <span>
                                                <i class="bi bi-calendar3 me-1"></i>

                                                ${AdminUI.formatDate(
                                                    item.publish_at,
                                                    true
                                                )}
                                            </span>
                                        `
                                        :''
                                }

                            </div>

                        </div>

                    </div>


                    {{-- Content --}}
                    <div class="mt-4 border-t border-slate-100 pt-4">

                        <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-600">
                            ${AdminUI.escapeHtml(
                                item.content??
                                ''
                            )}
                        </p>

                    </div>


                    {{-- Attachment --}}
                    ${
                        item.attachment
                            ?`
                                <div class="mt-4">

                                    <a
                                        href="${attachmentUrl(item.attachment)}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-md bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-200"
                                    >
                                        <i class="bi bi-paperclip"></i>

                                        View Attachment
                                    </a>

                                </div>
                            `
                            :''
                    }

                </div>

            </article>

        `).join('');
}


/*
|--------------------------------------------------------------------------
| Type Badge
|--------------------------------------------------------------------------
*/

function noticeTypeBadge(type){
    const map={
        notice:
            'bg-indigo-50 text-indigo-700',

        announcement:
            'bg-sky-50 text-sky-700',

        event:
            'bg-violet-50 text-violet-700',

        urgent:
            'bg-red-50 text-red-700'
    };


    return `
        <span class="rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${
            map[type]??
            map.notice
        }">
            ${AdminUI.escapeHtml(
                formatLabel(type)
            )}
        </span>
    `;
}


/*
|--------------------------------------------------------------------------
| Priority Badge
|--------------------------------------------------------------------------
*/

function priorityBadge(priority){
    const map={
        low:
            'bg-sky-50 text-sky-700',

        normal:
            'bg-slate-100 text-slate-600',

        high:
            'bg-amber-50 text-amber-700',

        urgent:
            'bg-red-50 text-red-700'
    };


    return `
        <span class="rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${
            map[priority]??
            map.normal
        }">
            ${AdminUI.escapeHtml(
                formatLabel(
                    priority??
                    'normal'
                )
            )}
        </span>
    `;
}


/*
|--------------------------------------------------------------------------
| Attachment URL
|--------------------------------------------------------------------------
*/

function attachmentUrl(path){
    if(!path){
        return '#';
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent accidental malformed paths.
    |--------------------------------------------------------------------------
    */

    const cleanPath=
        String(path)
            .replace(/^\/+/,'');


    return `/storage/${
        cleanPath
            .split('/')
            .map(segment=>
                encodeURIComponent(segment)
            )
            .join('/')
    }`;
}


/*
|--------------------------------------------------------------------------
| Label
|--------------------------------------------------------------------------
*/

function formatLabel(value){
    return String(value??'')
        .replace(/_/g,' ')
        .replace(
            /\b\w/g,
            character=>
                character.toUpperCase()
        );
}


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initNoticePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initNoticePage,
            50
        );

        return;
    }


    await loadNotices();
}


if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initNoticePage
    );
}else{
    initNoticePage();
}
</script>

@endpush