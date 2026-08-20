@extends('layouts.admin')

@section('title','Notifications')
@section('page_title','Notifications')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bell"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Notifications</h1>
                <p class="text-sm text-slate-500">Send announcements and manage notification history.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Notification.send'))
            <button type="button" onclick="openSendModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-send"></i>
                Send Notification
            </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
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
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Notifications</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search campaign title or message</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search notifications..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
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
                        <th class="w-[30%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Notification</th>
                        <th class="w-[15%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Audience</th>
                        <th class="w-[11%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Recipients</th>
                        <th class="w-[15%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Sender</th>
                        <th class="w-[12%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="w-[17%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Sent At</th>
                    </tr>
                </thead>
                <tbody id="campaignTable">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">Loading notifications...</td>
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
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-send"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Send Notification</h2>
                    <p class="text-xs text-slate-500">Send notification to selected association users.</p>
                </div>
            </div>
            <button type="button" onclick="closeSendModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="sendForm" class="flex min-h-0 flex-1 flex-col">
            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input id="title" type="text" maxlength="200" class="app-input" placeholder="Notification title">
                </div>

                <div>
                    <label class="form-label">Message <span class="text-red-500">*</span></label>
                    <textarea id="message" rows="4" maxlength="5000" class="app-input resize-none" placeholder="Notification message"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Type</label>
                        <select id="type" class="app-input">
                            <option value="info">Information</option>
                            <option value="success">Success</option>
                            <option value="warning">Warning</option>
                            <option value="danger">Important / Danger</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Audience</label>
                        <select id="audienceType" class="app-input">
                            <option value="all_active_members">All Active Members</option>
                            <option value="role">By Role</option>
                            <option value="users">Selected Users</option>
                        </select>
                    </div>
                </div>

                <div id="roleSection" class="hidden">
                    <label class="form-label">Role <span class="text-red-500">*</span></label>
                    <select id="roleId" class="app-input">
                        <option value="">Select Role</option>
                    </select>
                </div>

                <div id="userSection" class="hidden">
                    <label class="form-label">Select Users <span class="text-red-500">*</span></label>

                    <div class="relative">
                        <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input id="userSearch" type="text" placeholder="Search users..." class="app-input !pl-9">
                    </div>

                    <div id="userResults" class="mt-2 max-h-48 overflow-y-auto rounded-md border border-slate-200 bg-white p-2">
                        <p class="p-3 text-center text-xs text-slate-400">Search users.</p>
                    </div>
                </div>

                <div>
                    <label class="form-label">Action URL</label>
                    <input id="actionUrl" type="text" maxlength="500" placeholder="/member/projects" class="app-input">
                    <p class="mt-1 text-[11px] text-slate-400">Optional. User can open this page from the notification.</p>
                </div>

                <div id="sendError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button type="button" onclick="closeSendModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button id="sendButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">Send Notification</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label{display:block;margin-bottom:.4rem;font-size:.8rem;font-weight:600;color:rgb(51 65 85)}
</style>
@endsection

@push('scripts')
<script>
let campaigns=[];
let selectedUsers=new Set();
let availableRoles=[];
let currentPage=1;
let lastPage=1;
let total=0;

const el={
    table:document.getElementById('campaignTable'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    form:document.getElementById('sendForm'),
    title:document.getElementById('title'),
    message:document.getElementById('message'),
    type:document.getElementById('type'),
    audienceType:document.getElementById('audienceType'),
    roleId:document.getElementById('roleId'),
    userSearch:document.getElementById('userSearch'),
    userResults:document.getElementById('userResults'),
    actionUrl:document.getElementById('actionUrl'),
    sendButton:document.getElementById('sendButton')
};

async function loadCampaigns(page=1){
    currentPage=page;
    el.table.innerHTML=AdminUI.loadingState('Loading notifications...',6);

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        page
    });

    try{
        const response=await api(`/api/notification-campaigns?${query}`);
        const paginator=response.data??{};

        campaigns=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??campaigns.length);

        renderCampaigns();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadCampaigns
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),6);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadCampaigns
        });
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
        console.error('Notification statistics load failed:',error);
    }
}

