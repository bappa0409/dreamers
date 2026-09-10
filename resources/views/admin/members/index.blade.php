@extends('layouts.admin')

@section('title','Membership Management')
@section('page_title','Membership Management')

@section('content')
<div class="space-y-3">
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">Membership Management</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage association members, status, assigned roles and share holdings.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Member.create'))
            <button type="button" onclick="openMemberModal()" class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-person-plus"></i>
                Add Member
            </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Total Members</p>
                    <p id="totalMembers" class="text-xl font-bold text-slate-800">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/50 px-5 py-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class=" text-xs 2xl:text-sm text-emerald-700">Active</p>
                    <p id="activeMembers" class="text-xl font-bold text-emerald-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                    <i class="bi bi-person-check"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 px-5 py-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class=" text-xs 2xl:text-sm text-amber-700">Pending</p>
                    <p id="pendingMembers" class="text-xl font-bold text-amber-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-red-200 bg-red-50/50 px-5 py-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class=" text-xs 2xl:text-sm text-red-700">Suspended</p>
                    <p id="suspendedMembers" class="text-xl font-bold text-red-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-red-100 text-red-600">
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/50 px-5 py-2">
            <div class="flex items-center justify-between">
                <div>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Rejected / Inactive</p>
                    <p id="inactiveMembers" class="text-xl font-bold text-slate-600">0</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-200 text-slate-600">
                    <i class="bi bi-person-x"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            {{-- Header --}}
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Members
                    </p>
                    <p class="hidden text-[11px] text-slate-500 sm:block">
                        Search by name, email, mobile or member code.
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center gap-2 lg:w-auto">

                {{-- Search --}}
                <div class="relative min-w-0 flex-1 lg:w-[280px]">
                    <i
                        class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                    <input id="searchInput" type="text" placeholder="Search members..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-700 outline-none placeholder:text-slate-500 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                </div>

                {{-- Filter --}}
                <button type="button" onclick="toggleFilters()" id="filterButton"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 2xl:text-sm">
                    <i class="bi bi-funnel text-xs"></i>
                    <span>Filter</span>
                    <i id="filterChevron" class="bi bi-chevron-down text-[10px]"></i>
                </button>

                {{-- Clear --}}
                <button type="button" onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 2xl:text-sm">
                    <i class="bi bi-arrow-counterclockwise text-[10px]"></i>
                    <span class="hidden sm:inline">Reset</span>
                </button>
            </div>
        </div>


        {{-- Filter Options --}}
        <div id="filterPanel" class="mt-3 hidden border-t border-slate-100 pt-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[180px_auto]">

                {{-- Status --}}
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">
                        Status
                    </label>

                    <select id="statusFilter"
                        class="h-9 w-full cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 2xl:text-sm">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[820px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[22%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Member</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Code</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Phone</th>
                        <th class="w-[17%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Roles</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Joining</th>
                        <th class="w-[10%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="w-[12%] px-3 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="membersTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="membersCards" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-sm text-slate-500">Loading members...</div>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Member Modal --}}
