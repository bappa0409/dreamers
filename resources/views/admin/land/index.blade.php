@extends('layouts.admin')

@section('title','Land Management')
@section('page_title','Land Management')

@section('content')
<div class="space-y-3">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-map"></i>
            </div>
            <div>
                <h1 class="text-base font-bold tracking-tight text-slate-800">Land Management</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">Manage association-owned land, valuation, documents and sales.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Land.create'))
            <button type="button" onclick="openLandModal()" class="inline-flex w-fit cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="bi bi-plus-lg"></i>
                Add Land
            </button>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="grid grid-cols-2 gap-3 md:grid-cols-4">

    {{-- Purchase Value --}}
    <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-slate-500 2xl:text-sm">
                    Purchase Value
                </p>

                <p id="purchaseValue" class="mt-1 truncate text-xl font-bold text-slate-800">
                    {{ setting('currency_symbol','৳') }}0
                </p>
            </div>

            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    {{-- Current Value --}}
    <div class="rounded-md border border-indigo-200 bg-indigo-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-indigo-700 2xl:text-sm">
                    Current Value
                </p>

                <p id="currentValue" class="mt-1 truncate text-xl font-bold text-indigo-600">
                    {{ setting('currency_symbol','৳') }}0
                </p>
            </div>

            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-100 text-indigo-600">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>
    </div>

    {{-- Sold --}}
    <div class="rounded-md border border-emerald-200 bg-emerald-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-emerald-700 2xl:text-sm">
                    Sold
                </p>

                <p id="soldCount" class="mt-1 text-xl font-bold text-emerald-600">
                    0
                </p>
            </div>

            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>

    {{-- Profit / Loss --}}
    <div class="rounded-md border border-amber-200 bg-amber-50/50 px-5 py-2">
        <div class="flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-amber-700 2xl:text-sm">
                    Profit / Loss
                </p>

                <p id="profitLoss" class="mt-1 truncate text-xl font-bold text-amber-600">
                    {{ setting('currency_symbol','৳') }}0
                </p>
            </div>

            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-amber-100 text-amber-600">
                <i class="bi bi-bar-chart"></i>
            </div>
        </div>
    </div>

