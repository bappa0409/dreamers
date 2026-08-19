@extends('layouts.admin')

@section('title','User Role Assignment')
@section('page_title','User Role Assignment')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-person-gear text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">User Role Assignment</h1>
                <p class="text-sm text-slate-500">Assign one or more roles to association members.</p>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3 ">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search User</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by name, email, mobile or member code</p>
                </div>
            </div>

            <div class="flex w-full items-center sm:w-auto">
                <div class="relative w-full sm:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input id="searchInput" type="text" placeholder="Search users..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <button type="button" onclick="clearSearch()" class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-r-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-800">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">User</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Member</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Roles</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="usersTable">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">Loading users...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Role Assignment Modal --}}
<div id="roleModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-2xl overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-person-gear"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Assign Roles</h2>
                    <p id="roleUserName" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeRoleModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto p-5 sm:p-6">
            <div id="roleList" class="space-y-2">
                <div class="py-8 text-center text-sm text-slate-500">Loading roles...</div>
            </div>

            <div id="roleError" class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
            <button type="button" onclick="closeRoleModal()" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </button>

            <button id="saveRolesButton" type="button" onclick="saveUserRoles()" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                Save Roles
            </button>
        </div>
    </div>
</div>

<script>
let users=[];
let availableRoles=[];
let selectedUser=null;
let currentPage=1;
let lastPage=1;
let searchTimer=null;

async function loadUsers(page=1){
    currentPage=page;

    const tbody=document.getElementById('usersTable');
    tbody.innerHTML=`
        <tr>
            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                Loading users...
            </td>
        </tr>
    `;

    try{
        const search=document.getElementById('searchInput').value.trim();
        const params=new URLSearchParams();

        if(search)params.set('search',search);
        params.set('page',page);

        const response=await api(`/api/user-roles?${params.toString()}`);
        const paginator=response.data??{};

        users=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;

        renderUsers();
        renderPagination();

    }catch(error){
        console.error(error);

        tbody.innerHTML=`
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-red-500">
                    Failed to load users.
                </td>
            </tr>
        `;
    }
}

