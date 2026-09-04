@extends('layouts.admin')

@section('title','Meetings')
@section('page_title','Meetings')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Meeting Management</h1>
                <p class="text-xs text-slate-500">Manage meetings, agendas, attendees, decisions, minutes and expenses.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Meeting.create'))
        <button type="button" onclick="openMeetingModal()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Meeting
        </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">
        @php
            $stats=[
                [
                    'id'=>'totalMeetings',
                    'label'=>'Total Meetings',
                    'icon'=>'bi-calendar3',
                    'box'=>'border-slate-200 bg-white',
                    'text'=>'text-slate-800',
                    'iconbox'=>'bg-slate-100 text-slate-500'
                ],
                [
                    'id'=>'scheduledMeetings',
                    'label'=>'Scheduled',
                    'icon'=>'bi-calendar-check',
                    'box'=>'border-indigo-200 bg-indigo-50/40',
                    'text'=>'text-indigo-700',
                    'iconbox'=>'bg-indigo-100 text-indigo-600'
                ],
                [
                    'id'=>'ongoingMeetings',
                    'label'=>'Ongoing',
                    'icon'=>'bi-broadcast',
                    'box'=>'border-amber-200 bg-amber-50/40',
                    'text'=>'text-amber-700',
                    'iconbox'=>'bg-amber-100 text-amber-600'
                ],
                [
                    'id'=>'completedMeetings',
                    'label'=>'Completed',
                    'icon'=>'bi-check2-circle',
                    'box'=>'border-emerald-200 bg-emerald-50/40',
                    'text'=>'text-emerald-700',
                    'iconbox'=>'bg-emerald-100 text-emerald-600'
                ],
                [
                    'id'=>'actualExpense',
                    'label'=>'Actual Expense',
                    'icon'=>'bi-cash-stack',
                    'box'=>'border-sky-200 bg-sky-50/40 col-span-2 xl:col-span-1',
                    'text'=>'text-sky-700',
                    'iconbox'=>'bg-sky-100 text-sky-600'
                ]
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="rounded-md border p-4 {{ $stat['box'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs text-slate-500">{{ $stat['label'] }}</p>
                    <p id="{{ $stat['id'] }}" class="mt-2 truncate text-xl font-bold {{ $stat['text'] }}">
                        @if($stat['id']==='actualExpense')
                            {{ setting('currency_symbol','৳') }}0
                        @else
                            0
                        @endif
                    </p>
                </div>
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md {{ $stat['iconbox'] }}">
                    <i class="bi {{ $stat['icon'] }} text-base"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Meetings</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by meeting number, title or venue.</p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:w-auto xl:grid-cols-[280px_145px_145px_100px_auto] xl:gap-0">
                <div class="relative sm:col-span-2 xl:col-span-1">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search meeting..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 xl:rounded-r-none">
                </div>

                <select id="typeFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                    <option value="">All Types</option>
                    <option value="general">General</option>
                    <option value="annual">Annual</option>
                    <option value="executive">Executive</option>
                    <option value="emergency">Emergency</option>
                    <option value="special">Special</option>
                </select>

                <select id="statusFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select id="yearFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                </select>

                <button type="button" onclick="clearFilters()"
                    class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Meeting</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Date & Time</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Venue</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-600">Attendees</th>
                        <th class="px-4 py-3 text-center text-sm font-semibold text-slate-600">Decisions</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Expense</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="meetingTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-base text-slate-400">
                            Loading meetings...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile Cards --}}
    <div id="meetingMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-base text-slate-400">
            Loading meetings...
        </div>
    </div>

    <div id="paginationWrap" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- ================================================================