</div>

    {{-- Search Land --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            {{-- Header --}}
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Land
                    </p>
                    <p class="hidden text-[11px] text-slate-500 sm:block">
                        Code, title, location, mouza, khatian, dag or deed.
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center gap-2 lg:w-auto">

                {{-- Search --}}
                <div class="relative min-w-0 flex-1 lg:w-[280px]">
                    <i
                        class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-500 2xl:text-sm"></i>

                    <input id="searchInput" type="text" placeholder="Search land..."
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
                        <option value="planned">Planned</option>
                        <option value="negotiating">Negotiating</option>
                        <option value="purchased">Purchased</option>
                        <option value="sold">Sold</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    {{-- Listing --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="w-[24%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Land</th>
                        <th class="w-[18%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Location</th>
                        <th class="w-[12%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Area</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Purchase Price</th>
                        <th class="w-[13%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Current Value</th>
                        <th class="w-[10%] px-3 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="w-[10%] px-3 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="landTable">
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500">Loading lands...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="landCards" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-sm text-slate-500">Loading lands...</div>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- =====================================================================
LAND CREATE / EDIT MODAL
===================================================================== --}}
<div id="landModal"
     class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-map"></i>
                </div>
                <div>
                    <h2 id="landModalTitle" class="text-sm font-semibold text-slate-800">Add Land</h2>
                    <p class=" text-xs 2xl:text-sm text-slate-500">Association-owned property information.</p>
                </div>
            </div>

            <button type="button" onclick="closeLandModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="landForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">

                {{-- Basic Information --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Basic Information</h3>
                            <p class="text-[11px] text-slate-500">Title and description of the land.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="form-label">Title <span class="text-red-500">*</span></label>
                            <input id="title" maxlength="255" class="app-input" placeholder="Land title">
                        </div>

                        <div>
                            <label class="form-label">Description</label>
                            <textarea id="description" rows="2" class="app-input resize-none"></textarea>
                        </div>
                    </div>
                </section>

                {{-- Location --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Location</h3>
                            <p class="text-[11px] text-slate-500">Where the land is situated.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="form-label">District</label>
                            <select id="district" class="app-input">
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Upazila</label>
                            <select id="upazila" class="app-input">
                                <option value="">Select District First</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Mouza</label>
                            <input id="mouza" class="app-input">
                        </div>
                    </div>
                </section>

                {{-- Property --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-50 text-amber-600">
                            <i class="bi bi-bounding-box"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Property Information</h3>
                            <p class="text-[11px] text-slate-500">Legal identifiers and status.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="form-label">Khatian No</label>
                            <input id="khatianNo" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Dag No</label>
                            <input id="dagNo" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Status</label>
                            <select id="status" class="app-input">
                                <option value="planned">Planned</option>
                                <option value="negotiating">Negotiating</option>
                                <option value="purchased">Purchased</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Land Area</label>
                            <input id="landArea" type="number" min="0.0001" step="0.0001" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Area Unit</label>
                            <select id="areaUnit" class="app-input">
                                <option value="decimal">Decimal</option>
                                <option value="katha">Katha</option>
                                <option value="bigha">Bigha</option>
                                <option value="acre">Acre</option>
                                <option value="sqft">Sq Ft</option>
                                <option value="hectare">Hectare</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label">Purchase Date</label>
                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>
                                <input id="purchaseDate"
                                       type="text"
                                       class="app-input js-date-picker !pl-9"
                                       placeholder="Select date"
                                       autocomplete="off">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Deed No</label>
                            <input id="deedNo" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Registration No</label>
                            <input id="registrationNo" class="app-input">
                        </div>
                    </div>
                </section>

                {{-- Finance --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Financial Information</h3>
                            <p class="text-[11px] text-slate-500">Purchase price and valuation.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="form-label">Purchase Price</label>
                            <input id="purchasePrice" type="number" min="0" step="0.01" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Current Value</label>
                            <input id="currentValueInput" type="number" min="0" step="0.01" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Payment Account</label>
                            <select id="paymentAccount" class="app-input">
                                <option value="">Select cash / bank</option>
                            </select>
                            <p class="mt-1 text-[10px] text-slate-500">
                                Required when status is Purchased.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Seller --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                            <i class="bi bi-person"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Seller Information</h3>
                            <p class="text-[11px] text-slate-500">Contact details of the seller.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="form-label">Seller Name</label>
                            <input id="sellerName" class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Seller Phone</label>
                            <input id="sellerPhone" class="app-input">
                        </div>
                    </div>
                </section>

                {{-- Notes --}}
                <section class="rounded-md border border-slate-200 bg-white px-5 py-2">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">Notes</h3>
                            <p class="text-[11px] text-slate-500">Any additional remarks.</p>
                        </div>
                    </div>

                    <textarea id="notes" rows="3" class="app-input resize-none"></textarea>
                </section>

                <div id="landError"
                     class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button type="button"
                        onclick="closeLandModal()"
                        class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i> Close
                </button>

                <button id="saveLandButton"
                        type="submit"
                        class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    <span>Save Land</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
DETAILS MODAL
===================================================================== --}}
<div id="landDetailsModal"
     class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[95vh] w-full max-w-6xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-map"></i>
                </div>

                <div class="min-w-0">
                    <h2 id="detailsTitle" class="truncate text-sm font-semibold text-slate-800">Land Details</h2>
                    <p id="detailsCode" class=" text-xs 2xl:text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button"
                    onclick="AdminUI.closeModal('landDetailsModal')"
                    class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">
            <div id="landDetailsLoading" class="py-16 text-center text-base text-slate-500">
                Loading land...
            </div>

            <div id="landDetailsContent" class="hidden space-y-5">

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase text-slate-500">Purchase Price</p>
                        <p id="detailPurchasePrice" class="mt-1 text-base font-bold text-slate-700"></p>
                    </div>

                    <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase text-indigo-400">Current Value</p>
                        <p id="detailCurrentValue" class="mt-1 text-base font-bold text-indigo-700"></p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase text-slate-500">Area</p>
                        <p id="detailArea" class="mt-1 text-base font-bold text-slate-700"></p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase text-slate-500">Status</p>
                        <div id="detailStatus" class="mt-1"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div class="rounded-md border border-slate-200">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                                Property Information
                            </h3>
                        </div>
                        <div id="propertyInformation" class="grid grid-cols-2 gap-4 p-4 text-sm"></div>
                    </div>

                    <div class="rounded-md border border-slate-200">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500">
                                Purchase Information
                            </h3>
                        </div>
                        <div id="purchaseInformation" class="grid grid-cols-2 gap-4 p-4 text-sm"></div>
                    </div>
                </div>

                {{-- Valuations --}}
                <div class="rounded-md border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-700">Valuation History</h3>
                            <p class="text-[11px] text-slate-500">Historical market value changes.</p>
                        </div>

                        @if(auth()->user()->hasPermission('Land.update'))
                        <button id="addValuationButton"
                                type="button"
                                onclick="openValuationModal()"
                                class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-100">
                            <i class="bi bi-graph-up-arrow"></i>
                            Add Valuation
                        </button>
                        @endif
                    </div>

                    <div id="valuationList"></div>
                </div>

                {{-- Documents --}}
                <div class="rounded-md border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                        <div>
                            <h3 class="text-base font-bold text-slate-700">Documents</h3>
                            <p class="text-[11px] text-slate-500">Legal and property documents.</p>
                        </div>

                        @if(auth()->user()->hasPermission('Land.update'))
                        <button id="uploadDocumentButton"
                                type="button"
                                onclick="openDocumentModal()"
                                class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-100">
                            <i class="bi bi-cloud-arrow-up"></i>
                            Upload
                        </button>
                        @endif
                    </div>

                    <div id="documentList"></div>
                </div>

                {{-- Disposal --}}
                <div id="disposalSection"
                     class="hidden rounded-md border border-emerald-200 bg-emerald-50/30">
                    <div class="border-b border-emerald-100 px-4 py-3">
                        <h3 class="text-base font-bold text-emerald-700">
                            Sale / Disposal
                        </h3>
                    </div>

                    <div id="disposalInformation"
                         class="grid grid-cols-2 gap-4 p-4 text-sm md:grid-cols-4"></div>
                </div>

                {{-- Accounting --}}
                <div id="accountingSection"
                     class="hidden rounded-md border border-slate-200">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h3 class="text-base font-bold text-slate-700">
                            Accounting
                        </h3>
                    </div>

                    <div id="accountingInformation" class="p-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- =====================================================================
VALUATION MODAL
===================================================================== --}}
<div id="valuationModal"
     class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Add Valuation</h2>
                <p class=" text-xs 2xl:text-sm text-slate-500">Update current market value.</p>
            </div>

            <button type="button"
                    onclick="AdminUI.closeModal('valuationModal')"
                    class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="valuationForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <input id="valuationLandId" type="hidden">

                <div>
                    <label class="form-label">Valuation Date *</label>
                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>
                        <input id="valuationDate"
                               type="text"
                               class="app-input js-date-picker !pl-9"
                               autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label">Current Value *</label>
                    <input id="valuationCurrentValue"
                           type="number"
                           min="0"
                           step="0.01"
                           class="app-input">
                </div>

                <div>
                    <label class="form-label">Valued By</label>
                    <input id="valuedBy" maxlength="255" class="app-input">
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea id="valuationNotes"
                              rows="3"
                              class="app-input resize-none"></textarea>
                </div>

                <div id="valuationError"
                     class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                        onclick="AdminUI.closeModal('valuationModal')"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="valuationButton"
                        type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Save Valuation
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
DOCUMENT MODAL
===================================================================== --}}
<div id="documentModal"
     class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Upload Document</h2>
                <p class=" text-xs 2xl:text-sm text-slate-500">PDF or image, maximum 10MB.</p>
            </div>

            <button type="button"
                    onclick="AdminUI.closeModal('documentModal')"
                    class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="documentForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <input id="documentLandId" type="hidden">

                <div>
                    <label class="form-label">Document Type *</label>
                    <select id="documentType" class="app-input">
                        <option value="deed">Deed</option>
                        <option value="registration">Registration</option>
                        <option value="mutation">Mutation</option>
                        <option value="khatian">Khatian</option>
                        <option value="tax_receipt">Tax Receipt</option>
                        <option value="survey">Survey</option>
                        <option value="valuation">Valuation</option>
                        <option value="agreement">Agreement</option>
                        <option value="map">Map</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Document Number</label>
                    <input id="documentNumber" class="app-input">
                </div>

                <div>
                    <label class="form-label">Document Date</label>
                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>
                        <input id="documentDate"
                               type="text"
                               class="app-input js-date-picker !pl-9"
                               autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label">File *</label>
                    <input id="documentFile"
                           type="file"
                           accept=".pdf,.jpg,.jpeg,.png,.webp"
                           class="app-input">
                </div>

                <div>
                    <label class="form-label">Description</label>
                    <textarea id="documentDescription"
                              rows="3"
                              class="app-input resize-none"></textarea>
                </div>

                <div id="documentError"
                     class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                        onclick="AdminUI.closeModal('documentModal')"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="documentButton"
                        type="submit"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Upload
                </button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
SELL MODAL
===================================================================== --}}
<div id="sellModal"
     class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel max-h-[92vh] w-full max-w-xl overflow-y-auto rounded-md bg-white">

        <div class="flex items-start justify-between border-b border-slate-200 px-5 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">Sell Land</h2>
                <p id="sellLandInfo" class=" text-xs 2xl:text-sm text-slate-500"></p>
            </div>

            <button type="button"
                    onclick="AdminUI.closeModal('sellModal')"
                    class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="sellForm" novalidate data-js-validation="1">
            <div class="space-y-4 p-5">
                <input id="sellLandId" type="hidden">

                <div>
                    <label class="form-label">Sale Price *</label>
                    <input id="salePrice"
                           type="number"
                           min="0.01"
                           step="0.01"
                           class="app-input">
                </div>

                <div>
                    <label class="form-label">Selling Expense</label>
                    <input id="sellingExpense"
                           type="number"
                           min="0"
                           step="0.01"
                           value="0"
                           class="app-input">
                </div>

                <div>
                    <label class="form-label">Sale Date *</label>
                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-500"></i>
                        <input id="saleDate"
                               type="text"
                               class="app-input js-date-picker !pl-9"
                               autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label">Receive Account *</label>
                    <select id="receiveAccount" class="app-input">
                        <option value="">Select cash / bank</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Buyer Name</label>
                        <input id="buyerName" class="app-input">
                    </div>

                    <div>
                        <label class="form-label">Buyer Phone</label>
                        <input id="buyerPhone" class="app-input">
                    </div>
                </div>

                <div>
                    <label class="form-label">Reference No</label>
                    <input id="saleReference" maxlength="150" class="app-input">
                </div>

                <div>
                    <label class="form-label">Notes</label>
                    <textarea id="saleNotes"
                              rows="3"
                              class="app-input resize-none"></textarea>
                </div>

                <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                    <div class="grid grid-cols-3 gap-3 text-sm">
                        <div>
                            <p class="text-[10px] text-slate-500">Net Receipt</p>
                            <p id="saleNetPreview" class="mt-1 font-bold text-slate-700">
                                {{ setting('currency_symbol','৳') }}0
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-500">Book Value</p>
                            <p id="saleBookPreview" class="mt-1 font-bold text-slate-700">
                                {{ setting('currency_symbol','৳') }}0
                            </p>
                        </div>

                        <div>
                            <p class="text-[10px] text-slate-500">Profit / Loss</p>
                            <p id="saleProfitPreview" class="mt-1 font-bold text-slate-700">
                                {{ setting('currency_symbol','৳') }}0
                            </p>
                        </div>
                    </div>
                </div>

                <div id="sellError"
                     class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700"></div>
            </div>

            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
                <button type="button"
                        onclick="AdminUI.closeModal('sellModal')"
                        class="rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button id="sellButton"
                        type="submit"
                        class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Confirm Sale
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
@endsection

@push('scripts')
<script>
let lands=[];
let editingLand=null;
let selectedLand=null;
let accounts=[];
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(auth()->user()->hasPermission('Land.update'));
const canDelete=@json(auth()->user()->hasPermission('Land.delete'));
const currency=@json(setting('currency_symbol','৳'));

const el={
    table:document.getElementById('landTable'),
    cards:document.getElementById('landCards'),
    search:document.getElementById('searchInput'),
    statusFilter:document.getElementById('statusFilter'),
    form:document.getElementById('landForm'),
    title:document.getElementById('title'),
    description:document.getElementById('description'),
    district:document.getElementById('district'),
    upazila:document.getElementById('upazila'),
    mouza:document.getElementById('mouza'),
    khatianNo:document.getElementById('khatianNo'),
    dagNo:document.getElementById('dagNo'),
    landArea:document.getElementById('landArea'),
    areaUnit:document.getElementById('areaUnit'),
    purchasePrice:document.getElementById('purchasePrice'),
    currentValue:document.getElementById('currentValueInput'),
    purchaseDate:document.getElementById('purchaseDate'),
    sellerName:document.getElementById('sellerName'),
    sellerPhone:document.getElementById('sellerPhone'),
    deedNo:document.getElementById('deedNo'),
    registrationNo:document.getElementById('registrationNo'),
    paymentAccount:document.getElementById('paymentAccount'),
    status:document.getElementById('status'),
    notes:document.getElementById('notes'),
    saveButton:document.getElementById('saveLandButton')
};

/*
|--------------------------------------------------------------------------
| Bangladesh District -> Upazila Map
|--------------------------------------------------------------------------
| Covers all 64 districts. Single source of truth for the District/Upazila
| selects on this page.
*/
const bdDistrictUpazilas={
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

    const districts=Object.keys(bdDistrictUpazilas).sort();

    el.district.innerHTML=
        '<option value="">Select District</option>'+
        districts.map(d=>
            `<option value="${AdminUI.escapeHtml(d)}">${AdminUI.escapeHtml(d)}</option>`
        ).join('');
}

function populateUpazilaSelect(district,selectedUpazila=''){
    if(!el.upazila)return;

    const upazilas=bdDistrictUpazilas[district]??[];

    if(!district||!upazilas.length){
        el.upazila.innerHTML='<option value="">Select District First</option>';
        el.upazila.disabled=true;
        return;
    }

    el.upazila.disabled=false;

    el.upazila.innerHTML=
        '<option value="">Select Upazila</option>'+
        upazilas.map(u=>
            `<option value="${AdminUI.escapeHtml(u)}" ${u===selectedUpazila?'selected':''}>${AdminUI.escapeHtml(u)}</option>`
        ).join('');

    // Existing land data may hold an upazila that isn't in the list above
    // (older free-text entry) — keep it selectable so it isn't silently lost.
    if(selectedUpazila&&!upazilas.includes(selectedUpazila)){
        el.upazila.insertAdjacentHTML(
            'beforeend',
            `<option value="${AdminUI.escapeHtml(selectedUpazila)}" selected>${AdminUI.escapeHtml(selectedUpazila)}</option>`
        );
    }
}

function money(value){
    return currency+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function setPickerDate(element,value){
    if(!element)return;
    const date=value?String(value).substring(0,10):'';
    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(date,false)
            :element._flatpickr.clear();
    }
}

function infoItem(label,value){
    return `
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-wide text-slate-500">
                ${AdminUI.escapeHtml(label)}
            </p>
            <p class="mt-1 break-words font-semibold text-slate-700">
                ${AdminUI.escapeHtml(value??'—')}
            </p>
        </div>
    `;
}

function locationText(item){
    return[
        item.mouza,
        item.upazila,
        item.district
    ].filter(Boolean).join(', ')||'—';
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

async function loadOptions(){
    try{
        const response=await api('/api/lands/options');
        const data=response.data??{};
        accounts=Array.isArray(data.accounts)?data.accounts:[];

        const html=`
            <option value="">Select cash / bank</option>
            ${accounts.map(account=>`
                <option value="${account.id}">
                    ${AdminUI.escapeHtml(account.code)} - ${AdminUI.escapeHtml(account.name)}
                </option>
            `).join('')}
        `;

        el.paymentAccount.innerHTML=html;
        document.getElementById('receiveAccount').innerHTML=html;
    }catch(error){
        console.error('Land options failed:',error);
    }
}

async function loadLands(page=1){
    currentPage=page;

    el.table.innerHTML=AdminUI.loadingState('Loading lands...',7);
    el.cards.innerHTML=cardsLoadingHtml('Loading lands...');

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        page
    });

    try{
        const response=await api(`/api/lands?${query}`);
        const paginator=response.data??{};

        lands=Array.isArray(paginator.data)?paginator.data:[];
        currentPage=Number(paginator.current_page??1);
        lastPage=Number(paginator.last_page??1);
        total=Number(paginator.total??0);

        renderLands();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadLands
        });
    }catch(error){
        el.table.innerHTML=AdminUI.emptyState(AdminUI.extractError(error),7);
        el.cards.innerHTML=cardsEmptyHtml(AdminUI.extractError(error));
    }
}

