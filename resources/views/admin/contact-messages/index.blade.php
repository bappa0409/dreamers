@extends('layouts.admin')

@section('title','Contact Messages')
@section('page_title','Contact Messages')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-chat-square-text"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Contact Messages</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Messages submitted from the website contact form.</p>
            </div>
        </div>

        <span id="unreadBadge" class="hidden w-fit items-center gap-1.5 rounded-md bg-red-50 px-3 py-1.5  text-xs 2xl:text-sm font-semibold text-red-600">
            <i class="bi bi-envelope-exclamation"></i>
            <span id="unreadCount">0</span> unread
        </span>
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Messages</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by name, email or subject</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">
                <div class="relative w-full sm:min-w-[220px] lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search messages..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3  text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                    <option value="">All Messages</option>
                    <option value="unread">Unread</option>
                    <option value="read">Read</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[5%] px-3 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600"></th>
                        <th class="w-[20%] px-3 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Name</th>
                        <th class="w-[20%] px-3 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Email</th>
                        <th class="w-[25%] px-3 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Subject</th>
                        <th class="w-[15%] px-3 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Received</th>
                        <th class="w-[15%] px-3 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="messageTable">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">Loading messages...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- View Modal --}}
<div id="messageModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-chat-square-text"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Contact Message</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Submitted from the website contact form.</p>
                </div>
            </div>
            <button type="button" onclick="AdminUI.closeModal('messageModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="messageModalBody" class="space-y-4 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
            <button type="button" onclick="AdminUI.closeModal('messageModal')" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Close</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let messages=[];
let currentPage=1;
let lastPage=1;
let total=0;

const canDelete=@json(auth()->user()->hasPermission('ContactMessage.delete'));

const el={
    table:document.getElementById('messageTable'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    unreadBadge:document.getElementById('unreadBadge'),
    unreadCount:document.getElementById('unreadCount')
};

async function loadMessages(page=1){
    currentPage=page;
    el.table.innerHTML=AdminUI.loadingState('Loading messages...',6);

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        page
    });

    try{
        const response=await api(`/api/contact-messages?${query}`);
        const paginator=response.data??{};

        messages=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??messages.length);

        renderUnreadBadge(response.unread_count??0);
        renderMessages();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadMessages
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),6);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadMessages
        });
    }
}

function renderUnreadBadge(count){
    count=Number(count??0);

    if(count>0){
        el.unreadCount.textContent=count;
        el.unreadBadge.classList.remove('hidden');
        el.unreadBadge.classList.add('inline-flex');
    }else{
        el.unreadBadge.classList.add('hidden');
        el.unreadBadge.classList.remove('inline-flex');
    }

    window.updateContactMessageBadge?.(count);
}

function renderMessages(){
    if(!messages.length){
        el.table.innerHTML=AdminUI.emptyState('No contact messages found.',6);
        return;
    }

    el.table.innerHTML=messages.map(item=>`
        <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50 ${item.is_read?'':'bg-indigo-50/30'}">
            <td class="px-3 py-4 text-center">
                ${item.is_read
                    ?'<i class="bi bi-envelope-open text-slate-300" title="Read"></i>'
                    :'<span class="inline-flex h-2 w-2 rounded-full bg-indigo-600" title="Unread"></span>'}
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate  text-xs 2xl:text-sm font-semibold text-slate-800" title="${AdminUI.escapeHtml(item.name??'')}">
                    ${AdminUI.escapeHtml(item.name??'—')}
                </p>
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate  text-xs 2xl:text-sm text-slate-600" title="${AdminUI.escapeHtml(item.email??'')}">
                    ${AdminUI.escapeHtml(item.email??'—')}
                </p>
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate  text-xs 2xl:text-sm text-slate-600" title="${AdminUI.escapeHtml(item.subject??'')}">
                    ${AdminUI.escapeHtml(item.subject??'—')}
                </p>
            </td>

            <td class="overflow-hidden px-3 py-4">
                <p class="truncate text-[11px] text-slate-400">
                    ${AdminUI.formatDate(item.created_at,true)}
                </p>
            </td>

            <td class="px-3 py-4">
                <div class="flex justify-end gap-1">
                    <button type="button" onclick="viewMessage(${item.id})" title="View" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                        <i class="bi bi-eye text-sm"></i>
                    </button>

                    <button type="button" onclick="toggleRead(${item.id})" title="${item.is_read?'Mark as unread':'Mark as read'}" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700 transition hover:bg-amber-100">
                        <i class="bi ${item.is_read?'bi-envelope':'bi-envelope-check'} text-sm"></i>
                    </button>

                    ${canDelete?`
                        <button type="button" onclick="deleteMessage(${item.id})" title="Delete" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100">
                            <i class="bi bi-trash text-sm"></i>
                        </button>
                    `:''}
                </div>
            </td>
        </tr>
    `).join('');
}

window.viewMessage=async function(id){
    const message=messages.find(item=>Number(item.id)===Number(id));

    if(!message){
        Toast.error('Message not found.');
        return;
    }

    document.getElementById('messageModalBody').innerHTML=`
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Name</p>
                <p class="mt-0.5 text-sm font-medium text-slate-700">${AdminUI.escapeHtml(message.name??'—')}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email</p>
                <p class="mt-0.5 text-sm font-medium text-slate-700">${AdminUI.escapeHtml(message.email??'—')}</p>
            </div>
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Subject</p>
            <p class="mt-0.5 text-sm font-medium text-slate-700">${AdminUI.escapeHtml(message.subject??'—')}</p>
        </div>

        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Message</p>
            <p class="mt-1 whitespace-pre-line rounded-md border border-slate-200 bg-slate-50 p-3 text-sm leading-6 text-slate-700">${AdminUI.escapeHtml(message.message??'')}</p>
        </div>

        <p class="text-[11px] text-slate-400">Received ${AdminUI.formatDate(message.created_at,true)}</p>
    `;

    AdminUI.openModal('messageModal');

    if(!message.is_read){
        try{
            await api(`/api/contact-messages/${id}`);
            await loadMessages(currentPage);
        }catch(error){
            // Silently ignore — the message is still viewable.
        }
    }
};

window.toggleRead=function(id){
    AdminUI.request(`/api/contact-messages/${id}/toggle-read`,{
        method:'PATCH',
        data:{},
        onSuccess:()=>loadMessages(currentPage)
    });
};

window.deleteMessage=function(id){
    const message=messages.find(item=>Number(item.id)===Number(id));

    if(!message){
        Toast.error('Message not found.');
        return;
    }

    AdminUI.deleteRequest(`/api/contact-messages/${id}`,{
        message:`Delete the message from "${message.name}" permanently? This action cannot be undone.`,
        successMessage:'Contact message deleted successfully.',
        onSuccess:async()=>{
            if(messages.length===1&&currentPage>1)currentPage--;
            await loadMessages(currentPage);
        }
    });
};

window.clearFilters=function(){
    el.search.value='';
    el.statusFilter.value='';
    loadMessages(1);
};

async function initContactMessagesPage(){
    if(typeof window.AdminUI==='undefined'||typeof window.api==='undefined'){
        setTimeout(initContactMessagesPage,50);
        return;
    }

    el.search.addEventListener('input',AdminUI.debounce(()=>loadMessages(1)));
    el.statusFilter.addEventListener('change',()=>loadMessages(1));

    await loadMessages();
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initContactMessagesPage);
}else{
    initContactMessagesPage();
}
</script>
@endpush
