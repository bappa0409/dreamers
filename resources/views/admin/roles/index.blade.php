@extends('layouts.admin')

@section('title','Roles & Permissions')
@section('page_title','Roles & Permissions')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-shield-lock"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Roles & Permissions</h1>
                <p class="text-sm text-slate-500">Manage system roles, custom roles and access permissions.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Role.create'))
            <button
                type="button"
                onclick="openRoleModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-plus-lg text-[11px]"></i>
                Add Role
            </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Roles</p>
                    <p id="totalRoles" class="mt-2 text-xl font-bold text-slate-800">0</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-person-badge"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-indigo-600">System Roles</p>
                    <p id="systemRoles" class="mt-2 text-xl font-bold text-indigo-700">0</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                    <i class="bi bi-shield-lock"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-600">Custom Roles</p>
                    <p id="customRoles" class="mt-2 text-xl font-bold text-emerald-700">0</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-person-plus"></i>
                </div>
            </div>
        </div>

        <div class="col-span-2 rounded-md border border-amber-200 bg-amber-50/40 p-4 xl:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-amber-600">Permissions</p>
                    <p id="totalPermissions" class="mt-2 text-xl font-bold text-amber-700">0</p>
                </div>

                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-key"></i>
                </div>
            </div>
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
                    <p class="text-sm font-semibold text-slate-700">Search Roles</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by role name, display name or description</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="roleSearchInput"
                        type="text"
                        placeholder="Search roles..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >
                </div>

                <button
                    type="button"
                    onclick="clearRoleSearch()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
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
                        <th class="w-[34%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Role</th>
                        <th class="w-[13%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Type</th>
                        <th class="w-[13%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Users</th>
                        <th class="w-[24%] px-4 py-3 text-left text-xs font-semibold text-slate-600">Permissions</th>
                        <th class="w-[16%] px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="rolesTable">
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-slate-400">
                            Loading roles...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Role Modal --}}
