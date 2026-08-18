@extends('layouts.admin')

@section('title','Roles & Permissions')
@section('page_title','Roles & Permissions')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-shield-lock text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">
                    Roles & Permissions
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Manage system roles, custom roles and access permissions.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Role.create'))
            <button type="button"
                onclick="openRoleForm()"
                class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white  transition hover:bg-indigo-700 hover:shadow">
                <i class="bi bi-plus-lg text-[11px]"></i>
                Add Role
            </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-5 ">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Total Roles</p>
                    <p id="totalRoles" class="mt-2 text-3xl font-bold text-slate-800">0</p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-person-badge text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-5 ">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">System Roles</p>
                    <p id="systemRoles" class="mt-2 text-3xl font-bold text-indigo-600">0</p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-shield-lock text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-5 ">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Custom Roles</p>
                    <p id="customRoles" class="mt-2 text-3xl font-bold text-emerald-600">0</p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-person-plus text-lg"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-5 ">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Permissions</p>
                    <p id="totalPermissions" class="mt-2 text-3xl font-bold text-amber-600">0</p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i class="bi bi-key text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    {{-- Search --}}
<div class="rounded-md border border-slate-200 bg-white p-3 ">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-search text-sm"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-slate-700">Search Role</p>
                <p class="hidden text-[11px] text-slate-400 sm:block">
                    Find roles quickly
                </p>
            </div>
        </div>

        <div class="flex w-full items-center sm:w-auto">
            <div class="relative w-full sm:w-80">
                <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                <input
                    id="roleSearchInput"
                    type="text"
                    placeholder="Search roles..."
                    class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                >
            </div>

            <button
                type="button"
                onclick="clearRoleSearch()"
                class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-r-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
                title="Clear search"
            >
                <i class="bi bi-x-lg text-[10px]"></i>
                Clear
            </button>
        </div>

    </div>