<div id="memberModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600 border border-slate-300 ">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div>
                    <h2 id="modalTitle" class="text-sm font-semibold text-slate-800">Add Member</h2>
                    <p id="modalDescription" class=" text-xs 2xl:text-sm text-slate-500">Create member profile and login account.</p>
                </div>
            </div>

            <button type="button" onclick="closeMemberModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="memberForm" class="flex min-h-0 flex-1 flex-col" enctype="multipart/form-data" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Account Information</h3>
                            <p class="text-[11px] text-slate-500">Login account for this association member.</p>
                        </div>
                    </div>

                    <div class="mb-5 flex flex-col gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center">
                        <div id="memberImagePreview" class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-white">
                            <i class="bi bi-person text-2xl text-slate-300"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="form-label">Profile Photo <span class="text-red-500">*</span></p>

                            <label for="profile_photo" class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 py-2 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                <i class="bi bi-camera"></i>
                                <span id="memberImageButtonText">Choose Photo</span>
                            </label>

                            <input id="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="hidden">

                            <p id="memberImageHelp" class="mt-1.5 text-[10px] text-slate-500">
                                JPG, PNG or WEBP. Max 2MB.
                            </p>

                            <p data-field-error="profile_photo" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
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
                            <label class="form-label">Mobile <span class="text-red-500">*</span></label>
                            <input id="mobile" type="tel" inputmode="numeric" data-mobile="true" placeholder="01XXXXXXXXX" class="app-input" maxlength="11">
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Member Information</h3>
                            <p class="text-[11px] text-slate-500">Additional association membership details.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Father / Husband Name <span class="text-red-500">*</span></label>
                            <input id="father_or_husband_name" type="text" class="app-input" maxlength="150">
                        </div>

                        <div>
                            <label class="form-label">Mother's Name <span class="text-red-500">*</span></label>
                            <input id="mother_name" type="text" class="app-input" maxlength="150">
                        </div>

                        <div>
                            <label class="form-label">Alternate Mobile <span class="text-red-500">*</span></label>
                            <input id="alternate_phone" type="tel" inputmode="numeric" data-mobile="true" placeholder="01XXXXXXXXX" class="app-input" maxlength="11">
                        </div>

                        <div>
                            <label class="form-label">Date of Birth <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>

                                <input id="date_of_birth" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Gender <span class="text-red-500">*</span></label>
                            <select id="gender" class="app-input">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">NID / Birth Registration No. <span class="text-red-500">*</span></label>
                            <input id="nid_or_birth_reg_no" type="number" inputmode="numeric" class="app-input" maxlength="17">
                        </div>

                        <div>
                            <label class="form-label">NID / Birth Registration Document <span class="text-red-500">*</span></label>

                            <label for="nid_document" class="flex h-[38px] cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                <i class="bi bi-file-earmark-arrow-up"></i>
                                <span id="nidDocumentButtonText" class="truncate">Choose File</span>
                            </label>

                            <input id="nid_document" type="file" accept="image/jpeg,image/png,.pdf" class="hidden">

                            <p id="nidDocumentHelp" class="mt-1.5 text-[10px] text-slate-500">JPG, PNG or PDF. Max 5MB.</p>

                            <p data-field-error="nid_document" class="mt-1 hidden text-xs 2xl:text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Profession <span class="text-red-500">*</span></label>
                            <input id="profession" type="text" class="app-input" maxlength="150">
                        </div>

                        <div>
                            <label class="form-label">District <span class="text-red-500">*</span></label>
                            <select id="district" class="app-input">
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">City <span class="text-red-500">*</span></label>
                            <select id="city" class="app-input">
                                <option value="">Select District First</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Present Address <span class="text-red-500">*</span></label>
                        <textarea id="address" rows="3" class="app-input resize-none"></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Permanent Address <span class="text-red-500">*</span></label>
                        <textarea id="permanent_address" rows="3" class="app-input resize-none"></textarea>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Notes</label>
                        <textarea id="notes" rows="3" class="app-input resize-none"></textarea>
                    </div>
                </section>

                @if(auth()->user()->hasPermission('Finance.create') && filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN))
                <section id="initialShareSection" class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-layers"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Initial Share</h3>
                            <p class="text-[11px] text-slate-500">Issue this member's first share right away.</p>
                        </div>
                    </div>

                    <div id="initialShareFields" class="mt-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="form-label">Purchase Amount <span class="text-red-500">*</span></label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-500">{{ setting('currency_symbol','৳') }}</span>

                                    <input id="initial_share_amount" type="number" step="0.01" min="0.01" value="{{ setting('default_share_value',50000) }}" class="app-input !pl-8" placeholder="0.00">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Payment Method <span class="text-red-500">*</span></label>

                                <select id="initial_share_payment_method" class="app-input">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile_banking">Mobile Banking</option>
                                    <option value="online">Online</option>
                                </select>
                            </div>
                        </div>

                        <p class="mt-3 flex items-start gap-1.5 text-[11px] text-slate-500">
                            <i class="bi bi-info-circle mt-0.5"></i>
                            This one-time initial amount does not need to match the current share price setting — use it for what this member is actually paying now. It's sent for verification. All shares purchased after this will follow the configured share price.
                        </p>
                    </div>
                </section>
                @endif

                <div id="approvalInfo" class="hidden rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="flex gap-3">
                        <i class="bi bi-shield-check text-amber-600"></i>
                        <div>
                            <p class="text-base font-semibold text-amber-800">Approval Required</p>
                            <p class="mt-1 text-sm text-amber-700">Member will remain inactive until approval.</p>
                        </div>
                    </div>
                </div>

                <div id="formError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-700"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeMemberModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i> Close
                </button>

                <button id="saveButton" type="submit" class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    <span>Create Member</span>
                </button>
            </div>
        </form>
    </div>
</div>