function renderUsers(){
    const tbody=document.getElementById('usersTable');

    if(!users.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No users found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=users.map(user=>{
        const member=user.member??null;
        const roles=user.roles??[];

        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-6 py-4">
                    <div class="font-semibold text-slate-800">
                        ${escapeHtml(user.name??'N/A')}
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        ${escapeHtml(user.email??'')}
                    </div>

                    <div class="mt-1 text-xs text-slate-400">
                        ${escapeHtml(user.mobile??'')}
                    </div>
                </td>

                <td class="px-6 py-4">
                    ${
                        member
                            ?`
                                <div class="font-mono text-xs font-semibold text-indigo-600">
                                    ${escapeHtml(member.member_code??'')}
                                </div>

                                <div class="mt-1">
                                    ${memberStatusBadge(member.status)}
                                </div>
                            `
                            :`
                                <span class="text-xs text-slate-400">
                                    No member profile
                                </span>
                            `
                    }
                </td>

                <td class="px-6 py-4">
                    <div class="flex max-w-sm flex-wrap gap-1.5">
                        ${
                            roles.length
                                ?roles.map(role=>`
                                    <span class="rounded-md bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-700">
                                        ${escapeHtml(role.display_name||role.name)}
                                    </span>
                                `).join('')
                                :`
                                    <span class="text-xs text-slate-400">
                                        No roles
                                    </span>
                                `
                        }
                    </div>
                </td>

                <td class="px-6 py-4">
                    ${
                        user.is_active
                            ?`
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Active
                                </span>
                            `
                            :`
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500">
                                    Inactive
                                </span>
                            `
                    }
                </td>

                <td class="px-6 py-4 text-right">
                    <button onclick="openRoleModal(${user.id})" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">
                        <i class="bi bi-person-gear text-[10px]"></i>
                        Manage Roles
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function renderPagination(){
    const container=document.getElementById('paginationContainer');

    if(lastPage<=1){
        container.innerHTML='';
        return;
    }

    container.innerHTML=`
        <div class="flex items-center justify-between gap-3">
            <div class="text-xs text-slate-500">
                Page ${currentPage} of ${lastPage}
            </div>

            <div class="flex gap-2">
                <button
                    ${currentPage<=1?'disabled':''}
                    onclick="loadUsers(${currentPage-1})"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40"
                >
                    Previous
                </button>

                <button
                    ${currentPage>=lastPage?'disabled':''}
                    onclick="loadUsers(${currentPage+1})"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40"
                >
                    Next
                </button>
            </div>
        </div>
    `;
}

async function loadAvailableRoles(){
    if(availableRoles.length)return;

    const response=await api('/api/user-roles/available');
    availableRoles=Array.isArray(response.data)?response.data:[];
}

async function openRoleModal(userId){
    selectedUser=users.find(user=>Number(user.id)===Number(userId));

    if(!selectedUser){
        alert('User not found.');
        return;
    }

    const modal=document.getElementById('roleModal');
    const roleList=document.getElementById('roleList');

    document.getElementById('roleUserName').innerText=
        `${selectedUser.name??'User'}${selectedUser.member?.member_code?' - '+selectedUser.member.member_code:''}`;

    document.getElementById('roleError').classList.add('hidden');

    roleList.innerHTML=`
        <div class="py-8 text-center text-sm text-slate-500">
            Loading roles...
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    try{
        await loadAvailableRoles();
        renderRoleList();
    }catch(error){
        console.error(error);
        showRoleError('Failed to load roles.');
    }
}

function closeRoleModal(){
    document.getElementById('roleModal').classList.add('hidden');
    document.getElementById('roleModal').classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
    selectedUser=null;
}

function renderRoleList(){
    const container=document.getElementById('roleList');
    const selectedIds=(selectedUser?.roles??[]).map(role=>Number(role.id));

    container.innerHTML=availableRoles.map(role=>{
        const checked=selectedIds.includes(Number(role.id));
        const isMemberRole=role.name==='member'&&selectedUser?.member;

        return `
            <label class="flex cursor-pointer items-start gap-3 rounded-md border border-slate-200 p-3 transition hover:border-indigo-200 hover:bg-indigo-50/30">
                <input
                    type="checkbox"
                    class="user-role-checkbox mt-1 h-4 w-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                    value="${role.id}"
                    ${checked?'checked':''}
                    ${isMemberRole?'disabled':''}
                >

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-semibold text-slate-800">
                            ${escapeHtml(role.display_name||role.name)}
                        </span>

                        ${
                            role.is_system
                                ?`
                                    <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-semibold text-indigo-600">
                                        System
                                    </span>
                                `
                                :''
                        }

                        ${
                            isMemberRole
                                ?`
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
                                        Required
                                    </span>
                                `
                                :''
                        }
                    </div>

                    <div class="mt-1 font-mono text-[10px] text-slate-400">
                        ${escapeHtml(role.name)}
                    </div>

                    ${
                        role.description
                            ?`
                                <p class="mt-1 text-xs text-slate-500">
                                    ${escapeHtml(role.description)}
                                </p>
                            `
                            :''
                    }
                </div>
            </label>
        `;
    }).join('');
}

async function saveUserRoles(){
    if(!selectedUser)return;

    const button=document.getElementById('saveRolesButton');

    const roleIds=Array.from(
        document.querySelectorAll('.user-role-checkbox:checked')
    ).map(input=>Number(input.value));

    if(!roleIds.length){
        showRoleError('At least one role is required.');
        return;
    }

    button.disabled=true;
    button.innerText='Saving...';

    try{
        await api(`/api/user-roles/${selectedUser.id}`,{
            method:'PUT',
            body:JSON.stringify({
                role_ids:roleIds
            })
        });

        closeRoleModal();
        await loadUsers(currentPage);

    }catch(error){
        console.error(error);

        let message=error.data?.message||error.message||'Failed to update roles.';

        if(error.data?.errors){
            const errors=Object.values(error.data.errors).flat();
            if(errors.length)message=errors.join(' ');
        }

        showRoleError(message);

    }finally{
        button.disabled=false;
        button.innerText='Save Roles';
    }
}

function showRoleError(message){
    const box=document.getElementById('roleError');
    box.innerText=message;
    box.classList.remove('hidden');
}

function clearSearch(){
    document.getElementById('searchInput').value='';
    loadUsers(1);
}

function memberStatusBadge(status){
    const map={
        active:'bg-emerald-50 text-emerald-700',
        pending:'bg-amber-50 text-amber-700',
        suspended:'bg-red-50 text-red-700',
        inactive:'bg-slate-100 text-slate-500',
        rejected:'bg-red-50 text-red-700'
    };

    const className=map[status]??'bg-slate-100 text-slate-500';
    const label=status?status.charAt(0).toUpperCase()+status.slice(1):'Unknown';

    return `<span class="rounded-full px-2 py-1 text-[10px] font-semibold ${className}">${escapeHtml(label)}</span>`;
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

document.getElementById('searchInput').addEventListener('input',function(){
    clearTimeout(searchTimer);
    searchTimer=setTimeout(()=>loadUsers(1),350);
});

document.addEventListener('keydown',function(e){
    if(e.key==='Escape'&&!document.getElementById('roleModal').classList.contains('hidden')){
        closeRoleModal();
    }
});

document.addEventListener('DOMContentLoaded',function(){
    loadUsers();
});
</script>

@endsection