function renderCampaigns(){
    if(!campaigns.length){
        el.table.innerHTML=AdminUI.emptyState('No notifications found.',6);
        return;
    }

    el.table.innerHTML=campaigns.map(item=>`
        <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50">
            <td class="min-w-0 overflow-hidden px-3 py-4">
                <div class="flex min-w-0 items-start gap-2">
                    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full ${typeColor(item.type)}"></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-slate-800" title="${AdminUI.escapeHtml(item.title??'')}">
                            ${AdminUI.escapeHtml(item.title??'Untitled')}
                        </p>
                        <p class="mt-1 truncate text-[11px] text-slate-400" title="${AdminUI.escapeHtml(item.message??'')}">
                            ${AdminUI.escapeHtml(item.message??'')}
                        </p>
                    </div>
                </div>
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate text-xs text-slate-600" title="${AdminUI.escapeHtml(formatAudience(item.audience_type))}">
                    ${AdminUI.escapeHtml(formatAudience(item.audience_type))}
                </p>
            </td>

            <td class="overflow-hidden px-3 py-4">
                <p class="truncate text-xs font-semibold text-slate-700">${item.recipients_count??0}</p>
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate text-xs text-slate-600" title="${AdminUI.escapeHtml(item.sender?.name??'System')}">
                    ${AdminUI.escapeHtml(item.sender?.name??'System')}
                </p>
            </td>

            <td class="overflow-hidden px-3 py-4">
                ${notificationStatusBadge(item.status)}
            </td>

            <td class="overflow-hidden px-3 py-4">
                <p class="truncate whitespace-nowrap text-[11px] text-slate-500" title="${AdminUI.escapeHtml(AdminUI.formatDate(item.sent_at,true))}">
                    ${item.sent_at?AdminUI.formatDate(item.sent_at,true):'—'}
                </p>
            </td>
        </tr>
    `).join('');
}

function notificationStatusBadge(status){
    const map={
        sent:'bg-emerald-50 text-emerald-700',
        failed:'bg-red-50 text-red-700',
        sending:'bg-amber-50 text-amber-700',
        pending:'bg-slate-100 text-slate-600'
    };

    return `
        <span class="inline-flex max-w-full rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[status]??map.pending}">
            <span class="truncate">${AdminUI.escapeHtml(status??'pending')}</span>
        </span>
    `;
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
    }[value]??value??'—';
}

window.openSendModal=async function(){
    AdminUI.resetForm(el.form);
    AdminUI.clearError('sendError');

    selectedUsers.clear();
    el.type.value='info';
    el.audienceType.value='all_active_members';
    el.userSearch.value='';
    el.userResults.innerHTML='<p class="p-3 text-center text-xs text-slate-400">Search users.</p>';

    handleAudienceChange();
    AdminUI.openModal('sendModal');

    await loadRecipients();
};

window.closeSendModal=function(){
    AdminUI.closeModal('sendModal');
    selectedUsers.clear();
};

async function loadRecipients(search=''){
    el.userResults.innerHTML='<p class="p-3 text-center text-xs text-slate-400">Loading users...</p>';

    try{
        const response=await api(`/api/notification-campaigns/recipients?${AdminUI.query({search})}`);

        availableRoles=response.data?.roles??[];
        renderRoles();
        renderUsers(response.data?.users??[]);
    }catch(error){
        el.userResults.innerHTML=`
            <p class="p-3 text-center text-xs text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </p>
        `;
    }
}

function renderRoles(){
    const current=el.roleId.value;
    el.roleId.innerHTML='<option value="">Select Role</option>';

    availableRoles.forEach(role=>{
        const option=document.createElement('option');
        option.value=role.id;
        option.textContent=role.display_name||role.name;
        el.roleId.appendChild(option);
    });

    if([...el.roleId.options].some(option=>option.value===current)){
        el.roleId.value=current;
    }
}

