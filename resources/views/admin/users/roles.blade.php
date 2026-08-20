@extends('layouts.admin')

@section('title','User Role Assignment')
@section('page_title','User Role Assignment')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-person-gear"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">User Role Assignment</h1>
                <p class="text-sm text-slate-500">Assign one or more roles to association users.</p>
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
                    <p class="text-sm font-semibold text-slate-700">Search User</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by name, email, mobile or member code
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search users..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >
                </div>

                <button
                    type="button"
                    onclick="clearSearch()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-r-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
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
                        <th class="w-[27%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            User
                        </th>

                        <th class="w-[17%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Member
                        </th>

                        <th class="w-[31%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Roles
                        </th>

                        <th class="w-[11%] px-4 py-3 text-left text-xs font-semibold text-slate-600">
                            Status
                        </th>

                        <th class="w-[14%] px-4 py-3 text-right text-xs font-semibold text-slate-600">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody id="usersTable">
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                            Loading users...
                        </td>
                    </tr>
                </tbody>

            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Role Assignment Modal --}}
<div
    id="roleModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>
    <div class="app-modal-panel w-full max-w-4xl overflow-hidden rounded-md bg-white">

        {{-- Modal Header --}}
        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person-gear"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Assign Roles
                    </h2>

                    <p id="roleUserName" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeRoleModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="max-h-[70vh] overflow-y-auto p-5 sm:p-6">

            <div
                id="roleList"
                class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div class="col-span-full py-8 text-center text-sm text-slate-400">
                    Loading roles...
                </div>
            </div>

            <div
                id="roleError"
                class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"
            ></div>

        </div>

        {{-- Modal Footer --}}
        <div class="flex justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">

            <button
                type="button"
                onclick="closeRoleModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Cancel
            </button>

            <button
                id="saveRolesButton"
                type="button"
                onclick="saveUserRoles()"
                class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
            >
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
let total=0;

const el={
    table:document.getElementById('usersTable'),
    pagination:document.getElementById('paginationContainer'),
    search:document.getElementById('searchInput'),
    roleList:document.getElementById('roleList'),
    roleError:document.getElementById('roleError'),
    saveRolesButton:document.getElementById('saveRolesButton')
};

/*
|--------------------------------------------------------------------------
| Load Users
|--------------------------------------------------------------------------
*/

async function loadUsers(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState(
        'Loading users...',
        5
    );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        page
    });

    try{
        const response=await api(
            `/api/user-roles?${query}`
        );

        const paginator=response.data??{};

        users=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??users.length
        );

        renderUsers();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadUsers
        });

    }catch(error){

        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            5
        );

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadUsers
        });
    }
}

/*
|--------------------------------------------------------------------------
| Render Users
|--------------------------------------------------------------------------
*/