CREATE / EDIT MEETING MODAL
================================================================ --}}
<div id="meetingModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-people"></i>
                </div>
                <div>
                    <h3 id="meetingModalTitle" class="text-base font-semibold text-slate-800">Add Meeting</h3>
                    <p class="text-xs text-slate-500">Create and organize an association meeting.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('meetingModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="meetingForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="meetingError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="form-label">Meeting Title <span class="text-red-500">*</span></label>
                        <input id="meetingTitle" type="text" maxlength="255" class="app-input w-full">
                        <p data-field-error="meetingTitle" class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Meeting Type <span class="text-red-500">*</span></label>
                        <select id="meetingType" class="app-input w-full">
                            <option value="general">General</option>
                            <option value="annual">Annual</option>
                            <option value="executive">Executive</option>
                            <option value="emergency">Emergency</option>
                            <option value="special">Special</option>
                        </select>
                        <p data-field-error="meetingType" class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Meeting Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="meetingDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                        <p data-field-error="meetingDate" class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">Start Time</label>
                        <input id="meetingStartTime" type="time" class="app-input w-full">
                        <p data-field-error="meetingStartTime" class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div>
                        <label class="form-label">End Time</label>
                        <input id="meetingEndTime" type="time" class="app-input w-full">
                        <p data-field-error="meetingEndTime" class="mt-1 hidden text-sm text-red-600"></p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Venue</label>
                        <input id="meetingVenue" type="text" maxlength="255" class="app-input w-full">
                    </div>

                    <div>
                        <label class="form-label">Budget Amount</label>
                        <input id="meetingBudget" type="number" min="0" step="0.01" class="app-input w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="meetingDescription" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Notes</label>
                        <textarea id="meetingNotes" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="AdminUI.closeModal('meetingModal')"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Close
                </button>

                <button id="saveMeetingButton" type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Save Meeting
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
DETAILS MODAL
================================================================ --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[96vh] w-full max-w-6xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-card-list"></i>
                </div>
                <div>
                    <h3 id="detailsTitle" class="text-base font-semibold text-slate-800">Meeting Details</h3>
                    <p id="detailsSubtitle" class="text-sm text-slate-500">Meeting management and activity.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('detailsModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto">
            <div id="detailsContent" class="p-4 sm:p-5"></div>
        </div>
    </div>
</div>

{{-- ================================================================
AGENDA MODAL
================================================================ --}}
<div id="agendaModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 id="agendaModalTitle" class="text-base font-semibold text-slate-800">Add Agenda</h3>
                <p class="text-xs text-slate-500">Add or update a meeting agenda item.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('agendaModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="agendaForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="agendaId" type="hidden">

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="agendaError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input id="agendaTitle" type="text" maxlength="255" class="app-input w-full">
                    <p data-field-error="agendaTitle" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Sort Order</label>
                        <input id="agendaSortOrder" type="number" min="1" max="999" class="app-input w-full">
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select id="agendaStatus" class="app-input w-full">
                            <option value="pending">Pending</option>
                            <option value="discussed">Discussed</option>
                            <option value="deferred">Deferred</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="agendaDescription" rows="4" maxlength="5000" class="app-input w-full resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('agendaModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>
                <button id="saveAgendaButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Agenda
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
ATTENDEE MODAL
================================================================ --}}
<div id="attendeeModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 id="attendeeModalTitle" class="text-base font-semibold text-slate-800">Add Attendee</h3>
                <p class="text-xs text-slate-500">Invite a member or update attendance.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('attendeeModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="attendeeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="attendeeId" type="hidden">

            <div class="space-y-4 p-5">
                <div id="attendeeError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div id="attendeeMemberWrap">
                    <label class="form-label">Member <span class="text-red-500">*</span></label>
                    <select id="attendeeMemberId" class="app-input w-full">
                        <option value="">Select member</option>
                    </select>
                    <p data-field-error="attendeeMemberId" class="mt-1 hidden text-sm text-red-600"></p>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <select id="attendeeStatus" class="app-input w-full">
                        <option value="invited">Invited</option>
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="excused">Excused</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea id="attendeeNotes" rows="3" maxlength="3000" class="app-input w-full resize-none"></textarea>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('attendeeModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>
                <button id="saveAttendeeButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Attendee
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
DECISION MODAL
================================================================ --}}
<div id="decisionModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 id="decisionModalTitle" class="text-base font-semibold text-slate-800">Add Decision</h3>
                <p class="text-xs text-slate-500">Record resolution, voting and responsibility.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('decisionModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="decisionForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="decisionId" type="hidden">

            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="decisionError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="form-label">Title <span class="text-red-500">*</span></label>
                        <input id="decisionTitle" maxlength="255" class="app-input w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Decision <span class="text-red-500">*</span></label>
                        <textarea id="decisionText" rows="4" maxlength="10000" class="app-input w-full resize-none"></textarea>
                    </div>

                    <div>
                        <label class="form-label">Related Agenda</label>
                        <select id="decisionAgendaId" class="app-input w-full">
                            <option value="">No specific agenda</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Result <span class="text-red-500">*</span></label>
                        <select id="decisionResult" class="app-input w-full">
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="deferred">Deferred</option>
                            <option value="noted">Noted</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Responsible User</label>
                        <select id="decisionResponsibleUser" class="app-input w-full">
                            <option value="">Not assigned</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Due Date</label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="decisionDueDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Status</label>
                        <select id="decisionStatus" class="app-input w-full">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-2 md:col-span-2">
                        <div>
                            <label class="form-label">Votes For</label>
                            <input id="votesFor" type="number" min="0" class="app-input w-full">
                        </div>
                        <div>
                            <label class="form-label">Against</label>
                            <input id="votesAgainst" type="number" min="0" class="app-input w-full">
                        </div>
                        <div>
                            <label class="form-label">Abstain</label>
                            <input id="votesAbstain" type="number" min="0" class="app-input w-full">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Completion Notes</label>
                        <textarea id="decisionCompletionNotes" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('decisionModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>
                <button id="saveDecisionButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Decision
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
MINUTES MODAL
================================================================ --}}
<div id="minutesModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Meeting Minutes</h3>
                <p class="text-xs text-slate-500">Record the official minutes of the meeting.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('minutesModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="minutesForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 p-5">
                <div id="minutesError" class="mb-4 hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <label class="form-label">Minutes</label>
                <textarea id="meetingMinutes" rows="14" maxlength="20000" class="app-input w-full resize-y"></textarea>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('minutesModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>
                <button id="saveMinutesButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Minutes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================
EXPENSE MODAL
================================================================ --}}
<div id="expenseModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800">Add Meeting Expense</h3>
                <p class="text-xs text-slate-500">Expense will automatically post to accounting.</p>
            </div>
            <button type="button" onclick="AdminUI.closeModal('expenseModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="expenseForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
                <div id="expenseError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="form-label">Category <span class="text-red-500">*</span></label>
                        <input id="expenseCategory" maxlength="100" class="app-input w-full" placeholder="Food, Venue, Transport...">
                    </div>

                    <div>
                        <label class="form-label">Amount <span class="text-red-500">*</span></label>
                        <input id="expenseAmount" type="number" min="0.01" step="0.01" class="app-input w-full">
                    </div>

                    <div>
                        <label class="form-label">Expense Account <span class="text-red-500">*</span></label>
                        <select id="expenseAccountId" class="app-input w-full">
                            <option value="">Select expense account</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Payment Account <span class="text-red-500">*</span></label>
                        <select id="paymentAccountId" class="app-input w-full">
                            <option value="">Select Cash/Bank</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Expense Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                            <input id="expenseDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Payee</label>
                        <input id="expensePayee" maxlength="255" class="app-input w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Reference No</label>
                        <input id="expenseReference" maxlength="150" class="app-input w-full">
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label">Description</label>
                        <textarea id="expenseDescription" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2 rounded-md border border-indigo-100 bg-indigo-50/50 px-3 py-2 text-[11px] text-indigo-700">
                        Journal: Dr selected Expense Account, Cr selected Cash/Bank.
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('expenseModal')" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>
                <button id="saveExpenseButton" type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Post Expense
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

<style>
.form-label{
    display:block;
    margin-bottom:.4rem;
    font-size:.8rem;
    font-weight:600;
    color:rgb(51 65 85);
}
</style>

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Meeting.update'));
const canDelete=@json(auth()->user()->hasPermission('Meeting.delete'));

let meetings=[];
let currentPage=1;
let selectedMeeting=null;
let editingMeeting=null;
let editingAgenda=null;
let editingAttendee=null;
let editingDecision=null;
let members=[];
let users=[];
let expenseAccounts=[];
let paymentAccounts=[];

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

const money=value=>`${currency}${Number(value||0).toLocaleString(undefined,{
    minimumFractionDigits:2,
    maximumFractionDigits:2
})}`;

const date=value=>value?AdminUI.formatDate(value):'—';

const today=()=>{
    const now=new Date();
    const offset=now.getTimezoneOffset();

    return new Date(
        now.getTime()-offset*60000
    ).toISOString().slice(0,10);
};

function setDate(id,value){
    const element=$(id);
    if(!element)return;

    const dateValue=value
        ?String(value).substring(0,10)
        :'';

    element.value=dateValue;

    if(element._flatpickr){
        dateValue
            ?element._flatpickr.setDate(dateValue,false,'Y-m-d')
            :element._flatpickr.clear();
    }
}

function formatType(value){
    return String(value||'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function formatTime(value){
    if(!value)return '—';

    const text=String(value).substring(0,5);
    const [hour,minute]=text.split(':').map(Number);

    if(!Number.isFinite(hour))return text;

    const suffix=hour>=12?'PM':'AM';
    const h=hour%12||12;

    return `${h}:${String(minute||0).padStart(2,'0')} ${suffix}`;
}

function memberName(member){
    return[
        member.member_code,
        member.user?.name
    ].filter(Boolean).join(' - ');
}

function accountName(account){
    return[
        account?.code,
        account?.name
    ].filter(Boolean).join(' - ');
}

function meetingTime(meeting){
    if(!meeting.start_time&&!meeting.end_time)return '—';

    if(meeting.start_time&&meeting.end_time){
        return `${formatTime(meeting.start_time)} - ${formatTime(meeting.end_time)}`;
    }

    return formatTime(
        meeting.start_time||
        meeting.end_time
    );
}

function nextStatuses(status){
    return{
        draft:['scheduled','cancelled'],
        scheduled:['ongoing','cancelled'],
        ongoing:['completed','cancelled'],
        completed:[],
        cancelled:[]
    }[status]||[];
}

async function loadOptions(){
    try{
        const response=await api('/api/meetings/options');
        const data=response.data||{};

        members=data.members||[];
        users=data.users||[];
        expenseAccounts=data.expense_accounts||[];
        paymentAccounts=data.payment_accounts||[];

        $('attendeeMemberId').innerHTML=
            `<option value="">Select member</option>`+
            members.map(member=>`
                <option value="${member.id}">
                    ${esc(memberName(member))}
                </option>
            `).join('');

        $('decisionResponsibleUser').innerHTML=
            `<option value="">Not assigned</option>`+
            users.map(user=>`
                <option value="${user.id}">
                    ${esc(user.name)}
                </option>
            `).join('');

        $('expenseAccountId').innerHTML=
            `<option value="">Select expense account</option>`+
            expenseAccounts.map(account=>`
                <option value="${account.id}">
                    ${esc(accountName(account))}
                </option>
            `).join('');

        $('paymentAccountId').innerHTML=
            `<option value="">Select Cash/Bank</option>`+
            paymentAccounts.map(account=>`
                <option value="${account.id}">
                    ${esc(accountName(account))}
                </option>
            `).join('');
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/meetings/statistics');
        const data=response.data||{};

        $('totalMeetings').textContent=data.total||0;
        $('scheduledMeetings').textContent=data.scheduled||0;
        $('ongoingMeetings').textContent=data.ongoing||0;
        $('completedMeetings').textContent=data.completed||0;
        $('actualExpense').textContent=money(data.actual_expense);
    }catch(error){
        console.error(error);
    }
}

function initYears(){
    const current=new Date().getFullYear();
    let options=`<option value="">All Years</option>`;

    for(let year=current+1;year>=current-10;year--){
        options+=`<option value="${year}">${year}</option>`;
    }

    $('yearFilter').innerHTML=options;
}

async function loadMeetings(page=1){
    currentPage=page;

    const tbody=$('meetingTableBody');
    const grid=$('meetingMobileGrid');

    tbody.innerHTML=AdminUI.loadingState('Loading meetings...',9);

    grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-base text-slate-400">
            <div class="flex items-center justify-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading meetings...
            </div>
        </div>
    `;

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const type=$('typeFilter').value;
    const status=$('statusFilter').value;
    const year=$('yearFilter').value;

    if(search)params.set('search',search);
    if(type)params.set('type',type);
    if(status)params.set('status',status);
    if(year)params.set('year',year);

    try{
        const response=await api(
            `/api/meetings?${params.toString()}`
        );

        const paginator=response.data||{};
        meetings=paginator.data||[];

        renderMeetings();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadMeetings
        );
    }catch(error){
        const message=AdminUI.extractError(error);

        tbody.innerHTML=
            AdminUI.emptyState(message,9);

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(message)}
            </div>
        `;
    }
}

function meetingActions(meeting){
    const actions=[
        `
        <button type="button"
            onclick="viewMeeting(${meeting.id})"
            class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-eye"></i>
            View
        </button>
        `
    ];

    if(
        canUpdate&&
        !['completed','cancelled'].includes(meeting.status)
    ){
        actions.push(`
            <button type="button"
                onclick="editMeeting(${meeting.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <i class="bi bi-pencil"></i>
                Edit
            </button>
        `);
    }

    if(canUpdate&&meeting.status==='draft'){
        actions.push(`
            <button type="button"
                onclick="scheduleMeeting(${meeting.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-sky-200 bg-sky-50 px-2.5 py-1.5 text-[11px] font-semibold text-sky-700 transition hover:bg-sky-100">
                <i class="bi bi-calendar-check"></i>
                Schedule
            </button>
        `);
    }

    if(
        canDelete&&
        ['draft','cancelled'].includes(meeting.status)&&
        Number(meeting.actual_expense||0)===0
    ){
        actions.push(`
            <button type="button"
                onclick="deleteMeeting(${meeting.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50">
                <i class="bi bi-trash3"></i>
                Delete
            </button>
        `);
    }

    return actions.join('');
}

function renderMeetings(){
    const tbody=$('meetingTableBody');
    const grid=$('meetingMobileGrid');

    if(!meetings.length){
        tbody.innerHTML=
            AdminUI.emptyState(
                'No meetings found.',
                9
            );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-inbox"></i>
                </div>
                <p class="mt-3 text-base font-semibold text-slate-600">
                    No meetings found
                </p>
                <p class="mt-1 text-sm text-slate-400">
                    Try changing your filters.
                </p>
            </div>
        `;

        return;
    }

    tbody.innerHTML=
        meetings.map(meeting=>`
            <tr class="transition hover:bg-slate-50/70">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-700">
                                ${esc(meeting.meeting_no)}
                            </div>
                            <div class="mt-0.5 max-w-[220px] truncate text-sm text-slate-400">
                                ${esc(meeting.title)}
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-4 py-3 text-slate-500">
                    ${esc(formatType(meeting.type))}
                </td>

                <td class="px-4 py-3">
                    <div class="text-sm text-slate-600">
                        ${date(meeting.meeting_date)}
                    </div>
                    <div class="mt-0.5 text-[10px] text-slate-400">
                        ${esc(meetingTime(meeting))}
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="max-w-[170px] truncate text-sm text-slate-500">
                        ${esc(meeting.venue||'—')}
                    </div>
                </td>

                <td class="px-4 py-3 text-center font-medium text-slate-600">
                    ${meeting.attendees_count||0}
                </td>

                <td class="px-4 py-3 text-center font-medium text-slate-600">
                    ${meeting.decisions_count||0}
                </td>

                <td class="px-4 py-3 text-right">
                    <span class="font-semibold text-slate-700">
                        ${money(meeting.actual_expense)}
                    </span>
                </td>

                <td class="px-4 py-3">
                    ${AdminUI.statusBadge(meeting.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-1">
                        ${meetingActions(meeting)}
                    </div>
                </td>
            </tr>
        `).join('');

    grid.innerHTML=
        meetings.map(meeting=>`
            <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-700">
                                    ${esc(meeting.title)}
                                </p>
                                <p class="mt-0.5 text-[11px] font-medium text-indigo-600">
                                    ${esc(meeting.meeting_no)}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(meeting.status)}
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Attendees
                            </p>
                            <p class="mt-1 text-sm font-bold text-slate-700">
                                ${meeting.attendees_count||0}
                            </p>
                        </div>

                        <div class="rounded-md bg-sky-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-600">
                                Expense
                            </p>
                            <p class="mt-1 truncate text-sm font-bold text-sky-700">
                                ${money(meeting.actual_expense)}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-3 border-t border-slate-100 pt-3 text-sm">
                        <div>
                            <p class="text-[10px] text-slate-400">Type</p>
                            <p class="mt-0.5 font-medium text-slate-600">
                                ${esc(formatType(meeting.type))}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Date</p>
                            <p class="mt-0.5 font-medium text-slate-600">
                                ${date(meeting.meeting_date)}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Time</p>
                            <p class="mt-0.5 font-medium text-slate-600">
                                ${esc(meetingTime(meeting))}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">Decisions</p>
                            <p class="mt-0.5 font-medium text-slate-600">
                                ${meeting.decisions_count||0}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-[10px] text-slate-400">Venue</p>
                            <p class="mt-0.5 truncate font-medium text-slate-600">
                                ${esc(meeting.venue||'—')}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${meetingActions(meeting)}
                </div>
            </article>
        `).join('');
}

window.openMeetingModal=function(){
    editingMeeting=null;

    $('meetingForm').reset();
    AdminUI.clearError('meetingError');
    AdminUI.clearFieldErrors('meetingForm');

    $('meetingModalTitle').textContent='Add Meeting';
    $('meetingType').value='general';
    $('meetingBudget').value='0';

    AdminUI.openModal('meetingModal');
    window.initDatePickers?.();

    setDate('meetingDate',today());
};

window.editMeeting=async function(id){
    try{
        const response=await api(`/api/meetings/${id}`);
        const meeting=response.data;

        editingMeeting=meeting;

        $('meetingForm').reset();
        AdminUI.clearError('meetingError');
        AdminUI.clearFieldErrors('meetingForm');

        $('meetingModalTitle').textContent='Edit Meeting';
        $('meetingTitle').value=meeting.title||'';
        $('meetingType').value=meeting.type||'general';
        $('meetingStartTime').value=String(meeting.start_time||'').substring(0,5);
        $('meetingEndTime').value=String(meeting.end_time||'').substring(0,5);
        $('meetingVenue').value=meeting.venue||'';
        $('meetingBudget').value=meeting.budget_amount||0;
        $('meetingDescription').value=meeting.description||'';
        $('meetingNotes').value=meeting.notes||'';

        AdminUI.openModal('meetingModal');
        window.initDatePickers?.();

        setDate(
            'meetingDate',
            meeting.meeting_date
        );
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

$('meetingForm').addEventListener('submit',async event=>{
    event.preventDefault();

    AdminUI.clearError('meetingError');
    AdminUI.clearFieldErrors('meetingForm');

    const required={
        meetingTitle:'Meeting title is required.',
        meetingType:'Meeting type is required.',
        meetingDate:'Meeting date is required.'
    };

    if(
        !AdminUI.validateForm(
            'meetingForm',
            required
        )
    ){
        return;
    }

    const startTime=$('meetingStartTime').value;
    const endTime=$('meetingEndTime').value;

    if(startTime&&endTime&&endTime<=startTime){
        AdminUI.showFieldError(
            'meetingEndTime',
            'End time must be after start time.'
        );
        return;
    }

    const data={
        title:$('meetingTitle').value.trim(),
        type:$('meetingType').value,
        meeting_date:$('meetingDate').value,
        start_time:startTime||null,
        end_time:endTime||null,
        venue:$('meetingVenue').value.trim()||null,
        description:$('meetingDescription').value.trim()||null,
        budget_amount:Number($('meetingBudget').value||0),
        notes:$('meetingNotes').value.trim()||null
    };

    const button=$('saveMeetingButton');

    AdminUI.setLoading(
        button,
        editingMeeting?'Updating...':'Saving...'
    );

    try{
        await api(
            editingMeeting
                ?`/api/meetings/${editingMeeting.id}`
                :'/api/meetings',
            {
                method:editingMeeting?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('meetingModal');

        Toast.success(
            editingMeeting
                ?'Meeting updated successfully.'
                :'Meeting created successfully.'
        );

        await Promise.all([
            loadMeetings(
                editingMeeting
                    ?currentPage
                    :1
            ),
            loadStatistics()
        ]);
    }catch(error){
        if(
            !AdminUI.showValidationErrors(
                'meetingForm',
                error,
                {
                    title:'meetingTitle',
                    type:'meetingType',
                    meeting_date:'meetingDate',
                    start_time:'meetingStartTime',
                    end_time:'meetingEndTime'
                }
            )
        ){
            AdminUI.showError(
                'meetingError',
                AdminUI.extractError(error)
            );
        }
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.scheduleMeeting=function(id){
    AdminUI.confirm({
        title:'Schedule Meeting',
        message:'Schedule this meeting now?',
        confirmText:'Schedule',
        confirmClass:'bg-indigo-600 hover:bg-indigo-700',
        onConfirm:async()=>{
            try{
                await api(
                    `/api/meetings/${id}/schedule`,
                    {method:'POST'}
                );

                Toast.success(
                    'Meeting scheduled successfully.'
                );

                await Promise.all([
                    loadMeetings(currentPage),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(error)
                );
            }
        }
    });
};

window.changeMeetingStatus=function(status){
    if(!selectedMeeting)return;

    AdminUI.confirm({
        title:'Update Meeting Status',
        message:`Change meeting status to ${formatType(status)}?`,
        confirmText:'Update',
        confirmClass:
            status==='cancelled'
                ?'bg-red-600 hover:bg-red-700'
                :'bg-indigo-600 hover:bg-indigo-700',
        onConfirm:async()=>{
            try{
                await api(
                    `/api/meetings/${selectedMeeting.id}/status`,
                    {
                        method:'PUT',
                        body:JSON.stringify({status})
                    }
                );

                Toast.success(
                    'Meeting status updated.'
                );

                await Promise.all([
                    refreshDetails(),
                    loadMeetings(currentPage),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(error)
                );
            }
        }
    });
};

window.deleteMeeting=function(id){
    const meeting=meetings.find(
        item=>Number(item.id)===Number(id)
    );

    AdminUI.deleteRequest(
        `/api/meetings/${id}`,
        {
            message:
                `Delete "${meeting?.title||'this meeting'}" permanently?`,
            successMessage:
                'Meeting deleted successfully.',
            onSuccess:async()=>{
                await Promise.all([
                    loadMeetings(
                        meetings.length===1&&currentPage>1
                            ?currentPage-1
                            :currentPage
                    ),
                    loadStatistics()
                ]);
            }
        }
    );
};

function detailsToolbar(meeting){
    if(!canUpdate)return '';

    const statuses=nextStatuses(
        meeting.status
    );

    return`
        <div class="flex flex-wrap gap-1.5">
            ${
                meeting.status==='draft'
                    ?`
                    <button type="button"
                        onclick="scheduleMeetingFromDetails()"
                        class="rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">
                        <i class="bi bi-calendar-check me-1"></i>
                        Schedule
                    </button>
                    `
                    :''
            }

            ${statuses
                .filter(status=>status!=='scheduled')
                .map(status=>`
                    <button type="button"
                        onclick="changeMeetingStatus('${status}')"
                        class="rounded-md border ${
                            status==='cancelled'
                                ?'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                :status==='completed'
                                    ?'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    :'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100'
                        } px-2.5 py-1.5 text-[11px] font-semibold">
                        ${esc(formatType(status))}
                    </button>
                `).join('')}
        </div>
    `;
}

window.scheduleMeetingFromDetails=async function(){
    if(!selectedMeeting)return;

    try{
        await api(
            `/api/meetings/${selectedMeeting.id}/schedule`,
            {method:'POST'}
        );

        Toast.success(
            'Meeting scheduled successfully.'
        );

        await Promise.all([
            refreshDetails(),
            loadMeetings(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.viewMeeting=async function(id){
    AdminUI.openModal('detailsModal');

    $('detailsContent').innerHTML=`
        <div class="py-16 text-center text-base text-slate-400">
            <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
            <p class="mt-2">Loading meeting details...</p>
        </div>
    `;

    try{
        const response=await api(
            `/api/meetings/${id}`
        );

        selectedMeeting=response.data;

        renderDetails();
    }catch(error){
        $('detailsContent').innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(AdminUI.extractError(error))}
            </div>
        `;
    }
};

async function refreshDetails(){
    if(!selectedMeeting)return;

    const response=await api(
        `/api/meetings/${selectedMeeting.id}`
    );

    selectedMeeting=response.data;
    renderDetails();
}

function renderDetails(){
    const meeting=selectedMeeting;

    $('detailsTitle').textContent=
        meeting.title||'Meeting Details';

    $('detailsSubtitle').textContent=
        `${meeting.meeting_no} • ${formatType(meeting.type)}`;

    const attendance=
        meeting.attendance_summary||{};

    const decisionSummary=
        meeting.decision_summary||{};

    $('detailsContent').innerHTML=`
        <div class="space-y-5">
            <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    ${AdminUI.statusBadge(meeting.status)}
                    <span class="text-sm text-slate-500">
                        ${date(meeting.meeting_date)}
                    </span>
                    <span class="text-sm text-slate-300">•</span>
                    <span class="text-sm text-slate-500">
                        ${esc(meetingTime(meeting))}
                    </span>
                </div>

                ${detailsToolbar(meeting)}
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                ${detailStat('Budget',money(meeting.budget_amount),'slate')}
                ${detailStat('Actual Expense',money(meeting.actual_expense),'sky')}
                ${detailStat('Budget Variance',money(meeting.budget_variance),Number(meeting.budget_variance)>=0?'emerald':'red')}
                ${detailStat('Present',attendance.present||0,'emerald')}
                ${detailStat('Decisions',decisionSummary.total||0,'indigo')}
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                        Meeting Information
                    </p>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        ${info('Type',formatType(meeting.type))}
                        ${info('Date',date(meeting.meeting_date))}
                        ${info('Start',formatTime(meeting.start_time))}
                        ${info('End',formatTime(meeting.end_time))}
                        ${info('Venue',meeting.venue||'—',true)}
                        ${info('Created By',meeting.creator?.name||'—')}
                    </div>

                    ${
                        meeting.description
                            ?`
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    Description
                                </p>
                                <p class="mt-1 whitespace-pre-wrap text-sm leading-5 text-slate-600">
                                    ${esc(meeting.description)}
                                </p>
                            </div>
                            `
                            :''
                    }
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                        Attendance Summary
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        ${miniCount('Invited',attendance.invited||0,'slate')}
                        ${miniCount('Present',attendance.present||0,'emerald')}
                        ${miniCount('Absent',attendance.absent||0,'red')}
                        ${miniCount('Excused',attendance.excused||0,'amber')}
                    </div>
                </div>
            </div>

            ${sectionHeader(
                'Agenda',
                'bi-list-check',
                canUpdate&&!['completed','cancelled'].includes(meeting.status)
                    ?`<button onclick="openAgendaModal()" class="section-action">
                        <i class="bi bi-plus-lg"></i> Add Agenda
                      </button>`
                    :''
            )}
            ${agendaHtml(meeting.agendas||[])}

            ${sectionHeader(
                'Attendees',
                'bi-people',
                canUpdate&&!['completed','cancelled'].includes(meeting.status)
                    ?`<button onclick="openAttendeeModal()" class="section-action">
                        <i class="bi bi-person-plus"></i> Add Attendee
                      </button>`
                    :''
            )}
            ${attendeesHtml(meeting.attendees||[])}

            ${sectionHeader(
                'Decisions',
                'bi-check2-square',
                canUpdate
                    ?`<button onclick="openDecisionModal()" class="section-action">
                        <i class="bi bi-plus-lg"></i> Add Decision
                      </button>`
                    :''
            )}
            ${decisionsHtml(meeting.decisions||[])}

            ${sectionHeader(
                'Meeting Minutes',
                'bi-journal-text',
                canUpdate
                    ?`<button onclick="openMinutesModal()" class="section-action">
                        <i class="bi bi-pencil"></i> Edit Minutes
                      </button>`
                    :''
            )}

            <div class="rounded-md border border-slate-200 bg-white p-4">
                ${
                    meeting.minutes
                        ?`
                        <div class="whitespace-pre-wrap text-base leading-6 text-slate-600">
                            ${esc(meeting.minutes)}
                        </div>
                        `
                        :`
                        <div class="py-6 text-center text-base text-slate-400">
                            No meeting minutes recorded.
                        </div>
                        `
                }
            </div>

            ${sectionHeader(
                'Expenses',
                'bi-cash-stack',
                canUpdate&&!['draft','cancelled'].includes(meeting.status)
                    ?`<button onclick="openExpenseModal()" class="section-action">
                        <i class="bi bi-plus-lg"></i> Add Expense
                      </button>`
                    :''
            )}
            ${expensesHtml(meeting.expenses||[])}
        </div>
    `;
}

function detailStat(label,value,tone){
    const map={
        slate:'border-slate-200 bg-slate-50 text-slate-700',
        indigo:'border-indigo-200 bg-indigo-50 text-indigo-700',
        emerald:'border-emerald-200 bg-emerald-50 text-emerald-700',
        red:'border-red-200 bg-red-50 text-red-700',
        sky:'border-sky-200 bg-sky-50 text-sky-700'
    };

    return`
        <div class="rounded-md border p-3 ${map[tone]||map.slate}">
            <p class="text-[10px] opacity-70">
                ${esc(label)}
            </p>
            <p class="mt-1 truncate text-sm font-bold">
                ${esc(value)}
            </p>
        </div>
    `;
}

function miniCount(label,value,tone){
    const map={
        slate:'bg-slate-50 text-slate-700',
        emerald:'bg-emerald-50 text-emerald-700',
        red:'bg-red-50 text-red-700',
        amber:'bg-amber-50 text-amber-700'
    };

    return`
        <div class="rounded-md p-3 ${map[tone]}">
            <p class="text-[10px] opacity-70">${esc(label)}</p>
            <p class="mt-1 text-lg font-bold">${value}</p>
        </div>
    `;
}

function info(label,value,wide=false){
    return`
        <div class="${wide?'col-span-2':''}">
            <p class="text-[10px] text-slate-400">${esc(label)}</p>
            <p class="mt-0.5 break-words font-medium text-slate-600">
                ${esc(value)}
            </p>
        </div>
    `;
}

function sectionHeader(title,icon,action=''){
    return`
        <div class="flex items-center justify-between gap-3 rounded-md border border-slate-200 bg-white px-4 py-3">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi ${icon}"></i>
                </div>
                <h4 class="text-base font-bold text-slate-700">
                    ${esc(title)}
                </h4>
            </div>
            ${action}
        </div>
    `;
}

function agendaHtml(items){
    if(!items.length){
        return emptyBlock(
            'No agenda items added.'
        );
    }

    return`
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="divide-y divide-slate-100">
                ${items.map(item=>`
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 gap-3">
                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-slate-100 text-sm font-bold text-slate-500">
                                    ${item.sort_order}
                                </div>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-slate-700">
                                            ${esc(item.title)}
                                        </p>
                                        ${AdminUI.statusBadge(item.status)}
                                    </div>
                                    ${
                                        item.description
                                            ?`<p class="mt-1 text-sm leading-5 text-slate-500">${esc(item.description)}</p>`
                                            :''
                                    }
                                </div>
                            </div>

                            ${
                                canUpdate&&!['completed','cancelled'].includes(selectedMeeting.status)
                                    ?`
                                    <div class="flex shrink-0 gap-1">
                                        <button onclick="editAgenda(${item.id})"
                                            class="icon-action text-indigo-600">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button onclick="deleteAgenda(${item.id})"
                                            class="icon-action text-red-600">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                    `
                                    :''
                            }
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function attendeesHtml(items){
    if(!items.length){
        return emptyBlock(
            'No attendees added.'
        );
    }

    return`
        <div class="grid gap-3 md:grid-cols-2">
            ${items.map(item=>`
                <div class="rounded-md border border-slate-200 bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="bi bi-person"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-700">
                                    ${esc(item.member?.user?.name||'Member')}
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    ${esc(item.member?.member_code||'')}
                                </p>
                            </div>
                        </div>

                        ${AdminUI.statusBadge(item.status)}
                    </div>

                    ${
                        item.notes
                            ?`<p class="mt-3 text-sm text-slate-500">${esc(item.notes)}</p>`
                            :''
                    }

                    ${
                        canUpdate&&!['completed','cancelled'].includes(selectedMeeting.status)
                            ?`
                            <div class="mt-3 flex gap-1 border-t border-slate-100 pt-2">
                                <button onclick="editAttendee(${item.id})"
                                    class="text-[11px] font-semibold text-indigo-600">
                                    Edit
                                </button>
                                <button onclick="deleteAttendee(${item.id})"
                                    class="ml-auto text-[11px] font-semibold text-red-600">
                                    Remove
                                </button>
                            </div>
                            `
                            :''
                    }
                </div>
            `).join('')}
        </div>
    `;
}

function decisionsHtml(items){
    if(!items.length){
        return emptyBlock(
            'No decisions recorded.'
        );
    }

    return`
        <div class="space-y-3">
            ${items.map(item=>`
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-semibold text-indigo-600">
                                    ${esc(item.decision_no)}
                                </span>
                                ${AdminUI.statusBadge(item.status)}
                                <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-500">
                                    ${esc(formatType(item.result))}
                                </span>
                            </div>

                            <h5 class="mt-2 text-base font-bold text-slate-700">
                                ${esc(item.title)}
                            </h5>

                            <p class="mt-1 whitespace-pre-wrap text-sm leading-5 text-slate-500">
                                ${esc(item.decision)}
                            </p>
                        </div>

                        ${
                            canUpdate
                                ?`
                                <div class="flex shrink-0 gap-1">
                                    <button onclick="editDecision(${item.id})"
                                        class="icon-action text-indigo-600">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="deleteDecision(${item.id})"
                                        class="icon-action text-red-600">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                                `
                                :''
                        }
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-[11px] md:grid-cols-5">
                        ${infoMini('Agenda',item.agenda?.title||'—')}
                        ${infoMini('Responsible',item.responsible_user?.name||'—')}
                        ${infoMini('Due Date',date(item.due_date))}
                        ${infoMini('Votes For',item.votes_for||0)}
                        ${infoMini('Against / Abstain',`${item.votes_against||0} / ${item.votes_abstain||0}`)}
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function expensesHtml(items){
    if(!items.length){
        return emptyBlock(
            'No meeting expenses posted.'
        );
    }

    return`
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-sm">
                    <thead class="bg-slate-50">
                        <tr class="border-b border-slate-200">
                            <th class="px-3 py-2 text-left text-slate-500">Expense</th>
                            <th class="px-3 py-2 text-left text-slate-500">Category</th>
                            <th class="px-3 py-2 text-left text-slate-500">Account</th>
                            <th class="px-3 py-2 text-left text-slate-500">Date</th>
                            <th class="px-3 py-2 text-right text-slate-500">Amount</th>
                            <th class="px-3 py-2 text-left text-slate-500">Status</th>
                            <th class="px-3 py-2 text-right text-slate-500">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        ${items.map(item=>`
                            <tr>
                                <td class="px-3 py-2 font-medium text-slate-600">
                                    ${esc(item.expense_no)}
                                </td>
                                <td class="px-3 py-2 text-slate-500">
                                    ${esc(item.category)}
                                </td>
                                <td class="px-3 py-2 text-slate-500">
                                    ${esc(accountName(item.payment_account))}
                                </td>
                                <td class="px-3 py-2 text-slate-500">
                                    ${date(item.expense_date)}
                                </td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-700">
                                    ${money(item.amount)}
                                </td>
                                <td class="px-3 py-2">
                                    ${AdminUI.statusBadge(item.status)}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    ${
                                        canUpdate&&item.status==='posted'
                                            ?`
                                            <button onclick="cancelExpense(${item.id})"
                                                class="text-[11px] font-semibold text-red-600 hover:text-red-700">
                                                Reverse
                                            </button>
                                            `
                                            :'—'
                                    }
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function infoMini(label,value){
    return`
        <div>
            <p class="text-slate-400">${esc(label)}</p>
            <p class="mt-0.5 truncate font-medium text-slate-600">
                ${esc(value)}
            </p>
        </div>
    `;
}

function emptyBlock(message){
    return`
        <div class="rounded-md border border-slate-200 bg-white py-8 text-center text-base text-slate-400">
            ${esc(message)}
        </div>
    `;
}

window.openAgendaModal=function(){
    if(!selectedMeeting)return;

    editingAgenda=null;
    $('agendaForm').reset();
    $('agendaId').value='';
    $('agendaStatus').value='pending';

    const next=Math.max(
        0,
        ...(selectedMeeting.agendas||[])
            .map(item=>Number(item.sort_order||0))
    )+1;

    $('agendaSortOrder').value=next;

    AdminUI.clearError('agendaError');
    $('agendaModalTitle').textContent='Add Agenda';

    AdminUI.openModal('agendaModal');
};

window.editAgenda=function(id){
    const agenda=(selectedMeeting.agendas||[])
        .find(item=>Number(item.id)===Number(id));

    if(!agenda)return;

    editingAgenda=agenda;

    $('agendaId').value=agenda.id;
    $('agendaTitle').value=agenda.title||'';
    $('agendaSortOrder').value=agenda.sort_order||1;
    $('agendaStatus').value=agenda.status||'pending';
    $('agendaDescription').value=agenda.description||'';
    $('agendaModalTitle').textContent='Edit Agenda';

    AdminUI.clearError('agendaError');
    AdminUI.openModal('agendaModal');
};

$('agendaForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(!$('agendaTitle').value.trim()){
        AdminUI.showError(
            'agendaError',
            'Agenda title is required.'
        );
        return;
    }

    const data={
        title:$('agendaTitle').value.trim(),
        sort_order:Number($('agendaSortOrder').value||1),
        status:$('agendaStatus').value,
        description:$('agendaDescription').value.trim()||null
    };

    const button=$('saveAgendaButton');
    AdminUI.setLoading(button,'Saving...');

    try{
        await api(
            editingAgenda
                ?`/api/meetings/agendas/${editingAgenda.id}`
                :`/api/meetings/${selectedMeeting.id}/agendas`,
            {
                method:editingAgenda?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('agendaModal');
        Toast.success('Agenda saved successfully.');

        await refreshDetails();
    }catch(error){
        AdminUI.showError(
            'agendaError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteAgenda=function(id){
    AdminUI.deleteRequest(
        `/api/meetings/agendas/${id}`,
        {
            message:'Delete this agenda item?',
            successMessage:'Agenda deleted.',
            onSuccess:refreshDetails
        }
    );
};

window.openAttendeeModal=function(){
    editingAttendee=null;

    $('attendeeForm').reset();
    $('attendeeId').value='';
    $('attendeeStatus').value='invited';
    $('attendeeMemberId').disabled=false;
    $('attendeeMemberWrap').classList.remove('opacity-60');

    $('attendeeModalTitle').textContent='Add Attendee';

    AdminUI.clearError('attendeeError');
    AdminUI.openModal('attendeeModal');
};

window.editAttendee=function(id){
    const attendee=(selectedMeeting.attendees||[])
        .find(item=>Number(item.id)===Number(id));

    if(!attendee)return;

    editingAttendee=attendee;

    $('attendeeId').value=attendee.id;
    $('attendeeMemberId').value=attendee.member_id||'';
    $('attendeeMemberId').disabled=true;
    $('attendeeMemberWrap').classList.add('opacity-60');

    $('attendeeStatus').value=attendee.status||'invited';
    $('attendeeNotes').value=attendee.notes||'';
    $('attendeeModalTitle').textContent='Update Attendance';

    AdminUI.clearError('attendeeError');
    AdminUI.openModal('attendeeModal');
};

$('attendeeForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(
        !editingAttendee&&
        !$('attendeeMemberId').value
    ){
        AdminUI.showError(
            'attendeeError',
            'Please select a member.'
        );
        return;
    }

    const data={
        status:$('attendeeStatus').value,
        notes:$('attendeeNotes').value.trim()||null
    };

    if(!editingAttendee){
        data.member_id=Number(
            $('attendeeMemberId').value
        );
    }

    const button=$('saveAttendeeButton');
    AdminUI.setLoading(button,'Saving...');

    try{
        await api(
            editingAttendee
                ?`/api/meetings/attendees/${editingAttendee.id}`
                :`/api/meetings/${selectedMeeting.id}/attendees`,
            {
                method:editingAttendee?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('attendeeModal');

        Toast.success(
            editingAttendee
                ?'Attendance updated.'
                :'Attendee added.'
        );

        await refreshDetails();
    }catch(error){
        AdminUI.showError(
            'attendeeError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteAttendee=function(id){
    AdminUI.deleteRequest(
        `/api/meetings/attendees/${id}`,
        {
            message:'Remove this attendee?',
            successMessage:'Attendee removed.',
            onSuccess:refreshDetails
        }
    );
};

function syncDecisionAgendas(){
    $('decisionAgendaId').innerHTML=
        `<option value="">No specific agenda</option>`+
        (selectedMeeting?.agendas||[])
            .map(item=>`
                <option value="${item.id}">
                    ${item.sort_order}. ${esc(item.title)}
                </option>
            `).join('');
}

window.openDecisionModal=function(){
    editingDecision=null;

    $('decisionForm').reset();
    $('decisionId').value='';
    $('decisionResult').value='approved';
    $('decisionStatus').value='pending';
    $('votesFor').value='0';
    $('votesAgainst').value='0';
    $('votesAbstain').value='0';

    syncDecisionAgendas();

    setDate(
        'decisionDueDate',
        ''
    );

    $('decisionModalTitle').textContent='Add Decision';
    AdminUI.clearError('decisionError');

    AdminUI.openModal('decisionModal');
    window.initDatePickers?.();
};

window.editDecision=function(id){
    const item=(selectedMeeting.decisions||[])
        .find(item=>Number(item.id)===Number(id));

    if(!item)return;

    editingDecision=item;

    syncDecisionAgendas();

    $('decisionId').value=item.id;
    $('decisionTitle').value=item.title||'';
    $('decisionText').value=item.decision||'';
    $('decisionAgendaId').value=item.meeting_agenda_id||'';
    $('decisionResult').value=item.result||'approved';
    $('decisionResponsibleUser').value=item.responsible_user_id||'';
    $('decisionStatus').value=item.status||'pending';
    $('votesFor').value=item.votes_for||0;
    $('votesAgainst').value=item.votes_against||0;
    $('votesAbstain').value=item.votes_abstain||0;
    $('decisionCompletionNotes').value=item.completion_notes||'';

    $('decisionModalTitle').textContent='Edit Decision';

    AdminUI.clearError('decisionError');
    AdminUI.openModal('decisionModal');

    window.initDatePickers?.();

    setDate(
        'decisionDueDate',
        item.due_date
    );
};

$('decisionForm').addEventListener('submit',async event=>{
    event.preventDefault();

    if(
        !$('decisionTitle').value.trim()||
        !$('decisionText').value.trim()
    ){
        AdminUI.showError(
            'decisionError',
            'Decision title and details are required.'
        );
        return;
    }

    const agenda=$('decisionAgendaId').value;
    const responsible=$('decisionResponsibleUser').value;

    const data={
        meeting_agenda_id:agenda?Number(agenda):null,
        title:$('decisionTitle').value.trim(),
        decision:$('decisionText').value.trim(),
        result:$('decisionResult').value,
        votes_for:Number($('votesFor').value||0),
        votes_against:Number($('votesAgainst').value||0),
        votes_abstain:Number($('votesAbstain').value||0),
        responsible_user_id:responsible?Number(responsible):null,
        due_date:$('decisionDueDate').value||null,
        status:$('decisionStatus').value,
        completion_notes:$('decisionCompletionNotes').value.trim()||null
    };

    const button=$('saveDecisionButton');
    AdminUI.setLoading(button,'Saving...');

    try{
        await api(
            editingDecision
                ?`/api/meetings/decisions/${editingDecision.id}`
                :`/api/meetings/${selectedMeeting.id}/decisions`,
            {
                method:editingDecision?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('decisionModal');

        Toast.success(
            editingDecision
                ?'Decision updated.'
                :'Decision recorded.'
        );

        await refreshDetails();
    }catch(error){
        AdminUI.showError(
            'decisionError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteDecision=function(id){
    AdminUI.deleteRequest(
        `/api/meetings/decisions/${id}`,
        {
            message:'Delete this decision?',
            successMessage:'Decision deleted.',
            onSuccess:refreshDetails
        }
    );
};

window.openMinutesModal=function(){
    if(!selectedMeeting)return;

    $('meetingMinutes').value=
        selectedMeeting.minutes||'';

    AdminUI.clearError('minutesError');
    AdminUI.openModal('minutesModal');
};

$('minutesForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const button=$('saveMinutesButton');

    AdminUI.setLoading(
        button,
        'Saving...'
    );

    try{
        await api(
            `/api/meetings/${selectedMeeting.id}/minutes`,
            {
                method:'PUT',
                body:JSON.stringify({
                    minutes:
                        $('meetingMinutes')
                            .value.trim()||null
                })
            }
        );

        AdminUI.closeModal('minutesModal');
        Toast.success('Meeting minutes updated.');

        await refreshDetails();
    }catch(error){
        AdminUI.showError(
            'minutesError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.openExpenseModal=function(){
    if(!selectedMeeting)return;

    $('expenseForm').reset();
    AdminUI.clearError('expenseError');

    AdminUI.openModal('expenseModal');
    window.initDatePickers?.();

    setDate(
        'expenseDate',
        selectedMeeting.meeting_date||
        today()
    );
};

$('expenseForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const required=[
        ['expenseCategory','Expense category is required.'],
        ['expenseAccountId','Select an expense account.'],
        ['paymentAccountId','Select a payment account.'],
        ['expenseAmount','Expense amount is required.'],
        ['expenseDate','Expense date is required.']
    ];

    for(const [id,message] of required){
        if(!$(id).value){
            AdminUI.showError(
                'expenseError',
                message
            );
            return;
        }
    }

    const amount=Number(
        $('expenseAmount').value
    );

    if(
        !Number.isFinite(amount)||
        amount<=0
    ){
        AdminUI.showError(
            'expenseError',
            'Expense amount must be greater than zero.'
        );
        return;
    }

    const data={
        category:$('expenseCategory').value.trim(),
        expense_account_id:Number($('expenseAccountId').value),
        payment_account_id:Number($('paymentAccountId').value),
        amount,
        expense_date:$('expenseDate').value,
        payee:$('expensePayee').value.trim()||null,
        reference_no:$('expenseReference').value.trim()||null,
        description:$('expenseDescription').value.trim()||null
    };

    const button=$('saveExpenseButton');
    AdminUI.setLoading(button,'Posting...');

    try{
        await api(
            `/api/meetings/${selectedMeeting.id}/expenses`,
            {
                method:'POST',
                body:JSON.stringify(data)
            }
        );

        AdminUI.closeModal('expenseModal');

        Toast.success(
            'Meeting expense posted successfully.'
        );

        await Promise.all([
            refreshDetails(),
            loadMeetings(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'expenseError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.cancelExpense=function(id){
    AdminUI.confirm({
        title:'Reverse Expense',
        message:'This will reverse the accounting journal for this expense. Continue?',
        confirmText:'Reverse',
        confirmClass:'bg-red-600 hover:bg-red-700',
        onConfirm:async()=>{
            try{
                await api(
                    `/api/meetings/expenses/${id}/cancel`,
                    {method:'POST'}
                );

                Toast.success(
                    'Meeting expense reversed.'
                );

                await Promise.all([
                    refreshDetails(),
                    loadMeetings(currentPage),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(error)
                );
            }
        }
    });
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('typeFilter').value='';
    $('statusFilter').value='';
    $('yearFilter').value='';

    loadMeetings(1);
};

function debounce(callback,delay=350){
    let timer;

    return(...args)=>{
        clearTimeout(timer);

        timer=setTimeout(
            ()=>callback(...args),
            delay
        );
    };
}

async function initMeetingPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initMeetingPage,
            50
        );
        return;
    }

    initYears();

    window.initDatePickers?.();

    $('searchInput').addEventListener(
        'input',
        debounce(
            ()=>loadMeetings(1)
        )
    );

    $('typeFilter').addEventListener(
        'change',
        ()=>loadMeetings(1)
    );

    $('statusFilter').addEventListener(
        'change',
        ()=>loadMeetings(1)
    );

    $('yearFilter').addEventListener(
        'change',
        ()=>loadMeetings(1)
    );

    await Promise.all([
        loadOptions(),
        loadStatistics(),
        loadMeetings()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initMeetingPage
    );
}else{
    initMeetingPage();
}
</script>

<style>
.section-action{
    display:inline-flex;
    align-items:center;
    gap:.3rem;
    border:1px solid rgb(199 210 254);
    border-radius:.375rem;
    background:rgb(238 242 255);
    padding:.4rem .65rem;
    font-size:.7rem;
    font-weight:600;
    color:rgb(67 56 202);
    transition:.15s;
}
.section-action:hover{
    background:rgb(224 231 255);
}
.icon-action{
    display:inline-flex;
    height:2rem;
    width:2rem;
    align-items:center;
    justify-content:center;
    border-radius:.375rem;
    transition:.15s;
}
.icon-action:hover{
    background:rgb(248 250 252);
}
</style>
@endpush