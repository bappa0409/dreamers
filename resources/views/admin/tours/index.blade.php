@extends('layouts.admin')

@section('title','Tours')
@section('page_title','Tours')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-airplane"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Tour Management</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage association tours, participants, budgets and expenses.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Tour.create'))
        <button type="button" onclick="openTourModal()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Tour
        </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-6">
        @php
            $stats=[
                [
                    'id'=>'totalTours',
                    'label'=>'Total Tours',
                    'icon'=>'bi-airplane',
                    'box'=>'border-slate-200 bg-white',
                    'text'=>'text-slate-800',
                    'iconbox'=>'bg-slate-100 text-slate-500'
                ],
                [
                    'id'=>'upcomingTours',
                    'label'=>'Upcoming',
                    'icon'=>'bi-calendar-event',
                    'box'=>'border-indigo-200 bg-indigo-50/40',
                    'text'=>'text-indigo-700',
                    'iconbox'=>'bg-indigo-100 text-indigo-600'
                ],
                [
                    'id'=>'ongoingTours',
                    'label'=>'Ongoing',
                    'icon'=>'bi-signpost-split',
                    'box'=>'border-amber-200 bg-amber-50/40',
                    'text'=>'text-amber-700',
                    'iconbox'=>'bg-amber-100 text-amber-600'
                ],
                [
                    'id'=>'completedTours',
                    'label'=>'Completed',
                    'icon'=>'bi-check2-circle',
                    'box'=>'border-emerald-200 bg-emerald-50/40',
                    'text'=>'text-emerald-700',
                    'iconbox'=>'bg-emerald-100 text-emerald-600'
                ],
                [
                    'id'=>'totalBudget',
                    'label'=>'Total Budget',
                    'icon'=>'bi-wallet2',
                    'box'=>'border-sky-200 bg-sky-50/40',
                    'text'=>'text-sky-700',
                    'iconbox'=>'bg-sky-100 text-sky-600'
                ],
                [
                    'id'=>'actualExpense',
                    'label'=>'Actual Expense',
                    'icon'=>'bi-cash-stack',
                    'box'=>'border-rose-200 bg-rose-50/40',
                    'text'=>'text-rose-700',
                    'iconbox'=>'bg-rose-100 text-rose-600'
                ]
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="rounded-md border p-4 {{ $stat['box'] }}">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class=" text-xs 2xl:text-sm text-slate-500">{{ $stat['label'] }}</p>
                    <p id="{{ $stat['id'] }}" class="mt-2 truncate text-xl font-bold {{ $stat['text'] }}">
                        @if(in_array($stat['id'],['totalBudget','actualExpense']))
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
                    <p class="text-sm font-semibold text-slate-700">Search Tours</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Search by tour number, title or destination.</p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:w-auto xl:grid-cols-[300px_155px_110px_auto] xl:gap-0">
                <div class="relative sm:col-span-2 xl:col-span-1">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                    <input id="searchInput" type="text" placeholder="Search tour..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 xl:rounded-r-none">
                </div>

                <select id="statusFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="approved">Approved</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select id="yearFilter"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-xs 2xl:text-sm font-medium text-slate-600 outline-none focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                </select>

                <button type="button" onclick="clearFilters()"
                    class="inline-flex h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-xs 2xl:text-sm font-semibold text-slate-600 transition hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Tour</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Destination</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Schedule</th>
                        <th class="px-4 py-3 text-center  text-xs 2xl:text-sm font-semibold text-slate-600">Participants</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Budget</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Expense</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="tourTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-base text-slate-400">
                            Loading tours...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mobile --}}
    <div id="tourMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs 2xl:text-sm text-slate-400">
            Loading tours...
        </div>
    </div>

    <div id="paginationWrap" class="rounded-md border border-slate-200 bg-white px-4 py-3"></div>
</div>

