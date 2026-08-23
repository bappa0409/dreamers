@extends('layouts.admin')

@section('title','Membership Management')
@section('page_title','Membership Management')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">Membership Management</h1>
                <p class="text-sm text-slate-500">Manage association members, status, assigned roles and share holdings.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Member.create'))
            <button type="button" onclick="openMemberModal()" class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-person-plus"></i>
                Add Member
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white p-4">
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

        <div class="col-span-2 rounded-md border border-slate-200 bg-slate-50 p-4 md:col-span-1">
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

    <div class="rounded-md border border-slate-200 bg-white p-3">
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

                <select id="statusFilter" class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                    <option value="rejected">Rejected</option>
                </select>

                <button type="button" onclick="clearFilters()" class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[22%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Member</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Code</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Phone</th>
                        <th class="w-[17%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Roles</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Joining</th>
                        <th class="w-[10%] px-3 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="w-[12%] px-3 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="membersTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-400">Loading members...</td>
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
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-lg font-bold text-slate-800">Add Member</h2>
                    <p id="modalDescription" class="text-sm text-slate-500">Create member profile and login account.</p>
                </div>
            </div>

            <button type="button" onclick="closeMemberModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="memberForm" class="flex min-h-0 flex-1 flex-col" enctype="multipart/form-data">
            <div class="space-y-5 overflow-y-auto p-5">
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

                    <div class="mb-5 flex flex-col gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center">
                        <div id="memberImagePreview" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white">
                            <i class="bi bi-person text-2xl text-slate-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="mb-2 text-xs font-semibold text-slate-700">Profile Photo</p>

                            <label for="profile_photo" class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                <i class="bi bi-camera"></i>
                                <span id="memberImageButtonText">Choose Photo</span>
                            </label>

                            <input id="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="hidden">

                            <p id="memberImageHelp" class="mt-1.5 text-[10px] text-slate-400">
                                JPG, PNG or WEBP. Max 2MB.
                            </p>
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
                            <input id="mobile" type="text" class="app-input" maxlength="20">
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
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                                <input id="date_of_birth" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                            </div>
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
                            <p class="mt-1 text-xs text-amber-700">Member will remain inactive until approval.</p>
                        </div>
                    </div>
                </div>

                <div id="formError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeMemberModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button id="saveButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Create Member
                </button>
            </div>
        </form>
    </div>
</div>

@if(filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN))
{{-- Share Management Modal --}}
<div id="shareModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-layers"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Member Shares</h2>
                    <p id="shareMemberInfo" class="text-sm text-slate-500">View and manage share holdings.</p>
                </div>
            </div>

            <button type="button" onclick="closeShareModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500">Total Shares</p>
                            <p id="shareTotal" class="mt-2 text-2xl font-bold text-slate-800">0</p>
                        </div>

                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-layers"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-emerald-700">Active Shares</p>
                            <p id="shareActive" class="mt-2 text-2xl font-bold text-emerald-600">0</p>
                        </div>

                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-indigo-700">Active Share Value</p>
                            <p id="shareValue" class="mt-2 text-xl font-bold text-indigo-600">৳0.00</p>
                        </div>

                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->hasPermission('Finance.create'))
                <section class="mt-5 rounded-md border border-slate-200 bg-white">
                    <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-plus-lg"></i>
                            </div>

                            <div>
                                <h3 class="text-sm font-semibold text-slate-800">Purchase Additional Share</h3>
                                <p class="text-[11px] text-slate-400">Issue another share to this existing member.</p>
                            </div>
                        </div>

                        <button type="button" onclick="toggleSharePurchaseForm()" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3 text-xs font-semibold text-white transition hover:bg-indigo-700">
                            <i class="bi bi-plus-lg"></i>
                            Add Share
                        </button>
                    </div>

                    <form id="shareForm" class="hidden p-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="form-label">Purchase Amount <span class="text-red-500">*</span></label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">{{ setting('currency_symbol','৳') }}</span>

                                    <input id="share_purchase_amount" type="number" step="0.01" min="0.01" value="{{ setting('default_share_value',50000) }}" class="app-input !pl-8" placeholder="0.00">
                                </div>
                            </div>

                        

                            <div>
                                <label class="form-label">Payment Method <span class="text-red-500">*</span></label>

                                <select id="share_payment_method" class="app-input">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="form-label">Notes</label>

                                <textarea id="share_notes" rows="3" maxlength="3000" class="app-input resize-none" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <div id="shareFormError" class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>

                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" onclick="cancelSharePurchase()" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </button>

                            <button id="saveShareButton" type="submit" class="h-9 cursor-pointer rounded-md bg-indigo-600 px-4 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                                Purchase Share
                            </button>
                        </div>
                    </form>
                </section>
            @endif

            <section class="mt-5 overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Share History</p>
                        <p class="text-[11px] text-slate-400">All shares issued to this member.</p>
                    </div>

                    <span id="shareHistoryCount" class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold text-slate-500">
                        0 shares
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="border-b border-slate-200 bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Share No.</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Value</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Acquired</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Created By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Notes</th>
                            </tr>
                        </thead>

                        <tbody id="shareHistoryTable">
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">Loading shares...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button type="button" onclick="closeShareModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>
@endif

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>
@endsection

