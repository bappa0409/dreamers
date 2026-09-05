@extends('layouts.admin')

@section('title','Notice Management')
@section('page_title','Notice Management')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-megaphone"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Notice Management</h1>
                <p class="text-xs text-slate-500">Create, schedule, publish and notify members.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Notice.create'))
            <button type="button" onclick="openNoticeModal()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Add Notice
            </button>
        @endif
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Notices</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by title or notice content</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto lg:gap-0">
                <div class="relative w-full sm:min-w-[220px] lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search notices..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 lg:rounded-r-none">
                </div>

                <select id="typeFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                    <option value="">All Types</option>
                    <option value="notice">Notice</option>
                    <option value="announcement">Announcement</option>
                    <option value="event">Event</option>
                    <option value="urgent">Urgent</option>
                </select>

                <select id="priorityFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 lg:rounded-none lg:border-l-0">
                    <option value="">All Priority</option>
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
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
                        <th class="w-[32%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Notice</th>
                        <th class="w-[11%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                        <th class="w-[11%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Priority</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Publish</th>
                        <th class="w-[21%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Creator</th>
                        <th class="w-[12%] px-3 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody id="noticeTable">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-400">Loading notices...</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Notice Modal --}}
<div id="noticeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-megaphone"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-sm font-semibold text-slate-800">Add Notice</h2>
                    <p class="text-xs text-slate-500">Create and publish association notice.</p>
                </div>
            </div>
            <button type="button" onclick="closeNoticeModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="noticeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input id="title" maxlength="255" class="app-input">
                </div>

                <div>
                    <label class="form-label">Content <span class="text-red-500">*</span></label>
                    <textarea id="content" rows="5" maxlength="10000" class="app-input resize-none"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Type <span class="text-red-500">*</span></label>
                        <select id="type" class="app-input">
                            <option value="notice">Notice</option>
                            <option value="announcement">Announcement</option>
                            <option value="event">Event</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Priority <span class="text-red-500">*</span></label>
                        <select id="priority" class="app-input">
                            <option value="normal">Normal</option>
                            <option value="low">Low</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Publish At</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="publishAt" type="text" class="app-input js-datetime-picker !pl-9" placeholder="Select date & time" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Expires At</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="expiresAt" type="text" class="app-input js-datetime-picker !pl-9" placeholder="Select date & time" autocomplete="off" data-after="publishAt" data-validation-compare-message="Expires At must be after Publish At.">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">Attachment</label>
                    <label for="attachment" class="flex cursor-pointer items-center gap-3 rounded-md border border-dashed border-slate-300 bg-slate-50 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/30">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-paperclip"></i>
                        </div>
                        <div class="min-w-0">
                            <p id="attachmentName" class="truncate text-sm font-semibold text-slate-600">Choose attachment</p>
                            <p class="mt-0.5 text-[10px] text-slate-400">Maximum 5 MB</p>
                        </div>
                    </label>
                    <input id="attachment" type="file" class="hidden" data-max-size="5242880" data-validation-file-size-message="Attachment must not exceed 5 MB.">
                </div>

                <label class="flex cursor-pointer items-center gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3">
                    <input id="isPublished" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Publish Notice</p>
                        <p class="mt-0.5 text-[11px] text-slate-400">Visibility follows the publish and expiry time.</p>
                    </div>
                </label>

                @if(auth()->user()->hasPermission('Notification.send'))
                    <label class="flex cursor-pointer items-center gap-3 rounded-md border border-indigo-100 bg-indigo-50/40 p-3">
                        <input id="notifyMembers" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                        <div>
                            <p class="text-sm font-semibold text-indigo-700">Notify Members</p>
                            <p class="mt-0.5 text-[11px] text-indigo-500">Send notification when this notice is published now.</p>
                        </div>
                    </label>

                    <div id="audienceSection" class="hidden rounded-md border border-slate-200 bg-slate-50/40 p-4">
                        <label class="form-label">Notification Audience</label>

                        <select id="audienceType" class="app-input">
                            <option value="all_active_members">All Active Members</option>
                            <option value="role">Specific Role</option>
                            <option value="users">Selected Users</option>
                        </select>

                        <div id="roleAudience" class="mt-3 hidden">
                            <label class="form-label">Role <span class="text-red-500">*</span></label>
                            <select id="roleId" class="app-input">
                                <option value="">Select Role</option>
                            </select>
                        </div>

                        <div id="userAudience" class="mt-3 hidden">
                            <label class="form-label">Users</label>

                            <div class="relative">
                                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="userSearch" type="text" placeholder="Search users..." class="app-input !pl-9">
                            </div>

                            <div id="userResults" class="mt-2 max-h-44 overflow-y-auto rounded-md border border-slate-200 bg-white p-2">
                                <p class="p-3 text-center text-sm text-slate-400">Search users.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div id="formError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">
                <button type="button" onclick="closeNoticeModal()" class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Cancel</button>
                <button id="saveButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Save Notice</button>
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
let notices=[];
let editingNotice=null;
let currentPage=1;
let lastPage=1;
let total=0;
let selectedUsers=new Set();
let availableRoles=[];

