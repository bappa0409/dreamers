@extends('layouts.admin')

@section('title','Notifications')
@section('page_title','Notifications')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bell"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">Notifications</h1>
                <p class="mt-1 text-sm text-slate-500">Send announcements and manage notification history.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Notification.send'))
            <button type="button" onclick="openSendModal()" class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-send"></i>
                Send Notification
            </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-slate-500">Campaigns</p>
            <p id="totalCampaigns" class="mt-2 text-2xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <p class="text-xs text-emerald-700">Sent</p>
            <p id="sentCampaigns" class="mt-2 text-2xl font-bold text-emerald-600">0</p>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <p class="text-xs text-red-700">Failed</p>
            <p id="failedCampaigns" class="mt-2 text-2xl font-bold text-red-600">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-4">
            <p class="text-xs text-indigo-700">Recipients</p>
            <p id="totalRecipients" class="mt-2 text-2xl font-bold text-indigo-600">0</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Notifications</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search campaign title or message</p>
                </div>
            </div>

            <div class="flex w-full items-center sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 pl-9 pr-3 text-sm outline-none focus:border-indigo-400">
                </div>

                <select id="statusFilter" class="h-9 border border-slate-300 bg-white px-3 text-xs text-slate-600">
                    <option value="">All</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>

                <button type="button" onclick="clearFilters()" class="h-9 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Notification</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Audience</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Recipients</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Sender</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Sent At</th>
                    </tr>
                </thead>

                <tbody id="campaignTable">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Send Modal --}}
<div id="sendModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-send"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Send Notification</h2>
                    <p class="mt-1 text-xs text-slate-500">Send notification to selected association users.</p>
                </div>
            </div>

            <button type="button" onclick="closeSendModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="sendForm" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-5">

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Title</label>
                    <input id="title" type="text" maxlength="200" class="app-input">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Message</label>
                    <textarea id="message" rows="4" maxlength="5000" class="app-input resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Type</label>

                        <select id="type" class="app-input">
                            <option value="info">Information</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Important / Danger</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Audience</label>

                        <select id="audienceType" class="app-input">
                            <option value="all_active_members">All Active Members</option>
                            <option value="role">By Role</option>
                            <option value="users">Selected Users</option>
                        </select>
                    </div>
                </div>

                <div id="roleSection" class="hidden">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Role</label>
                    <select id="roleId" class="app-input">
                        <option value="">Select Role</option>
                    </select>
                </div>

                <div id="userSection" class="hidden">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Select Users</label>

                    <input id="userSearch" type="text" placeholder="Search users..." class="app-input">

                    <div id="userResults" class="mt-2 max-h-48 space-y-1 overflow-y-auto rounded-md border border-slate-200 p-2"></div>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Action URL</label>
                    <input id="actionUrl" type="text" maxlength="500" placeholder="/member/projects" class="app-input">
                    <p class="mt-1 text-[11px] text-slate-400">Optional. User can open this page from the notification.</p>
                </div>

                <div id="sendError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closeSendModal()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="sendButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                    Send Notification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let campaigns=[];
let selectedUsers=new Set();
let availableRoles=[];
let currentPage=1;
let lastPage=1;
let searchTimer=null;
let userSearchTimer=null;