@push('scripts')
<script>
let members=[];
let memberSummary=null;
let editingMember=null;
let currentPage=1;
let lastPage=1;
let total=0;
let previewObjectUrl=null;
let selectedShareMember=null;
let memberShares=[];

const canEditMember=@json(
    auth()->user()->hasPermission('Member.update')||
    auth()->user()->hasPermission('Member.edit')
);

const canDeleteMember=@json(
    auth()->user()->hasPermission('Member.delete')
);

const canViewShares=@json(
    auth()->user()->hasPermission('Member.view')
);

const canIssueShare=@json(
    auth()->user()->hasPermission('Finance.create')
);

const shareEnabled=@json(
    filter_var(
        setting('share_enabled',false),
        FILTER_VALIDATE_BOOLEAN
    )
);

const defaultShareValue=Number(
    @json(setting('default_share_value',50000))
);

const currencySymbol=@json(
    setting('currency_symbol','৳')
);

const el={
    table:document.getElementById('membersTable'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('memberForm'),
    saveButton:document.getElementById('saveButton'),
    name:document.getElementById('name'),
    email:document.getElementById('email'),
    mobile:document.getElementById('mobile'),
    language:document.getElementById('language'),
    phone:document.getElementById('phone'),
    alternatePhone:document.getElementById('alternate_phone'),
    dateOfBirth:document.getElementById('date_of_birth'),
    gender:document.getElementById('gender'),
    city:document.getElementById('city'),
    district:document.getElementById('district'),
    address:document.getElementById('address'),
    notes:document.getElementById('notes'),
    profilePhoto:document.getElementById('profile_photo'),
    imagePreview:document.getElementById('memberImagePreview'),
    imageButtonText:document.getElementById('memberImageButtonText'),
    imageHelp:document.getElementById('memberImageHelp')
};

async function loadMembers(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState(
        'Loading members...',
        7
    );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value,
        page
    });

    try{
        const response=await api(
            `/api/members?${query}`
        );

        const paginator=response.data??{};

        members=Array.isArray(paginator.data)
            ?paginator.data
            :(Array.isArray(response.data)
                ?response.data
                :[]);

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??members.length
        );

        memberSummary=response.summary??null;

        renderMembers();
        updateStats();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadMembers
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(
            AdminUI.extractError(error),
            7
        );
    }
}

