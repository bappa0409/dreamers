@extends('layouts.admin')

@section('title','Membership Management')
@section('page_title','Membership Management')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5  md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people text-base"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-800">Membership Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage association members, status and assigned roles.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Member.create'))
            <button type="button" onclick="openMemberModal()" class="inline-flex w-fit items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white  hover:bg-indigo-700">
                <i class="bi bi-person-plus text-[12px]"></i>
                Add Member
            </button>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4 ">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Total Members</p>
                    <p id="totalMembers" class="mt-2 text-2xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-emerald-700">Active</p>
                    <p id="activeMembers" class="mt-2 text-2xl font-bold text-emerald-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-person-check"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-amber-700">Pending</p>
                    <p id="pendingMembers" class="mt-2 text-2xl font-bold text-amber-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-red-700">Suspended</p>
                    <p id="suspendedMembers" class="mt-2 text-2xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">Rejected / Inactive</p>
                    <p id="inactiveMembers" class="mt-2 text-2xl font-bold text-slate-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-200 text-slate-600">
                    <i class="bi bi-person-x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Search --}}
    <div class="rounded-md border border-slate-200 bg-white p-3 ">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Members</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by name, email, mobile or member code</p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-80">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input id="searchInput" type="text" placeholder="Search members..." class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select id="statusFilter" class="h-9 border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white ">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Member</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Member Code</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Phone</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Roles</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Joining Date</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-5 py-3.5 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="membersTable">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-500">Loading members...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Member Modal --}}
<div id="memberModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-5 sm:px-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-indigo-600 text-white">
                    <i class="bi bi-person-plus"></i>
                </div>

                <div>
                    <h2 id="modalTitle" class="text-lg font-bold text-slate-800">Add Member</h2>
                    <p id="modalDescription" class="mt-1 text-sm text-slate-500">Create member profile and login account.</p>
                </div>
            </div>

            <button type="button" onclick="closeMemberModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="memberForm" class="flex min-h-0 flex-1 flex-col">
            <input type="hidden" id="memberId">

            <div class="space-y-5 overflow-y-auto p-5 sm:p-6">

                {{-- Account --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-person"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Account Information</h3>
                            <p class="text-[11px] text-slate-400">Login account for this association member.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Name <span class="text-red-500">*</span></label>
                            <input id="name" type="text" class="app-input" maxlength="150">
                        </div>

                        <div>
                            <label class="form-label">Email <span class="text-red-500">*</span></label>
                            <input id="email" type="email" class="app-input" maxlength="255">
                        </div>

                        <div>
                            <label class="form-label">Mobile</label>
                            <input id="mobile" type="text" class="app-input" maxlength="20" placeholder="01XXXXXXXXX">
                        </div>

                        <div>
                            <label class="form-label">Language</label>
                            <select id="language" class="app-input">
                                <option value="en">English</option>
                                <option value="bn">বাংলা</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Member --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Member Information</h3>
                            <p class="text-[11px] text-slate-400">Additional association membership details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Phone</label>
                            <input id="phone" type="text" class="app-input" maxlength="30">
                        </div>

                        <div>
                            <label class="form-label">Alternate Phone</label>
                            <input id="alternate_phone" type="text" class="app-input" maxlength="30">
                        </div>

                        <div>
                            <label class="form-label">Date of Birth</label>
                            <input id="date_of_birth" type="date" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Gender</label>
                            <select id="gender" class="app-input">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">City</label>
                            <input id="city" type="text" class="app-input" maxlength="100">
                        </div>

                        <div>
                            <label class="form-label">District</label>
                            <input id="district" type="text" class="app-input" maxlength="100">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Address</label>
                        <textarea id="address" rows="3" class="app-input resize-none"></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Notes</label>
                        <textarea id="notes" rows="3" class="app-input resize-none"></textarea>
                    </div>
                </section>

                <div id="approvalInfo" class="hidden rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <i class="bi bi-shield-check text-amber-600"></i>

                        <div>
                            <p class="text-sm font-semibold text-amber-800">Approval Required</p>
                            <p class="mt-1 text-xs text-amber-700">
                                Member will remain inactive until approval. After approval, a password setup link can be sent to the member.
                            </p>
                        </div>
                    </div>
                </div>

                <div id="formError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
                <button type="button" onclick="closeMemberModal()" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    Create Member
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Role Modal --}}
<div id="roleModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-xl overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-violet-600 text-white">
                    <i class="bi bi-shield-lock"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Manage Roles</h2>
                    <p id="roleMemberName" class="mt-1 text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeRoleModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="max-h-[70vh] space-y-5 overflow-y-auto p-5">
            <div>
                <label class="form-label">Assign New Role</label>

                <div class="flex items-center">
                    <select id="roleSelect" class="h-9 min-w-0 flex-1 rounded-l-md border border-r-0 border-slate-300 bg-white px-3 text-sm outline-none focus:border-indigo-400">
                        <option value="">Select Role</option>
                    </select>

                    <button id="assignRoleButton" type="button" onclick="assignSelectedRole()" class="h-9 rounded-r-md bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                        Assign
                    </button>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Assigned Roles</h3>
                <div id="assignedRoles" class="space-y-2">
                    <div class="text-sm text-slate-500">Loading...</div>
                </div>
            </div>

            <div id="roleError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
        </div>

        <div class="flex justify-end border-t border-slate-200 px-5 py-4">
            <button type="button" onclick="closeRoleModal()" class="rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