@if(filter_var(setting('share_enabled',false),FILTER_VALIDATE_BOOLEAN))
{{-- Share Management Modal --}}
<div id="shareModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-layers"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">Member Shares</h2>
                    <p id="shareMemberInfo" class=" text-xs 2xl:text-sm text-slate-500">View and manage share holdings.</p>
                </div>
            </div>

            <button type="button" onclick="closeShareModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class=" text-xs 2xl:text-sm text-slate-500">Total Shares</p>
                            <p id="shareTotal" class="text-xl font-bold text-slate-800">0</p>
                        </div>

                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-layers"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-emerald-200 bg-emerald-50/50 px-5 py-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class=" text-xs 2xl:text-sm text-emerald-700">Active Shares</p>
                            <p id="shareActive" class="text-xl font-bold text-emerald-600">0</p>
                        </div>

                        <div class="flex h-9 w-9 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-indigo-200 bg-indigo-50/50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-indigo-700">Active Share Value</p>
                            <p id="shareValue" class="text-xl font-bold text-indigo-600">{{ setting('currency_symbol','৳') }}0.00</p>
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
                                <p class="text-[11px] text-slate-500">Issue another share to this existing member.</p>
                            </div>
                        </div>

                        <button type="button" onclick="toggleSharePurchaseForm()" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                            <i class="bi bi-plus-lg"></i>
                            Add Share
                        </button>
                    </div>

                    <form id="shareForm" class="hidden p-4" novalidate data-js-validation="1">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="form-label">Purchase Amount <span class="text-red-500">*</span></label>

                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-base text-slate-500">{{ setting('currency_symbol','৳') }}</span>

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

                        <div id="shareFormError" class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-700"></div>

                        <div class="mt-4 flex justify-end gap-2">
                            <button type="button" onclick="cancelSharePurchase()" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                Cancel
                            </button>

                            <button id="saveShareButton" type="submit" class="h-9 cursor-pointer rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
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
                        <p class="text-[11px] text-slate-500">All shares issued to this member.</p>
                    </div>

                    <span id="shareHistoryCount" class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[10px] font-semibold text-slate-500">
                        0 shares
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] text-base">
                        <thead class="border-b border-slate-200 bg-white">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Share No.</th>
                                <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Value</th>
                                <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Acquired</th>
                                <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Created By</th>
                                <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Notes</th>
                            </tr>
                        </thead>

                        <tbody id="shareHistoryTable">
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-xs 2xl:text-sm text-center text-slate-500">Loading shares...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button type="button" onclick="closeShareModal()" class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <i class="bi bi-x-lg"></i> Close
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
    auth()->user()->hasPermission('Member.update')
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
    cards:document.getElementById('membersCards'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    form:document.getElementById('memberForm'),
    saveButton:document.getElementById('saveButton'),
    name:document.getElementById('name'),
    email:document.getElementById('email'),
    mobile:document.getElementById('mobile'),
    alternatePhone:document.getElementById('alternate_phone'),
    fatherOrHusbandName:document.getElementById('father_or_husband_name'),
    motherName:document.getElementById('mother_name'),
    dateOfBirth:document.getElementById('date_of_birth'),
    gender:document.getElementById('gender'),
    nidOrBirthRegNo:document.getElementById('nid_or_birth_reg_no'),
    profession:document.getElementById('profession'),
    city:document.getElementById('city'),
    district:document.getElementById('district'),
    address:document.getElementById('address'),
    permanentAddress:document.getElementById('permanent_address'),
    notes:document.getElementById('notes'),
    profilePhoto:document.getElementById('profile_photo'),
    imagePreview:document.getElementById('memberImagePreview'),
    imageButtonText:document.getElementById('memberImageButtonText'),
    imageHelp:document.getElementById('memberImageHelp'),
    nidDocument:document.getElementById('nid_document'),
    nidDocumentButtonText:document.getElementById('nidDocumentButtonText'),
    nidDocumentHelp:document.getElementById('nidDocumentHelp'),
    initialShareSection:document.getElementById('initialShareSection'),
    initialShareFields:document.getElementById('initialShareFields'),
    initialShareAmount:document.getElementById('initial_share_amount'),
    initialSharePaymentMethod:document.getElementById('initial_share_payment_method')
};