async function loadCampaigns(page=1){
    currentPage=page;

    const params=new URLSearchParams();
    const search=document.getElementById('searchInput').value.trim();
    const status=document.getElementById('statusFilter').value;

    if(search)params.set('search',search);
    if(status)params.set('status',status);
    params.set('page',page);

    try{
        const response=await api(`/api/notification-campaigns?${params.toString()}`);
        const paginator=response.data??{};

        campaigns=paginator.data??[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;

        renderCampaigns();
        renderPagination();

    }catch(error){
        console.error(error);
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/notification-campaigns/statistics');
        const stats=response.data??{};

        document.getElementById('totalCampaigns').innerText=stats.total??0;
        document.getElementById('sentCampaigns').innerText=stats.sent??0;
        document.getElementById('failedCampaigns').innerText=stats.failed??0;
        document.getElementById('totalRecipients').innerText=stats.recipients??0;

    }catch(error){
        console.error(error);
    }
}

function renderCampaigns(){
    const tbody=document.getElementById('campaignTable');

    if(!campaigns.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-slate-400">
                    No notifications found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=campaigns.map(item=>`
        <tr class="border-b border-slate-100 hover:bg-slate-50">
            <td class="px-5 py-4">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 h-2.5 w-2.5 rounded-full ${typeColor(item.type)}"></span>

                    <div>
                        <p class="font-semibold text-slate-800">${escapeHtml(item.title)}</p>
                        <p class="mt-1 max-w-sm truncate text-xs text-slate-400">${escapeHtml(item.message)}</p>
                    </div>
                </div>
            </td>

            <td class="px-5 py-4 text-xs text-slate-600">
                ${escapeHtml(formatAudience(item.audience_type))}
            </td>

            <td class="px-5 py-4 font-semibold text-slate-700">
                ${item.recipients_count??0}
            </td>

            <td class="px-5 py-4 text-xs text-slate-600">
                ${escapeHtml(item.sender?.name??'System')}
            </td>

            <td class="px-5 py-4">
                ${statusBadge(item.status)}
            </td>

            <td class="px-5 py-4 text-xs text-slate-500">
                ${formatDate(item.sent_at)}
            </td>
        </tr>
    `).join('');
}

function openSendModal(){
    document.getElementById('sendForm').reset();
    selectedUsers.clear();
    document.getElementById('sendError').classList.add('hidden');

    const modal=document.getElementById('sendModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add('overflow-hidden');

    handleAudienceChange();
    loadRecipients();
}

function closeSendModal(){
    const modal=document.getElementById('sendModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.body.classList.remove('overflow-hidden');
}

async function loadRecipients(search=''){
    try{
        const params=new URLSearchParams();

        if(search)params.set('search',search);

        const response=await api(`/api/notification-campaigns/recipients?${params.toString()}`);

        availableRoles=response.data?.roles??[];

        renderRoles();
        renderUsers(response.data?.users??[]);

    }catch(error){
        console.error(error);
    }
}

function renderRoles(){
    const select=document.getElementById('roleId');
    const current=select.value;

    select.innerHTML='<option value="">Select Role</option>';

    availableRoles.forEach(role=>{
        const option=document.createElement('option');
        option.value=role.id;
        option.textContent=role.display_name||role.name;
        select.appendChild(option);
    });

    select.value=current;
}

function renderUsers(users){
    const container=document.getElementById('userResults');

    if(!users.length){
        container.innerHTML='<p class="p-3 text-center text-xs text-slate-400">No users found.</p>';
        return;
    }

    container.innerHTML=users.map(user=>`
        <label class="flex cursor-pointer items-center gap-3 rounded-md p-2 hover:bg-slate-50">
            <input
                type="checkbox"
                class="notification-user-checkbox h-4 w-4 rounded border-slate-300 text-indigo-600"
                value="${user.id}"
                ${selectedUsers.has(Number(user.id))?'checked':''}
                onchange="toggleUser(${user.id},this.checked)"
            >

            <div class="min-w-0">
                <p class="truncate text-xs font-semibold text-slate-700">${escapeHtml(user.name)}</p>
                <p class="truncate text-[10px] text-slate-400">${escapeHtml(user.email??'')}</p>
            </div>
        </label>
    `).join('');
}

function toggleUser(id,checked){
    if(checked){
        selectedUsers.add(Number(id));
    }else{
        selectedUsers.delete(Number(id));
    }
}

function handleAudienceChange(){
    const type=document.getElementById('audienceType').value;

    document.getElementById('roleSection').classList.toggle(
        'hidden',
        type!=='role'
    );

    document.getElementById('userSection').classList.toggle(
        'hidden',
        type!=='users'
    );
}

document.getElementById('sendForm').addEventListener('submit',async function(e){
    e.preventDefault();

    const audienceType=document.getElementById('audienceType').value;

    const data={
        title:document.getElementById('title').value.trim(),
        message:document.getElementById('message').value.trim(),
        type:document.getElementById('type').value,
        audience_type:audienceType,
        action_url:document.getElementById('actionUrl').value.trim()||null
    };

    if(audienceType==='role'){
        data.role_id=Number(document.getElementById('roleId').value)||null;
    }

    if(audienceType==='users'){
        data.user_ids=Array.from(selectedUsers);
    }

    const button=document.getElementById('sendButton');
    button.disabled=true;
    button.innerText='Sending...';

    try{
        await api('/api/notification-campaigns/send',{
            method:'POST',
            body:JSON.stringify(data)
        });

        closeSendModal();

        await Promise.all([
            loadCampaigns(1),
            loadStatistics()
        ]);

    }catch(error){
        const box=document.getElementById('sendError');
        box.innerText=extractError(error);
        box.classList.remove('hidden');

    }finally{
        button.disabled=false;
        button.innerText='Send Notification';
    }
});

function renderPagination(){
    const container=document.getElementById('paginationContainer');

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    container.innerHTML=`
        <div class="flex items-center justify-between">
            <span class="text-xs text-slate-500">
                Page ${currentPage} of ${lastPage}
            </span>

            <div class="flex gap-2">
                <button ${currentPage<=1?'disabled':''} onclick="loadCampaigns(${currentPage-1})" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs disabled:opacity-40">
                    Previous
                </button>

                <button ${currentPage>=lastPage?'disabled':''} onclick="loadCampaigns(${currentPage+1})" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs disabled:opacity-40">
                    Next
                </button>
            </div>
        </div>
    `;
}

function clearFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('statusFilter').value='';
    loadCampaigns(1);
}

function statusBadge(status){
    const map={
        sent:'bg-emerald-50 text-emerald-700',
        failed:'bg-red-50 text-red-700',
        sending:'bg-amber-50 text-amber-700',
        pending:'bg-slate-100 text-slate-600'
    };

    return `<span class="rounded-full px-2.5 py-1 text-[10px] font-semibold ${map[status]??map.pending}">${escapeHtml(status)}</span>`;
}

function typeColor(type){
    return {
        info:'bg-sky-500',
        success:'bg-emerald-500',
        warning:'bg-amber-500',
        danger:'bg-red-500'
    }[type]??'bg-slate-500';
}

function formatAudience(value){
    return {
        all_active_members:'All Active Members',
        role:'Role',
        users:'Selected Users'
    }[value]??value;
}

function formatDate(value){
    if(!value)return 'N/A';

    return new Date(value).toLocaleString('en-GB',{
        day:'2-digit',
        month:'short',
        year:'numeric',
        hour:'2-digit',
        minute:'2-digit'
    });
}

function extractError(error){
    if(error.data?.errors){
        const errors=Object.values(error.data.errors).flat();
        if(errors.length)return errors.join(' ');
    }

    return error.data?.message||error.message||'Something went wrong.';
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

document.getElementById('audienceType').addEventListener('change',handleAudienceChange);

document.getElementById('userSearch').addEventListener('input',function(){
    clearTimeout(userSearchTimer);

    userSearchTimer=setTimeout(()=>{
        loadRecipients(this.value.trim());
    },350);
});

document.getElementById('searchInput').addEventListener('input',function(){
    clearTimeout(searchTimer);

    searchTimer=setTimeout(()=>{
        loadCampaigns(1);
    },350);
});

document.getElementById('statusFilter').addEventListener('change',()=>{
    loadCampaigns(1);
});

document.addEventListener('DOMContentLoaded',()=>{
    Promise.all([
        loadCampaigns(),
        loadStatistics()
    ]);
});
</script>

@endsection