<style>
.form-label{
    display:block;
    margin-bottom:.45rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

<script>
let members=[];
let memberSummary=null;
let editingMember=null;
let roleMemberId=null;
let assignedRoles=[];
let availableRoles=[];
let currentPage=1;
let lastPage=1;
let searchTimer=null;

const canEditMember=@json(
    auth()->user()->hasPermission('Member.update') ||
    auth()->user()->hasPermission('Member.edit')
);

const canDeleteMember=@json(
    auth()->user()->hasPermission('Member.delete')
);

const canManageRoles=@json(
    auth()->user()->hasPermission('Role.update') ||
    auth()->user()->hasPermission('Role.edit')
);

async function loadMembers(page=1){
    currentPage=page;

    const tbody=document.getElementById('membersTable');

    tbody.innerHTML=`
        <tr>
            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                Loading members...
            </td>
        </tr>
    `;

    try{
        const params=new URLSearchParams();
        const search=document.getElementById('searchInput').value.trim();
        const status=document.getElementById('statusFilter').value;

        if(search)params.set('search',search);
        if(status)params.set('status',status);
        params.set('page',page);

        const response=await api(`/api/members?${params.toString()}`);
        const paginator=response.data??{};

        members=Array.isArray(paginator.data)
            ?paginator.data
            :(Array.isArray(response.data)?response.data:[]);

        currentPage=paginator.current_page??1;
        lastPage=paginator.last_page??1;
        memberSummary=response.summary??null;

        renderMembers();
        renderPagination();
        updateStats();

    }catch(error){
        console.error(error);

        tbody.innerHTML=`
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-red-500">
                    Failed to load members.
                </td>
            </tr>
        `;
    }
}

function renderMembers(){
    const tbody=document.getElementById('membersTable');

    if(!members.length){
        tbody.innerHTML=`
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                    No members found.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML=members.map(member=>{
        const user=member.user??{};
        const roles=user.roles??[];

        const roleButton=canManageRoles?`
            <button onclick="openRoleModal(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-violet-50 px-2.5 py-1.5 text-[11px] font-semibold text-violet-600 hover:bg-violet-100">
                <i class="bi bi-shield-lock text-[10px]"></i>
                Roles
            </button>
        `:'';

        const editButton=canEditMember?`
            <button onclick="editMember(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">
                <i class="bi bi-pencil-square text-[10px]"></i>
                Edit
            </button>
        `:'';

        const setupButton=canEditMember&&member.status==='active'?`
            <button onclick="sendPasswordSetup(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-2.5 py-1.5 text-[11px] font-semibold text-sky-700 hover:bg-sky-100">
                <i class="bi bi-envelope text-[10px]"></i>
                Setup Link
            </button>
        `:'';

        const suspendButton=canEditMember&&member.status==='active'?`
            <button onclick="suspendMember(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1.5 text-[11px] font-semibold text-amber-700 hover:bg-amber-100">
                <i class="bi bi-pause-circle text-[10px]"></i>
                Suspend
            </button>
        `:'';

        const activateButton=canEditMember&&['inactive','suspended'].includes(member.status)?`
            <button onclick="activateMember(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-100">
                <i class="bi bi-check-circle text-[10px]"></i>
                Activate
            </button>
        `:'';

        const deleteButton=canDeleteMember&&member.status!=='active'?`
            <button onclick="deleteMember(${member.id})" class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                <i class="bi bi-trash text-[10px]"></i>
                Delete
            </button>
        `:'';

        return `
            <tr class="border-b border-slate-100 hover:bg-slate-50">
                <td class="px-5 py-4">
                    <div class="font-semibold text-slate-800">
                        ${escapeHtml(user.name??'N/A')}
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        ${escapeHtml(user.email??'')}
                    </div>
                </td>

                <td class="px-5 py-4">
                    <span class="font-mono text-xs font-semibold text-indigo-600">
                        ${escapeHtml(member.member_code??'N/A')}
                    </span>
                </td>

                <td class="px-5 py-4 text-xs text-slate-600">
                    ${escapeHtml(member.phone||user.mobile||'N/A')}
                </td>

                <td class="px-5 py-4">
                    <div class="flex max-w-[230px] flex-wrap gap-1">
                        ${
                            roles.length
                                ?roles.map(role=>`
                                    <span class="rounded-md bg-violet-50 px-2 py-1 text-[10px] font-semibold text-violet-700">
                                        ${escapeHtml(role.display_name||role.name)}
                                    </span>
                                `).join('')
                                :'<span class="text-xs text-slate-400">No roles</span>'
                        }
                    </div>
                </td>

                <td class="px-5 py-4 text-xs text-slate-600">
                    ${formatDate(member.joining_date)}
                </td>

                <td class="px-5 py-4">
                    ${statusBadge(member.status)}
                </td>

                <td class="px-5 py-4">
                    <div class="flex flex-wrap justify-end gap-1.5">
                        ${roleButton}
                        ${editButton}
                        ${setupButton}
                        ${suspendButton}
                        ${activateButton}
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
            <span class="text-xs text-slate-500">
                Page ${currentPage} of ${lastPage}
            </span>

            <div class="flex gap-2">
                <button ${currentPage<=1?'disabled':''} onclick="loadMembers(${currentPage-1})" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40">
                    Previous
                </button>

                <button ${currentPage>=lastPage?'disabled':''} onclick="loadMembers(${currentPage+1})" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-40">
                    Next
                </button>
            </div>
        </div>
    `;
}

function openMemberModal(member=null){
    editingMember=member;

    const modal=document.getElementById('memberModal');
    const form=document.getElementById('memberForm');

    form.reset();

    document.getElementById('formError').classList.add('hidden');
    document.getElementById('memberId').value=member?.id??'';

    const name=document.getElementById('name');
    const email=document.getElementById('email');
    const mobile=document.getElementById('mobile');
    const language=document.getElementById('language');

    if(member){
        document.getElementById('modalTitle').innerText='Edit Member';
        document.getElementById('modalDescription').innerText='Update member profile information.';
        document.getElementById('saveButton').innerText='Update Member';
        document.getElementById('approvalInfo').classList.add('hidden');

        name.disabled=true;
        email.disabled=true;
        mobile.disabled=true;
        language.disabled=true;

        fillMemberForm(member);
    }else{
        document.getElementById('modalTitle').innerText='Add Member';
        document.getElementById('modalDescription').innerText='Create member profile and login account.';
        document.getElementById('saveButton').innerText='Create Member';
        document.getElementById('approvalInfo').classList.remove('hidden');

        name.disabled=false;
        email.disabled=false;
        mobile.disabled=false;
        language.disabled=false;
        language.value='en';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeMemberModal(){
    const modal=document.getElementById('memberModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');

    document.getElementById('memberForm').reset();
    editingMember=null;
}

function fillMemberForm(member){
    const user=member.user??{};

    document.getElementById('name').value=user.name??'';
    document.getElementById('email').value=user.email??'';
    document.getElementById('mobile').value=user.mobile??'';
    document.getElementById('language').value=user.language??'en';
    document.getElementById('phone').value=member.phone??'';
    document.getElementById('alternate_phone').value=member.alternate_phone??'';
    document.getElementById('date_of_birth').value=member.date_of_birth??'';
    document.getElementById('gender').value=member.gender??'';
    document.getElementById('city').value=member.city??'';
    document.getElementById('district').value=member.district??'';
    document.getElementById('address').value=member.address??'';
    document.getElementById('notes').value=member.notes??'';
}

document.getElementById('memberForm').addEventListener('submit',async function(e){
    e.preventDefault();

    const button=document.getElementById('saveButton');
    const errorBox=document.getElementById('formError');

    errorBox.classList.add('hidden');
    button.disabled=true;
    button.innerText=editingMember?'Updating...':'Creating...';

    const profile={
        phone:document.getElementById('phone').value.trim()||null,
        alternate_phone:document.getElementById('alternate_phone').value.trim()||null,
        date_of_birth:document.getElementById('date_of_birth').value||null,
        gender:document.getElementById('gender').value||null,
        city:document.getElementById('city').value.trim()||null,
        district:document.getElementById('district').value.trim()||null,
        address:document.getElementById('address').value.trim()||null,
        notes:document.getElementById('notes').value.trim()||null
    };

    try{
        if(editingMember){
            await api(`/api/members/${editingMember.id}`,{
                method:'PUT',
                body:JSON.stringify(profile)
            });
        }else{
            const data={
                name:document.getElementById('name').value.trim(),
                email:document.getElementById('email').value.trim(),
                mobile:document.getElementById('mobile').value.trim()||null,
                language:document.getElementById('language').value||'en',
                ...profile
            };

            if(!data.name)throw new Error('Name is required.');
            if(!data.email)throw new Error('Email is required.');

            await api('/api/members',{
                method:'POST',
                body:JSON.stringify(data)
            });
        }

        closeMemberModal();
        await loadMembers(currentPage);

    }catch(error){
        errorBox.innerText=extractError(error);
        errorBox.classList.remove('hidden');

    }finally{
        button.disabled=false;
        button.innerText=editingMember?'Update Member':'Create Member';
    }
});

function editMember(id){
    const member=members.find(item=>Number(item.id)===Number(id));
    if(member)openMemberModal(member);
}

async function sendPasswordSetup(id){
    if(!confirm('Send a new password setup link to this member?'))return;

    try{
        await api(`/api/members/${id}/send-password-setup`,{
            method:'POST',
            body:JSON.stringify({})
        });

        alert('Password setup link sent successfully.');

    }catch(error){
        alert(extractError(error));
    }
}

async function suspendMember(id){
    if(!confirm('Suspend this member?'))return;

    try{
        await api(`/api/members/${id}/suspend`,{
            method:'POST',
            body:JSON.stringify({})
        });

        await loadMembers(currentPage);

    }catch(error){
        alert(extractError(error));
    }
}

async function activateMember(id){
    if(!confirm('Activate this member?'))return;

    try{
        await api(`/api/members/${id}/activate`,{
            method:'POST',
            body:JSON.stringify({})
        });

        await loadMembers(currentPage);

    }catch(error){
        alert(extractError(error));
    }
}

async function deleteMember(id){
    const member=members.find(item=>Number(item.id)===Number(id));

    if(!member)return;

    if(member.status==='active'){
        alert('Active members cannot be deleted.');
        return;
    }

    if(!confirm(`Delete ${member.user?.name??'this member'}?`))return;

    try{
        await api(`/api/members/${id}`,{
            method:'DELETE'
        });

        await loadMembers(currentPage);

    }catch(error){
        alert(extractError(error));
    }
}

async function openRoleModal(memberId){
    if(!canManageRoles)return;

    const member=members.find(item=>Number(item.id)===Number(memberId));

    if(!member)return;

    roleMemberId=memberId;

    document.getElementById('roleMemberName').innerText=
        `${member.user?.name??'Member'} - ${member.member_code??''}`;

    document.getElementById('assignedRoles').innerHTML=
        '<div class="text-sm text-slate-500">Loading...</div>';

    document.getElementById('roleError').classList.add('hidden');

    const modal=document.getElementById('roleModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    document.body.classList.add('overflow-hidden');

    await loadMemberRoles();
}

function closeRoleModal(){
    const modal=document.getElementById('roleModal');

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');

    roleMemberId=null;
    assignedRoles=[];
    availableRoles=[];
}

async function loadMemberRoles(){
    try{
        const response=await api(`/api/members/${roleMemberId}/roles`);

        assignedRoles=response.data?.assigned_roles??[];
        availableRoles=response.data?.available_roles??[];

        renderAssignedRoles();
        renderRoleOptions();

    }catch(error){
        showRoleError(extractError(error));
    }
}

function renderAssignedRoles(){
    const container=document.getElementById('assignedRoles');

    if(!assignedRoles.length){
        container.innerHTML=`
            <div class="rounded-md border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">
                No roles assigned.
            </div>
        `;
        return;
    }

    container.innerHTML=assignedRoles.map(role=>{
        const isMember=role.name==='member';

        return `
            <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-800">
                        ${escapeHtml(role.display_name||role.name)}
                    </p>

                    <p class="mt-1 truncate text-xs text-slate-400">
                        ${escapeHtml(role.name)}
                    </p>
                </div>

                ${
                    isMember
                        ?'<span class="rounded-full bg-slate-200 px-2.5 py-1 text-[10px] font-semibold text-slate-600">Default</span>'
                        :`
                            <button onclick="removeMemberRole(${role.id})" class="rounded-md bg-red-50 px-2.5 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-100">
                                Remove
                            </button>
                        `
                }
            </div>
        `;
    }).join('');
}

function renderRoleOptions(){
    const select=document.getElementById('roleSelect');
    const assignedIds=assignedRoles.map(role=>Number(role.id));

    const options=availableRoles.filter(role=>{
        return !assignedIds.includes(Number(role.id));
    });

    select.innerHTML='<option value="">Select Role</option>';

    options.forEach(role=>{
        const option=document.createElement('option');

        option.value=role.id;
        option.textContent=role.display_name||role.name;

        select.appendChild(option);
    });

    document.getElementById('assignRoleButton').disabled=!options.length;
}

async function assignSelectedRole(){
    const roleId=document.getElementById('roleSelect').value;
    const button=document.getElementById('assignRoleButton');

    if(!roleId){
        showRoleError('Please select a role.');
        return;
    }

    button.disabled=true;
    button.innerText='Assigning...';

    try{
        await api(`/api/members/${roleMemberId}/roles`,{
            method:'POST',
            body:JSON.stringify({
                role_id:Number(roleId)
            })
        });

        await loadMemberRoles();
        await loadMembers(currentPage);

    }catch(error){
        showRoleError(extractError(error));

    }finally{
        button.disabled=false;
        button.innerText='Assign';
    }
}

async function removeMemberRole(roleId){
    if(!confirm('Remove this role from the member?'))return;

    try{
        await api(`/api/members/${roleMemberId}/roles`,{
            method:'DELETE',
            body:JSON.stringify({
                role_id:Number(roleId)
            })
        });

        await loadMemberRoles();
        await loadMembers(currentPage);

    }catch(error){
        showRoleError(extractError(error));
    }
}

function updateStats(){
    const stats=memberSummary??{
        total:members.length,
        active:members.filter(m=>m.status==='active').length,
        pending:members.filter(m=>m.status==='pending').length,
        suspended:members.filter(m=>m.status==='suspended').length,
        inactive:members.filter(m=>['inactive','rejected'].includes(m.status)).length
    };

    document.getElementById('totalMembers').innerText=stats.total??0;
    document.getElementById('activeMembers').innerText=stats.active??0;
    document.getElementById('pendingMembers').innerText=stats.pending??0;
    document.getElementById('suspendedMembers').innerText=stats.suspended??0;
    document.getElementById('inactiveMembers').innerText=stats.inactive??0;
}

function statusBadge(status){
    const classes={
        active:'bg-emerald-50 text-emerald-700',
        pending:'bg-amber-50 text-amber-700',
        inactive:'bg-slate-100 text-slate-600',
        suspended:'bg-red-50 text-red-700',
        rejected:'bg-red-50 text-red-700'
    };

    const label=status
        ?status.charAt(0).toUpperCase()+status.slice(1)
        :'Unknown';

    return `
        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold ${classes[status]??'bg-slate-100 text-slate-600'}">
            ${escapeHtml(label)}
        </span>
    `;
}

function clearFilters(){
    document.getElementById('searchInput').value='';
    document.getElementById('statusFilter').value='';
    loadMembers(1);
}

function showRoleError(message){
    const box=document.getElementById('roleError');

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

function formatDate(date){
    if(!date)return 'N/A';

    return new Date(date).toLocaleDateString('en-GB');
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
        loadMembers(1);
    },350);
});

document.getElementById('statusFilter').addEventListener('change',()=>{
    loadMembers(1);
});

document.addEventListener('keydown',function(e){
    if(e.key!=='Escape')return;

    if(!document.getElementById('roleModal').classList.contains('hidden')){
        closeRoleModal();
        return;
    }

    if(!document.getElementById('memberModal').classList.contains('hidden')){
        closeMemberModal();
    }
});

document.addEventListener('DOMContentLoaded',function(){
    loadMembers();
});
</script>

@endsection