/*
|--------------------------------------------------------------------------
| Bangladesh District -> City/Upazila Map
|--------------------------------------------------------------------------
| Covers all 64 districts. Feel free to extend the upazila lists below —
| this file is the single source of truth for the District/City selects.
*/
const bdDistrictCities={
    'Dhaka':['Dhaka Sadar','Dhamrai','Dohar','Keraniganj','Nawabganj','Savar'],
    'Faridpur':['Faridpur Sadar','Alfadanga','Bhanga','Boalmari','Charbhadrasan','Madhukhali','Nagarkanda','Sadarpur','Saltha'],
    'Gazipur':['Gazipur Sadar','Kaliakair','Kaliganj','Kapasia','Sreepur'],
    'Gopalganj':['Gopalganj Sadar','Kashiani','Kotalipara','Muksudpur','Tungipara'],
    'Kishoreganj':['Kishoreganj Sadar','Austagram','Bajitpur','Bhairab','Hossainpur','Itna','Karimganj','Katiadi','Kuliarchar','Mithamain','Nikli','Pakundia','Tarail'],
    'Madaripur':['Madaripur Sadar','Kalkini','Rajoir','Shibchar'],
    'Manikganj':['Manikganj Sadar','Daulatpur','Ghior','Harirampur','Saturia','Shivalaya','Singair'],
    'Munshiganj':['Munshiganj Sadar','Gazaria','Lohajang','Sirajdikhan','Sreenagar','Tongibari'],
    'Narayanganj':['Narayanganj Sadar','Araihazar','Bandar','Rupganj','Sonargaon'],
    'Narsingdi':['Narsingdi Sadar','Belabo','Monohardi','Palash','Raipura','Shibpur'],
    'Rajbari':['Rajbari Sadar','Baliakandi','Goalandaghat','Pangsha','Kalukhali'],
    'Shariatpur':['Shariatpur Sadar','Bhedarganj','Damudya','Gosairhat','Naria','Zajira'],
    'Tangail':['Tangail Sadar','Basail','Bhuapur','Delduar','Dhanbari','Ghatail','Gopalpur','Kalihati','Madhupur','Mirzapur','Nagarpur','Sakhipur'],
    'Jamalpur':['Jamalpur Sadar','Bakshiganj','Dewanganj','Islampur','Madarganj','Melandaha','Sarishabari'],
    'Mymensingh':['Mymensingh Sadar','Bhaluka','Dhobaura','Fulbaria','Gaffargaon','Gauripur','Haluaghat','Ishwarganj','Muktagacha','Nandail','Phulpur','Trishal'],
    'Netrokona':['Netrokona Sadar','Atpara','Barhatta','Durgapur','Kalmakanda','Kendua','Khaliajuri','Madan','Mohanganj','Purbadhala'],
    'Sherpur':['Sherpur Sadar','Jhenaigati','Nakla','Nalitabari','Sreebardi'],
    'Bandarban':['Bandarban Sadar','Alikadam','Lama','Naikhongchhari','Rowangchhari','Ruma','Thanchi'],
    'Brahmanbaria':['Brahmanbaria Sadar','Akhaura','Ashuganj','Bancharampur','Bijoynagar','Kasba','Nabinagar','Nasirnagar','Sarail'],
    'Chandpur':['Chandpur Sadar','Faridganj','Haimchar','Haziganj','Kachua','Matlab Dakshin','Matlab Uttar','Shahrasti'],
    'Chattogram':['Chattogram Sadar','Anwara','Banshkhali','Boalkhali','Chandanaish','Fatikchhari','Hathazari','Lohagara','Mirsharai','Patiya','Rangunia','Raozan','Sandwip','Satkania','Sitakunda'],
    'Cumilla':['Cumilla Sadar','Barura','Brahmanpara','Burichang','Chandina','Chauddagram','Daudkandi','Debidwar','Homna','Laksam','Lalmai','Meghna','Muradnagar','Nangalkot','Titas'],
    "Cox's Bazar":["Cox's Bazar Sadar",'Chakaria','Kutubdia','Maheshkhali','Pekua','Ramu','Teknaf','Ukhia'],
    'Feni':['Feni Sadar','Chhagalnaiya','Daganbhuiyan','Parshuram','Sonagazi','Fulgazi'],
    'Khagrachhari':['Khagrachhari Sadar','Dighinala','Lakshmichhari','Mahalchhari','Manikchhari','Matiranga','Panchhari','Ramgarh'],
    'Lakshmipur':['Lakshmipur Sadar','Kamalnagar','Raipur','Ramganj','Ramgati'],
    'Noakhali':['Noakhali Sadar','Begumganj','Chatkhil','Companiganj','Hatiya','Kabirhat','Senbagh','Sonaimuri','Subarnachar'],
    'Rangamati':['Rangamati Sadar','Baghaichhari','Barkal','Belaichhari','Juraichhari','Kaptai','Kawkhali','Langadu','Naniarchar','Rajasthali'],
    'Bogura':['Bogura Sadar','Adamdighi','Dhunat','Dhupchanchia','Gabtali','Kahaloo','Nandigram','Sariakandi','Shajahanpur','Sherpur','Shibganj','Sonatola'],
    'Joypurhat':['Joypurhat Sadar','Akkelpur','Kalai','Khetlal','Panchbibi'],
    'Naogaon':['Naogaon Sadar','Atrai','Badalgachhi','Dhamoirhat','Manda','Mahadebpur','Niamatpur','Patnitala','Porsha','Raninagar','Sapahar'],
    'Natore':['Natore Sadar','Bagatipara','Baraigram','Gurudaspur','Lalpur','Singra'],
    'Chapainawabganj':['Chapainawabganj Sadar','Bholahat','Gomastapur','Nachole','Shibganj'],
    'Pabna':['Pabna Sadar','Atgharia','Bera','Bhangura','Chatmohar','Faridpur','Ishwardi','Santhia','Sujanagar'],
    'Rajshahi':['Rajshahi Sadar','Bagha','Bagmara','Charghat','Durgapur','Godagari','Mohanpur','Paba','Puthia','Tanore'],
    'Sirajganj':['Sirajganj Sadar','Belkuchi','Chauhali','Kamarkhanda','Kazipur','Raiganj','Shahjadpur','Tarash','Ullapara'],
    'Bagerhat':['Bagerhat Sadar','Chitalmari','Fakirhat','Kachua','Mollahat','Mongla','Morrelganj','Rampal','Sarankhola'],
    'Chuadanga':['Chuadanga Sadar','Alamdanga','Damurhuda','Jibannagar'],
    'Jashore':['Jashore Sadar','Abhaynagar','Bagherpara','Chaugachha','Jhikargachha','Keshabpur','Manirampur','Sharsha'],
    'Jhenaidah':['Jhenaidah Sadar','Harinakunda','Kaliganj','Kotchandpur','Maheshpur','Shailkupa'],
    'Khulna':['Khulna Sadar','Batiaghata','Dacope','Dumuria','Dighalia','Koyra','Paikgachha','Phultala','Rupsa','Terokhada'],
    'Kushtia':['Kushtia Sadar','Bheramara','Daulatpur','Khoksa','Kumarkhali','Mirpur'],
    'Magura':['Magura Sadar','Mohammadpur','Shalikha','Sreepur'],
    'Meherpur':['Meherpur Sadar','Gangni','Mujibnagar'],
    'Narail':['Narail Sadar','Kalia','Lohagara'],
    'Satkhira':['Satkhira Sadar','Assasuni','Debhata','Kalaroa','Kaliganj','Shyamnagar','Tala'],
    'Barguna':['Barguna Sadar','Amtali','Bamna','Betagi','Patharghata','Taltali'],
    'Barishal':['Barishal Sadar','Agailjhara','Babuganj','Bakerganj','Banaripara','Gaurnadi','Hizla','Mehendiganj','Muladi','Wazirpur'],
    'Bhola':['Bhola Sadar','Borhanuddin','Char Fasson','Daulatkhan','Lalmohan','Manpura','Tazumuddin'],
    'Jhalokati':['Jhalokati Sadar','Kathalia','Nalchity','Rajapur'],
    'Patuakhali':['Patuakhali Sadar','Bauphal','Dashmina','Dumki','Galachipa','Kalapara','Mirzaganj','Rangabali'],
    'Pirojpur':['Pirojpur Sadar','Bhandaria','Kaukhali','Mathbaria','Nazirpur','Nesarabad','Zianagar'],
    'Habiganj':['Habiganj Sadar','Ajmiriganj','Bahubal','Baniyachong','Chunarughat','Lakhai','Madhabpur','Nabiganj'],
    'Moulvibazar':['Moulvibazar Sadar','Barlekha','Juri','Kamalganj','Kulaura','Rajnagar','Sreemangal'],
    'Sunamganj':['Sunamganj Sadar','Bishwamvarpur','Chhatak','Derai','Dharmapasha','Dowarabazar','Jagannathpur','Jamalganj','Sulla','Tahirpur'],
    'Sylhet':['Sylhet Sadar','Balaganj','Beanibazar','Bishwanath','Companiganj','Fenchuganj','Golapganj','Gowainghat','Jaintiapur','Kanaighat','Osmani Nagar','Zakiganj'],
    'Dinajpur':['Dinajpur Sadar','Birampur','Birganj','Biral','Bochaganj','Chirirbandar','Fulbari','Ghoraghat','Hakimpur','Kaharole','Khansama','Nawabganj','Parbatipur'],
    'Gaibandha':['Gaibandha Sadar','Fulchhari','Gobindaganj','Palashbari','Sadullapur','Saghata','Sundarganj'],
    'Kurigram':['Kurigram Sadar','Bhurungamari','Char Rajibpur','Chilmari','Phulbari','Nageshwari','Rajarhat','Raomari','Ulipur'],
    'Lalmonirhat':['Lalmonirhat Sadar','Aditmari','Hatibandha','Kaliganj','Patgram'],
    'Nilphamari':['Nilphamari Sadar','Dimla','Domar','Jaldhaka','Kishoreganj','Saidpur'],
    'Panchagarh':['Panchagarh Sadar','Atwari','Boda','Debiganj','Tetulia'],
    'Rangpur':['Rangpur Sadar','Badarganj','Gangachara','Kaunia','Mithapukur','Pirgachha','Pirganj','Taraganj'],
    'Thakurgaon':['Thakurgaon Sadar','Baliadangi','Haripur','Pirganj','Ranisankail']
};