function renderMembers(){
    if(!members.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No members found.',
            7
        );
        return;
    }

    el.table.innerHTML=members.map(member=>{
        const user=member.user??{};
        const roles=user.roles??[];
        const photoUrl=getProfilePhotoUrl(member);

        const initial=String(
            user.name??'M'
        ).trim().charAt(0).toUpperCase();

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="min-w-0 px-3 py-3">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50">
                            ${
                                photoUrl
                                    ?`
                                        <img
                                            src="${AdminUI.escapeHtml(photoUrl)}"
                                            class="h-full w-full object-cover"
                                            alt="${AdminUI.escapeHtml(user.name??'Member')}">
                                    `
                                    :`
                                        <span class="text-xs font-bold text-slate-400">
                                            ${AdminUI.escapeHtml(initial)}
                                        </span>
                                    `
                            }
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-slate-800">
                                ${AdminUI.escapeHtml(user.name??'N/A')}
                            </p>

                            <p class="mt-0.5 truncate text-[10px] text-slate-400">
                                ${AdminUI.escapeHtml(user.email??'')}
                            </p>
                        </div>
                    </div>
                </td>

                <td class="px-3 py-3">
                    <p class="truncate font-mono text-xs font-semibold text-indigo-600">
                        ${AdminUI.escapeHtml(member.member_code??'N/A')}
                    </p>
                </td>

                <td class="px-3 py-3">
                    <p class="truncate text-xs text-slate-600">
                        ${AdminUI.escapeHtml(
                            member.phone||
                            user.mobile||
                            'N/A'
                        )}
                    </p>
                </td>

                <td class="px-3 py-3">
                    ${
                        roles.length
                            ?roles.slice(0,2).map(role=>`
                                <span class="mr-1 rounded-md bg-violet-50 px-2 py-1 text-[10px] font-semibold text-violet-700">
                                    ${AdminUI.escapeHtml(
                                        role.display_name||
                                        role.name
                                    )}
                                </span>
                            `).join('')
                            :'<span class="text-xs text-slate-400">No roles</span>'
                    }
                </td>

                <td class="px-3 py-3 text-xs text-slate-600">
                    ${AdminUI.formatDate(member.joining_date)}
                </td>

                <td class="px-3 py-3">
                    ${AdminUI.statusBadge(member.status)}
                </td>

                <td class="px-3 py-3">
                    <div class="flex items-center justify-end gap-1">
                        ${
                            canViewShares&&shareEnabled
                                ?`
                                    <button
                                        type="button"
                                        onclick="openShareModal(${member.id})"
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                        title="Shares">
                                        <i class="bi bi-layers text-xs"></i>
                                    </button>
                                `
                                :''
                        }

                        ${
                            canEditMember
                                ?`
                                    <button
                                        type="button"
                                        onclick="editMember(${member.id})"
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                        title="Edit">
                                        <i class="bi bi-pencil-square text-xs"></i>
                                    </button>
                                `
                                :''
                        }

                        ${
                            canDeleteMember&&member.status!=='active'
                                ?`
                                    <button
                                        type="button"
                                        onclick="deleteMember(${member.id})"
                                        class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                        title="Delete">
                                        <i class="bi bi-trash text-xs"></i>
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

function getProfilePhotoUrl(member){
    if(member?.profile_photo_url){
        return member.profile_photo_url;
    }

    if(!member?.profile_photo){
        return null;
    }

    const value=String(
        member.profile_photo
    );

    if(
        value.startsWith('http://')||
        value.startsWith('https://')||
        value.startsWith('/storage/')
    ){
        return value;
    }

    return `/storage/${value.replace(/^\/+/,'')}`;
}

function resetProfilePhoto(){
    if(previewObjectUrl){
        URL.revokeObjectURL(
            previewObjectUrl
        );

        previewObjectUrl=null;
    }

    el.profilePhoto.value='';

    el.imagePreview.innerHTML=`
        <i class="bi bi-person text-2xl text-slate-300"></i>
    `;

    el.imageButtonText.textContent=
        'Choose Photo';

    el.imageHelp.textContent=
        'JPG, PNG or WEBP. Max 2MB.';

    el.imageHelp.className=
        'mt-1.5 text-[10px] text-slate-400';
}

function showExistingProfilePhoto(member){
    const url=getProfilePhotoUrl(
        member
    );

    if(!url){
        return;
    }

    el.imagePreview.innerHTML=`
        <img
            src="${AdminUI.escapeHtml(url)}"
            class="h-full w-full object-cover"
            alt="Profile photo">
    `;

    el.imageButtonText.textContent=
        'Replace Photo';
}

el.profilePhoto.addEventListener(
    'change',
    function(){
        const file=this.files?.[0];

        if(!file){
            return;
        }

        if(file.size>2*1024*1024){
            Toast.error(
                'Profile photo must be 2MB or smaller.'
            );

            this.value='';
            return;
        }

        const allowed=[
            'image/jpeg',
            'image/png',
            'image/webp'
        ];

        if(!allowed.includes(file.type)){
            Toast.error(
                'Only JPG, PNG or WEBP images are allowed.'
            );

            this.value='';
            return;
        }

        if(previewObjectUrl){
            URL.revokeObjectURL(
                previewObjectUrl
            );
        }

        previewObjectUrl=
            URL.createObjectURL(file);

        el.imagePreview.innerHTML=`
            <img
                src="${previewObjectUrl}"
                class="h-full w-full object-cover"
                alt="Profile preview">
        `;

        el.imageButtonText.textContent=
            'Change Photo';

        el.imageHelp.textContent=
            file.name;

        el.imageHelp.className=
            'mt-1.5 truncate text-[10px] text-emerald-600';
    }
);

window.openMemberModal=function(member=null){
    editingMember=member;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('formError');
    resetProfilePhoto();

    document.getElementById(
        'modalTitle'
    ).innerText=
        member
            ?'Edit Member'
            :'Add Member';

    document.getElementById(
        'modalDescription'
    ).innerText=
        member
            ?'Update member profile information.'
            :'Create member profile and login account.';

    el.saveButton.innerText=
        member
            ?'Update Member'
            :'Create Member';

    if(member){
        const user=member.user??{};

        el.name.value=user.name??'';
        el.email.value=user.email??'';
        el.mobile.value=user.mobile??'';
        el.language.value=user.language??'en';

        el.phone.value=member.phone??'';
        el.alternatePhone.value=
            member.alternate_phone??'';

        el.gender.value=
            member.gender??'';

        el.city.value=
            member.city??'';

        el.district.value=
            member.district??'';

        el.address.value=
            member.address??'';

        el.notes.value=
            member.notes??'';

        setPickerDate(
            el.dateOfBirth,
            member.date_of_birth
        );

        showExistingProfilePhoto(
            member
        );
    }else{
        el.language.value='en';
    }

    AdminUI.openModal(
        'memberModal'
    );

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }
};

window.closeMemberModal=function(){
    AdminUI.closeModal(
        'memberModal'
    );

    editingMember=null;

    if(previewObjectUrl){
        URL.revokeObjectURL(
            previewObjectUrl
        );

        previewObjectUrl=null;
    }
};

function setPickerDate(element,value){
    const date=value
        ?String(value).substring(0,10)
        :'';

    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(
                date,
                false
            )
            :element._flatpickr.clear();
    }
}

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'formError'
        );

        const editing=
            Boolean(editingMember);

        const data={
            phone:
                el.phone.value.trim()||
                null,

            alternate_phone:
                el.alternatePhone.value.trim()||
                null,

            date_of_birth:
                el.dateOfBirth.value||
                null,

            gender:
                el.gender.value||
                null,

            city:
                el.city.value.trim()||
                null,

            district:
                el.district.value.trim()||
                null,

            address:
                el.address.value.trim()||
                null,

            notes:
                el.notes.value.trim()||
                null
        };

        if(!editing){
            data.name=
                el.name.value.trim();

            data.email=
                el.email.value.trim();

            data.mobile=
                el.mobile.value.trim()||
                null;

            data.language=
                el.language.value||
                'en';

            if(!data.name){
                AdminUI.showError(
                    'formError',
                    'Name is required.'
                );

                return;
            }

            if(!data.email){
                AdminUI.showError(
                    'formError',
                    'Email is required.'
                );

                return;
            }
        }

        const formData=
            new FormData();

        Object.entries(data)
            .forEach(([key,value])=>{
                if(
                    value!==null&&
                    value!==undefined
                ){
                    formData.append(
                        key,
                        value
                    );
                }
            });

        if(
            el.profilePhoto.files?.[0]
        ){
            formData.append(
                'profile_photo',
                el.profilePhoto.files[0]
            );
        }

        if(editing){
            formData.append(
                '_method',
                'PUT'
            );
        }

        AdminUI.setLoading(
            el.saveButton,
            editing
                ?'Updating...'
                :'Creating...'
        );

        try{
            await api(
                editing
                    ?`/api/members/${editingMember.id}`
                    :'/api/members',
                {
                    method:'POST',
                    body:formData
                }
            );

            closeMemberModal();

            Toast.success(
                editing
                    ?'Member updated successfully.'
                    :'Member created successfully.'
            );

            await loadMembers(
                editing
                    ?currentPage
                    :1
            );
        }catch(error){
            AdminUI.showError(
                'formError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                el.saveButton
            );
        }
    }
);

window.editMember=function(id){
    const member=members.find(
        item=>
            Number(item.id)===
            Number(id)
    );

    if(!member){
        Toast.error(
            'Member not found.'
        );

        return;
    }

    openMemberModal(
        member
    );
};

window.deleteMember=function(id){
    const member=members.find(
        item=>
            Number(item.id)===
            Number(id)
    );

    if(!member){
        Toast.error(
            'Member not found.'
        );

        return;
    }

    AdminUI.deleteRequest(
        `/api/members/${id}`,
        {
            title:'Delete Member?',
            message:'This member will be permanently deleted.',
            confirmText:'Delete',
            successMessage:'Member deleted successfully.',
            onSuccess:()=>
                loadMembers(currentPage)
        }
    );
};

/*
|--------------------------------------------------------------------------
| Share Management
|--------------------------------------------------------------------------
*/

window.openShareModal=async function(id){
    if(!shareEnabled){
        Toast.error(
            'Share purchasing is currently disabled.'
        );

        return;
    }

    const member=members.find(
        item=>
            Number(item.id)===
            Number(id)
    );

    if(!member){
        Toast.error(
            'Member not found.'
        );

        return;
    }

    selectedShareMember=member;
    memberShares=[];

    const user=
        member.user??{};

    const info=
        document.getElementById(
            'shareMemberInfo'
        );

    if(info){
        info.textContent=
            `${user.name??'Member'} • ${member.member_code??''}`;
    }

    document.getElementById(
        'shareTotal'
    ).textContent='0';

    document.getElementById(
        'shareActive'
    ).textContent='0';

    document.getElementById(
        'shareValue'
    ).textContent=money(0);

    document.getElementById(
        'shareHistoryCount'
    ).textContent='0 shares';

    document.getElementById(
        'shareHistoryTable'
    ).innerHTML=
        AdminUI.loadingState(
            'Loading shares...',
            6
        );

    resetShareForm();

    AdminUI.openModal(
        'shareModal'
    );

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }

    await loadMemberShares();
};

window.closeShareModal=function(){
    if(!shareEnabled){
        return;
    }

    AdminUI.closeModal(
        'shareModal'
    );

    selectedShareMember=null;
    memberShares=[];

    resetShareForm();
};

async function loadMemberShares(){
    if(
        !shareEnabled||
        !selectedShareMember
    ){
        return;
    }

    const table=
        document.getElementById(
            'shareHistoryTable'
        );

    if(!table){
        return;
    }

    table.innerHTML=
        AdminUI.loadingState(
            'Loading shares...',
            6
        );

    try{
        const response=await api(
            `/api/members/${selectedShareMember.id}/shares`
        );

        const data=
            response.data??{};

        const summary=
            data.summary??{};

        memberShares=
            Array.isArray(data.shares)
                ?data.shares
                :[];

        document.getElementById(
            'shareTotal'
        ).textContent=
            Number(
                summary.total_shares??
                memberShares.length
            );

        document.getElementById(
            'shareActive'
        ).textContent=
            Number(
                summary.active_shares??
                memberShares.filter(
                    item=>
                        item.status===
                        'active'
                ).length
            );

        document.getElementById(
            'shareValue'
        ).textContent=
            money(
                summary.active_share_value
            );

        document.getElementById(
            'shareHistoryCount'
        ).textContent=
            `${memberShares.length} share${memberShares.length===1?'':'s'}`;

        renderShareHistory();
    }catch(error){
        table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                6
            );
    }
}

function renderShareHistory(){
    if(!shareEnabled){
        return;
    }

    const table=
        document.getElementById(
            'shareHistoryTable'
        );

    if(!table){
        return;
    }

    if(!memberShares.length){
        table.innerHTML=
            AdminUI.emptyState(
                'No shares found for this member.',
                6
            );

        return;
    }

    table.innerHTML=
        memberShares.map(
            share=>`
                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <span class="font-mono text-xs font-semibold text-indigo-600">
                            ${AdminUI.escapeHtml(
                                share.share_no??'—'
                            )}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-right text-xs font-semibold text-slate-700">
                        ${money(
                            share.purchase_amount
                        )}
                    </td>

                    <td class="px-4 py-3 text-xs text-slate-600">
                        ${
                            share.acquired_date
                                ?AdminUI.formatDate(
                                    share.acquired_date
                                )
                                :'—'
                        }
                    </td>

                    <td class="px-4 py-3">
                        ${shareStatusBadge(
                            share.status
                        )}
                    </td>

                    <td class="px-4 py-3">
                        <p class="text-xs font-medium text-slate-600">
                            ${AdminUI.escapeHtml(
                                share.creator?.name??
                                'System'
                            )}
                        </p>
                    </td>

                    <td class="max-w-[200px] px-4 py-3">
                        <p
                            class="truncate text-xs text-slate-500"
                            title="${AdminUI.escapeHtml(
                                share.notes??''
                            )}">
                            ${AdminUI.escapeHtml(
                                share.notes??'—'
                            )}
                        </p>
                    </td>
                </tr>
            `
        ).join('');
}

function shareStatusBadge(status){
    const styles={
        pending:
            'bg-amber-50 text-amber-700',

        active:
            'bg-emerald-50 text-emerald-700',

        transferred:
            'bg-sky-50 text-sky-700',

        cancelled:
            'bg-red-50 text-red-600',

        retired:
            'bg-slate-100 text-slate-600'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${styles[status]??styles.retired}">
            ${AdminUI.escapeHtml(
                titleCase(status)
            )}
        </span>
    `;
}