async function loadStatistics(){
    try{
        const response=await api('/api/lands/statistics');
        const stats=response.data??{};

        document.getElementById('purchaseValue').innerText=money(stats.purchase_value);
        document.getElementById('currentValue').innerText=money(stats.current_value);
        document.getElementById('soldCount').innerText=stats.sold??0;

        const profitElement=document.getElementById('profitLoss');
        const profit=Number(stats.profit_loss??0);

        profitElement.innerText=money(profit);
        profitElement.classList.remove(
            'text-slate-800',
            'text-emerald-700',
            'text-red-700'
        );

        profitElement.classList.add(
            profit>0
                ?'text-emerald-700'
                :profit<0
                    ?'text-red-700'
                    :'text-slate-800'
        );
    }catch(error){
        console.error(error);
    }
}

function landActionButtons(item,{withLabel=false}={}){
    const buttons=[];

    buttons.push(`
        <button type="button"
                onclick="viewLand(${item.id})"
                title="View Details"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-slate-100 text-slate-600 transition hover:bg-slate-200">
            <i class="bi bi-eye text-sm"></i>
            ${withLabel?'View':''}
        </button>
    `);

    if(canUpdate&&item.status==='purchased'){
        buttons.push(`
            <button type="button"
                    onclick="openSellModal(${item.id})"
                    title="Sell Land"
                    class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100">
                <i class="bi bi-cash-stack text-sm"></i>
                ${withLabel?'Sell':''}
            </button>
        `);
    }

    if(canUpdate&&item.status!=='sold'){
        buttons.push(`
            <button type="button"
                    onclick="editLand(${item.id})"
                    title="Edit Land"
                    class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100">
                <i class="bi bi-pencil-square text-sm"></i>
                ${withLabel?'Edit':''}
            </button>
        `);
    }

    if(canDelete&&item.status!=='sold'){
        buttons.push(`
            <button type="button"
                    onclick="deleteLand(${item.id})"
                    title="Delete Land"
                    class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-red-50 text-red-600 transition hover:bg-red-100">
                <i class="bi bi-trash text-sm"></i>
                ${withLabel?'Delete':''}
            </button>
        `);
    }

    return buttons.join('');
}