<div id="roleModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <div class="min-w-0">
                    <h2 id="roleModalTitle" class="text-lg font-bold text-slate-800">Add Role</h2>
                    <p class="text-xs text-slate-500">Configure role information and access permissions.</p>
                </div>
            </div>

            <button type="button" onclick="closeRoleModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="roleForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5 sm:p-6">
                {{-- Role Information --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-person-badge"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Role Information</h3>
                            <p class="text-[11px] text-slate-400">Basic identity of this role.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Role Name *</label>

                            <div class="relative">
                                <i class="bi bi-code-slash pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="roleName"
                                    type="text"
                                    maxlength="100"
                                    class="app-input !pl-9"
                                    placeholder="project_manager"
                                >
                            </div>

                            <p class="mt-1 text-[10px] text-slate-400">Lowercase letters, numbers and underscore only.</p>
                        </div>

                        <div>
                            <label class="form-label">Display Name *</label>

                            <div class="relative">
                                <i class="bi bi-type pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="roleDisplayName"
                                    type="text"
                                    maxlength="150"
                                    class="app-input !pl-9"
                                    placeholder="Project Manager"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Description</label>

                        <textarea
                            id="roleDescription"
                            rows="3"
                            maxlength="1000"
                            class="app-input resize-none"
                            placeholder="Describe what this role is responsible for..."
                        ></textarea>
                    </div>
                </section>

                {{-- Permissions --}}
                <section class="overflow-hidden rounded-md border border-slate-200 bg-white">
                    <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-100 text-violet-600">
                                <i class="bi bi-key"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">Access Permissions</h3>
                                <p class="text-[11px] text-slate-400">Select exactly what this role can access.</p>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick="selectAllPermissions()"
                                class="cursor-pointer rounded-md border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 transition hover:bg-indigo-100"
                            >
                                <i class="bi bi-check2-all mr-1"></i>
                                Select All
                            </button>

                            <button
                                type="button"
                                onclick="clearAllPermissions()"
                                class="cursor-pointer rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                            >
                                <i class="bi bi-x-circle mr-1"></i>
                                Clear
                            </button>
                        </div>
                    </div>

                    <div id="permissionContainer" class="space-y-3 p-4">
                        <div class="py-8 text-center text-sm text-slate-400">
                            Loading permissions...
                        </div>
                    </div>
                </section>

                <div id="roleFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"></div>
            </div>

            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
                <button
                    type="button"
                    onclick="closeRoleModal()"
                    class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="saveRoleButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                >
                    Save Role
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

<script>
let roles=[];
let permissionGroups={};
let editingRole=null;
let currentPage=1;

const perPage=10;

const canEditRole=@json(
    auth()->user()->hasPermission('Role.update')
);

const canDeleteRole=@json(
    auth()->user()->hasPermission('Role.delete')
);

const el={
    table:document.getElementById('rolesTable'),
    search:document.getElementById('roleSearchInput'),
    form:document.getElementById('roleForm'),
    name:document.getElementById('roleName'),
    displayName:document.getElementById('roleDisplayName'),
    description:document.getElementById('roleDescription'),
    permissionContainer:document.getElementById('permissionContainer'),
    saveButton:document.getElementById('saveRoleButton')
};

async function loadRoles(){
    el.table.innerHTML=AdminUI.loadingState('Loading roles...',5);

    try{
        const response=await api('/api/roles');

        roles=Array.isArray(response.data)
            ?response.data
            :[];

        renderRoles();
        updateRoleStats();
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            5
        );
    }
}

async function loadPermissions(){
    el.permissionContainer.innerHTML=`
        <div class="py-8 text-center text-sm text-slate-400">
            <span class="inline-flex items-center gap-2">
                <i class="bi bi-arrow-repeat animate-spin"></i>
                Loading permissions...
            </span>
        </div>
    `;

    try{
        const response=await api('/api/roles/permissions');

        permissionGroups=
            response.data??{};

        renderPermissionGroups();
        updateRoleStats();
    }catch(error){
        el.permissionContainer.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-5 text-center text-xs text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        `;
    }
}

function filteredRoles(){
    const search=
        el.search.value
            .trim()
            .toLowerCase();

    if(!search){
        return roles;
    }

    return roles.filter(role=>{
        const haystack=[
            role.name,
            role.display_name,
            role.description
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(search);
    });
}

function paginatedRoles(){
    const filtered=filteredRoles();

    const lastPage=Math.max(
        Math.ceil(filtered.length/perPage),
        1
    );

    if(currentPage>lastPage){
        currentPage=lastPage;
    }

    const start=
        (currentPage-1)*perPage;

    return{
        data:filtered.slice(
            start,
            start+perPage
        ),
        total:filtered.length,
        lastPage
    };
}

function renderRoles(){
    const result=paginatedRoles();

    if(!result.data.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No roles found.',
                5
            );

        renderRolePagination(
            result.total,
            result.lastPage
        );

        return;
    }

    el.table.innerHTML=result.data.map(role=>{
        const isSystem=
            Boolean(role.is_system);

        const isSystemAnalyst=
            role.name==='system_analyst';

        const permissionCount=
            role.permissions?.length??0;

        const userCount=
            role.users_count??0;

        return`
            <tr class="border-b border-slate-100 transition last:border-0 hover:bg-slate-50">
                <td class="min-w-0 px-4 py-3">
                    <div class="min-w-0">
                        <p
                            class="truncate text-xs font-semibold text-slate-800"
                            title="${AdminUI.escapeHtml(role.display_name||role.name||'')}"
                        >
                            ${AdminUI.escapeHtml(role.display_name||role.name||'—')}
                        </p>

                        <p
                            class="mt-1 truncate font-mono text-[10px] text-slate-400"
                            title="${AdminUI.escapeHtml(role.name??'')}"
                        >
                            ${AdminUI.escapeHtml(role.name??'')}
                        </p>

                        ${
                            role.description
                                ?`
                                    <p
                                        class="mt-1 truncate text-[11px] text-slate-500"
                                        title="${AdminUI.escapeHtml(role.description)}"
                                    >
                                        ${AdminUI.escapeHtml(role.description)}
                                    </p>
                                `
                                :''
                        }
                    </div>
                </td>

                <td class="px-4 py-3">
                    ${
                        isSystem
                            ?`
                                <span class="inline-flex rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700">
                                    System
                                </span>
                            `
                            :`
                                <span class="inline-flex rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
                                    Custom
                                </span>
                            `
                    }
                </td>

                <td class="px-4 py-3">
                    <p class="text-xs font-semibold text-slate-700">${userCount}</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">
                        ${Number(userCount)===1?'user':'users'}
                    </p>
                </td>

                <td class="min-w-0 px-4 py-3">
                    ${
                        isSystemAnalyst
                            ?`
                                <span class="inline-flex rounded-md bg-violet-50 px-2 py-1 text-[10px] font-semibold text-violet-700">
                                    Full Access
                                </span>
                            `
                            :`
                                <span class="inline-flex max-w-full rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600">
                                    ${permissionCount} permission${Number(permissionCount)===1?'':'s'}
                                </span>
                            `
                    }
                </td>

                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-1">
                        ${
                            canEditRole&&!isSystemAnalyst
                                ?`
                                    <button
                                        type="button"
                                        onclick="editRole(${role.id})"
                                        title="Edit Role"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                    >
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>
                                `
                                :''
                        }

                        ${
                            canDeleteRole&&!isSystem
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteRole(${role.id})"
                                        title="Delete Role"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                    >
                                        <i class="bi bi-trash text-xs"></i>
                                    </button>
                                `
                                :''
                        }

                        ${
                            isSystemAnalyst
                                ?`
                                    <span
                                        title="Protected System Role"
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-400"
                                    >
                                        <i class="bi bi-lock text-xs"></i>
                                    </span>
                                `
                                :''
                        }
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    renderRolePagination(
        result.total,
        result.lastPage
    );
}

function renderRolePagination(total,lastPage){
    AdminUI.renderPagination({
        container:'paginationContainer',
        currentPage,
        lastPage,
        total,
        onPageChange:page=>{
            currentPage=page;
            renderRoles();
        }
    });
}

function updateRoleStats(){
    document.getElementById('totalRoles').innerText=
        roles.length;

    document.getElementById('systemRoles').innerText=
        roles.filter(
            role=>Boolean(role.is_system)
        ).length;

    document.getElementById('customRoles').innerText=
        roles.filter(
            role=>!Boolean(role.is_system)
        ).length;

    document.getElementById('totalPermissions').innerText=
        Object.values(permissionGroups)
            .flat()
            .length;
}

function renderPermissionGroups(){
    const groups=
        Object.entries(permissionGroups);

    if(!groups.length){
        el.permissionContainer.innerHTML=`
            <div class="rounded-md border border-dashed border-slate-300 p-6 text-center text-xs text-slate-400">
                No permissions found.
            </div>
        `;

        return;
    }

    el.permissionContainer.innerHTML=
        groups.map(([module,permissions])=>`
            <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                            <i class="bi bi-grid"></i>
                        </div>

                        <div class="min-w-0">
                            <h4 class="truncate text-xs font-semibold text-slate-800">
                                ${AdminUI.escapeHtml(module)}
                            </h4>

                            <p class="text-[10px] text-slate-400">
                                ${permissions.length} permission${permissions.length===1?'':'s'}
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        data-module="${AdminUI.escapeHtml(module)}"
                        class="module-toggle-btn cursor-pointer rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[10px] font-semibold text-slate-600 transition hover:bg-indigo-50 hover:text-indigo-600"
                    >
                        Toggle All
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-2 p-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    ${permissions.map(permission=>`
                        <label class="group flex min-w-0 cursor-pointer items-start gap-2.5 rounded-md border border-slate-200 bg-white p-3 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                            <input
                                type="checkbox"
                                class="permission-checkbox mt-0.5 h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-indigo-600"
                                value="${permission.id}"
                                data-module="${AdminUI.escapeHtml(module)}"
                            >

                            <span class="min-w-0">
                                <span
                                    class="block truncate text-xs font-semibold text-slate-700 group-hover:text-indigo-700"
                                    title="${AdminUI.escapeHtml(permission.display_name||permission.name||'')}"
                                >
                                    ${AdminUI.escapeHtml(permission.display_name||permission.name||'')}
                                </span>

                                <span
                                    class="mt-1 block truncate font-mono text-[9px] text-slate-400"
                                    title="${AdminUI.escapeHtml(permission.name??'')}"
                                >
                                    ${AdminUI.escapeHtml(permission.name??'')}
                                </span>
                            </span>
                        </label>
                    `).join('')}
                </div>
            </div>
        `).join('');

    document
        .querySelectorAll('.module-toggle-btn')
        .forEach(button=>{
            button.addEventListener('click',()=>{
                toggleModulePermissions(
                    button.dataset.module
                );
            });
        });
}

window.openRoleModal=function(role=null){
    editingRole=role;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('roleFormError');
    clearAllPermissions();

    document.getElementById('roleModalTitle').innerText=
        role
            ?'Edit Role'
            :'Add Role';

    el.saveButton.innerText=
        role
            ?'Update Role'
            :'Save Role';

    el.name.disabled=
        Boolean(role);

    if(role){
        el.name.value=
            role.name??'';

        el.displayName.value=
            role.display_name??'';

        el.description.value=
            role.description??'';

        const permissionIds=
            (role.permissions??[])
                .map(permission=>
                    Number(permission.id)
                );

        document
            .querySelectorAll('.permission-checkbox')
            .forEach(checkbox=>{
                checkbox.checked=
                    permissionIds.includes(
                        Number(checkbox.value)
                    );
            });
    }

    AdminUI.openModal('roleModal');
};

window.closeRoleModal=function(){
    AdminUI.closeModal('roleModal');

    editingRole=null;
    el.name.disabled=false;
};

window.editRole=function(id){
    const role=
        roles.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!role){
        Toast.error('Role not found.');
        return;
    }

    if(role.name==='system_analyst'){
        Toast.warning(
            'System Analyst role cannot be modified.'
        );

        return;
    }

    openRoleModal(role);
};

function selectedPermissionIds(){
    return[
        ...document.querySelectorAll(
            '.permission-checkbox:checked'
        )
    ].map(
        checkbox=>
            Number(checkbox.value)
    );
}

window.selectAllPermissions=function(){
    document
        .querySelectorAll('.permission-checkbox')
        .forEach(checkbox=>{
            checkbox.checked=true;
        });
};

window.clearAllPermissions=function(){
    document
        .querySelectorAll('.permission-checkbox')
        .forEach(checkbox=>{
            checkbox.checked=false;
        });
};

function toggleModulePermissions(module){
    const checkboxes=[
        ...document.querySelectorAll(
            '.permission-checkbox'
        )
    ].filter(
        checkbox=>
            checkbox.dataset.module===module
    );

    const shouldCheck=
        checkboxes.some(
            checkbox=>!checkbox.checked
        );

    checkboxes.forEach(checkbox=>{
        checkbox.checked=shouldCheck;
    });
}

el.name.addEventListener('input',function(){
    if(editingRole){
        return;
    }

    this.value=
        this.value
            .toLowerCase()
            .replace(/\s+/g,'_')
            .replace(/[^a-z0-9_]/g,'');
});

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('roleFormError');

    const data={
        name:el.name.value.trim(),
        display_name:el.displayName.value.trim(),
        description:
            el.description.value.trim()||null,
        permission_ids:
            selectedPermissionIds()
    };

    if(!data.name){
        AdminUI.showError(
            'roleFormError',
            'Role name is required.'
        );

        return;
    }

    if(!/^[a-z0-9_]+$/.test(data.name)){
        AdminUI.showError(
            'roleFormError',
            'Role name may contain only lowercase letters, numbers and underscores.'
        );

        return;
    }

    if(!data.display_name){
        AdminUI.showError(
            'roleFormError',
            'Display name is required.'
        );

        return;
    }

    AdminUI.setLoading(
        el.saveButton,
        editingRole
            ?'Updating...'
            :'Saving...'
    );

    try{
        const editing=
            Boolean(editingRole);

        await api(
            editing
                ?`/api/roles/${editingRole.id}`
                :'/api/roles',
            {
                method:
                    editing
                        ?'PUT'
                        :'POST',

                body:JSON.stringify(data)
            }
        );

        closeRoleModal();

        Toast.success(
            editing
                ?'Role updated successfully.'
                :'Role created successfully.'
        );

        await Promise.all([
            loadRoles(),
            loadPermissions()
        ]);
    }catch(error){
        AdminUI.showError(
            'roleFormError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(
            el.saveButton
        );
    }
});

window.deleteRole=function(id){
    const role=
        roles.find(
            item=>
                Number(item.id)===
                Number(id)
        );

    if(!role){
        Toast.error('Role not found.');
        return;
    }

    if(role.is_system){
        Toast.warning(
            'System roles cannot be deleted.'
        );

        return;
    }

    AdminUI.deleteRequest(
        `/api/roles/${id}`,
        {
            title:'Delete Role?',
            message:'This role will be permanently deleted. Users assigned to this role may lose related access permissions.',
            confirmText:'Delete',
            successMessage:'Role deleted successfully.',
            onSuccess:async()=>{
                const remaining=
                    filteredRoles().length-1;

                if(
                    remaining>0&&
                    (currentPage-1)*perPage>=remaining
                ){
                    currentPage=
                        Math.max(
                            currentPage-1,
                            1
                        );
                }

                await Promise.all([
                    loadRoles(),
                    loadPermissions()
                ]);
            }
        }
    );
};

window.clearRoleSearch=function(){
    el.search.value='';
    currentPage=1;

    renderRoles();
};

async function initRolePage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initRolePage,
            50
        );

        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(()=>{
            currentPage=1;
            renderRoles();
        })
    );

    await Promise.all([
        loadPermissions(),
        loadRoles()
    ]);

    updateRoleStats();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initRolePage
    );
}else{
    initRolePage();
}
</script>
@endsection