window.toggleSharePurchaseForm=function(){
    if(!shareEnabled){
        Toast.error(
            'Share purchasing is currently disabled.'
        );

        return;
    }

    if(!canIssueShare){
        Toast.error(
            'You do not have permission to purchase shares.'
        );

        return;
    }

    const form=
        document.getElementById(
            'shareForm'
        );

    if(!form){
        return;
    }

    form.classList.toggle(
        'hidden'
    );

    if(
        !form.classList.contains(
            'hidden'
        )
    ){
        const amount=
            document.getElementById(
                'share_purchase_amount'
            );

        if(amount){
            amount.value=
                defaultShareValue;
        }

        setShareDate(
            document.getElementById(
                'share_acquired_date'
            ),
            new Date()
        );
    }

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }
};

window.cancelSharePurchase=function(){
    resetShareForm();
};

function resetShareForm(){
    if(!shareEnabled){
        return;
    }

    const form=
        document.getElementById(
            'shareForm'
        );

    if(!form){
        return;
    }

    form.reset();

    form.classList.add(
        'hidden'
    );

    const amount=
        document.getElementById(
            'share_purchase_amount'
        );

    if(amount){
        amount.value=
            defaultShareValue;
    }

    const method=
        document.getElementById(
            'share_payment_method'
        );

    if(method){
        method.value='cash';
    }

    AdminUI.clearError(
        'shareFormError'
    );

    const date=
        document.getElementById(
            'share_acquired_date'
        );

    if(date?._flatpickr){
        date._flatpickr.clear();
    }

    if(date){
        date.value='';
    }
}