function renderLands(){
    if(!lands.length){
        el.table.innerHTML=AdminUI.emptyState('No lands found.',7);
        el.cards.innerHTML=cardsEmptyHtml('No lands found.');
        return;
    }

    el.table.innerHTML=lands.map(item=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            <td class="min-w-0 px-3 py-3">
                <p class="truncate font-mono text-xs 2xl:text-sm font-semibold text-indigo-600">
                    ${AdminUI.escapeHtml(item.land_code??'—')}
                </p>
                <p class="mt-0.5 truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                    ${AdminUI.escapeHtml(item.title??'Untitled')}
                </p>
            </td>

            <td class="px-3 py-3">
                <p class="truncate text-xs 2xl:text-sm text-slate-600">
                    ${AdminUI.escapeHtml(locationText(item))}
                </p>
            </td>

            <td class="px-3 py-3 text-xs 2xl:text-sm text-slate-600">
                ${
                    item.land_area
                        ?`${AdminUI.escapeHtml(item.land_area)} ${AdminUI.escapeHtml(item.area_unit??'')}`
                        :'—'
                }
            </td>

            <td class="px-3 py-3 text-xs 2xl:text-sm font-semibold text-slate-700">
                ${money(item.purchase_price)}
            </td>

            <td class="px-3 py-3 text-xs 2xl:text-sm font-semibold text-indigo-700">
                ${money(item.current_value)}
            </td>

            <td class="px-3 py-3">
                ${AdminUI.statusBadge(item.status)}
            </td>

            <td class="px-3 py-3">
                <div class="flex items-center justify-end gap-1">
                    ${landActionButtons(item)}
                </div>
            </td>
        </tr>
    `).join('');

    el.cards.innerHTML=lands.map(item=>`
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="truncate font-mono text-xs 2xl:text-sm font-semibold text-indigo-600">
                        ${AdminUI.escapeHtml(item.land_code??'—')}
                    </p>
                    <p class="mt-0.5 truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                        ${AdminUI.escapeHtml(item.title??'Untitled')}
                    </p>
                    <p class="mt-1 truncate text-[11px] text-slate-500">
                        <i class="bi bi-geo-alt me-1"></i>
                        ${AdminUI.escapeHtml(locationText(item))}
                    </p>
                </div>

                <div class="shrink-0">
                    ${AdminUI.statusBadge(item.status)}
                </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 rounded-md bg-slate-50/60 p-3 text-[11px]">
                <div class="min-w-0">
                    <p class="text-slate-500 text-xs 2xl:text-sm">Purchase Price</p>
                    <p class="truncate font-semibold text-slate-700">
                        ${money(item.purchase_price)}
                    </p>
                </div>

                <div class="min-w-0">
                    <p class="text-slate-500 text-xs 2xl:text-sm">Current Value</p>
                    <p class="truncate font-semibold text-indigo-700">
                        ${money(item.current_value)}
                    </p>
                </div>

                <div class="min-w-0">
                    <p class="text-slate-500 text-xs 2xl:text-sm">Area</p>
                    <p class="truncate font-medium text-slate-700">
                        ${
                            item.land_area
                                ?`${AdminUI.escapeHtml(item.land_area)} ${AdminUI.escapeHtml(item.area_unit??'')}`
                                :'—'
                        }
                    </p>
                </div>

                <div class="min-w-0">
                    <p class="text-slate-500 text-xs 2xl:text-sm">Purchased</p>
                    <p class="truncate font-medium text-slate-700">
                        ${item.purchase_date?AdminUI.formatDate(item.purchase_date):'—'}
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center gap-1.5 border-t border-slate-100 pt-3">
                ${landActionButtons(item,{withLabel:true})}
            </div>
        </div>
    `).join('');
}

window.openLandModal=function(item=null){
    editingLand=item;

    AdminUI.resetForm(el.form);
    AdminUI.clearError('landError');

    document.getElementById('landModalTitle').innerText=
        item?'Edit Land':'Add Land';

    populateDistrictSelect();

    if(item){
        el.title.value=item.title??'';
        el.description.value=item.description??'';
        el.district.value=item.district??'';
        populateUpazilaSelect(item.district??'',item.upazila??'');
        el.mouza.value=item.mouza??'';
        el.khatianNo.value=item.khatian_no??'';
        el.dagNo.value=item.dag_no??'';
        el.landArea.value=item.land_area??'';
        el.areaUnit.value=item.area_unit??'decimal';
        el.purchasePrice.value=item.purchase_price??0;
        el.currentValue.value=item.current_value??'';
        el.sellerName.value=item.seller_name??'';
        el.sellerPhone.value=item.seller_phone??'';
        el.deedNo.value=item.deed_no??'';
        el.registrationNo.value=item.registration_no??'';
        el.paymentAccount.value=item.payment_account_id??'';
        el.status.value=item.status??'planned';
        el.notes.value=item.notes??'';

        setPickerDate(el.purchaseDate,item.purchase_date);
    }else{
        el.status.value='planned';
        el.areaUnit.value='decimal';
        populateUpazilaSelect('');
        setPickerDate(el.purchaseDate,null);
    }

    AdminUI.openModal('landModal');

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }
};

window.closeLandModal=function(){
    AdminUI.closeModal('landModal');
    editingLand=null;
};

window.editLand=async function(id){
    try{
        const response=await api(`/api/lands/${id}`);
        openLandModal(response.data);
    }catch(error){
        Toast.error(AdminUI.extractError(error));
    }
};

el.form.addEventListener('submit',async event=>{
    event.preventDefault();
    AdminUI.clearError('landError');

    const title=el.title.value.trim();

    if(!title){
        AdminUI.showError('landError','Land title is required.');
        return;
    }

    const status=el.status.value;

    if(status==='purchased'){
        if(!el.purchasePrice.value||Number(el.purchasePrice.value)<=0){
            AdminUI.showError('landError','Purchase price is required for purchased land.');
            return;
        }

        if(!el.purchaseDate.value){
            AdminUI.showError('landError','Purchase date is required for purchased land.');
            return;
        }

        if(!el.paymentAccount.value){
            AdminUI.showError('landError','Payment account is required for purchased land.');
            return;
        }
    }

    const data={
        title,
        description:el.description.value.trim()||null,
        district:el.district.value.trim()||null,
        upazila:el.upazila.value.trim()||null,
        mouza:el.mouza.value.trim()||null,
        khatian_no:el.khatianNo.value.trim()||null,
        dag_no:el.dagNo.value.trim()||null,
        land_area:el.landArea.value||null,
        area_unit:el.areaUnit.value,
        purchase_price:el.purchasePrice.value||0,
        current_value:el.currentValue.value||null,
        purchase_date:el.purchaseDate.value||null,
        seller_name:el.sellerName.value.trim()||null,
        seller_phone:el.sellerPhone.value.trim()||null,
        deed_no:el.deedNo.value.trim()||null,
        registration_no:el.registrationNo.value.trim()||null,
        payment_account_id:el.paymentAccount.value||null,
        status,
        notes:el.notes.value.trim()||null
    };

    AdminUI.setLoading(
        el.saveButton,
        editingLand?'Updating...':'Saving...'
    );

    try{
        const editing=Boolean(editingLand);

        await api(
            editing
                ?`/api/lands/${editingLand.id}`
                :'/api/lands',
            {
                method:editing?'PUT':'POST',
                body:JSON.stringify(data)
            }
        );

        closeLandModal();

        Toast.success(
            editing
                ?'Land updated successfully.'
                :'Land created successfully.'
        );

        await Promise.all([
            loadLands(editing?currentPage:1),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'landError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(el.saveButton);
    }
});

window.viewLand=async function(id){
    const loading=document.getElementById('landDetailsLoading');
    const content=document.getElementById('landDetailsContent');

    loading.innerHTML='Loading land...';
    loading.classList.remove('hidden');
    content.classList.add('hidden');

    AdminUI.openModal('landDetailsModal');

    try{
        const response=await api(`/api/lands/${id}`);
        selectedLand=response.data;

        renderLandDetails(selectedLand);

        loading.classList.add('hidden');
        content.classList.remove('hidden');
    }catch(error){
        loading.innerHTML=`
            <div class="text-red-600">
                ${AdminUI.escapeHtml(AdminUI.extractError(error))}
            </div>
        `;
    }
};

function renderLandDetails(land){
    document.getElementById('detailsTitle').innerText=land.title??'Land Details';
    document.getElementById('detailsCode').innerText=land.land_code??'';

    document.getElementById('detailPurchasePrice').innerText=
        money(land.purchase_price);

    document.getElementById('detailCurrentValue').innerText=
        money(land.current_value);

    document.getElementById('detailArea').innerText=
        land.land_area
            ?`${land.land_area} ${land.area_unit??''}`
            :'—';

    document.getElementById('detailStatus').innerHTML=
        AdminUI.statusBadge(land.status);

    document.getElementById('propertyInformation').innerHTML=[
        infoItem('District',land.district),
        infoItem('Upazila',land.upazila),
        infoItem('Mouza',land.mouza),
        infoItem('Khatian No',land.khatian_no),
        infoItem('Dag No',land.dag_no),
        infoItem('Deed No',land.deed_no),
        infoItem('Registration No',land.registration_no),
        infoItem(
            'Area',
            land.land_area
                ?`${land.land_area} ${land.area_unit??''}`
                :null
        )
    ].join('');

    document.getElementById('purchaseInformation').innerHTML=[
        infoItem(
            'Purchase Date',
            land.purchase_date
                ?AdminUI.formatDate(land.purchase_date)
                :null
        ),
        infoItem('Purchase Price',money(land.purchase_price)),
        infoItem('Current Value',money(land.current_value)),
        infoItem('Seller',land.seller_name),
        infoItem('Seller Phone',land.seller_phone),
        infoItem(
            'Payment Account',
            land.payment_account
                ?`${land.payment_account.code} - ${land.payment_account.name}`
                :null
        )
    ].join('');

    renderValuations(land.valuations??[]);
    renderDocuments(land.documents??[]);
    renderDisposal(land);
    renderAccounting(land);

    const disabled=['sold','cancelled'].includes(land.status);

    document.getElementById('addValuationButton')
        ?.classList.toggle('hidden',disabled);

    document.getElementById('uploadDocumentButton')
        ?.classList.toggle('hidden',false);
}

function renderValuations(items){
    const container=document.getElementById('valuationList');

    if(!items.length){
        container.innerHTML=`
            <div class="p-8 text-center text-sm text-slate-500">
                No valuation history.
            </div>
        `;
        return;
    }

    container.innerHTML=`
        <div class="divide-y divide-slate-100">
            ${items.map(item=>`
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <div>
                        <p class="text-sm font-bold text-slate-700">
                            ${money(item.current_value)}
                        </p>

                        <p class="mt-1 text-[10px] text-slate-500">
                            ${AdminUI.formatDate(item.valuation_date)}
                            ${
                                item.valued_by
                                    ?` • ${AdminUI.escapeHtml(item.valued_by)}`
                                    :''
                            }
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] text-slate-500">
                            Previous
                        </p>

                        <p class="text-sm font-semibold text-slate-500">
                            ${money(item.previous_value)}
                        </p>
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

window.openValuationModal=function(){
    if(!selectedLand)return;

    document.getElementById('valuationForm').reset();
    document.getElementById('valuationLandId').value=selectedLand.id;
    document.getElementById('valuationCurrentValue').value=
        selectedLand.current_value??'';

    setPickerDate(
        document.getElementById('valuationDate'),
        new Date().toISOString().slice(0,10)
    );

    AdminUI.clearError('valuationError');
    AdminUI.openModal('valuationModal');
};

document.getElementById('valuationForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const id=document.getElementById('valuationLandId').value;
    const button=document.getElementById('valuationButton');

    const data={
        valuation_date:document.getElementById('valuationDate').value,
        current_value:document.getElementById('valuationCurrentValue').value,
        valued_by:document.getElementById('valuedBy').value.trim()||null,
        notes:document.getElementById('valuationNotes').value.trim()||null
    };

    if(!data.valuation_date||data.current_value===''){
        AdminUI.showError(
            'valuationError',
            'Valuation date and current value are required.'
        );
        return;
    }

    AdminUI.setLoading(button,'Saving...');

    try{
        await api(`/api/lands/${id}/valuations`,{
            method:'POST',
            body:JSON.stringify(data)
        });

        AdminUI.closeModal('valuationModal');
        Toast.success('Valuation added successfully.');

        await Promise.all([
            viewLand(id),
            loadLands(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'valuationError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

function renderDocuments(items){
    const container=document.getElementById('documentList');

    if(!items.length){
        container.innerHTML=`
            <div class="p-8 text-center text-sm text-slate-500">
                No documents uploaded.
            </div>
        `;
        return;
    }

    container.innerHTML=`
        <div class="divide-y divide-slate-100">
            ${items.map(item=>`
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-700">
                                ${formatDocumentType(item.document_type)}
                            </p>

                            <p class="mt-1 truncate text-[10px] text-slate-500">
                                ${AdminUI.escapeHtml(
                                    item.document_number||
                                    item.file_name||
                                    'Document'
                                )}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-1">
                        ${
                            item.file_url
                                ?`
                                    <a href="${AdminUI.escapeHtml(item.file_url)}"
                                       target="_blank"
                                       rel="noopener"
                                       title="View"
                                       class="flex h-8 w-8 items-center justify-center rounded-md bg-slate-100 text-slate-600 hover:bg-slate-200">
                                        <i class="bi bi-eye text-sm"></i>
                                    </a>
                                `
                                :''
                        }

                        ${
                            canUpdate
                                ?`
                                    <button type="button"
                                            onclick="deleteLandDocument(${item.id})"
                                            title="Delete"
                                            class="flex h-8 w-8 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100">
                                        <i class="bi bi-trash text-sm"></i>
                                    </button>
                                `
                                :''
                        }
                    </div>
                </div>
            `).join('')}
        </div>
    `;
}

function formatDocumentType(value){
    return AdminUI.escapeHtml(
        String(value??'other')
            .replaceAll('_',' ')
            .replace(/\b\w/g,char=>char.toUpperCase())
    );
}

window.openDocumentModal=function(){
    if(!selectedLand)return;

    document.getElementById('documentForm').reset();
    document.getElementById('documentLandId').value=selectedLand.id;

    setPickerDate(
        document.getElementById('documentDate'),
        null
    );

    AdminUI.clearError('documentError');
    AdminUI.openModal('documentModal');
};

document.getElementById('documentForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const id=document.getElementById('documentLandId').value;
    const button=document.getElementById('documentButton');
    const file=document.getElementById('documentFile').files[0];

    AdminUI.clearError('documentError');

    if(!file){
        AdminUI.showError('documentError','Please select a file.');
        return;
    }

    const formData=new FormData();

    formData.append(
        'document_type',
        document.getElementById('documentType').value
    );

    const documentNumber=
        document.getElementById('documentNumber').value.trim();

    const documentDate=
        document.getElementById('documentDate').value;

    const description=
        document.getElementById('documentDescription').value.trim();

    if(documentNumber){
        formData.append('document_number',documentNumber);
    }

    if(documentDate){
        formData.append('document_date',documentDate);
    }

    if(description){
        formData.append('description',description);
    }

    formData.append('file',file);

    AdminUI.setLoading(button,'Uploading...');

    try{
        await api(`/api/lands/${id}/documents`,{
            method:'POST',
            body:formData
        });

        AdminUI.closeModal('documentModal');
        Toast.success('Document uploaded successfully.');

        await viewLand(id);
    }catch(error){
        AdminUI.showError(
            'documentError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteLandDocument=function(documentId){
    if(!selectedLand)return;

    const landId=selectedLand.id;

    AdminUI.deleteRequest(
        `/api/lands/${landId}/documents/${documentId}`,
        {
            message:'Delete this document permanently?',
            successMessage:'Document deleted successfully.',
            onSuccess:async()=>{
                await viewLand(landId);
            }
        }
    );
};

function renderDisposal(land){
    const section=document.getElementById('disposalSection');
    const disposal=land.disposal;

    if(!disposal){
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');

    const gainLoss=Number(disposal.gain_loss??0);

    document.getElementById('disposalInformation').innerHTML=[
        infoItem(
            'Sale Date',
            disposal.sale_date
                ?AdminUI.formatDate(disposal.sale_date)
                :null
        ),
        infoItem('Sale Price',money(disposal.sale_price)),
        infoItem('Selling Expense',money(disposal.selling_expense)),
        infoItem('Net Sale',money(disposal.net_sale_amount)),
        infoItem(
            gainLoss>=0?'Profit':'Loss',
            money(Math.abs(gainLoss))
        ),
        infoItem('Buyer',disposal.buyer_name),
        infoItem('Buyer Phone',disposal.buyer_phone),
        infoItem(
            'Receive Account',
            disposal.receive_account
                ?`${disposal.receive_account.code} - ${disposal.receive_account.name}`
                :null
        )
    ].join('');
}

function renderAccounting(land){
    const section=document.getElementById('accountingSection');
    const purchaseTransaction=land.finance_transaction;
    const saleTransaction=land.disposal?.finance_transaction;

    if(!purchaseTransaction&&!saleTransaction){
        section.classList.add('hidden');
        return;
    }

    section.classList.remove('hidden');

    document.getElementById('accountingInformation').innerHTML=`
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            ${
                purchaseTransaction
                    ?`
                        <div class="rounded-md bg-slate-50 p-3">
                            <p class="text-[10px] uppercase text-slate-500">
                                Purchase Journal
                            </p>
                            <p class="mt-1 text-sm font-bold text-slate-700">
                                ${AdminUI.escapeHtml(
                                    purchaseTransaction.transaction_no??'—'
                                )}
                            </p>
                        </div>
                    `
                    :''
            }

            ${
                saleTransaction
                    ?`
                        <div class="rounded-md bg-emerald-50 p-3">
                            <p class="text-[10px] uppercase text-emerald-500">
                                Sale Journal
                            </p>
                            <p class="mt-1 text-sm font-bold text-emerald-700">
                                ${AdminUI.escapeHtml(
                                    saleTransaction.transaction_no??'—'
                                )}
                            </p>
                        </div>
                    `
                    :''
            }
        </div>
    `;
}

window.openSellModal=async function(id){
    let item=lands.find(
        item=>Number(item.id)===Number(id)
    );

    if(!item){
        Toast.error('Land record not found.');
        return;
    }

    document.getElementById('sellForm').reset();
    AdminUI.clearError('sellError');

    document.getElementById('sellLandId').value=item.id;

    document.getElementById('sellLandInfo').innerText=
        `${item.land_code} • ${item.title}`;

    document.getElementById('sellingExpense').value='0';

    setPickerDate(
        document.getElementById('saleDate'),
        new Date().toISOString().slice(0,10)
    );

    document.getElementById('saleBookPreview').innerText=
        money(item.purchase_price);

    updateSalePreview();

    AdminUI.openModal('sellModal');

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }
};

function updateSalePreview(){
    const price=Number(
        document.getElementById('salePrice').value||0
    );

    const expense=Number(
        document.getElementById('sellingExpense').value||0
    );

    const id=document.getElementById('sellLandId').value;

    const land=lands.find(
        item=>Number(item.id)===Number(id)
    );

    const book=Number(land?.purchase_price??0);
    const net=price-expense;
    const profit=net-book;

    document.getElementById('saleNetPreview').innerText=
        money(net);

    document.getElementById('saleBookPreview').innerText=
        money(book);

    const profitEl=document.getElementById(
        'saleProfitPreview'
    );

    profitEl.innerText=money(profit);

    profitEl.classList.remove(
        'text-slate-700',
        'text-emerald-700',
        'text-red-700'
    );

    profitEl.classList.add(
        profit>0
            ?'text-emerald-700'
            :profit<0
                ?'text-red-700'
                :'text-slate-700'
    );
}

document.getElementById('salePrice')
    .addEventListener('input',updateSalePreview);

document.getElementById('sellingExpense')
    .addEventListener('input',updateSalePreview);

document.getElementById('sellForm').addEventListener('submit',async event=>{
    event.preventDefault();

    const id=document.getElementById('sellLandId').value;
    const button=document.getElementById('sellButton');

    AdminUI.clearError('sellError');

    const data={
        sale_price:document.getElementById('salePrice').value,
        selling_expense:document.getElementById('sellingExpense').value||0,
        sale_date:document.getElementById('saleDate').value,
        receive_account_id:document.getElementById('receiveAccount').value,
        buyer_name:document.getElementById('buyerName').value.trim()||null,
        buyer_phone:document.getElementById('buyerPhone').value.trim()||null,
        reference_no:document.getElementById('saleReference').value.trim()||null,
        notes:document.getElementById('saleNotes').value.trim()||null
    };

    if(!data.sale_price||Number(data.sale_price)<=0){
        AdminUI.showError('sellError','Valid sale price is required.');
        return;
    }

    if(Number(data.selling_expense)<0){
        AdminUI.showError(
            'sellError',
            'Selling expense cannot be negative.'
        );
        return;
    }

    if(
        Number(data.selling_expense)>
        Number(data.sale_price)
    ){
        AdminUI.showError(
            'sellError',
            'Selling expense cannot exceed sale price.'
        );
        return;
    }

    if(!data.sale_date){
        AdminUI.showError('sellError','Sale date is required.');
        return;
    }

    if(!data.receive_account_id){
        AdminUI.showError(
            'sellError',
            'Receive account is required.'
        );
        return;
    }

    AdminUI.setLoading(button,'Selling...');

    try{
        await api(`/api/lands/${id}/sell`,{
            method:'POST',
            body:JSON.stringify(data)
        });

        AdminUI.closeModal('sellModal');

        Toast.success('Land sold successfully.');

        await Promise.all([
            loadLands(currentPage),
            loadStatistics()
        ]);
    }catch(error){
        AdminUI.showError(
            'sellError',
            AdminUI.extractError(error)
        );
    }finally{
        AdminUI.resetLoading(button);
    }
});

window.deleteLand=function(id){
    const item=lands.find(
        land=>Number(land.id)===Number(id)
    );

    if(!item){
        Toast.error('Land record not found.');
        return;
    }

    AdminUI.deleteRequest(
        `/api/lands/${id}`,
        {
            title:'Delete Land?',
            message:
                `Delete "${item.land_code} - ${item.title}" permanently?`,
            confirmText:'Delete',
            successMessage:
                'Land deleted successfully.',
            onSuccess:async()=>{
                if(
                    lands.length===1&&
                    currentPage>1
                ){
                    currentPage--;
                }

                await Promise.all([
                    loadLands(currentPage),
                    loadStatistics()
                ]);
            }
        }
    );
};

window.clearFilters=function(){
    el.search.value='';
    el.statusFilter.value='';
    loadLands(1);
};

async function initLandPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(initLandPage,50);
        return;
    }

    if(typeof window.initDatePickers==='function'){
        window.initDatePickers();
    }

    populateDistrictSelect();
    populateUpazilaSelect('');

    el.district.addEventListener(
        'change',
        ()=>populateUpazilaSelect(el.district.value)
    );

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadLands(1)
        )
    );

    el.statusFilter.addEventListener(
        'change',
        ()=>loadLands(1)
    );

    await Promise.all([
        loadOptions(),
        loadLands(),
        loadStatistics()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initLandPage
    );
}else{
    initLandPage();
}
</script>
@endpush