const canUpdate=@json(auth()->user()->hasPermission('Notice.update'));
const canDelete=@json(auth()->user()->hasPermission('Notice.delete'));
const canNotify=@json(auth()->user()->hasPermission('Notification.send'));

const el={
    table:document.getElementById('noticeTable'),
    search:document.getElementById('searchInput'),
    typeFilter:document.getElementById('typeFilter'),
    priorityFilter:document.getElementById('priorityFilter'),
    form:document.getElementById('noticeForm'),
    title:document.getElementById('title'),
    content:document.getElementById('content'),
    type:document.getElementById('type'),
    priority:document.getElementById('priority'),
    publishAt:document.getElementById('publishAt'),
    expiresAt:document.getElementById('expiresAt'),
    attachment:document.getElementById('attachment'),
    attachmentName:document.getElementById('attachmentName'),
    isPublished:document.getElementById('isPublished'),
    saveButton:document.getElementById('saveButton')
};

function stripHtml(value){
    const div=document.createElement('div');
    div.innerHTML=value??'';
    return div.textContent||div.innerText||'';
}

function setDateTimePicker(element,value){
    if(!element)return;

    if(!value){
        element.value='';
        if(element._flatpickr)element._flatpickr.clear();
        return;
    }

    const date=new Date(value);

    if(element._flatpickr)element._flatpickr.setDate(date,false);
    else element.value=value;
}

async function loadNotices(page=1){
    currentPage=page;
    el.table.innerHTML=AdminUI.loadingState('Loading notices...',6);

    const query=AdminUI.query({
        search:el.search.value.trim(),
        type:el.typeFilter.value,
        priority:el.priorityFilter.value,
        page
    });

    try{
        const response=await api(`/api/notices?${query}`);
        const paginator=response.data??{};

        notices=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??notices.length);

        renderNotices();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadNotices
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),6);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadNotices
        });
    }
}

function renderNotices(){
    if(!notices.length){
        el.table.innerHTML=AdminUI.emptyState('No notices found.',6);
        return;
    }

    el.table.innerHTML=notices.map(item=>`
        <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50">
            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate text-xs font-semibold text-slate-800" title="${AdminUI.escapeHtml(item.title??'')}">
                    ${AdminUI.escapeHtml(item.title??'Untitled')}
                </p>
                <p class="mt-1 truncate text-[11px] text-slate-400" title="${AdminUI.escapeHtml(stripHtml(item.content))}">
                    ${AdminUI.escapeHtml(stripHtml(item.content))}
                </p>
                ${item.attachment?`
                    <p class="mt-1.5 flex items-center gap-1 text-[10px] text-indigo-500">
                        <i class="bi bi-paperclip"></i>
                        Attachment
                    </p>
                `:''}
            </td>

            <td class="overflow-hidden px-3 py-4">
                ${typeBadge(item.type)}
            </td>

            <td class="overflow-hidden px-3 py-4">
                ${priorityBadge(item.priority)}
            </td>

            <td class="overflow-hidden px-3 py-4">
                ${publishBadge(item)}
            </td>

            <td class="min-w-0 overflow-hidden px-3 py-4">
                <p class="truncate text-sm font-medium text-slate-600" title="${AdminUI.escapeHtml(item.creator?.name??'System')}">
                    ${AdminUI.escapeHtml(item.creator?.name??'System')}
                </p>
                ${item.publish_at?`
                    <p class="mt-1 truncate text-[10px] text-slate-400">
                        ${AdminUI.formatDate(item.publish_at,true)}
                    </p>
                `:''}
            </td>

            <td class="px-3 py-4">
                <div class="flex justify-end gap-1">
                    ${canUpdate?`
                        <button type="button" onclick="editNotice(${item.id})" title="Edit" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                            <i class="bi bi-pencil-square text-sm"></i>
                        </button>

                        <button type="button" onclick="togglePublish(${item.id})" title="${item.is_published?'Unpublish':'Publish'}" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700 transition hover:bg-amber-100">
                            <i class="bi ${item.is_published?'bi-eye-slash':'bi-eye'} text-sm"></i>
                        </button>
                    `:''}

                    ${canDelete?`
                        <button type="button" onclick="deleteNotice(${item.id})" title="Delete" class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100">
                            <i class="bi bi-trash text-sm"></i>
                        </button>
                    `:''}
                </div>
            </td>
        </tr>
    `).join('');
}