function renderUsers(){

    if(!users.length){

        el.table.innerHTML=AdminUI.emptyState(
            'No users found.',
            5
        );

        return;
    }

    el.table.innerHTML=users.map(user=>{

        const member=user.member??null;
        const roles=user.roles??[];

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">

                <td class="min-w-0 px-4 py-3">

                    <p
                        class="truncate text-xs font-semibold text-slate-800"
                        title="${AdminUI.escapeHtml(user.name??'')}"
                    >
                        ${AdminUI.escapeHtml(user.name??'N/A')}
                    </p>

                    <p
                        class="mt-1 truncate text-[11px] text-slate-500"
                        title="${AdminUI.escapeHtml(user.email??'')}"
                    >
                        ${AdminUI.escapeHtml(user.email??'')}
                    </p>

                    ${
                        user.mobile
                            ?`
                                <p class="mt-1 truncate text-[10px] text-slate-400">
                                    ${AdminUI.escapeHtml(user.mobile)}
                                </p>
                            `
                            :''
                    }

                </td>

                <td class="px-4 py-3">

                    ${
                        member
                            ?`
                                <p class="truncate font-mono text-[11px] font-semibold text-indigo-600">
                                    ${AdminUI.escapeHtml(member.member_code??'')}
                                </p>

                                <div class="mt-1">
                                    ${memberStatusBadge(member.status)}
                                </div>
                            `
                            :`
                                <span class="text-[11px] text-slate-400">
                                    No member profile
                                </span>
                            `
                    }

                </td>

                <td class="min-w-0 px-4 py-3">

                    ${
                        roles.length
                            ?`
                                <div class="flex max-h-14 flex-wrap gap-1 overflow-hidden">

                                    ${roles.slice(0,4).map(role=>`
                                        <span
                                            class="max-w-[130px] truncate rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700"
                                            title="${AdminUI.escapeHtml(role.display_name||role.name||'')}"
                                        >
                                            ${AdminUI.escapeHtml(role.display_name||role.name||'')}
                                        </span>
                                    `).join('')}

                                    ${
                                        roles.length>4
                                            ?`
                                                <span
                                                    class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600"
                                                    title="${roles.slice(4).map(role=>AdminUI.escapeHtml(role.display_name||role.name||'')).join(', ')}"
                                                >
                                                    +${roles.length-4}
                                                </span>
                                            `
                                            :''
                                    }

                                </div>
                            `
                            :`
                                <span class="text-[11px] text-slate-400">
                                    No roles
                                </span>
                            `
                    }

                </td>

                <td class="px-4 py-3">

                    ${
                        user.is_active
                            ?`
                                <span class="rounded-md bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-700">
                                    Active
                                </span>
                            `
                            :`
                                <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">
                                    Inactive
                                </span>
                            `
                    }

                </td>

                <td class="px-4 py-3 text-right">

                    <button
                        type="button"
                        onclick="openRoleModal(${user.id})"
                        title="Manage Roles"
                        class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                    >
                        <i class="bi bi-person-gear text-xs"></i>
                    </button>

                </td>

            </tr>
        `;

    }).join('');
}

/*
|--------------------------------------------------------------------------
| Load Available Roles
|--------------------------------------------------------------------------
*/

async function loadAvailableRoles(){

    if(availableRoles.length){
        return;
    }

    const response=await api(
        '/api/user-roles/available'
    );

    availableRoles=Array.isArray(response.data)
        ?response.data
        :[];
}

/*
|--------------------------------------------------------------------------
| Open Role Modal
|--------------------------------------------------------------------------
*/

window.openRoleModal=async function(userId){

    selectedUser=users.find(
        user=>Number(user.id)===Number(userId)
    );

    if(!selectedUser){
        Toast.error('User not found.');
        return;
    }

    AdminUI.clearError('roleError');

    document.getElementById('roleUserName').innerText=
        `${selectedUser.name??'User'}${
            selectedUser.member?.member_code
                ?' • '+selectedUser.member.member_code
                :''
        }`;

    el.roleList.innerHTML=`
        <div class="col-span-full py-8 text-center text-sm text-slate-400">
            <i class="bi bi-arrow-repeat mr-1 animate-spin"></i>
            Loading roles...
        </div>
    `;

    AdminUI.openModal('roleModal');

    try{

        await loadAvailableRoles();

        renderRoleList();

    }catch(error){

        el.roleList.innerHTML='';

        AdminUI.showError(
            'roleError',
            AdminUI.extractError(error)
        );
    }
};

/*
|--------------------------------------------------------------------------
| Close Role Modal
|--------------------------------------------------------------------------
*/

window.closeRoleModal=function(){

    AdminUI.closeModal('roleModal');

    selectedUser=null;

    AdminUI.clearError('roleError');
};

/*
|--------------------------------------------------------------------------
| Render Roles
|--------------------------------------------------------------------------
*/

function renderRoleList(){

    if(!availableRoles.length){

        el.roleList.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-400">
                No roles available.
            </div>
        `;

        return;
    }

    const selectedIds=new Set(
        (selectedUser?.roles??[])
            .map(role=>Number(role.id))
    );

    el.roleList.innerHTML=availableRoles.map(role=>{

        const roleId=Number(role.id);

        const checked=selectedIds.has(roleId);

        const isMemberRole=
            role.name==='member'&&
            Boolean(selectedUser?.member);

        return`
            <label
                class="
                    relative flex min-h-[105px] cursor-pointer flex-col
                    rounded-md border border-slate-200 bg-white p-3
                    transition
                    hover:border-indigo-300
                    hover:bg-indigo-50/30
                    has-[:checked]:border-indigo-300
                    has-[:checked]:bg-indigo-50/50
                    ${isMemberRole?'cursor-not-allowed opacity-75':''}
                "
            >

                <div class="flex items-start justify-between gap-2">

                    <div class="min-w-0">

                        <p
                            class="truncate text-xs font-semibold text-slate-800"
                            title="${AdminUI.escapeHtml(role.display_name||role.name||'')}"
                        >
                            ${AdminUI.escapeHtml(role.display_name||role.name||'')}
                        </p>

                        <p class="mt-1 truncate font-mono text-[9px] text-slate-400">
                            ${AdminUI.escapeHtml(role.name)}
                        </p>

                    </div>

                    <input
                        type="checkbox"
                        class="user-role-checkbox h-4 w-4 shrink-0 cursor-pointer rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                        value="${role.id}"
                        ${checked?'checked':''}
                        ${isMemberRole?'disabled':''}
                    >

                </div>

                ${
                    role.description
                        ?`
                            <p
                                class="mt-2 line-clamp-2 text-[10px] leading-4 text-slate-500"
                                title="${AdminUI.escapeHtml(role.description)}"
                            >
                                ${AdminUI.escapeHtml(role.description)}
                            </p>
                        `
                        :''
                }

                <div class="mt-auto flex flex-wrap gap-1 pt-2">

                    ${
                        role.is_system
                            ?`
                                <span class="rounded-md bg-indigo-50 px-1.5 py-0.5 text-[9px] font-semibold text-indigo-600">
                                    System
                                </span>
                            `
                            :''
                    }

                    ${
                        isMemberRole
                            ?`
                                <span class="rounded-md bg-slate-100 px-1.5 py-0.5 text-[9px] font-semibold text-slate-500">
                                    Required
                                </span>
                            `
                            :''
                    }

                </div>

            </label>
        `;

    }).join('');
}

/*
|--------------------------------------------------------------------------
| Save Roles
|--------------------------------------------------------------------------
*/

window.saveUserRoles=async function(){

    if(!selectedUser){
        return;
    }

    AdminUI.clearError('roleError');

    /*
     * Disabled required roles are not returned by :checked in every
     * browser scenario, therefore explicitly include them.
     */
    const roleIds=new Set();

    document
        .querySelectorAll('.user-role-checkbox')
        .forEach(input=>{

            if(input.checked||input.disabled){
                roleIds.add(
                    Number(input.value)
                );
            }

        });

    const ids=Array.from(roleIds);

    if(!ids.length){

        AdminUI.showError(
            'roleError',
            'At least one role is required.'
        );

        return;
    }

    AdminUI.setLoading(
        el.saveRolesButton,
        'Saving...'
    );

    try{

        await api(
            `/api/user-roles/${selectedUser.id}`,
            {
                method:'PUT',
                body:JSON.stringify({
                    role_ids:ids
                })
            }
        );

        closeRoleModal();

        Toast.success(
            'User roles updated successfully.'
        );

        await loadUsers(currentPage);

    }catch(error){

        AdminUI.showError(
            'roleError',
            AdminUI.extractError(error)
        );

    }finally{

        AdminUI.resetLoading(
            el.saveRolesButton
        );
    }
};

/*
|--------------------------------------------------------------------------
| Clear Search
|--------------------------------------------------------------------------
*/

window.clearSearch=function(){

    el.search.value='';

    loadUsers(1);
};

/*
|--------------------------------------------------------------------------
| Member Status Badge
|--------------------------------------------------------------------------
*/

function memberStatusBadge(status){

    const map={
        active:'bg-emerald-50 text-emerald-700',
        pending:'bg-amber-50 text-amber-700',
        suspended:'bg-red-50 text-red-700',
        rejected:'bg-red-50 text-red-700',
        inactive:'bg-slate-100 text-slate-500'
    };

    return`
        <span class="rounded-md px-2 py-1 text-[10px] font-semibold ${map[status]??map.inactive}">
            ${AdminUI.escapeHtml(
                AdminUI.titleCase(status??'unknown')
            )}
        </span>
    `;
}

/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initUserRolesPage(){

    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initUserRolesPage,
            50
        );

        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadUsers(1)
        )
    );

    await loadUsers();
}

if(document.readyState==='loading'){

    document.addEventListener(
        'DOMContentLoaded',
        initUserRolesPage
    );

}else{

    initUserRolesPage();
}
</script>

@endsection