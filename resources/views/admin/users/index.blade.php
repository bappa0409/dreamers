@extends('layouts.admin')

@section('title','User Accounts')
@section('page_title','User Accounts')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">User Accounts</h1>
                <p class="text-sm text-slate-500">Manage login accounts, account status and password resets.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('User.create'))
            <button type="button" onclick="openUserModal()" class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white  hover:bg-indigo-700">
                <i class="bi bi-plus-lg text-[11px]"></i>
                Add User
            </button>
        @endif
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
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search name, email, mobile or member code</p>
                </div>
            </div>

            <div class="flex w-full items-center sm:w-auto">
                <div class="relative w-full sm:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input id="searchInput" type="text" placeholder="Search users..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 items-center justify-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1000px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">User</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Member</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Roles</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Language</th>
                        <th class="px-6 py-4 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-6 py-4 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="usersTable">
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">Loading users...</td>
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
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-person"></i>
                </div>

                <div>
                    <h2 id="userModalTitle" class="text-lg font-bold text-slate-800">Add User</h2>
                    <p class="text-sm text-slate-500">Manage user account information.</p>
                </div>
            </div>

            <button type="button" onclick="closeUserModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="userForm">
            <input type="hidden" id="userId">

            <div class="max-h-[70vh] space-y-5 overflow-y-auto p-5 sm:p-6">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Name <span class="text-red-500">*</span></label>
                        <input id="name" type="text" class="app-input">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
                        <input id="email" type="email" class="app-input">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mobile</label>
                        <input id="mobile" type="text" class="app-input">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Language</label>
                        <select id="language" class="app-input">
                            <option value="en">English</option>
                            <option value="bn">বাংলা</option>
                        </select>
                    </div>

                    <div id="passwordField">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Password <span class="text-red-500">*</span></label>
                        <input id="password" type="password" class="app-input" minlength="8">
                    </div>

                    <div id="passwordConfirmationField">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" type="password" class="app-input" minlength="8">
                    </div>

                </div>

                <div id="userFormError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" onclick="closeUserModal()" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveUserButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
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
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-amber-500 text-white">
                    <i class="bi bi-key"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Reset Password</h2>
                    <p id="passwordUserName" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closePasswordModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="passwordForm">
            <div class="space-y-4 p-5">

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">New Password</label>
                    <input id="newPassword" type="password" class="app-input" minlength="8">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Confirm Password</label>
                    <input id="newPasswordConfirmation" type="password" class="app-input" minlength="8">
                </div>

                <div id="passwordError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="closePasswordModal()" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="resetPasswordButton" type="submit" class="rounded-md bg-amber-500 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-600 disabled:opacity-60">
                    Reset Password
                </button>
            </div>
        </form>

    </div>
</div>

<script>
let users=[];
let editingUser=null;
let passwordUser=null;
let currentPage=1;
let lastPage=1;
let searchTimer=null;

const canEditUser=@json(auth()->user()->hasPermission('User.update'));
const canDeleteUser=@json(auth()->user()->hasPermission('User.delete'));