function setShareDate(element,date){
    if(!element){
        return;
    }

    const value=[
        date.getFullYear(),
        String(
            date.getMonth()+1
        ).padStart(2,'0'),
        String(
            date.getDate()
        ).padStart(2,'0')
    ].join('-');

    element.value=value;

    if(element._flatpickr){
        element._flatpickr.setDate(
            value,
            false
        );
    }
}

const shareForm=
    document.getElementById(
        'shareForm'
    );

if(
    shareEnabled&&
    shareForm
){
    shareForm.addEventListener(
        'submit',
        async event=>{
            event.preventDefault();

            if(!shareEnabled){
                Toast.error(
                    'Share purchasing is currently disabled.'
                );

                return;
            }

            if(!selectedShareMember){
                Toast.error(
                    'Member not found.'
                );

                return;
            }

            AdminUI.clearError(
                'shareFormError'
            );

            const purchaseAmount=
                Number(
                    document.getElementById(
                        'share_purchase_amount'
                    ).value
                );

            const acquiredDate=
                document.getElementById(
                    'share_acquired_date'
                ).value;

            const paymentMethod=
                document.getElementById(
                    'share_payment_method'
                ).value;

            const notes=
                document.getElementById(
                    'share_notes'
                ).value.trim();

            if(
                !Number.isFinite(
                    purchaseAmount
                )||
                purchaseAmount<=0
            ){
                AdminUI.showError(
                    'shareFormError',
                    'Purchase amount must be greater than zero.'
                );

                return;
            }

            if(!acquiredDate){
                AdminUI.showError(
                    'shareFormError',
                    'Acquired date is required.'
                );

                return;
            }

            const button=
                document.getElementById(
                    'saveShareButton'
                );

            AdminUI.setLoading(
                button,
                'Purchasing...'
            );

            try{
                const response=await api(
                    `/api/members/${selectedShareMember.id}/shares`,
                    {
                        method:'POST',
                        body:JSON.stringify({
                            purchase_amount:
                                purchaseAmount,

                            acquired_date:
                                acquiredDate,

                            payment_method:
                                paymentMethod,

                            notes:
                                notes||null
                        })
                    }
                );

                Toast.success(
                    response.message??
                    'Additional share purchased successfully.'
                );

                resetShareForm();

                await loadMemberShares();
            }catch(error){
                AdminUI.showError(
                    'shareFormError',
                    AdminUI.extractError(
                        error
                    )
                );
            }finally{
                AdminUI.resetLoading(
                    button
                );
            }
        }
    );
}