</div>

    {{-- Roles Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Role</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Type</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Users</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Permissions</th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="rolesTable">
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-500">Loading roles...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Role Modal --}}
<div id="roleModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white sm:rounded-3xl">

        {{-- Header --}}
        <div class="app-modal-header relative shrink-0 overflow-hidden border-b border-slate-200 px-5 py-5 sm:px-7">
            <div class="absolute -right-10 -top-12 h-36 w-36 rounded-full bg-indigo-100/60 blur-2xl"></div>

            <div class="relative flex items-start justify-between gap-4">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-lg text-white ">
                        <i class="bi bi-shield-lock"></i>
                    </div>

                    <div class="min-w-0">
                        <h2 id="roleModalTitle" class="text-xl font-bold tracking-tight text-slate-800">Add Role</h2>
                        <p class="mt-1 text-sm text-slate-500">Configure role information and access permissions.</p>
                    </div>
                </div>

                <button type="button" onclick="closeRoleForm()" class="app-modal-close" aria-label="Close">
                    <i class="bi bi-x-lg text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="overflow-y-auto">
            <form id="roleForm">
                <input type="hidden" id="roleId">

                <div class="space-y-7 p-5 sm:p-7">

                    {{-- Role Information --}}
                    <section class="rounded-md border border-slate-200 bg-white p-4  sm:p-5">
                        <div class="mb-5 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                                <i class="bi bi-person-badge"></i>
                            </div>

                            <div>
                                <h3 class="font-semibold text-slate-800">Role Information</h3>
                                <p class="text-xs text-slate-500">Basic identity of this role.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Role Name <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="bi bi-code-slash"></i>
                                    </div>

                                    <input id="roleName" type="text" class="app-input !pl-10" placeholder="project_manager">
                                </div>

                                <p class="mt-1.5 text-xs text-slate-400">Lowercase letters, numbers and underscore only.</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Display Name <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                        <i class="bi bi-type"></i>
                                    </div>

                                    <input id="roleDisplayName" type="text" class="app-input !pl-10" placeholder="Project Manager">
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                            <textarea id="roleDescription" rows="3" class="app-input resize-none" placeholder="Describe what this role is responsible for..."></textarea>
                        </div>
                    </section>

                    {{-- Permissions --}}
                    <section class="overflow-hidden rounded-md border border-slate-200 bg-white ">
                        <div class="flex flex-col gap-4 border-b border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-violet-100 text-violet-600">
                                    <i class="bi bi-key"></i>
                                </div>

                                <div>
                                    <h3 class="font-semibold text-slate-800">Access Permissions</h3>
                                    <p class="text-xs text-slate-500">Choose exactly what this role can access.</p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllPermissions()" class="rounded-md border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-xs font-semibold text-indigo-700 hover:border-indigo-300 hover:bg-indigo-100">
                                    <i class="bi bi-check2-all mr-1"></i>
                                    Select All
                                </button>

                                <button type="button" onclick="clearAllPermissions()" class="rounded-md border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                                    <i class="bi bi-x-circle mr-1"></i>
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div id="permissionContainer" class="space-y-4 p-4 sm:p-5">
                            <div class="flex items-center justify-center gap-2 py-10 text-sm text-slate-500">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                                Loading permissions...
                            </div>
                        </div>
                    </section>

                    <div id="roleFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700"></div>
                </div>

                {{-- Footer --}}
                <div class="sticky bottom-0 z-10 border-t border-slate-200 bg-white/95 px-5 py-4 backdrop-blur sm:px-7">
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" onclick="closeRoleForm()" class="rounded-md border border-slate-300 bg-white px-5 py-2.5 font-medium text-slate-700 hover:bg-slate-50">
                            Cancel
                        </button>

                        <button id="saveRoleButton" type="submit" class="rounded-md bg-indigo-600 px-6 py-2.5 font-semibold text-white  hover:bg-indigo-700 hover:shadow-md disabled:opacity-60">
                            <i class="bi bi-check2-circle mr-1"></i>
                            Save Role
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let roles=[];
let permissionGroups={};
let editingRole=null;
let roleSearchTimer=null;

const canEditRole=@json(
    auth()->user()->hasPermission('Role.update') ||
    auth()->user()->hasPermission('Role.edit')
);

const canDeleteRole=@json(
    auth()->user()->hasPermission('Role.delete')
);

async function loadRoles(){
    const tbody=document.getElementById('rolesTable');

    tbody.innerHTML=`
        <tr>
            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                Loading roles...
            </td>
        </tr>
    `;

    try{
        const response=await api('/api/roles');

        roles=Array.isArray(response.data)?response.data:[];

        renderRoles();
        updateRoleStats();

    }catch(error){
        console.error(error);

        tbody.innerHTML=`
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-red-500">
                    Failed to load roles.
                </td>
            </tr>
        `;
    }
}

async function loadPermissions(){
    try{
        const response=await api('/api/roles/permissions');

        permissionGroups=response.data??{};

        renderPermissionGroups();
        updateRoleStats();

    }catch(error){
        console.error(error);

        document.getElementById('permissionContainer').innerHTML=`
            <div class="py-8 text-center text-sm text-red-500">
                Failed to load permissions.
            </div>
        `;
    }
}

function filteredRoles(){
    const search=document.getElementById('roleSearchInput').value.trim().toLowerCase();

    if(!search)return roles;

    return roles.filter(role=>{
        return String(role.name??'').toLowerCase().includes(search)||
            String(role.display_name??'').toLowerCase().includes(search)||
            String(role.description??'').toLowerCase().includes(search);
    });
}

function renderRoles(){
    const tbody=document.getElementById('rolesTable');
    const data=filteredRoles();

    if(!data.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                    No roles found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=data.map(role=>{
        const isSystem=Boolean(role.is_system);
        const isSystemAnalyst=role.name==='system_analyst';
        const permissionCount=role.permissions?.length??0;
        const userCount=role.users_count??0;

        const editButton=canEditRole&&!isSystemAnalyst?`
            <button onclick="editRole(${role.id})" class="rounded-md bg-indigo-50 px-3 py-1.5 text-indigo-600 hover:bg-indigo-100">
                <i class="bi bi-pencil-square mr-1"></i>
                Edit
            </button>
        `:'';

        const deleteButton=canDeleteRole&&!isSystem?`
            <button onclick="deleteRole(${role.id})" class="rounded-md bg-red-50 px-3 py-1.5 text-red-600 hover:bg-red-100">
                <i class="bi bi-trash mr-1"></i>
                Delete
            </button>
        `:'';

        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-6 py-4">
                    <div class="font-semibold text-slate-800">
                        ${escapeHtml(role.display_name||role.name)}
                    </div>

                    <div class="mt-1 font-mono text-xs text-slate-400">
                        ${escapeHtml(role.name)}
                    </div>

                    ${role.description?`
                        <div class="mt-1 max-w-md text-xs text-slate-500">
                            ${escapeHtml(role.description)}
                        </div>
                    `:''}
                </td>

                <td class="px-6 py-4">
                    ${
                        isSystem
                            ?`
                                <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                    System
                                </span>
                            `
                            :`
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Custom
                                </span>
                            `
                    }
                </td>

                <td class="px-6 py-4">
                    <div class="font-semibold text-slate-700">${userCount}</div>
                    <div class="text-xs text-slate-400">users</div>
                </td>

                <td class="px-6 py-4">
                    ${
                        isSystemAnalyst
                            ?`
                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                    Full Access
                                </span>
                            `
                            :`
                                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
                                    ${permissionCount} permissions
                                </span>
                            `
                    }
                </td>

                <td class="px-6 py-4">
                    <div class="flex flex-wrap justify-end gap-2">
                        ${editButton}
                        ${deleteButton}

                        ${isSystemAnalyst?`
                            <span class="rounded-md bg-slate-100 px-3 py-1.5 text-xs text-slate-400">
                                Protected
                            </span>
                        `:''}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateRoleStats(){
    document.getElementById('totalRoles').innerText=roles.length;
    document.getElementById('systemRoles').innerText=roles.filter(role=>Boolean(role.is_system)).length;
    document.getElementById('customRoles').innerText=roles.filter(role=>!Boolean(role.is_system)).length;

    const permissions=Object.values(permissionGroups).flat();
    document.getElementById('totalPermissions').innerText=permissions.length;
}

function renderPermissionGroups(){
    const container=document.getElementById('permissionContainer');
    const groups=Object.entries(permissionGroups);

    if(!groups.length){
        container.innerHTML=`
            <div class="rounded-md border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                No permissions found.
            </div>
        `;
        return;
    }

    container.innerHTML=groups.map(([module,permissions])=>{
        return `
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                            <i class="bi bi-grid"></i>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-slate-800">
                                ${escapeHtml(module)}
                            </h4>

                            <p class="text-[11px] text-slate-400">
                                ${permissions.length} permissions
                            </p>
                        </div>
                    </div>

                    <button type="button" onclick="toggleModulePermissions('${escapeJs(module)}')" class="rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600">
                        Toggle All
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-2.5 p-4 sm:grid-cols-2 lg:grid-cols-3">
                    ${permissions.map(permission=>`
                        <label class="group flex cursor-pointer items-start gap-3 rounded-md border border-slate-200 bg-white p-3.5 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                            <input
                                type="checkbox"
                                class="permission-checkbox mt-0.5 h-4 w-4 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                value="${permission.id}"
                                data-module="${escapeHtml(module)}"
                            >

                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-700 group-hover:text-indigo-700">
                                    ${escapeHtml(permission.display_name||permission.name)}
                                </span>

                                <span class="mt-1 block break-all font-mono text-[10px] text-slate-400">
                                    ${escapeHtml(permission.name)}
                                </span>
                            </span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `;
    }).join('');
}

function openRoleForm(role=null){
    editingRole=role;

    const modal=document.getElementById('roleModal');
    const form=document.getElementById('roleForm');
    const errorBox=document.getElementById('roleFormError');

    form.reset();
    clearAllPermissions();

    errorBox.classList.add('hidden');
    errorBox.innerText='';

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add('overflow-hidden');

    document.getElementById('roleId').value=role?.id??'';

    if(role){
        document.getElementById('roleModalTitle').innerText='Edit Role';
        document.getElementById('saveRoleButton').innerHTML='<i class="bi bi-check2-circle mr-1"></i> Update Role';

        document.getElementById('roleName').value=role.name??'';
        document.getElementById('roleDisplayName').value=role.display_name??'';
        document.getElementById('roleDescription').value=role.description??'';

        const permissionIds=(role.permissions??[]).map(permission=>Number(permission.id));

        document.querySelectorAll('.permission-checkbox').forEach(checkbox=>{
            checkbox.checked=permissionIds.includes(Number(checkbox.value));
        });
    }else{
        document.getElementById('roleModalTitle').innerText='Add Role';
        document.getElementById('saveRoleButton').innerHTML='<i class="bi bi-check2-circle mr-1"></i> Save Role';
    }
}

function closeRoleForm(){
    const modal=document.getElementById('roleModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');

    document.body.classList.remove('overflow-hidden');
    editingRole=null;
}

function editRole(id){
    const role=roles.find(item=>Number(item.id)===Number(id));

    if(!role){
        alert('Role not found.');
        return;
    }

    if(role.name==='system_analyst'){
        alert('System Analyst role cannot be modified.');
        return;
    }

    openRoleForm(role);
}

function selectedPermissionIds(){
    return Array.from(
        document.querySelectorAll('.permission-checkbox:checked')
    ).map(checkbox=>Number(checkbox.value));
}

function selectAllPermissions(){
    document.querySelectorAll('.permission-checkbox').forEach(checkbox=>{
        checkbox.checked=true;
    });
}

function clearAllPermissions(){
    document.querySelectorAll('.permission-checkbox').forEach(checkbox=>{
        checkbox.checked=false;
    });
}

function toggleModulePermissions(module){
    const checkboxes=Array.from(
        document.querySelectorAll('.permission-checkbox')
    ).filter(checkbox=>checkbox.dataset.module===module);

    const shouldCheck=checkboxes.some(checkbox=>!checkbox.checked);

    checkboxes.forEach(checkbox=>{
        checkbox.checked=shouldCheck;
    });
}

document.getElementById('roleName').addEventListener('input',function(){
    if(editingRole)return;

    this.value=this.value
        .toLowerCase()
        .replace(/\s+/g,'_')
        .replace(/[^a-z0-9_]/g,'');
});

document.getElementById('roleForm').addEventListener('submit',async function(e){
    e.preventDefault();

    const button=document.getElementById('saveRoleButton');
    const errorBox=document.getElementById('roleFormError');

    const data={
        name:document.getElementById('roleName').value.trim(),
        display_name:document.getElementById('roleDisplayName').value.trim(),
        description:document.getElementById('roleDescription').value.trim()||null,
        permission_ids:selectedPermissionIds()
    };

    if(!data.name){
        showRoleFormError('Role name is required.');
        return;
    }

    if(!data.display_name){
        showRoleFormError('Display name is required.');
        return;
    }

    button.disabled=true;
    button.innerText=editingRole?'Updating...':'Saving...';

    errorBox.classList.add('hidden');
    errorBox.innerText='';

    try{
        if(editingRole){
            await api(`/api/roles/${editingRole.id}`,{
                method:'PUT',
                body:JSON.stringify(data)
            });

            alert('Role updated successfully.');
        }else{
            await api('/api/roles',{
                method:'POST',
                body:JSON.stringify(data)
            });

            alert('Role created successfully.');
        }

        closeRoleForm();
        await loadRoles();

    }catch(error){
        console.error(error);

        let message=error.data?.message||error.message||'Something went wrong.';

        if(error.data?.errors){
            const errors=Object.values(error.data.errors).flat();

            if(errors.length){
                message=errors.join(' ');
            }
        }

        showRoleFormError(message);

    }finally{
        button.disabled=false;
        button.innerHTML=editingRole
            ?'<i class="bi bi-check2-circle mr-1"></i> Update Role'
            :'<i class="bi bi-check2-circle mr-1"></i> Save Role';
    }
});

async function deleteRole(id){
    const role=roles.find(item=>Number(item.id)===Number(id));

    if(!role)return;

    if(role.is_system){
        alert('System roles cannot be deleted.');
        return;
    }

    if(!confirm(`Delete role "${role.display_name||role.name}"?`))return;

    try{
        await api(`/api/roles/${id}`,{
            method:'DELETE'
        });

        alert('Role deleted successfully.');
        await loadRoles();

    }catch(error){
        console.error(error);

        alert(
            error.data?.message||
            error.message||
            'Failed to delete role.'
        );
    }
}

function showRoleFormError(message){
    const box=document.getElementById('roleFormError');

    box.innerText=message;
    box.classList.remove('hidden');
}

function clearRoleSearch(){
    document.getElementById('roleSearchInput').value='';
    renderRoles();
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

function escapeJs(value){
    return String(value)
        .replaceAll('\\','\\\\')
        .replaceAll("'","\\'");
}

document.getElementById('roleSearchInput').addEventListener('input',function(){
    clearTimeout(roleSearchTimer);

    roleSearchTimer=setTimeout(()=>{
        renderRoles();
    },200);
});

document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){
        closeRoleForm();
    }
});

document.addEventListener('DOMContentLoaded',async function(){
    await Promise.all([
        loadPermissions(),
        loadRoles()
    ]);

    updateRoleStats();
});
</script>

@endsection