@extends('layouts.admin')

@section('title','User Accounts')
@section('page_title','User Accounts')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">User Accounts</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage login accounts, account status and password resets.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('User.create'))
            <button
                type="button"
                onclick="openUserModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-plus-lg text-[11px]"></i>
                Add User
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
                    <p class="text-sm font-semibold text-slate-700">Search Users</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by name, email, mobile or member code</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search users..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3  text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >
                </div>

                <select
                    id="statusFilter"
                    class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400"
                >
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
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
                        <th class="w-[25%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">User</th>
                        <th class="w-[14%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Member</th>
                        <th class="w-[20%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Roles</th>
                        <th class="w-[10%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Language</th>
                        <th class="w-[11%] px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="w-[20%] px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="usersTable">
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-base text-slate-400">
                            Loading users...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- User Modal --}}
<div id="userModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-2xl overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person"></i>
                </div>

                <div>
                    <h2 id="userModalTitle" class="text-sm font-semibold text-slate-800">Add User</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Manage user account information.</p>
                </div>
            </div>

            <button type="button" onclick="closeUserModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="userForm" novalidate data-js-validation="1">
            <input type="hidden" id="userId">

            <div class="max-h-[70vh] space-y-4 overflow-y-auto p-5 sm:p-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Name <span class="text-red-500">*</span></label>
                        <input id="name" type="text" maxlength="255" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Email <span class="text-red-500">*</span></label>
                        <input id="email" type="email" maxlength="255" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Mobile</label>
                        <input id="mobile" type="text" maxlength="30" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Language</label>
                        <select id="language" class="app-input cursor-pointer">
                            <option value="en">English</option>
                            <option value="bn">বাংলা</option>
                        </select>
                    </div>

                    <div id="passwordField">
                        <label class="form-label">Password <span class="text-red-500">*</span></label>
                        <input id="password" type="password" minlength="8" class="app-input">
                    </div>

                    <div id="passwordConfirmationField">
                        <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" type="password" minlength="8" class="app-input">
                    </div>
                </div>

                <div id="userFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
                <button
                    type="button"
                    onclick="closeUserModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Close
                </button>

                <button
                    id="saveUserButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                >
                    Save User
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reset Password Modal --}}
<div id="passwordModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-md overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                    <i class="bi bi-key"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Reset Password</h2>
                    <p id="passwordUserName" class=" text-xs 2xl:text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closePasswordModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="passwordForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <div>
                    <label class="form-label">New Password</label>
                    <input id="newPassword" type="password" minlength="8" class="app-input">
                </div>

                <div>
                    <label class="form-label">Confirm Password</label>
                    <input id="newPasswordConfirmation" type="password" minlength="8" class="app-input">
                </div>

                <div id="passwordError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button
                    type="button"
                    onclick="closePasswordModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="resetPasswordButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
                >
                    Reset Password
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
let users=[];
let editingUser=null;
let passwordUser=null;
let currentPage=1;
let lastPage=1;
let total=0;

const canEditUser=@json(auth()->user()->hasPermission('User.update'));
const canDeleteUser=@json(auth()->user()->hasPermission('User.delete'));

const el={
    table:document.getElementById('usersTable'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('userForm'),
    saveButton:document.getElementById('saveUserButton'),
    passwordForm:document.getElementById('passwordForm'),
    resetPasswordButton:document.getElementById('resetPasswordButton')
};

async function loadUsers(page=1){
    currentPage=page;
    el.table.innerHTML=AdminUI.loadingState('Loading users...',6);

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value,
        page
    });

    try{
        const response=await api(`/api/users?${query}`);
        const paginator=response.data??{};

        users=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??users.length);

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
            6
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