function money(value){
    return`${currencySymbol}${Number(
        value??0
    ).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}

function titleCase(value){
    if(!value){
        return'—';
    }

    return String(value)
        .replaceAll('_',' ')
        .replace(
            /\b\w/g,
            char=>char.toUpperCase()
        );
}

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

function updateStats(){
    const stats=
        memberSummary??{
            total:
                members.length,

            active:
                members.filter(
                    x=>x.status==='active'
                ).length,

            pending:
                members.filter(
                    x=>x.status==='pending'
                ).length,

            suspended:
                members.filter(
                    x=>x.status==='suspended'
                ).length,

            inactive:
                members.filter(
                    x=>[
                        'inactive',
                        'rejected'
                    ].includes(x.status)
                ).length
        };

    document.getElementById(
        'totalMembers'
    ).innerText=
        stats.total??0;

    document.getElementById(
        'activeMembers'
    ).innerText=
        stats.active??0;

    document.getElementById(
        'pendingMembers'
    ).innerText=
        stats.pending??0;

    document.getElementById(
        'suspendedMembers'
    ).innerText=
        stats.suspended??0;

    document.getElementById(
        'inactiveMembers'
    ).innerText=
        stats.inactive??0;
}

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';

    loadMembers(1);
};

/*
|--------------------------------------------------------------------------
| Init
|--------------------------------------------------------------------------
*/

async function initMembersPage(){
    if(
        typeof window.AdminUI===
            'undefined'||
        typeof window.api===
            'undefined'
    ){
        setTimeout(
            initMembersPage,
            50
        );

        return;
    }

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadMembers(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadMembers(1)
    );

    await loadMembers();
}

if(
    document.readyState===
    'loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        initMembersPage
    );
}else{
    initMembersPage();
}
</script>
@endpush