function renderUsers(users){
    if(!users.length){
        el.userResults.innerHTML='<p class="p-3 text-center text-xs text-slate-400">No users found.</p>';
        return;
    }

    el.userResults.innerHTML=users.map(user=>`
        <label class="flex cursor-pointer items-center gap-3 rounded-md p-2 transition hover:bg-slate-50">
            <input type="checkbox" value="${user.id}" ${selectedUsers.has(Number(user.id))?'checked':''} onchange="toggleUser(${user.id},this.checked)" class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600">
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold text-slate-700" title="${AdminUI.escapeHtml(user.name??'')}">
                    ${AdminUI.escapeHtml(user.name??'User')}
                </p>
                <p class="truncate text-[10px] text-slate-400" title="${AdminUI.escapeHtml(user.email??'')}">
                    ${AdminUI.escapeHtml(user.email??'')}
                </p>
            </div>
        </label>
    `).join('');
}

window.toggleUser=function(id,checked){
    id=Number(id);
    checked?selectedUsers.add(id):selectedUsers.delete(id);
};

function handleAudienceChange(){
    const type=el.audienceType.value;

    document.getElementById('roleSection').classList.toggle('hidden',type!=='role');
    document.getElementById('userSection').classList.toggle('hidden',type!=='users');

    AdminUI.clearError('sendError');

    if(type==='users'){
        loadRecipients(el.userSearch.value.trim());
    }
}

el.form.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('sendError');

    const audienceType=el.audienceType.value;
    const data={
        title:el.title.value.trim(),
        message:el.message.value.trim(),
        type:el.type.value,
        audience_type:audienceType,
        action_url:el.actionUrl.value.trim()||null
    };

    if(!data.title){
        AdminUI.showError('sendError','Notification title is required.');
        return;
    }

    if(!data.message){
        AdminUI.showError('sendError','Notification message is required.');
        return;
    }

    if(audienceType==='role'){
        const roleId=Number(el.roleId.value);

        if(!roleId){
            AdminUI.showError('sendError','Please select a role.');
            return;
        }

        data.role_id=roleId;
    }

    if(audienceType==='users'){
        if(!selectedUsers.size){
            AdminUI.showError('sendError','Please select at least one user.');
            return;
        }

        data.user_ids=[...selectedUsers];
    }

    const audienceLabel=
        audienceType==='all_active_members'
            ?'all active members'
            :audienceType==='role'
                ?'the selected role'
                :`${selectedUsers.size} selected user(s)`;

    const confirmed=await AdminUI.confirm({
        title:'Send Notification?',
        message:`Send "${data.title}" to ${audienceLabel}?`,
        confirmText:'Send',
        cancelText:'Cancel',
        type:'primary'
    });

    if(!confirmed)return;

    AdminUI.setLoading(el.sendButton,'Sending...');

    try{
        await api('/api/notification-campaigns/send',{
            method:'POST',
            body:JSON.stringify(data)
        });

        closeSendModal();
        Toast.success('Notification sent successfully.');

        await Promise.all([
            loadCampaigns(1),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError('sendError',AdminUI.extractError(error));
    }finally{
        AdminUI.resetLoading(el.sendButton);
    }
});

window.clearFilters=function(){
    el.search.value='';
    el.statusFilter.value='';
    loadCampaigns(1);
};

async function initNotificationPage(){
    if(typeof window.AdminUI==='undefined'||typeof window.api==='undefined'){
        setTimeout(initNotificationPage,50);
        return;
    }

    el.search.addEventListener('input',AdminUI.debounce(()=>loadCampaigns(1)));
    el.statusFilter.addEventListener('change',()=>loadCampaigns(1));
    el.audienceType.addEventListener('change',handleAudienceChange);
    el.userSearch.addEventListener('input',AdminUI.debounce(event=>loadRecipients(event.target.value.trim())));

    await Promise.all([
        loadCampaigns(),
        loadStatistics()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initNotificationPage);
}else{
    initNotificationPage();
}
</script>
@endpush