function renderUsers(){
    if(!users.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No users found.',
            6
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
                        class="truncate  text-xs 2xl:text-sm font-semibold text-slate-800"
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
                                    No profile
                                </span>
                            `
                    }
                </td>

                <td class="min-w-0 px-4 py-3">
                    ${
                        roles.length
                            ?`
                                <div class="flex max-h-14 flex-wrap gap-1 overflow-hidden">
                                    ${roles.slice(0,3).map(role=>`
                                        <span
                                            class="max-w-full truncate rounded-md bg-indigo-50 px-2 py-1 text-[10px] font-semibold text-indigo-700"
                                            title="${AdminUI.escapeHtml(role.display_name||role.name||'')}"
                                        >
                                            ${AdminUI.escapeHtml(role.display_name||role.name||'')}
                                        </span>
                                    `).join('')}

                                    ${
                                        roles.length>3
                                            ?`
                                                <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600">
                                                    +${roles.length-3}
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
                    <span class="text-[11px] font-medium text-slate-600">
                        ${user.language==='bn'?'বাংলা':'English'}
                    </span>
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

                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-1">
                        ${
                            canEditUser
                                ?`
                                    <button
                                        type="button"
                                        onclick="editUser(${user.id})"
                                        title="Edit User"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                    >
                                        <i class="bi bi-pencil-square text-sm"></i>
                                    </button>

                                    <button
                                        type="button"
                                        onclick="openPasswordModal(${user.id})"
                                        title="Reset Password"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-700 transition hover:bg-amber-100"
                                    >
                                        <i class="bi bi-key text-sm"></i>
                                    </button>

                                    <button
                                        type="button"
                                        onclick="toggleUserStatus(${user.id})"
                                        title="${user.is_active?'Deactivate':'Activate'} User"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md ${
                                            user.is_active
                                                ?'bg-red-50 text-red-600 hover:bg-red-100'
                                                :'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                                        } transition"
                                    >
                                        <i class="bi ${user.is_active?'bi-person-x':'bi-person-check'} text-sm"></i>
                                    </button>
                                `
                                :''
                        }

                        ${
                            canDeleteUser&&!member
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteUser(${user.id})"
                                        title="Delete User"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                    >
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                `
                                :''
                        }
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

window.openUserModal=function(user=null){
    editingUser=user;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('userFormError');

    const passwordField=document.getElementById('passwordField');
    const passwordConfirmationField=document.getElementById('passwordConfirmationField');

    document.getElementById('userModalTitle').innerText=
        user?'Edit User':'Add User';

    el.saveButton.innerText=
        user?'Update User':'Save User';

    if(user){
        document.getElementById('userId').value=user.id;
        document.getElementById('name').value=user.name??'';
        document.getElementById('email').value=user.email??'';
        document.getElementById('mobile').value=user.mobile??'';
        document.getElementById('language').value=user.language??'en';

        passwordField.classList.add('hidden');
        passwordConfirmationField.classList.add('hidden');
    }else{
        document.getElementById('language').value='en';

        passwordField.classList.remove('hidden');
        passwordConfirmationField.classList.remove('hidden');
    }

    AdminUI.openModal('userModal');
};

window.closeUserModal=function(){
    AdminUI.closeModal('userModal');
    editingUser=null;
};

window.editUser=function(id){
    const user=users.find(
        item=>Number(item.id)===Number(id)
    );

    if(!user){
        Toast.error('User not found.');
        return;
    }

    openUserModal(user);
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('userFormError');

    const name=document.getElementById('name').value.trim();
    const email=document.getElementById('email').value.trim();

    if(!name){
        AdminUI.showError(
            'userFormError',
            'Name is required.'
        );
        return;
    }

    if(!email){
        AdminUI.showError(
            'userFormError',
            'Email is required.'
        );
        return;
    }

    const data={
        name,
        email,
        mobile:document.getElementById('mobile').value.trim()||null,
        language:document.getElementById('language').value||'en'
    };

    if(!editingUser){
        const password=document.getElementById('password').value;
        const confirmation=document.getElementById('password_confirmation').value;

        if(password.length<8){
            AdminUI.showError(
                'userFormError',
                'Password must contain at least 8 characters.'
            );
            return;
        }

        if(password!==confirmation){
            AdminUI.showError(
                'userFormError',
                'Password confirmation does not match.'
            );
            return;
        }

        data.password=password;
        data.password_confirmation=confirmation;
    }

    AdminUI.setLoading(
        el.saveButton,
        editingUser?'Updating...':'Saving...'
    );

    try{
        const editing=Boolean(editingUser);

        await api(
            editing
                ?`/api/users/${editingUser.id}`
                :'/api/users',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closeUserModal();

        Toast.success(
            editing
                ?'User updated successfully.'
                :'User created successfully.'
        );

        await loadUsers(
            editing?currentPage:1
        );
    }catch(error){
        AdminUI.showError(
            'userFormError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

window.openPasswordModal=function(id){
    passwordUser=users.find(
        item=>Number(item.id)===Number(id)
    );

    if(!passwordUser){
        Toast.error('User not found.');
        return;
    }

    AdminUI.resetForm(el.passwordForm);
    AdminUI.clearError('passwordError');

    document.getElementById('passwordUserName').innerText=
        passwordUser.name??'';

    AdminUI.openModal('passwordModal');
};

window.closePasswordModal=function(){
    AdminUI.closeModal('passwordModal');
    passwordUser=null;
};

el.passwordForm.addEventListener('submit',async event=>{
    event.preventDefault();

    if(!passwordUser)return;

    AdminUI.clearError('passwordError');

    const password=document.getElementById('newPassword').value;
    const confirmation=document.getElementById('newPasswordConfirmation').value;

    if(password.length<8){
        AdminUI.showError(
            'passwordError',
            'Password must contain at least 8 characters.'
        );
        return;
    }

    if(password!==confirmation){
        AdminUI.showError(
            'passwordError',
            'Password confirmation does not match.'
        );
        return;
    }

    AdminUI.setLoading(
        el.resetPasswordButton,
        'Resetting...'
    );

    try{
        await api(
            `/api/users/${passwordUser.id}/reset-password`,
            {
                method:'POST',
                body:JSON.stringify({
                    password,
                    password_confirmation:confirmation
                })
            }
        );

        closePasswordModal();

        Toast.success(
            'Password reset successfully.'
        );
    }catch(error){
        AdminUI.showError(
            'passwordError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(
            el.resetPasswordButton
        );
    }
});

window.toggleUserStatus=function(id){
    const user=users.find(
        item=>Number(item.id)===Number(id)
    );

    if(!user){
        Toast.error('User not found.');
        return;
    }

    const action=user.is_active
        ?'Deactivate'
        :'Activate';

    AdminUI.confirm({
        title:`${action} User?`,
        message:user.is_active
            ?'This user will no longer be able to access the system until reactivated.'
            :'This user will be allowed to access the system again.',
        confirmText:action,
        confirmClass:user.is_active
            ?'bg-red-600 hover:bg-red-700'
            :'bg-emerald-600 hover:bg-emerald-700',
        onConfirm:async()=>{
            try{
                await api(
                    `/api/users/${id}/toggle-status`,
                    {
                        method:'PATCH',
                        body:JSON.stringify({})
                    }
                );

                Toast.success(
                    user.is_active
                        ?'User deactivated successfully.'
                        :'User activated successfully.'
                );

                await loadUsers(currentPage);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(error)
                );
            }
        }
    });
};

window.deleteUser=function(id){
    const user=users.find(
        item=>Number(item.id)===Number(id)
    );

    if(!user){
        Toast.error('User not found.');
        return;
    }

    AdminUI.deleteRequest(
        `/api/users/${id}`,
        {
            title:'Delete User?',
            message:'This user account will be permanently deleted. This action cannot be undone.',
            confirmText:'Delete',
            successMessage:'User deleted successfully.',
            onSuccess:async()=>{
                if(users.length===1&&currentPage>1){
                    currentPage--;
                }

                await loadUsers(currentPage);
            }
        }
    );
};

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';
    loadUsers(1);
};

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
            ${AdminUI.escapeHtml(AdminUI.titleCase(status??'unknown'))}
        </span>
    `;
}

async function initUsersPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initUsersPage,50);
        return;
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadUsers(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadUsers(1)
    );

    await loadUsers();
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initUsersPage
    );
}else{
    initUsersPage();
}
</script>
@endsection