{{-- =============================================================
CREATE / EDIT TOUR
============================================================= --}}
<div id="tourModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-airplane"></i>
                </div>

                <div>
                    <h3 id="tourModalTitle" class="text-sm font-semibold text-slate-800">Add Tour</h3>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Create and plan an association tour.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('tourModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="tourForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                <div id="tourError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                {{-- Basic Information --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Basic Information</h3>
                            <p class="text-[11px] text-slate-400">Title and destination of the tour.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="form-label">Tour Title <span class="text-red-500">*</span></label>
                            <input id="tourTitle" type="text" maxlength="255" class="app-input w-full" data-validation-required-message="Tour title is required.">
                            <p data-field-error="tourTitle" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Destination <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-geo-alt pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="tourDestination" type="text" maxlength="255" class="app-input w-full !pl-9" data-validation-required-message="Destination is required.">
                            </div>
                            <p data-field-error="tourDestination" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>
                    </div>
                </section>

                {{-- Schedule --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Schedule</h3>
                            <p class="text-[11px] text-slate-400">When the tour starts and ends.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Start Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="tourStartDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off" data-validation-required-message="Start date is required.">
                            </div>
                            <p data-field-error="tourStartDate" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">End Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="tourEndDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off" data-validation-required-message="End date is required." data-on-or-after="tourStartDate" data-validation-compare-message="End date cannot be earlier than start date.">
                            </div>
                            <p data-field-error="tourEndDate" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>
                    </div>
                </section>

                {{-- Budget --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Budget</h3>
                            <p class="text-[11px] text-slate-400">Planned spending for this tour.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Budget Amount</label>
                            <input id="tourBudget" type="number" min="0" step="0.01" class="app-input w-full">
                        </div>
                    </div>
                </section>

                {{-- Description & Notes --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Description &amp; Notes</h3>
                            <p class="text-[11px] text-slate-400">Any additional details.</p>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div>
                            <label class="form-label">Description</label>
                            <textarea id="tourDescription" rows="4" maxlength="5000" class="app-input w-full resize-none"></textarea>
                        </div>

                        <div>
                            <label class="form-label">Notes</label>
                            <textarea id="tourNotes" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                        </div>
                    </div>
                </section>

                <div class="rounded-md border border-indigo-100 bg-indigo-50/50 px-3 py-2 text-[11px] leading-5 text-indigo-700">
                    New tours are created as Draft. Approve the tour after planning is complete.
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="AdminUI.closeModal('tourModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i> Close
                </button>

                <button id="saveTourButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i> Save Tour
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =============================================================
DETAILS
============================================================= --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[96vh] w-full max-w-6xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-airplane-engines"></i>
                </div>

                <div class="min-w-0">
                    <h3 id="detailsTitle" class="truncate text-lg font-bold text-slate-800">Tour Details</h3>
                    <p id="detailsSubtitle" class=" text-xs 2xl:text-sm text-slate-500"></p>
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

{{-- =============================================================
PARTICIPANT MODAL
============================================================= --}}
<div id="participantModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-person"></i>
                </div>
                <div>
                    <h3 id="participantModalTitle" class="text-sm font-semibold text-slate-800">Add Participant</h3>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Add a member to this tour.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('participantModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="participantForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <input id="participantId" type="hidden">

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                <div id="participantError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-person-vcard"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Participant Information</h3>
                            <p class="text-[11px] text-slate-400">Member and participation status.</p>
                        </div>
                    </div>

                    <div class="grid gap-4">
                        <div id="participantMemberWrap">
                            <label class="form-label">Member <span class="text-red-500">*</span></label>

                            <select id="participantMemberId" class="app-input w-full" data-validation-required-message="Please select a member.">
                                <option value="">Select member</option>
                            </select>
                            <p data-field-error="participantMemberId" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Status</label>

                            <select id="participantStatus" class="app-input w-full">
                                <option value="registered">Registered</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="attended">Attended</option>
                                <option value="absent">Absent</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Notes</label>
                            <textarea id="participantNotes" rows="3" maxlength="3000" class="app-input w-full resize-none"></textarea>
                        </div>
                    </div>
                </section>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('participantModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>

                <button id="saveParticipantButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Participant
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =============================================================
EXPENSE MODAL
============================================================= --}}
<div id="expenseModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Add Tour Expense</h3>
                    <p class=" text-xs 2xl:text-sm text-slate-500">The expense will automatically post to accounting.</p>
                </div>
            </div>

            <button type="button" onclick="AdminUI.closeModal('expenseModal')" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="expenseForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto p-5">
                <div id="expenseError" class="hidden rounded-md border border-red-200 bg-red-50 px-3 py-2 text-base text-red-700"></div>

                {{-- Expense Details --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-rose-50 text-rose-600">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Expense Details</h3>
                            <p class="text-[11px] text-slate-400">What this expense is for.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Category <span class="text-red-500">*</span></label>
                            <input id="expenseCategory" maxlength="100" class="app-input w-full" placeholder="Transport, Hotel, Food..." data-validation-required-message="Expense category is required.">
                            <p data-field-error="expenseCategory" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Amount <span class="text-red-500">*</span></label>
                            <input id="expenseAmount" type="number" min="0.01" step="0.01" class="app-input w-full" data-validation-required-message="Expense amount is required." data-validation-min-message="Expense amount must be greater than zero.">
                            <p data-field-error="expenseAmount" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Expense Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>
                                <input id="expenseDate" type="text" class="app-input js-date-picker !pl-9" placeholder="Select date" autocomplete="off" data-validation-required-message="Expense date is required.">
                            </div>
                            <p data-field-error="expenseDate" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Payee</label>
                            <input id="expensePayee" maxlength="255" class="app-input w-full">
                        </div>

                        <div class="md:col-span-2">
                            <label class="form-label">Reference No</label>
                            <input id="expenseReference" maxlength="150" class="app-input w-full">
                        </div>
                    </div>
                </section>

                {{-- Accounting --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Accounting</h3>
                            <p class="text-[11px] text-slate-400">Which accounts this expense posts to.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Expense Account <span class="text-red-500">*</span></label>
                            <select id="expenseAccountId" class="app-input w-full" data-validation-required-message="Select an expense account.">
                                <option value="">Select expense account</option>
                            </select>
                            <p data-field-error="expenseAccountId" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label class="form-label">Payment Account <span class="text-red-500">*</span></label>
                            <select id="paymentAccountId" class="app-input w-full" data-validation-required-message="Select a payment account.">
                                <option value="">Select Cash/Bank</option>
                            </select>
                            <p data-field-error="paymentAccountId" class="mt-1 hidden text-sm text-red-600"></p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-md border border-indigo-100 bg-indigo-50/50 px-3 py-2 text-[11px] text-indigo-700">
                        Accounting: Dr selected Expense Account, Cr selected Cash/Bank account.
                    </div>
                </section>

                {{-- Description --}}
                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Description</h3>
                            <p class="text-[11px] text-slate-400">Any additional details.</p>
                        </div>
                    </div>

                    <textarea id="expenseDescription" rows="3" maxlength="5000" class="app-input w-full resize-none"></textarea>
                </section>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button" onclick="AdminUI.closeModal('expenseModal')"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    Cancel
                </button>

                <button id="saveExpenseButton" type="submit"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Post Expense
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
@endsection

@push('scripts')
<script>
const currency=@json(setting('currency_symbol','৳'));
const canUpdate=@json(auth()->user()->hasPermission('Tour.update'));
const canDelete=@json(auth()->user()->hasPermission('Tour.delete'));

let tours=[];
let currentPage=1;
let selectedTour=null;
let editingTour=null;
let editingParticipant=null;

let members=[];
let expenseAccounts=[];
let paymentAccounts=[];

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

function money(value){
    return `${currency}${Number(value||0).toLocaleString(undefined,{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    })}`;
}

function date(value){
    return value
        ?AdminUI.formatDate(value)
        :'—';
}

function today(){
    const now=new Date();
    const offset=now.getTimezoneOffset();

    return new Date(
        now.getTime()-offset*60000
    ).toISOString().slice(0,10);
}

function setDate(id,value){
    const element=$(id);
    if(!element)return;

    const dateValue=value
        ?String(value).substring(0,10)
        :'';

    element.value=dateValue;

    if(element._flatpickr){
        dateValue
            ?element._flatpickr.setDate(
                dateValue,
                false,
                'Y-m-d'
            )
            :element._flatpickr.clear();
    }
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

function tourDuration(tour){
    if(!tour.start_date&&!tour.end_date){
        return '—';
    }

    if(tour.start_date&&tour.end_date){
        return `${date(tour.start_date)} - ${date(tour.end_date)}`;
    }

    return date(
        tour.start_date||
        tour.end_date
    );
}

function nextStatuses(status){
    return{
        draft:[
            'cancelled'
        ],
        approved:[
            'upcoming',
            'ongoing',
            'cancelled'
        ],
        upcoming:[
            'ongoing',
            'cancelled'
        ],
        ongoing:[
            'completed',
            'cancelled'
        ],
        completed:[],
        cancelled:[]
    }[status]||[];
}

function formatStatus(value){
    return String(value||'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

async function loadOptions(){
    try{
        const response=await api(
            '/api/tours/options'
        );

        const data=response.data||{};

        members=data.members||[];
        expenseAccounts=data.expense_accounts||[];
        paymentAccounts=data.payment_accounts||[];

        $('participantMemberId').innerHTML=
            `<option value="">Select member</option>`+
            members.map(member=>`
                <option value="${member.id}">
                    ${esc(memberName(member))}
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
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

async function loadStatistics(){
    try{
        const response=await api(
            '/api/tours/statistics'
        );

        const data=response.data||{};

        $('totalTours').textContent=
            data.total||0;

        $('upcomingTours').textContent=
            data.upcoming||0;

        $('ongoingTours').textContent=
            data.ongoing||0;

        $('completedTours').textContent=
            data.completed||0;

        $('totalBudget').textContent=
            money(data.total_budget);

        $('actualExpense').textContent=
            money(data.actual_expense);
    }catch(error){
        console.error(error);
    }
}

function initYears(){
    const current=new Date().getFullYear();

    let html=`
        <option value="">
            All Years
        </option>
    `;

    for(
        let year=current+2;
        year>=current-10;
        year--
    ){
        html+=`
            <option value="${year}">
                ${year}
            </option>
        `;
    }

    $('yearFilter').innerHTML=html;
}

async function loadTours(page=1){
    currentPage=page;

    const tbody=$('tourTableBody');
    const grid=$('tourMobileGrid');

    tbody.innerHTML=
        AdminUI.loadingState(
            'Loading tours...',
            8
        );

    grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs 2xl:text-sm text-slate-400">
            <div class="flex items-center justify-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading tours...
            </div>
        </div>
    `;

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const status=$('statusFilter').value;
    const year=$('yearFilter').value;

    if(search){
        params.set(
            'search',
            search
        );
    }

    if(status){
        params.set(
            'status',
            status
        );
    }

    if(year){
        params.set(
            'year',
            year
        );
    }

    try{
        const response=await api(
            `/api/tours?${params.toString()}`
        );

        const paginator=response.data||{};

        tours=paginator.data||[];

        renderTours();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadTours
        );
    }catch(error){
        const message=
            AdminUI.extractError(error);

        tbody.innerHTML=
            AdminUI.emptyState(
                message,
                8
            );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(message)}
            </div>
        `;
    }
}

function tourActions(tour){
    const actions=[
        `
        <button
            type="button"
            onclick="viewTour(${tour.id})"
            title="View Details"
            class="flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
        >
            <i class="bi bi-eye text-sm"></i>
        </button>
        `
    ];

    if(
        canUpdate&&
        !['completed','cancelled'].includes(
            tour.status
        )
    ){
        actions.push(`
            <button type="button"
                onclick="editTour(${tour.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 transition hover:bg-indigo-100">
                <i class="bi bi-pencil"></i>
                Edit
            </button>
        `);
    }

    if(
        canUpdate&&
        tour.status==='draft'
    ){
        actions.push(`
            <button type="button"
                onclick="approveTour(${tour.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                <i class="bi bi-check2-circle"></i>
                Approve
            </button>
        `);
    }

    if(
        canDelete&&
        ['draft','cancelled'].includes(
            tour.status
        )&&
        Number(tour.actual_expense||0)===0
    ){
        actions.push(`
            <button type="button"
                onclick="deleteTour(${tour.id})"
                class="inline-flex cursor-pointer items-center gap-1 rounded-md border border-red-200 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-red-600 transition hover:bg-red-50">
                <i class="bi bi-trash3"></i>
                Delete
            </button>
        `);
    }

    return actions.join('');
}

function renderTours(){
    const tbody=$('tourTableBody');
    const grid=$('tourMobileGrid');

    if(!tours.length){
        tbody.innerHTML=
            AdminUI.emptyState(
                'No tours found.',
                8
            );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <i class="bi bi-inbox"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-slate-600">
                    No tours found
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    Try changing your filters.
                </p>
            </div>
        `;

        return;
    }

    tbody.innerHTML=
        tours.map(tour=>`
            <tr class="transition hover:bg-slate-50/70">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-airplane"></i>
                        </div>

                        <div class="min-w-0">
                            <div class="font-semibold  text-xs 2xl:text-sm text-slate-700">
                                ${esc(tour.tour_no)}
                            </div>

                            <div class="mt-0.5 max-w-[230px] truncate text-sm text-slate-400">
                                ${esc(tour.title)}
                            </div>
                        </div>
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="max-w-[180px] truncate text-base text-slate-500">
                        <i class="bi bi-geo-alt me-1 text-slate-400"></i>
                        ${esc(tour.destination)}
                    </div>
                </td>

                <td class="px-4 py-3">
                    <div class="text-sm text-slate-600">
                        ${date(tour.start_date)}
                    </div>

                    <div class="mt-0.5 text-[10px] text-slate-400">
                        to ${date(tour.end_date)}
                    </div>
                </td>

                <td class="px-4 py-3 text-center font-medium text-slate-600">
                    ${tour.participants_count||0}
                </td>

                <td class="px-4 py-3  text-xs 2xl:text-sm text-right font-semibold text-slate-700">
                    ${money(tour.budget_amount)}
                </td>

                <td class="px-4 py-3  text-xs 2xl:text-sm text-right font-semibold text-slate-700">
                    ${money(tour.actual_expense)}
                </td>

                <td class="px-4 py-3">
                    ${AdminUI.statusBadge(tour.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex flex-wrap justify-end gap-1">
                        ${tourActions(tour)}
                    </div>
                </td>
            </tr>
        `).join('');

    grid.innerHTML=
        tours.map(tour=>`
            <article class="overflow-hidden rounded-md border border-slate-200 bg-white">
                <div class="border-b border-slate-100 px-4 py-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <i class="bi bi-airplane"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-base font-bold text-slate-700">
                                    ${esc(tour.title)}
                                </p>

                                <p class="mt-0.5 text-[11px] font-medium text-indigo-600">
                                    ${esc(tour.tour_no)}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(tour.status)}
                        </div>
                    </div>
                </div>

                <div class="space-y-4 p-4">
                    <div class="rounded-md bg-slate-50 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            Destination
                        </p>

                        <p class="mt-1 truncate text-base font-semibold text-slate-700">
                            <i class="bi bi-geo-alt me-1 text-indigo-500"></i>
                            ${esc(tour.destination)}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-md bg-indigo-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-indigo-500">
                                Budget
                            </p>

                            <p class="mt-1 truncate text-base font-bold text-indigo-700">
                                ${money(tour.budget_amount)}
                            </p>
                        </div>

                        <div class="rounded-md bg-rose-50 p-3">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-500">
                                Expense
                            </p>

                            <p class="mt-1 truncate text-base font-bold text-rose-700">
                                ${money(tour.actual_expense)}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-x-3 gap-y-3 border-t border-slate-100 pt-3 text-sm">
                        <div>
                            <p class="text-[10px] text-slate-400">
                                Start
                            </p>

                            <p class="mt-0.5 font-medium text-slate-600">
                                ${date(tour.start_date)}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">
                                End
                            </p>

                            <p class="mt-0.5 font-medium text-slate-600">
                                ${date(tour.end_date)}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">
                                Participants
                            </p>

                            <p class="mt-0.5 font-medium text-slate-600">
                                ${tour.participants_count||0}
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-400">
                                Variance
                            </p>

                            <p class="mt-0.5 font-medium ${
                                Number(tour.budget_amount||0)-
                                Number(tour.actual_expense||0)>=0
                                    ?'text-emerald-600'
                                    :'text-red-600'
                            }">
                                ${money(
                                    Number(tour.budget_amount||0)-
                                    Number(tour.actual_expense||0)
                                )}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-1.5 border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                    ${tourActions(tour)}
                </div>
            </article>
        `).join('');
}

window.openTourModal=function(){
    editingTour=null;

    $('tourForm').reset();

    AdminUI.clearError(
        'tourError'
    );

    AdminUI.clearFieldErrors(
        'tourForm'
    );

    $('tourModalTitle').textContent=
        'Add Tour';

    $('tourBudget').value='0';

    AdminUI.openModal(
        'tourModal'
    );

    window.initDatePickers?.();

    setDate(
        'tourStartDate',
        ''
    );

    setDate(
        'tourEndDate',
        ''
    );
};

window.editTour=async function(id){
    try{
        const response=await api(
            `/api/tours/${id}`
        );

        const tour=response.data;

        editingTour=tour;

        $('tourForm').reset();

        AdminUI.clearError(
            'tourError'
        );

        AdminUI.clearFieldErrors(
            'tourForm'
        );

        $('tourModalTitle').textContent=
            'Edit Tour';

        $('tourTitle').value=
            tour.title||'';

        $('tourDestination').value=
            tour.destination||'';

        $('tourBudget').value=
            tour.budget_amount||0;

        $('tourDescription').value=
            tour.description||'';

        $('tourNotes').value=
            tour.notes||'';

        AdminUI.openModal(
            'tourModal'
        );

        window.initDatePickers?.();

        setDate(
            'tourStartDate',
            tour.start_date
        );

        setDate(
            'tourEndDate',
            tour.end_date
        );
    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

$('tourForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'tourError'
        );

        AdminUI.clearFieldErrors(
            'tourForm'
        );
const start=
            $('tourStartDate').value;

        const end=
            $('tourEndDate').value;
const data={
            title:
                $('tourTitle').value.trim(),

            destination:
                $('tourDestination').value.trim(),

            description:
                $('tourDescription').value.trim()||
                null,

            start_date:start,

            end_date:end,

            budget_amount:
                Number(
                    $('tourBudget').value||0
                ),

            notes:
                $('tourNotes').value.trim()||
                null
        };

        const button=
            $('saveTourButton');

        AdminUI.setLoading(
            button,
            editingTour
                ?'Updating...'
                :'Saving...'
        );

        try{
            await api(
                editingTour
                    ?`/api/tours/${editingTour.id}`
                    :'/api/tours',
                {
                    method:
                        editingTour
                            ?'PUT'
                            :'POST',

                    body:JSON.stringify(data)
                }
            );

            AdminUI.closeModal(
                'tourModal'
            );

            Toast.success(
                editingTour
                    ?'Tour updated successfully.'
                    :'Tour created successfully.'
            );

            await Promise.all([
                loadTours(
                    editingTour
                        ?currentPage
                        :1
                ),
                loadStatistics()
            ]);
        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    'tourForm',
                    error,
                    {
                        title:
                            'tourTitle',

                        destination:
                            'tourDestination',

                        start_date:
                            'tourStartDate',

                        end_date:
                            'tourEndDate',

                        budget_amount:
                            'tourBudget',

                        description:
                            'tourDescription',

                        notes:
                            'tourNotes'
                    }
                )
            ){
                AdminUI.showError(
                    'tourError',
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

window.approveTour=function(id){
    AdminUI.confirm({
        title:'Approve Tour',

        message:
            'Approve this tour? After approval it will move into the active tour workflow.',

        confirmText:'Approve',

        confirmClass:
            'bg-emerald-600 hover:bg-emerald-700',

        onConfirm:async()=>{
            try{
                await api(
                    `/api/tours/${id}/approve`,
                    {
                        method:'POST'
                    }
                );

                Toast.success(
                    'Tour approved successfully.'
                );

                await Promise.all([
                    loadTours(
                        currentPage
                    ),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(
                        error
                    )
                );
            }
        }
    });
};

window.deleteTour=function(id){
    const tour=tours.find(
        item=>
            Number(item.id)===
            Number(id)
    );

    AdminUI.deleteRequest(
        `/api/tours/${id}`,
        {
            message:
                `Delete "${tour?.title||'this tour'}" permanently?`,

            successMessage:
                'Tour deleted successfully.',

            onSuccess:async()=>{
                await Promise.all([
                    loadTours(
                        tours.length===1&&
                        currentPage>1
                            ?currentPage-1
                            :currentPage
                    ),
                    loadStatistics()
                ]);
            }
        }
    );
};

function detailsToolbar(tour){
    if(!canUpdate){
        return '';
    }

    const statuses=
        nextStatuses(
            tour.status
        );

    return`
        <div class="flex flex-wrap gap-1.5">
            ${
                tour.status==='draft'
                    ?`
                    <button type="button"
                        onclick="approveSelectedTour()"
                        class="rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                        <i class="bi bi-check2-circle me-1"></i>
                        Approve
                    </button>
                    `
                    :''
            }

            ${statuses
                .filter(
                    status=>
                        !(
                            tour.status==='draft'&&
                            ['approved','upcoming'].includes(status)
                        )
                )
                .map(status=>`
                    <button type="button"
                        onclick="changeTourStatus('${status}')"
                        class="rounded-md border ${
                            status==='cancelled'
                                ?'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                :status==='completed'
                                    ?'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    :'border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100'
                        } px-2.5 py-1.5 text-[11px] font-semibold transition">
                        ${esc(
                            formatStatus(
                                status
                            )
                        )}
                    </button>
                `)
                .join('')}
        </div>
    `;
}

window.approveSelectedTour=async function(){
    if(!selectedTour){
        return;
    }

    try{
        await api(
            `/api/tours/${selectedTour.id}/approve`,
            {
                method:'POST'
            }
        );

        Toast.success(
            'Tour approved successfully.'
        );

        await Promise.all([
            refreshDetails(),
            loadTours(
                currentPage
            ),
            loadStatistics()
        ]);
    }catch(error){
        Toast.error(
            AdminUI.extractError(
                error
            )
        );
    }
};

window.changeTourStatus=function(status){
    if(!selectedTour){
        return;
    }

    AdminUI.confirm({
        title:'Update Tour Status',

        message:
            `Change tour status to ${formatStatus(status)}?`,

        confirmText:'Update',

        confirmClass:
            status==='cancelled'
                ?'bg-red-600 hover:bg-red-700'
                :'bg-indigo-600 hover:bg-indigo-700',

        onConfirm:async()=>{
            try{
                await api(
                    `/api/tours/${selectedTour.id}/status`,
                    {
                        method:'PUT',
                        body:JSON.stringify({
                            status
                        })
                    }
                );

                Toast.success(
                    'Tour status updated successfully.'
                );

                await Promise.all([
                    refreshDetails(),
                    loadTours(
                        currentPage
                    ),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(
                        error
                    )
                );
            }
        }
    });
};

window.viewTour=async function(id){
    AdminUI.openModal(
        'detailsModal'
    );

    $('detailsContent').innerHTML=`
        <div class="py-16 text-center text-base text-slate-400">
            <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>

            <p class="mt-2">
                Loading tour details...
            </p>
        </div>
    `;

    try{
        const response=await api(
            `/api/tours/${id}`
        );

        selectedTour=
            response.data;

        renderDetails();
    }catch(error){
        $('detailsContent').innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(
                    AdminUI.extractError(
                        error
                    )
                )}
            </div>
        `;
    }
};

async function refreshDetails(){
    if(!selectedTour){
        return;
    }

    const response=await api(
        `/api/tours/${selectedTour.id}`
    );

    selectedTour=
        response.data;

    renderDetails();
}

function renderDetails(){
    const tour=
        selectedTour;

    $('detailsTitle').textContent=
        tour.title||
        'Tour Details';

    $('detailsSubtitle').textContent=
        `${tour.tour_no} • ${tour.destination}`;

    const registered=
        (tour.participants||[])
            .filter(
                item=>
                    item.status==='registered'
            )
            .length;

    const confirmed=
        (tour.participants||[])
            .filter(
                item=>
                    item.status==='confirmed'
            )
            .length;

    const attended=
        (tour.participants||[])
            .filter(
                item=>
                    item.status==='attended'
            )
            .length;

    const cancelled=
        (tour.participants||[])
            .filter(
                item=>
                    item.status==='cancelled'
            )
            .length;

    $('detailsContent').innerHTML=`
        <div class="space-y-5">
            <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-slate-50/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    ${AdminUI.statusBadge(tour.status)}

                    <span class=" text-xs 2xl:text-sm text-slate-500">
                        ${date(tour.start_date)}
                    </span>

                    <span class="text-sm text-slate-300">
                        →
                    </span>

                    <span class=" text-xs 2xl:text-sm text-slate-500">
                        ${date(tour.end_date)}
                    </span>
                </div>

                ${detailsToolbar(tour)}
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                ${detailStat(
                    'Budget',
                    money(
                        tour.budget_amount
                    ),
                    'indigo'
                )}

                ${detailStat(
                    'Actual Expense',
                    money(
                        tour.actual_expense
                    ),
                    'rose'
                )}

                ${detailStat(
                    'Budget Variance',
                    money(
                        tour.budget_variance
                    ),
                    Number(
                        tour.budget_variance
                    )>=0
                        ?'emerald'
                        :'red'
                )}

                ${detailStat(
                    'Participants',
                    (tour.participants||[]).length,
                    'sky'
                )}

                ${detailStat(
                    'Attended',
                    attended,
                    'emerald'
                )}
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                        Tour Information
                    </p>

                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        ${info(
                            'Tour No',
                            tour.tour_no
                        )}

                        ${info(
                            'Destination',
                            tour.destination
                        )}

                        ${info(
                            'Start Date',
                            date(
                                tour.start_date
                            )
                        )}

                        ${info(
                            'End Date',
                            date(
                                tour.end_date
                            )
                        )}

                        ${info(
                            'Created By',
                            tour.creator?.name||
                            '—'
                        )}

                        ${info(
                            'Approved By',
                            tour.approver?.name||
                            '—'
                        )}

                        ${info(
                            'Approved At',
                            tour.approved_at
                                ?AdminUI.formatDate(
                                    tour.approved_at
                                )
                                :'—'
                        )}
                    </div>

                    ${
                        tour.description
                            ?`
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    Description
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm leading-5 text-slate-600">
                                    ${esc(
                                        tour.description
                                    )}
                                </p>
                            </div>
                            `
                            :''
                    }

                    ${
                        tour.notes
                            ?`
                            <div class="mt-4 border-t border-slate-100 pt-3">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    Notes
                                </p>

                                <p class="mt-1 whitespace-pre-wrap text-sm leading-5 text-slate-600">
                                    ${esc(
                                        tour.notes
                                    )}
                                </p>
                            </div>
                            `
                            :''
                    }
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-4">
                    <p class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">
                        Participant Summary
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        ${miniCount(
                            'Registered',
                            registered,
                            'slate'
                        )}

                        ${miniCount(
                            'Confirmed',
                            confirmed,
                            'indigo'
                        )}

                        ${miniCount(
                            'Attended',
                            attended,
                            'emerald'
                        )}

                        ${miniCount(
                            'Cancelled',
                            cancelled,
                            'red'
                        )}
                    </div>
                </div>
            </div>

            ${sectionHeader(
                'Participants',
                'bi-people',
                canUpdate&&
                !['completed','cancelled']
                    .includes(tour.status)
                    ?`
                    <button onclick="openParticipantModal()" class="section-action">
                        <i class="bi bi-person-plus"></i>
                        Add Participant
                    </button>
                    `
                    :''
            )}

            ${participantsHtml(
                tour.participants||[]
            )}

            ${sectionHeader(
                'Tour Expenses',
                'bi-cash-stack',
                canUpdate&&
                ![
                    'draft',
                    'completed',
                    'cancelled'
                ].includes(
                    tour.status
                )
                    ?`
                    <button onclick="openExpenseModal()" class="section-action">
                        <i class="bi bi-plus-lg"></i>
                        Add Expense
                    </button>
                    `
                    :''
            )}

            ${expensesHtml(
                tour.expenses||[]
            )}
        </div>
    `;
}

function detailStat(
    label,
    value,
    tone
){
    const map={
        slate:
            'border-slate-200 bg-slate-50 text-slate-700',

        indigo:
            'border-indigo-200 bg-indigo-50 text-indigo-700',

        emerald:
            'border-emerald-200 bg-emerald-50 text-emerald-700',

        red:
            'border-red-200 bg-red-50 text-red-700',

        rose:
            'border-rose-200 bg-rose-50 text-rose-700',

        sky:
            'border-sky-200 bg-sky-50 text-sky-700'
    };

    return`
        <div class="rounded-md border p-3 ${map[tone]||map.slate}">
            <p class="text-[10px] opacity-70">
                ${esc(label)}
            </p>

            <p class="mt-1 truncate text-base font-bold">
                ${esc(value)}
            </p>
        </div>
    `;
}

function miniCount(
    label,
    value,
    tone
){
    const map={
        slate:
            'bg-slate-50 text-slate-700',

        indigo:
            'bg-indigo-50 text-indigo-700',

        emerald:
            'bg-emerald-50 text-emerald-700',

        red:
            'bg-red-50 text-red-700'
    };

    return`
        <div class="rounded-md p-3 ${map[tone]||map.slate}">
            <p class="text-[10px] opacity-70">
                ${esc(label)}
            </p>

            <p class="mt-1 text-lg font-bold">
                ${value}
            </p>
        </div>
    `;
}

function info(
    label,
    value,
    wide=false
){
    return`
        <div class="${wide?'col-span-2':''}">
            <p class="text-[10px] text-slate-400">
                ${esc(label)}
            </p>

            <p class="mt-0.5 break-words font-medium text-slate-600">
                ${esc(value)}
            </p>
        </div>
    `;
}

function sectionHeader(
    title,
    icon,
    action=''
){
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

function participantsHtml(items){
    if(!items.length){
        return emptyBlock(
            'No participants added.'
        );
    }

    return`
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            ${items.map(item=>`
                <div class="rounded-md border border-slate-200 bg-white p-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <i class="bi bi-person"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="truncate text-base font-semibold text-slate-700">
                                    ${esc(
                                        item.member
                                            ?.user
                                            ?.name||
                                        'Member'
                                    )}
                                </p>

                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    ${esc(
                                        item.member
                                            ?.member_code||
                                        ''
                                    )}
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(
                                item.status
                            )}
                        </div>
                    </div>

                    ${
                        item.notes
                            ?`
                            <p class="mt-3 border-t border-slate-100 pt-2 text-sm leading-5 text-slate-500">
                                ${esc(
                                    item.notes
                                )}
                            </p>
                            `
                            :''
                    }

                    ${
                        canUpdate&&
                        ![
                            'completed',
                            'cancelled'
                        ].includes(
                            selectedTour.status
                        )
                            ?`
                            <div class="mt-3 flex gap-1 border-t border-slate-100 pt-2">
                                <button type="button"
                                    onclick="editParticipant(${item.id})"
                                    class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-700">
                                    Edit
                                </button>

                                <button type="button"
                                    onclick="deleteParticipant(${item.id})"
                                    class="ml-auto text-[11px] font-semibold text-red-600 hover:text-red-700">
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

function expensesHtml(items){
    if(!items.length){
        return emptyBlock(
            'No tour expenses posted.'
        );
    }

    return`
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead class="bg-slate-50">
                        <tr class="border-b border-slate-200">
                            <th class="px-3 py-2 text-left text-slate-500">
                                Expense
                            </th>

                            <th class="px-3 py-2 text-left text-slate-500">
                                Category
                            </th>

                            <th class="px-3 py-2 text-left text-slate-500">
                                Paid From
                            </th>

                            <th class="px-3 py-2 text-left text-slate-500">
                                Payee
                            </th>

                            <th class="px-3 py-2 text-left text-slate-500">
                                Date
                            </th>

                            <th class="px-3 py-2 text-right text-slate-500">
                                Amount
                            </th>

                            <th class="px-3 py-2 text-left text-slate-500">
                                Status
                            </th>

                            <th class="px-3 py-2 text-right text-slate-500">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        ${items.map(item=>`
                            <tr>
                                <td class="px-3 py-2 text-xs 2xl:text-sm">
                                    <div class="font-medium text-slate-600">
                                        ${esc(
                                            item.expense_no
                                        )}
                                    </div>

                                    ${
                                        item.reference_no
                                            ?`
                                            <div class="mt-0.5 text-[10px] text-slate-400">
                                                Ref: ${esc(
                                                    item.reference_no
                                                )}
                                            </div>
                                            `
                                            :''
                                    }
                                </td>

                                <td class="px-3 py-2 text-slate-500">
                                    ${esc(
                                        item.category
                                    )}
                                </td>

                                <td class="px-3 py-2 text-slate-500">
                                    ${esc(
                                        accountName(
                                            item.payment_account
                                        )
                                    )}
                                </td>

                                <td class="px-3 py-2 text-slate-500">
                                    ${esc(
                                        item.payee||
                                        '—'
                                    )}
                                </td>

                                <td class="px-3 py-2 text-slate-500">
                                    ${date(
                                        item.expense_date
                                    )}
                                </td>

                                <td class="px-3 py-2 text-right font-semibold text-slate-700">
                                    ${money(
                                        item.amount
                                    )}
                                </td>

                                <td class="px-3 py-2 text-xs 2xl:text-sm">
                                    ${AdminUI.statusBadge(
                                        item.status
                                    )}
                                </td>

                                <td class="px-3 py-2 text-right">
                                    ${
                                        canUpdate&&
                                        item.status==='posted'
                                            ?`
                                            <button type="button"
                                                onclick="cancelExpense(${item.id})"
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

function emptyBlock(message){
    return`
        <div class="rounded-md border border-slate-200 bg-white py-8 text-center text-base text-slate-400">
            ${esc(message)}
        </div>
    `;
}

window.openParticipantModal=function(){
    if(!selectedTour){
        return;
    }

    editingParticipant=null;

    $('participantForm').reset();

    $('participantId').value='';

    $('participantStatus').value=
        'registered';

    $('participantMemberId').disabled=
        false;

    $('participantMemberWrap')
        .classList
        .remove(
            'opacity-60'
        );

    $('participantModalTitle').textContent=
        'Add Participant';

    AdminUI.clearError(
        'participantError'
    );

    AdminUI.clearFieldErrors(
        'participantForm'
    );

    AdminUI.openModal(
        'participantModal'
    );
};

window.editParticipant=function(id){
    const participant=
        (selectedTour.participants||[])
            .find(
                item=>
                    Number(item.id)===
                    Number(id)
            );

    if(!participant){
        return;
    }

    editingParticipant=
        participant;

    $('participantId').value=
        participant.id;

    $('participantMemberId').value=
        participant.member_id||'';

    $('participantMemberId').disabled=
        true;

    $('participantMemberWrap')
        .classList
        .add(
            'opacity-60'
        );

    $('participantStatus').value=
        participant.status||
        'registered';

    $('participantNotes').value=
        participant.notes||'';

    $('participantModalTitle').textContent=
        'Update Participant';

    AdminUI.clearError(
        'participantError'
    );

    AdminUI.clearFieldErrors(
        'participantForm'
    );

    AdminUI.openModal(
        'participantModal'
    );
};

$('participantForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'participantError'
        );
const data={
            status:
                $('participantStatus').value,

            notes:
                $('participantNotes')
                    .value
                    .trim()||
                null
        };

        if(!editingParticipant){
            data.member_id=
                Number(
                    $('participantMemberId')
                        .value
                );
        }

        const button=
            $('saveParticipantButton');

        AdminUI.setLoading(
            button,
            'Saving...'
        );

        try{
            await api(
                editingParticipant
                    ?`/api/tours/participants/${editingParticipant.id}`
                    :`/api/tours/${selectedTour.id}/participants`,
                {
                    method:
                        editingParticipant
                            ?'PUT'
                            :'POST',

                    body:
                        JSON.stringify(
                            data
                        )
                }
            );

            AdminUI.closeModal(
                'participantModal'
            );

            Toast.success(
                editingParticipant
                    ?'Participant updated successfully.'
                    :'Participant added successfully.'
            );

            await Promise.all([
                refreshDetails(),
                loadTours(
                    currentPage
                )
            ]);
        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    'participantForm',
                    error,
                    {
                        member_id:'participantMemberId',
                        status:'participantStatus',
                        notes:'participantNotes'
                    }
                )
            ){
                AdminUI.showError(
                    'participantError',
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

window.deleteParticipant=function(id){
    AdminUI.deleteRequest(
        `/api/tours/participants/${id}`,
        {
            message:
                'Remove this participant from the tour?',

            successMessage:
                'Participant removed successfully.',

            onSuccess:async()=>{
                await Promise.all([
                    refreshDetails(),
                    loadTours(
                        currentPage
                    )
                ]);
            }
        }
    );
};

window.openExpenseModal=function(){
    if(!selectedTour){
        return;
    }

    $('expenseForm').reset();

    AdminUI.clearError(
        'expenseError'
    );

    AdminUI.clearFieldErrors(
        'expenseForm'
    );

    AdminUI.openModal(
        'expenseModal'
    );

    window.initDatePickers?.();

    setDate(
        'expenseDate',
        selectedTour.start_date||
        today()
    );
};

$('expenseForm').addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'expenseError'
        );
const amount=
            Number(
                $('expenseAmount').value
            );
const data={
            category:
                $('expenseCategory')
                    .value
                    .trim(),

            expense_account_id:
                Number(
                    $('expenseAccountId')
                        .value
                ),

            payment_account_id:
                Number(
                    $('paymentAccountId')
                        .value
                ),

            amount,

            expense_date:
                $('expenseDate').value,

            payee:
                $('expensePayee')
                    .value
                    .trim()||
                null,

            reference_no:
                $('expenseReference')
                    .value
                    .trim()||
                null,

            description:
                $('expenseDescription')
                    .value
                    .trim()||
                null
        };

        const button=
            $('saveExpenseButton');

        AdminUI.setLoading(
            button,
            'Posting...'
        );

        try{
            await api(
                `/api/tours/${selectedTour.id}/expenses`,
                {
                    method:'POST',
                    body:
                        JSON.stringify(
                            data
                        )
                }
            );

            AdminUI.closeModal(
                'expenseModal'
            );

            Toast.success(
                'Tour expense posted successfully.'
            );

            await Promise.all([
                refreshDetails(),
                loadTours(
                    currentPage
                ),
                loadStatistics()
            ]);
        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    'expenseForm',
                    error,
                    {
                        category:'expenseCategory',
                        expense_account_id:'expenseAccountId',
                        payment_account_id:'paymentAccountId',
                        amount:'expenseAmount',
                        expense_date:'expenseDate',
                        payee:'expensePayee',
                        reference_no:'expenseReference',
                        description:'expenseDescription'
                    }
                )
            ){
                AdminUI.showError(
                    'expenseError',
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

window.cancelExpense=function(id){
    AdminUI.confirm({
        title:'Reverse Expense',

        message:
            'This will reverse the accounting journal for this expense. Continue?',

        confirmText:'Reverse',

        confirmClass:
            'bg-red-600 hover:bg-red-700',

        onConfirm:async()=>{
            try{
                await api(
                    `/api/tours/expenses/${id}/cancel`,
                    {
                        method:'POST'
                    }
                );

                Toast.success(
                    'Tour expense reversed successfully.'
                );

                await Promise.all([
                    refreshDetails(),
                    loadTours(
                        currentPage
                    ),
                    loadStatistics()
                ]);
            }catch(error){
                Toast.error(
                    AdminUI.extractError(
                        error
                    )
                );
            }
        }
    });
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('statusFilter').value='';
    $('yearFilter').value='';

    loadTours(1);
};

function debounce(
    callback,
    delay=350
){
    let timer;

    return(...args)=>{
        clearTimeout(
            timer
        );

        timer=setTimeout(
            ()=>callback(...args),
            delay
        );
    };
}

async function initTourPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initTourPage,
            50
        );

        return;
    }

    initYears();

    window.initDatePickers?.();

    $('searchInput').addEventListener(
        'input',
        debounce(
            ()=>loadTours(1)
        )
    );

    $('statusFilter').addEventListener(
        'change',
        ()=>loadTours(1)
    );

    $('yearFilter').addEventListener(
        'change',
        ()=>loadTours(1)
    );

    await Promise.all([
        loadOptions(),
        loadStatistics(),
        loadTours()
    ]);
}

if(
    document.readyState==='loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        initTourPage
    );
}else{
    initTourPage();
}
</script>
@endpush