function populateDistrictSelect(){
    if(!el.district)return;

    const districts=Object.keys(
        bdDistrictCities
    ).sort();

    el.district.innerHTML=
        '<option value="">Select District</option>'+
        districts.map(d=>
            `<option value="${AdminUI.escapeHtml(d)}">${AdminUI.escapeHtml(d)}</option>`
        ).join('');
}

function populateCitySelect(district,selectedCity=''){
    if(!el.city)return;

    const cities=bdDistrictCities[district]??[];

    if(!district||!cities.length){
        el.city.innerHTML=
            '<option value="">Select District First</option>';

        el.city.disabled=true;
        return;
    }

    el.city.disabled=false;

    el.city.innerHTML=
        '<option value="">Select City</option>'+
        cities.map(c=>
            `<option value="${AdminUI.escapeHtml(c)}" ${c===selectedCity?'selected':''}>${AdminUI.escapeHtml(c)}</option>`
        ).join('');

    // Existing member data may hold a city that isn't in the list above
    // (older free-text entry) — keep it selectable so it isn't silently lost.
    if(
        selectedCity&&
        !cities.includes(selectedCity)
    ){
        el.city.insertAdjacentHTML(
            'beforeend',
            `<option value="${AdminUI.escapeHtml(selectedCity)}" selected>${AdminUI.escapeHtml(selectedCity)}</option>`
        );
    }
}

function cardsLoadingHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-500">
            ${AdminUI.escapeHtml(message)}
        </div>
    `;
}

function cardsEmptyHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-500">
            ${AdminUI.escapeHtml(message)}
        </div>
    `;
}

async function loadMembers(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState(
        'Loading members...',
        7
    );

    el.cards.innerHTML=cardsLoadingHtml(
        'Loading members...'
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

        el.cards.innerHTML=cardsEmptyHtml(
            AdminUI.extractError(error)
        );
    }
}

function memberActionButtons(member,{withLabel=false}={}){
    const buttons=[];

    if(canViewShares&&shareEnabled){
        buttons.push(`
            <button
                type="button"
                onclick="openShareModal(${member.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                title="Shares">
                <i class="bi bi-layers text-sm"></i>
                ${withLabel?'Shares':''}
            </button>
        `);
    }

    if(canEditMember){
        buttons.push(`
            <button
                type="button"
                onclick="editMember(${member.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                title="Edit">
                <i class="bi bi-pencil-square text-sm"></i>
                ${withLabel?'Edit':''}
            </button>
        `);
    }

    if(canDeleteMember&&member.status!=='active'){
        buttons.push(`
            <button
                type="button"
                onclick="deleteMember(${member.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-red-50 text-red-600 transition hover:bg-red-100"
                title="Delete">
                <i class="bi bi-trash text-sm"></i>
                ${withLabel?'Delete':''}
            </button>
        `);
    }

    return buttons.join('');
}