async function loadUsers(page=1){
    currentPage=page;

    const tbody=document.getElementById('usersTable');

    tbody.innerHTML=`
        <tr>
            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                Loading users...
            </td>
        </tr>
    `;

    try{
        const search=document.getElementById('searchInput').value.trim();
        const status=document.getElementById('statusFilter').value;
        const params=new URLSearchParams();

        if(search)params.set('search',search);
        if(status)params.set('status',status);
        params.set('page',page);

        const response=await api(`/api/users?${params.toString()}`);
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
                <td colspan="6" class="px-6 py-12 text-center text-red-500">
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
                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                    No users found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=users.map(user=>{
        const member=user.member??null;
        const roles=user.roles??[];

        const editButton=canEditUser?`
            <button onclick="editUser(${user.id})" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">
                <i class="bi bi-pencil-square text-[10px]"></i>
                Edit
            </button>
        `:'';

        const passwordButton=canEditUser?`
            <button onclick="openPasswordModal(${user.id})" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700 hover:bg-amber-100">
                <i class="bi bi-key text-[10px]"></i>
                Password
            </button>
        `:'';

        const statusButton=canEditUser?`
            <button onclick="toggleUserStatus(${user.id})" class="inline-flex items-center gap-1 rounded-md ${user.is_active?'bg-red-50 text-red-600 hover:bg-red-100':'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'} px-2.5 py-1.5 text-[11px] font-semibold">
                <i class="bi ${user.is_active?'bi-person-x':'bi-person-check'} text-[10px]"></i>
                ${user.is_active?'Deactivate':'Activate'}
            </button>
        `:'';

        const deleteButton=canDeleteUser&&!member?`
            <button onclick="deleteUser(${user.id})" class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                <i class="bi bi-trash text-[10px]"></i>
                Delete
            </button>
        `:'';

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
                    <span class="text-xs font-medium text-slate-600">
                        ${user.language==='bn'?'বাংলা':'English'}
                    </span>
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

                <td class="px-6 py-4">
                    <div class="flex flex-wrap justify-end gap-1.5">
                        ${editButton}
                        ${passwordButton}
                        ${statusButton}
                        ${deleteButton}
                    </div>
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

function openUserModal(user=null){
    editingUser=user;

    document.getElementById('userForm').reset();
    document.getElementById('userFormError').classList.add('hidden');

    const modal=document.getElementById('userModal');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add('overflow-hidden');

    const passwordField=document.getElementById('passwordField');
    const passwordConfirmationField=document.getElementById('passwordConfirmationField');

    if(user){
        document.getElementById('userModalTitle').innerText='Edit User';
        document.getElementById('saveUserButton').innerText='Update User';

        document.getElementById('userId').value=user.id;
        document.getElementById('name').value=user.name??'';
        document.getElementById('email').value=user.email??'';
        document.getElementById('mobile').value=user.mobile??'';
        document.getElementById('language').value=user.language??'en';

        passwordField.classList.add('hidden');
        passwordConfirmationField.classList.add('hidden');

    }else{
        document.getElementById('userModalTitle').innerText='Add User';
        document.getElementById('saveUserButton').innerText='Save User';

        passwordField.classList.remove('hidden');
        passwordConfirmationField.classList.remove('hidden');

        document.getElementById('language').value='en';
    }
}

function closeUserModal(){
    document.getElementById('userModal').classList.add('hidden');
    document.getElementById('userModal').classList.remove('flex');

    document.body.classList.remove('overflow-hidden');
    editingUser=null;
}

function editUser(id){
    const user=users.find(item=>Number(item.id)===Number(id));

    if(!user)return;

    openUserModal(user);
}

document.getElementById('userForm').addEventListener('submit',async function(e){
    e.preventDefault();

    const button=document.getElementById('saveUserButton');

    const data={
        name:document.getElementById('name').value.trim(),
        email:document.getElementById('email').value.trim(),
        mobile:document.getElementById('mobile').value.trim()||null,
        language:document.getElementById('language').value||'en'
    };

    if(!editingUser){
        data.password=document.getElementById('password').value;
        data.password_confirmation=document.getElementById('password_confirmation').value;
    }

    button.disabled=true;
    button.innerText=editingUser?'Updating...':'Saving...';

    try{
        if(editingUser){
            await api(`/api/users/${editingUser.id}`,{
                method:'PUT',
                body:JSON.stringify(data)
            });
        }else{
            await api('/api/users',{
                method:'POST',
                body:JSON.stringify(data)
            });
        }

        closeUserModal();
        await loadUsers(currentPage);

    }catch(error){
        showUserError(extractError(error));

    }finally{
        button.disabled=false;
        button.innerText=editingUser?'Update User':'Save User';
    }
});

function openPasswordModal(id){
    passwordUser=users.find(item=>Number(item.id)===Number(id));

    if(!passwordUser)return;

    document.getElementById('passwordForm').reset();
    document.getElementById('passwordError').classList.add('hidden');

    document.getElementById('passwordUserName').innerText=passwordUser.name??'';

    const modal=document.getElementById('passwordModal');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add('overflow-hidden');
}

function closePasswordModal(){
    document.getElementById('passwordModal').classList.add('hidden');
    document.getElementById('passwordModal').classList.remove('flex');

    document.body.classList.remove('overflow-hidden');
    passwordUser=null;
}

document.getElementById('passwordForm').addEventListener('submit',async function(e){
    e.preventDefault();

    if(!passwordUser)return;

    const password=document.getElementById('newPassword').value;
    const confirmation=document.getElementById('newPasswordConfirmation').value;

    if(password.length<8){
        showPasswordError('Password must contain at least 8 characters.');
        return;
    }

    if(password!==confirmation){
        showPasswordError('Password confirmation does not match.');
        return;
    }

    const button=document.getElementById('resetPasswordButton');

    button.disabled=true;
    button.innerText='Resetting...';

    try{
        await api(`/api/users/${passwordUser.id}/reset-password`,{
            method:'POST',
            body:JSON.stringify({
                password,
                password_confirmation:confirmation
            })
        });

        closePasswordModal();

    }catch(error){
        showPasswordError(extractError(error));

    }finally{
        button.disabled=false;
        button.innerText='Reset Password';
    }
});

async function toggleUserStatus(id){
    if(!confirm('Change this user account status?'))return;

    try{
        await api(`/api/users/${id}/toggle-status`,{
            method:'PATCH',
            body:JSON.stringify({})
        });

        await loadUsers(currentPage);

    }catch(error){
        alert(extractError(error));
    }
}

async function deleteUser(id){
    if(!confirm('Delete this user account permanently?'))return;

    try{
        await api(`/api/users/${id}`,{
            method:'DELETE'
        });

        await loadUsers(currentPage);

    }catch(error){
        alert(extractError(error));
    }
}

function clearFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('statusFilter').value='';

    loadUsers(1);
}

function showUserError(message){
    const box=document.getElementById('userFormError');
    box.innerText=message;
    box.classList.remove('hidden');
}

function showPasswordError(message){
    const box=document.getElementById('passwordError');
    box.innerText=message;
    box.classList.remove('hidden');
}

function extractError(error){
    if(error.data?.errors){
        const errors=Object.values(error.data.errors).flat();

        if(errors.length)return errors.join(' ');
    }

    return error.data?.message||error.message||'Something went wrong.';
}

function memberStatusBadge(status){
    const classes={
        active:'bg-emerald-50 text-emerald-700',
        pending:'bg-amber-50 text-amber-700',
        suspended:'bg-red-50 text-red-700',
        rejected:'bg-red-50 text-red-700',
        inactive:'bg-slate-100 text-slate-500'
    };

    const label=status
        ?status.charAt(0).toUpperCase()+status.slice(1)
        :'Unknown';

    return `
        <span class="rounded-full px-2 py-1 text-[10px] font-semibold ${classes[status]??'bg-slate-100 text-slate-500'}">
            ${escapeHtml(label)}
        </span>
    `;
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

    searchTimer=setTimeout(()=>{
        loadUsers(1);
    },350);
});

document.getElementById('statusFilter').addEventListener('change',()=>{
    loadUsers(1);
});

document.addEventListener('DOMContentLoaded',function(){
    loadUsers();
});
</script>

@endsection