function typeBadge(type){
    const map={
        notice:'bg-indigo-50 text-indigo-700',
        announcement:'bg-sky-50 text-sky-700',
        event:'bg-violet-50 text-violet-700',
        urgent:'bg-red-50 text-red-700'
    };

    return `<span class="inline-flex max-w-full rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[type]??map.notice}">
        <span class="truncate">${AdminUI.escapeHtml(type??'notice')}</span>
    </span>`;
}

function priorityBadge(priority){
    const map={
        low:'bg-sky-50 text-sky-700',
        normal:'bg-slate-100 text-slate-600',
        high:'bg-amber-50 text-amber-700',
        urgent:'bg-red-50 text-red-700'
    };

    return `<span class="inline-flex max-w-full rounded-md px-2 py-1 text-[10px] font-semibold capitalize ${map[priority]??map.normal}">
        <span class="truncate">${AdminUI.escapeHtml(priority??'normal')}</span>
    </span>`;
}

function publishBadge(item){
    if(!item.is_published)return '<span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">Draft</span>';
    if(item.publish_at&&new Date(item.publish_at)>new Date())return '<span class="rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-600">Scheduled</span>';
    if(item.expires_at&&new Date(item.expires_at)<new Date())return '<span class="rounded-md bg-red-50 px-2 py-1 text-[10px] font-semibold text-red-600">Expired</span>';
    return '<span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">Published</span>';
}

window.openNoticeModal=function(notice=null){
    editingNotice=notice;
    selectedUsers.clear();

    AdminUI.resetForm(el.form);
    AdminUI.clearError('formError');

    document.getElementById('modalTitle').innerText=notice?'Edit Notice':'Add Notice';
    el.saveButton.innerText=notice?'Update Notice':'Save Notice';
    el.type.value='notice';
    el.priority.value='normal';
    el.attachment.value='';
    el.attachmentName.innerText='Choose attachment';

    if(notice){
        el.title.value=notice.title??'';
        el.content.value=notice.content??'';
        el.type.value=notice.type??'notice';
        el.priority.value=notice.priority??'normal';
        el.isPublished.checked=Boolean(notice.is_published);

        setDateTimePicker(el.publishAt,notice.publish_at);
        setDateTimePicker(el.expiresAt,notice.expires_at);

        if(notice.attachment)el.attachmentName.innerText='Existing attachment';
    }else{
        el.isPublished.checked=false;
        setDateTimePicker(el.publishAt,null);
        setDateTimePicker(el.expiresAt,null);
    }

    if(canNotify){
        const notify=document.getElementById('notifyMembers');

        notify.checked=false;
        document.getElementById('audienceSection').classList.add('hidden');
        document.getElementById('audienceType').value='all_active_members';
        document.getElementById('roleAudience').classList.add('hidden');
        document.getElementById('userAudience').classList.add('hidden');
        document.getElementById('userSearch').value='';
        document.getElementById('userResults').innerHTML='<p class="p-3 text-center text-sm text-slate-400">Search users.</p>';

        if(!notice)loadRecipients();
    }

    AdminUI.openModal('noticeModal');
};