function renderMembers(){
    if(!members.length){
        el.table.innerHTML=AdminUI.emptyState(
            'No members found.',
            7
        );

        el.cards.innerHTML=cardsEmptyHtml(
            'No members found.'
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
                                        <span class="text-sm font-bold text-slate-500">
                                            ${AdminUI.escapeHtml(initial)}
                                        </span>
                                    `
                            }
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                                ${AdminUI.escapeHtml(user.name??'N/A')}
                            </p>

                            <p class="mt-0.5 truncate text-[10px] text-slate-500">
                                ${AdminUI.escapeHtml(user.email??'')}
                            </p>
                        </div>
                    </div>
                </td>

                <td class="px-3 py-3">
                    <p class="truncate font-mono text-xs 2xl:text-sm font-semibold text-indigo-600">
                        ${AdminUI.escapeHtml(member.member_code??'N/A')}
                    </p>
                </td>

                <td class="px-3 py-3">
                    <p class="truncate text-xs 2xl:text-sm text-slate-600">
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
                            :'<span class="text-xs 2xl:text-sm text-slate-500">No roles</span>'
                    }
                </td>

                <td class="px-3 py-3 text-xs 2xl:text-sm text-slate-600">
                    ${AdminUI.formatDate(member.joining_date)}
                </td>

                <td class="px-3 py-3">
                    ${AdminUI.statusBadge(member.status)}
                </td>

                <td class="px-3 py-3">
                    <div class="flex items-center justify-end gap-1">
                        ${memberActionButtons(member)}
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    el.cards.innerHTML=members.map(member=>{
        const user=member.user??{};
        const roles=user.roles??[];
        const photoUrl=getProfilePhotoUrl(member);

        const initial=String(
            user.name??'M'
        ).trim().charAt(0).toUpperCase();

        return`
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-50">
                            ${
                                photoUrl
                                    ?`
                                        <img
                                            src="${AdminUI.escapeHtml(photoUrl)}"
                                            class="h-full w-full object-cover"
                                            alt="${AdminUI.escapeHtml(user.name??'Member')}">
                                    `
                                    :`
                                        <span class="text-sm font-bold text-slate-500">
                                            ${AdminUI.escapeHtml(initial)}
                                        </span>
                                    `
                            }
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                                ${AdminUI.escapeHtml(user.name??'N/A')}
                            </p>
                            <p class="mt-0.5 truncate text-[11px] text-slate-500">
                                ${AdminUI.escapeHtml(user.email??'')}
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        ${AdminUI.statusBadge(member.status)}
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 rounded-md bg-slate-50/60 p-3 text-[11px]">
                    <div class="min-w-0">
                        <p class="text-slate-500 text-xs 2xl:text-sm">Code</p>
                        <p class="truncate font-mono font-semibold text-indigo-600">
                            ${AdminUI.escapeHtml(member.member_code??'N/A')}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-500 text-xs 2xl:text-sm">Phone</p>
                        <p class="truncate font-medium text-slate-700">
                            ${AdminUI.escapeHtml(
                                member.phone||
                                user.mobile||
                                'N/A'
                            )}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-500 text-xs 2xl:text-sm">Joining</p>
                        <p class="truncate font-medium text-slate-700">
                            ${AdminUI.formatDate(member.joining_date)}
                        </p>
                    </div>

                    <div class="min-w-0">
                        <p class="text-slate-500 text-xs 2xl:text-sm">Roles</p>
                        <div class="mt-0.5">
                            ${
                                roles.length
                                    ?roles.slice(0,2).map(role=>`
                                        <span class="mb-1 mr-1 inline-block rounded-md bg-violet-50 px-1.5 py-0.5 text-[9px] font-semibold text-violet-700">
                                            ${AdminUI.escapeHtml(
                                                role.display_name||
                                                role.name
                                            )}
                                        </span>
                                    `).join('')
                                    :'<span class="text-slate-500 text-xs 2xl:text-sm">No roles</span>'
                            }
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3">
                    ${memberActionButtons(member,{withLabel:true})}
                </div>
            </div>
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
        'mt-1.5 text-[10px] text-slate-500';
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

function resetNidDocument(){
    if(!el.nidDocument)return;

    el.nidDocument.value='';

    if(el.nidDocumentButtonText){
        el.nidDocumentButtonText.textContent=
            'Choose File';
    }

    if(el.nidDocumentHelp){
        el.nidDocumentHelp.textContent=
            'JPG, PNG or PDF. Max 5MB.';

        el.nidDocumentHelp.className=
            'mt-1.5 text-[10px] text-slate-500';
    }
}

function showExistingNidDocument(member){
    if(
        !el.nidDocumentButtonText||
        !member?.nid_document_url
    ){
        return;
    }

    el.nidDocumentButtonText.textContent=
        'Replace Document';

    if(el.nidDocumentHelp){
        el.nidDocumentHelp.innerHTML=
            `<a href="${AdminUI.escapeHtml(member.nid_document_url)}" target="_blank" class="text-indigo-600 underline">View current document</a>`;
    }
}

if(el.nidDocument){
    el.nidDocument.addEventListener(
        'change',
        function(){
            const file=this.files?.[0];

            if(!file){
                return;
            }

            if(file.size>5*1024*1024){
                Toast.error(
                    'NID/Birth registration document must be 5MB or smaller.'
                );

                this.value='';
                return;
            }

            const allowed=[
                'image/jpeg',
                'image/png',
                'application/pdf'
            ];

            if(!allowed.includes(file.type)){
                Toast.error(
                    'Only JPG, PNG or PDF files are allowed.'
                );

                this.value='';
                return;
            }

            if(el.nidDocumentButtonText){
                el.nidDocumentButtonText.textContent=
                    'Change Document';
            }

            if(el.nidDocumentHelp){
                el.nidDocumentHelp.textContent=
                    file.name;

                el.nidDocumentHelp.className=
                    'mt-1.5 truncate text-[10px] text-emerald-600';
            }
        }
    );
}

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

    resetNidDocument();

    if(member){
        const user=member.user??{};

        el.name.value=user.name??'';
        el.email.value=user.email??'';
        el.mobile.value=user.mobile??'';

        el.alternatePhone.value=
            member.alternate_phone??'';

        el.fatherOrHusbandName.value=
            member.father_or_husband_name??'';

        el.motherName.value=
            member.mother_name??'';

        el.gender.value=
            member.gender??'';

        el.nidOrBirthRegNo.value=
            member.nid_or_birth_reg_no??'';

        el.profession.value=
            member.profession??'';

        el.district.value=
            member.district??'';

        populateCitySelect(
            member.district??'',
            member.city??''
        );

        el.address.value=
            member.address??'';

        el.permanentAddress.value=
            member.permanent_address??'';

        el.notes.value=
            member.notes??'';

        setPickerDate(
            el.dateOfBirth,
            member.date_of_birth
        );

        showExistingProfilePhoto(
            member
        );

        showExistingNidDocument(
            member
        );
    }else{
        populateCitySelect('');
    }

    if(el.initialShareSection){
        el.initialShareSection.classList.toggle(
            'hidden',
            Boolean(member)
        );

        if(el.initialShareAmount){
            el.initialShareAmount.value=
                defaultShareValue||
                '';
        }
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
            alternate_phone:
                el.alternatePhone.value.trim()||
                null,

            father_or_husband_name:
                el.fatherOrHusbandName.value.trim()||
                null,

            mother_name:
                el.motherName.value.trim()||
                null,

            date_of_birth:
                el.dateOfBirth.value||
                null,

            gender:
                el.gender.value||
                null,

            nid_or_birth_reg_no:
                el.nidOrBirthRegNo.value.trim()||
                null,

            profession:
                el.profession.value.trim()||
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

            permanent_address:
                el.permanentAddress.value.trim()||
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
        }

        if(
            !AdminUI.validateForm(
                'memberForm',
                {
                    alternate_phone:
                        'A valid 11 digit alternate mobile number is required.'
                }
            )
        ){
            return;
        }

        if(!editing){
            if(!el.profilePhoto.files?.[0]){
                AdminUI.showFieldError(
                    'profile_photo',
                    'Profile photo is required.'
                );

                return;
            }

            if(!el.nidDocument?.files?.[0]){
                AdminUI.showFieldError(
                    'nid_document',
                    'NID / Birth registration document is required.'
                );

                return;
            }

            if(
                el.initialShareSection&&
                !el.initialShareSection.classList.contains('hidden')
            ){
                const initialAmount=Number(
                    el.initialShareAmount?.value
                );

                if(
                    !Number.isFinite(initialAmount)||
                    initialAmount<=0
                ){
                    AdminUI.showError(
                        'formError',
                        'Initial share amount is required.'
                    );

                    return;
                }

                data.initial_share_amount=
                    initialAmount;

                data.initial_share_payment_method=
                    el.initialSharePaymentMethod?.value||
                    'cash';
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

        if(
            el.nidDocument?.files?.[0]
        ){
            formData.append(
                'nid_document',
                el.nidDocument.files[0]
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
            const hasFieldErrors=
                AdminUI.showValidationErrors(
                    el.form,
                    error
                );

            if(!hasFieldErrors){
                AdminUI.showError(
                    'formError',
                    AdminUI.extractError(error)
                );
            }
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
                        <span class="font-mono text-sm font-semibold text-indigo-600">
                            ${AdminUI.escapeHtml(
                                share.share_no??'—'
                            )}
                        </span>
                    </td>

                    <td class="px-4 py-3 text-right text-sm font-semibold text-slate-700">
                        ${money(
                            share.purchase_amount
                        )}
                    </td>

                    <td class="px-4 py-3 text-xs 2xl:text-sm text-slate-600">
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
                        <p class="text-sm font-medium text-slate-600">
                            ${AdminUI.escapeHtml(
                                share.creator?.name??
                                'System'
                            )}
                        </p>
                    </td>

                    <td class="max-w-[200px] px-4 py-3">
                        <p
                            class="truncate text-sm text-slate-500"
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
                const hasFieldErrors=
                    AdminUI.showValidationErrors(
                        shareForm,
                        error,
                        {
                            purchase_amount:
                                'share_purchase_amount',

                            acquired_date:
                                'share_acquired_date',

                            payment_method:
                                'share_payment_method',

                            notes:
                                'share_notes'
                        }
                    );

                if(!hasFieldErrors){
                    AdminUI.showError(
                        'shareFormError',
                        AdminUI.extractError(
                            error
                        )
                    );
                }
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

    if(el.district){
        populateDistrictSelect();

        el.district.addEventListener(
            'change',
            ()=>populateCitySelect(el.district.value)
        );
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