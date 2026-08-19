@extends('layouts.admin')

@section('title','Notices')
@section('page_title','Notices')

@section('content')

<div class="space-y-5">

    <div class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-megaphone"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">Association Notices</h1>
                <p class="mt-1 text-sm text-slate-500">Latest announcements and important updates.</p>
            </div>
        </div>
    </div>

    <div id="noticeList" class="space-y-3">
        <div class="rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
            Loading notices...
        </div>
    </div>

</div>

<script>
async function loadNotices(){
    const response=await api('/api/notices?published_only=1');
    const notices=response.data?.data??[];

    const container=document.getElementById('noticeList');

    if(!notices.length){
        container.innerHTML=`
            <div class="rounded-md border border-slate-200 bg-white p-8 text-center text-sm text-slate-400">
                No notices available.
            </div>
        `;
        return;
    }

    container.innerHTML=notices.map(item=>`
        <article id="notice-${item.id}" class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="mb-2 flex flex-wrap items-center gap-2">
                        <span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-600">
                            ${escapeHtml(item.type)}
                        </span>

                        ${priorityBadge(item.priority)}
                    </div>

                    <h2 class="text-lg font-bold text-slate-800">
                        ${escapeHtml(item.title)}
                    </h2>

                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                        ${escapeHtml(item.content)}
                    </p>
                </div>
            </div>

            ${
                item.attachment
                    ?`
                        <a href="/storage/${encodeURI(item.attachment)}" target="_blank" class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-200">
                            <i class="bi bi-paperclip"></i>
                            View Attachment
                        </a>
                    `
                    :''
            }
        </article>
    `).join('');
}

function priorityBadge(priority){
    const map={
        low:'bg-sky-50 text-sky-700',
        normal:'bg-slate-100 text-slate-600',
        high:'bg-amber-50 text-amber-700',
        urgent:'bg-red-50 text-red-700'
    };

    return `<span class="rounded-md px-2 py-1 text-[10px] font-semibold ${map[priority]??map.normal}">${escapeHtml(priority)}</span>`;
}

function escapeHtml(value){
    if(value===null||value===undefined)return '';

    return String(value)
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");
}

document.addEventListener('DOMContentLoaded',loadNotices);
</script>

@endsection