window.closeNoticeModal=function(){
    AdminUI.closeModal('noticeModal');
    editingNotice=null;
    selectedUsers.clear();
};

window.editNotice=function(id){
    const notice=notices.find(item=>Number(item.id)===Number(id));

    if(!notice){
        Toast.error('Notice not found.');
        return;
    }

    openNoticeModal(notice);
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('formError');

    const title=el.title.value.trim();
    const content=el.content.value.trim();

    const formData=new FormData();

    formData.append('title',title);
    formData.append('content',content);
    formData.append('type',el.type.value);
    formData.append('priority',el.priority.value);
    formData.append('is_published',el.isPublished.checked?'1':'0');

    if(el.publishAt.value)formData.append('publish_at',el.publishAt.value);
    if(el.expiresAt.value)formData.append('expires_at',el.expiresAt.value);
    if(el.attachment.files[0])formData.append('attachment',el.attachment.files[0]);

    let notify=false;

    if(canNotify&&!editingNotice){
        notify=document.getElementById('notifyMembers').checked;

        formData.append('notify_members',notify?'1':'0');

        if(notify){
            const audience=document.getElementById('audienceType').value;

            formData.append('audience_type',audience);

            if(audience==='role'){
                const roleId=document.getElementById('roleId').value;

                if(!roleId){
                    AdminUI.showFieldError('roleId','Please select a notification role.');
                    document.getElementById('roleId').focus();
                    return;
                }

                formData.append('role_id',roleId);
            }

            if(audience==='users'){
                if(!selectedUsers.size){
                    AdminUI.showFieldError('userSearch','Please select at least one user.');
                    document.getElementById('userSearch').focus();
                    return;
                }

                selectedUsers.forEach(id=>formData.append('user_ids[]',id));
            }
        }
    }

    AdminUI.setLoading(el.saveButton,editingNotice?'Updating...':'Saving...');

    try{
        const editing=Boolean(editingNotice);
        const pageAfterSave=editing?currentPage:1;

        let response;

        if(editing){
            formData.append('_method','PUT');

            response=await api(`/api/notices/${editingNotice.id}`,{
                method:'POST',
                body:formData
            });
        }else{
            response=await api('/api/notices',{
                method:'POST',
                body:formData
            });
        }

        closeNoticeModal();

        if(response?.notification_sent===false&&notify){
            Toast.warning(
                response.message||
                'Notice saved, but member notification could not be sent.'
            );
        }else{
            Toast.success(
                response?.message||
                (editing
                    ?'Notice updated successfully.'
                    :'Notice created successfully.')
            );
        }

        await loadNotices(pageAfterSave);
    }catch(error){
        if(!AdminUI.showValidationErrors(el.form,error,{
            title:'title',
            content:'content',
            type:'type',
            priority:'priority',
            publish_at:'publishAt',
            expires_at:'expiresAt',
            attachment:'attachment',
            audience_type:'audienceType',
            role_id:'roleId',
            user_ids:'userSearch'
        })){
            AdminUI.showError('formError',AdminUI.extractError(error));
        }
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

window.togglePublish=function(id){
    const notice=notices.find(item=>Number(item.id)===Number(id));

    if(!notice){
        Toast.error('Notice not found.');
        return;
    }

    const publishing=!notice.is_published;

    AdminUI.request(`/api/notices/${id}/toggle-publish`,{
        method:'PATCH',
        data:{},
        confirmMessage:publishing
            ?`Publish "${notice.title}"?`
            :`Unpublish "${notice.title}"?`,
        successMessage:publishing
            ?'Notice published successfully.'
            :'Notice unpublished successfully.',
        onSuccess:()=>loadNotices(currentPage)
    });
};

window.deleteNotice=function(id){
    const notice=notices.find(item=>Number(item.id)===Number(id));

    if(!notice){
        Toast.error('Notice not found.');
        return;
    }

    AdminUI.deleteRequest(`/api/notices/${id}`,{
        message:`Delete "${notice.title}" permanently? This action cannot be undone.`,
        successMessage:'Notice deleted successfully.',
        onSuccess:async()=>{
            if(notices.length===1&&currentPage>1)currentPage--;
            await loadNotices(currentPage);
        }
    });
};

async function loadRecipients(search=''){
    if(!canNotify)return;

    const results=document.getElementById('userResults');

    if(results){
        results.innerHTML='<p class="p-3 text-center text-sm text-slate-400">Loading users...</p>';
    }

    try{
        const response=await api(`/api/notification-campaigns/recipients?${AdminUI.query({search})}`);

        availableRoles=response.data?.roles??[];
        renderRoles();
        renderUsers(response.data?.users??[]);
    }catch(error){
        if(results){
            results.innerHTML=`
                <p class="p-3 text-center text-sm text-red-600">
                    ${AdminUI.escapeHtml(AdminUI.extractError(error))}
                </p>
            `;
        }
    }
}

function renderRoles(){
    const select=document.getElementById('roleId');
    if(!select)return;

    select.innerHTML='<option value="">Select Role</option>';

    availableRoles.forEach(role=>{
        const option=document.createElement('option');
        option.value=role.id;
        option.textContent=role.display_name||role.name;
        select.appendChild(option);
    });
}

function renderUsers(users){
    const container=document.getElementById('userResults');
    if(!container)return;

    if(!users.length){
        container.innerHTML='<p class="p-3 text-center text-sm text-slate-400">No users found.</p>';
        return;
    }

    container.innerHTML=users.map(user=>`
        <label class="flex cursor-pointer items-center gap-2 rounded-md p-2 transition hover:bg-slate-50">
            <input type="checkbox" value="${user.id}" ${selectedUsers.has(Number(user.id))?'checked':''} onchange="toggleUser(${user.id},this.checked)" class="h-4 w-4 shrink-0 rounded border-slate-300 text-indigo-600">
            <div class="min-w-0">
                <p class="truncate text-xs font-semibold text-slate-700">${AdminUI.escapeHtml(user.name??'User')}</p>
                <p class="truncate text-[10px] text-slate-400">${AdminUI.escapeHtml(user.email??'')}</p>
            </div>
        </label>
    `).join('');
}

window.toggleUser=function(id,checked){
    id=Number(id);
    checked?selectedUsers.add(id):selectedUsers.delete(id);
};

window.clearFilters=function(){
    el.search.value='';
    el.typeFilter.value='';
    el.priorityFilter.value='';
    loadNotices(1);
};

el.attachment.addEventListener('change',()=>{
    const file=el.attachment.files[0];

    el.attachmentName.innerText=file
        ?`${file.name} • ${AdminUI.formatBytes(file.size)}`
        :'Choose attachment';
});

async function initNoticePage(){
    if(typeof window.AdminUI==='undefined'||typeof window.api==='undefined'){
        setTimeout(initNoticePage,50);
        return;
    }

    if(typeof window.initDatePickers==='function')window.initDatePickers();

    el.search.addEventListener('input',AdminUI.debounce(()=>loadNotices(1)));
    el.typeFilter.addEventListener('change',()=>loadNotices(1));
    el.priorityFilter.addEventListener('change',()=>loadNotices(1));

    if(canNotify){
        document.getElementById('notifyMembers').addEventListener('change',function(){
            document.getElementById('audienceSection').classList.toggle('hidden',!this.checked);

            if(this.checked){
                el.isPublished.checked=true;
            }
        });

        el.isPublished.addEventListener('change',function(){
            if(this.checked){
                return;
            }

            const notify=document.getElementById('notifyMembers');

            if(notify.checked){
                notify.checked=false;
                document.getElementById('audienceSection').classList.add('hidden');
            }
        });

        document.getElementById('audienceType').addEventListener('change',function(){
            document.getElementById('roleAudience').classList.toggle('hidden',this.value!=='role');
            document.getElementById('userAudience').classList.toggle('hidden',this.value!=='users');

            if(this.value==='users')loadRecipients(document.getElementById('userSearch').value.trim());
        });

        document.getElementById('userSearch').addEventListener(
            'input',
            AdminUI.debounce(event=>loadRecipients(event.target.value.trim()))
        );
    }

    await loadNotices();
}

if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',initNoticePage);
}else{
    initNoticePage();
}
</script>
@endpush