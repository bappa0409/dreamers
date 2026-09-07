@extends('layouts.admin')

@section('title','Assets')
@section('page_title','Assets')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-building"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Assets</h1>
                <p class=" text-xs 2xl:text-sm text-slate-500">
                    Manage association assets, depreciation, sale and disposal.
                </p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openAssetModal()"
            class="inline-flex w-fit items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs 2xl:text-sm font-semibold text-white hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Asset
        </button>
        @endif
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class=" text-xs 2xl:text-sm text-slate-500">Active Assets</p>
            <p id="summaryActive" class="mt-1 text-sm font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class=" text-xs 2xl:text-sm text-slate-500">Purchase Cost</p>
            <p id="summaryPurchaseCost" class="mt-1 text-sm font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class=" text-xs 2xl:text-sm text-slate-500">Accumulated Depreciation</p>
            <p id="summaryDepreciation" class="mt-1 text-sm font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class=" text-xs 2xl:text-sm text-slate-500">Book Value</p>
            <p id="summaryBookValue" class="mt-1 text-sm font-bold text-slate-800">৳0.00</p>
        </div>
    </div>

    {{-- Search / Filter --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Assets
                    </p>

                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by code, name, category, vendor, serial or reference.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[minmax(220px,280px)_240px_150px_170px_auto] lg:gap-0">

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search..."
                        class="h-9 w-full rounded-md border border-slate-300 pl-9 pr-3  text-xs 2xl:text-sm outline-none focus:border-indigo-400 lg:rounded-r-none">
                </div>

                <div class="relative">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="dateRangeFilter"
                        type="text"
                        class="js-date-range h-9 w-full border border-slate-300 bg-white pl-9 pr-3 text-xs 2xl:text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 lg:border-l-0"
                        placeholder="Select date range"
                        autocomplete="off">
                </div>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 px-3 text-xs 2xl:text-sm outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="pending_approval">Pending Approval</option>
                    <option value="rejected">Rejected</option>
                    <option value="active">Active</option>
                    <option value="sold">Sold</option>
                    <option value="disposed">Disposed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select
                    id="categoryFilter"
                    class="h-9 border border-slate-300 px-3 text-xs 2xl:text-sm outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Categories</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 rounded-md border border-slate-300 bg-slate-50 px-3  text-xs 2xl:text-sm font-semibold text-slate-600 hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">

        {{-- Desktop / tablet table --}}
        <div class="hidden w-full overflow-x-auto md:block">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Asset</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Cost</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Depreciation</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Book Value</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Purchase Date</th>
                        <th class="px-4 py-3 text-left  text-xs 2xl:text-sm font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right  text-xs 2xl:text-sm font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="assetTable">
                    <tr>
                        <td colspan="8" class="px-4 py-10  text-xs 2xl:text-sm text-center text-slate-400">
                            Loading assets...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div id="assetCards" class="divide-y divide-slate-100 md:hidden">
            <div class="px-4 py-10 text-center text-sm text-slate-400">Loading assets...</div>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Add / Edit Asset Modal --}}
<div id="assetModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-2xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-building-add"></i>
                </div>

                <div>
                    <h2 id="assetModalTitle" class="text-sm font-semibold text-slate-800">
                        Add Asset
                    </h2>

                    <p class=" text-xs 2xl:text-sm text-slate-500">
                        Record association-owned fixed asset.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="closeAssetModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="assetForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-5 overflow-y-auto p-5">

                <section class="rounded-md border border-slate-200 bg-white p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Asset Information
                            </h3>

                            <p class="text-[11px] text-slate-400">
                                Enter purchase and accounting details.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div>
                            <label class="form-label">
                                Asset Name
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="assetName"
                                type="text"
                                maxlength="150"
                                class="app-input"
                                required>

                            <p data-field-error="assetName" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">Category</label>

                            <input
                                id="assetCategory"
                                type="text"
                                maxlength="100"
                                list="assetCategoryList"
                                class="app-input">

                            <datalist id="assetCategoryList"></datalist>
                        </div>

                        <div>
                            <label class="form-label">
                                Asset Account
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="assetAccountId" class="app-input" required>
                                <option value="">Select Asset Account</option>
                            </select>

                            <p data-field-error="assetAccountId" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">
                                Payment Account
                                <span class="text-red-500">*</span>
                            </label>

                            <select id="paymentAccountId" class="app-input" required>
                                <option value="">Select Cash/Bank</option>
                            </select>

                            <p data-field-error="paymentAccountId" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">
                                Purchase Cost
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="purchaseCost"
                                type="number"
                                min="0.01"
                                step="0.01"
                                class="app-input"
                                required>

                            <p data-field-error="purchaseCost" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">
                                Purchase Date
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                                <input
                                    id="purchaseDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                    required>
                            </div>

                            <p data-field-error="purchaseDate" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">Vendor</label>

                            <input
                                id="vendor"
                                type="text"
                                maxlength="150"
                                class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Reference</label>

                            <input
                                id="reference"
                                type="text"
                                maxlength="150"
                                class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Location</label>

                            <input
                                id="location"
                                type="text"
                                maxlength="150"
                                class="app-input">
                        </div>

                        <div>
                            <label class="form-label">Serial No</label>

                            <input
                                id="serialNo"
                                type="text"
                                maxlength="150"
                                class="app-input">
                        </div>
                    </div>
                </section>

                <section class="rounded-md border border-slate-200 bg-slate-50/50 p-4">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                            <i class="bi bi-graph-down-arrow"></i>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-slate-800">
                                Depreciation Settings
                            </h3>

                            <p class="text-[11px] text-slate-400">
                                Configure straight-line depreciation.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div>
                            <label class="form-label">
                                Useful Life (Months)
                            </label>

                            <input
                                id="usefulLifeMonths"
                                type="number"
                                min="1"
                                max="1200"
                                class="app-input">

                            <p data-field-error="usefulLifeMonths" class="invalid-feedback hidden"></p>
                        </div>

                        <div>
                            <label class="form-label">
                                Salvage Value
                            </label>

                            <input
                                id="salvageValue"
                                type="number"
                                min="0"
                                step="0.01"
                                value="0"
                                class="app-input">

                            <p data-field-error="salvageValue" class="invalid-feedback hidden"></p>
                        </div>
                    </div>
                </section>

                <div>
                    <label class="form-label">Description</label>

                    <textarea
                        id="assetDescription"
                        rows="3"
                        maxlength="3000"
                        class="app-input resize-none"></textarea>
                </div>

                <div id="assetError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeAssetModal()"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <i class="bi bi-x-lg"></i>
                    Close
                </button>

                <button
                    id="assetSaveButton"
                    type="submit"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2  text-xs 2xl:text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    <i class="bi bi-check2-circle"></i>
                    Save Asset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Details Modal --}}
<div id="assetDetailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-building"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Asset Details
                    </h2>

                    <p id="assetDetailsSubtitle" class=" text-xs 2xl:text-sm text-slate-500">
                        View asset and accounting information.
                    </p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('assetDetailsModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="assetDetailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="AdminUI.closeModal('assetDetailsModal')"
                class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2  text-xs 2xl:text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="bi bi-x-lg"></i>
                Close
            </button>
        </div>
    </div>
</div>

{{-- Sell Modal --}}
<div id="sellModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Sell Asset
                    </h2>

                    <p id="sellAssetInfo" class=" text-xs 2xl:text-sm text-slate-500">—</p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('sellModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="sellForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">

                <div>
                    <label class="form-label">
                        Sale Amount
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="saleAmount"
                        type="number"
                        min="0"
                        step="0.01"
                        class="app-input"
                        required>

                    <p data-field-error="saleAmount" class="invalid-feedback hidden"></p>
                </div>

                <div>
                    <label class="form-label">
                        Receive Account
                        <span class="text-red-500">*</span>
                    </label>

                    <select id="saleReceiveAccount" class="app-input" required>
                        <option value="">Select Cash/Bank</option>
                    </select>

                    <p data-field-error="saleReceiveAccount" class="invalid-feedback hidden"></p>
                </div>

                <div>
                    <label class="form-label">
                        Sale Date
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                        <input
                            id="saleDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                            required>
                    </div>

                    <p data-field-error="saleDate" class="invalid-feedback hidden"></p>
                </div>

                <div>
                    <label class="form-label">Note</label>

                    <textarea
                        id="saleNote"
                        rows="3"
                        maxlength="2000"
                        class="app-input resize-none"></textarea>
                </div>

                <div id="sellError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('sellModal')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>

                <button
                    id="sellButton"
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="bi bi-cash-coin"></i>
                    Sell Asset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Dispose Modal --}}
<div id="disposeModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-trash3"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Dispose Asset
                    </h2>

                    <p id="disposeAssetInfo" class=" text-xs 2xl:text-sm text-slate-500">—</p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('disposeModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="disposeForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">

                <div>
                    <label class="form-label">
                        Disposal Date
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                        <input
                            id="disposeDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                            required>
                    </div>

                    <p data-field-error="disposeDate" class="invalid-feedback hidden"></p>
                </div>

                <div>
                    <label class="form-label">
                        Reason
                        <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="disposeNote"
                        rows="4"
                        maxlength="2000"
                        class="app-input resize-none"
                        required></textarea>

                    <p data-field-error="disposeNote" class="invalid-feedback hidden"></p>
                </div>

                <div id="disposeError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('disposeModal')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>

                <button
                    id="disposeButton"
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                    <i class="bi bi-trash3"></i>
                    Dispose Asset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Depreciation Modal --}}
<div id="depreciationModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                    <i class="bi bi-graph-down-arrow"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Post Depreciation
                    </h2>

                    <p id="depreciationAssetInfo" class=" text-xs 2xl:text-sm text-slate-500">—</p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('depreciationModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="depreciationForm" class="flex min-h-0 flex-1 flex-col" novalidate data-js-validation="1">
            <div class="space-y-4 overflow-y-auto p-5">

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            Monthly
                        </p>

                        <p id="depreciationMonthly" class="mt-1 text-base font-bold text-slate-700">
                            ৳0.00
                        </p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                            Remaining
                        </p>

                        <p id="depreciationRemaining" class="mt-1 text-base font-bold text-slate-700">
                            ৳0.00
                        </p>
                    </div>
                </div>

                <div>
                    <label class="form-label">
                        Depreciation Date
                        <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                        <input
                            id="depreciationDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                            required>
                    </div>

                    <p data-field-error="depreciationDate" class="invalid-feedback hidden"></p>
                </div>

                <div>
                    <label class="form-label">Description</label>

                    <textarea
                        id="depreciationDescription"
                        rows="3"
                        maxlength="2000"
                        class="app-input resize-none"></textarea>
                </div>

                <div id="depreciationError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="AdminUI.closeModal('depreciationModal')"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                    <i class="bi bi-x-lg"></i>
                    Cancel
                </button>

                <button
                    id="depreciationButton"
                    type="submit"
                    class="inline-flex items-center justify-center gap-1.5 rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                    <i class="bi bi-graph-down-arrow"></i>
                    Post Depreciation
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Depreciation History Modal --}}
<div id="depreciationHistoryModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-violet-50 text-violet-600">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-slate-800">
                        Depreciation History
                    </h2>

                    <p id="depreciationHistoryAsset" class=" text-xs 2xl:text-sm text-slate-500">—</p>
                </div>
            </div>

            <button
                type="button"
                onclick="AdminUI.closeModal('depreciationHistoryModal')"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="depreciationHistoryContent" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="AdminUI.closeModal('depreciationHistoryModal')"
                class="inline-flex items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700">
                <i class="bi bi-x-lg"></i>
                Close
            </button>
        </div>
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
let assets=[];
let currentPage=1;
let lastPage=1;
let total=0;
let optionsLoaded=false;

let selectedFrom='';
let selectedTo='';
let dateRangePicker=null;

let editingAsset=null;
let selectedAsset=null;
let depreciationAsset=null;

let assetAccounts=[];
let cashBankAccounts=[];
let categories=[];

const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('assetTable'),
    cards:document.getElementById('assetCards'),
    search:document.getElementById('searchInput'),
    dateRange:document.getElementById('dateRangeFilter'),
    status:document.getElementById('statusFilter'),
    category:document.getElementById('categoryFilter'),
    form:document.getElementById('assetForm'),
    saveButton:document.getElementById('assetSaveButton')
};

function localDate(date=new Date()){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return `${year}-${month}-${day}`;
}

function setPickerDate(element,value){
    if(!element)return;

    const date=value
        ?String(value).substring(0,10)
        :'';

    element.value=date;

    if(element._flatpickr){
        date
            ?element._flatpickr.setDate(date,false)
            :element._flatpickr.clear();
    }
}

function money(value){
    return `${@json(setting('currency_symbol','৳'))}${Number(
        value??0
    ).toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}

function cardsLoadingHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-400">
            ${AdminUI.escapeHtml(message)}
        </div>
    `;
}

function cardsEmptyHtml(message){
    return`
        <div class="px-4 py-10 text-center text-sm text-slate-400">
            ${AdminUI.escapeHtml(message)}
        </div>
    `;
}

function initDateRangePicker(){
    if(
        !el.dateRange||
        typeof window.flatpickr==='undefined'
    ){
        return;
    }

    if(el.dateRange._flatpickr){
        el.dateRange._flatpickr.destroy();
    }

    dateRangePicker=flatpickr(
        el.dateRange,
        {
            mode:'range',
            dateFormat:'Y-m-d',
            allowInput:false,
            disableMobile:true,

            onChange(selectedDates,dateStr,instance){
                if(selectedDates.length===2){
                    selectedFrom=instance.formatDate(
                        selectedDates[0],
                        'Y-m-d'
                    );

                    selectedTo=instance.formatDate(
                        selectedDates[1],
                        'Y-m-d'
                    );

                    loadAssets(1);
                    return;
                }

                if(selectedDates.length===0){
                    selectedFrom='';
                    selectedTo='';

                    loadAssets(1);
                }
            }
        }
    );
}

async function loadOptions(force=false){
    if(optionsLoaded&&!force){
        return;
    }

    const response=await api(
        '/api/finance/assets/options'
    );

    const data=response.data??{};

    assetAccounts=
        data.asset_accounts??[];

    cashBankAccounts=
        data.cash_bank_accounts??[];

    categories=
        data.categories??[];

    document.getElementById(
        'assetAccountId'
    ).innerHTML=`
        <option value="">Select Asset Account</option>

        ${assetAccounts.map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    const cashOptions=`
        <option value="">Select Cash/Bank</option>

        ${cashBankAccounts.map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    document.getElementById(
        'paymentAccountId'
    ).innerHTML=cashOptions;

    document.getElementById(
        'saleReceiveAccount'
    ).innerHTML=cashOptions;

    el.category.innerHTML=`
        <option value="">All Categories</option>

        ${categories.map(item=>`
            <option value="${AdminUI.escapeHtml(item)}">
                ${AdminUI.escapeHtml(item)}
            </option>
        `).join('')}
    `;

    document.getElementById(
        'assetCategoryList'
    ).innerHTML=
        categories.map(item=>`
            <option value="${AdminUI.escapeHtml(item)}"></option>
        `).join('');

    optionsLoaded=true;
}

async function loadSummary(){
    try{
        const response=await api(
            '/api/finance/assets/summary'
        );

        const data=response.data??{};

        document.getElementById(
            'summaryActive'
        ).textContent=
            data.active_assets??0;

        document.getElementById(
            'summaryPurchaseCost'
        ).textContent=
            money(data.purchase_cost);

        document.getElementById(
            'summaryDepreciation'
        ).textContent=
            money(
                data.accumulated_depreciation
            );

        document.getElementById(
            'summaryBookValue'
        ).textContent=
            money(data.book_value);

    }catch(error){
        console.error(error);
    }
}

async function loadAssets(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading assets...',
            8
        );

    el.cards.innerHTML=
        cardsLoadingHtml(
            'Loading assets...'
        );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        from:selectedFrom,
        to:selectedTo,
        status:el.status.value,
        category:el.category.value,
        page
    });

    try{
        const response=await api(
            `/api/finance/assets?${query}`
        );

        const paginator=
            response.data??{};

        assets=
            paginator.data??[];

        currentPage=Number(
            paginator.current_page??1
        );

        lastPage=Number(
            paginator.last_page??1
        );

        total=Number(
            paginator.total??0
        );

        renderAssetTable();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadAssets
        });

    }catch(error){
        el.table.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                8
            );

        el.cards.innerHTML=
            cardsEmptyHtml(
                AdminUI.extractError(error)
            );
    }
}

function assetActionButtons(item,{withLabel=false}={}){
    const buttons=[];

    buttons.push(`
        <button
            type="button"
            onclick="viewAsset(${item.id})"
            class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-slate-50 text-slate-500 transition hover:bg-slate-100"
            title="View">
            <i class="bi bi-eye text-sm"></i>
            ${withLabel?'View':''}
        </button>
    `);

    if(
        canUpdate&&
        item.status==='active'
    ){
        buttons.push(`
            <button
                type="button"
                onclick="editAsset(${item.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                title="Edit">
                <i class="bi bi-pencil text-sm"></i>
                ${withLabel?'Edit':''}
            </button>
        `);

        buttons.push(`
            <button
                type="button"
                onclick="openDepreciationModal(${item.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-violet-50 text-violet-600 transition hover:bg-violet-100"
                title="Depreciate">
                <i class="bi bi-graph-down-arrow text-sm"></i>
                ${withLabel?'Depreciate':''}
            </button>
        `);

        buttons.push(`
            <button
                type="button"
                onclick="openSellModal(${item.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                title="Sell">
                <i class="bi bi-cash-coin text-sm"></i>
                ${withLabel?'Sell':''}
            </button>
        `);

        buttons.push(`
            <button
                type="button"
                onclick="openDisposeModal(${item.id})"
                class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-red-50 text-red-600 transition hover:bg-red-100"
                title="Dispose">
                <i class="bi bi-trash3 text-sm"></i>
                ${withLabel?'Dispose':''}
            </button>
        `);
    }

    buttons.push(`
        <button
            type="button"
            onclick="viewDepreciationHistory(${item.id})"
            class="flex ${withLabel?'h-8 flex-1 items-center justify-center gap-1.5 rounded-md px-2 text-[11px] font-semibold':'h-8 w-8 items-center justify-center rounded-md'} bg-slate-50 text-slate-500 transition hover:bg-slate-100"
            title="Depreciation History">
            <i class="bi bi-clock-history text-sm"></i>
            ${withLabel?'History':''}
        </button>
    `);

    return buttons.join('');
}

function renderAssetTable(){
    if(!assets.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No asset records found.',
                8
            );

        el.cards.innerHTML=
            cardsEmptyHtml(
                'No asset records found.'
            );

        return;
    }

    el.table.innerHTML=
        assets.map(item=>{
            const bookValue=Math.max(
                Number(item.purchase_cost??0)-
                Number(item.accumulated_depreciation??0),
                0
            );

            return`
                <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">

                    <td class="px-4 py-3">
                        <button
                            type="button"
                            onclick="viewAsset(${item.id})"
                            class="font-mono text-sm font-semibold text-indigo-600 hover:underline">
                            ${AdminUI.escapeHtml(item.asset_code)}
                        </button>

                        <p class="mt-1 text-sm font-medium text-slate-700">
                            ${AdminUI.escapeHtml(item.name)}
                        </p>

                        ${
                            item.serial_no
                                ?`
                                    <p class="text-[10px] text-slate-400">
                                        SN: ${AdminUI.escapeHtml(item.serial_no)}
                                    </p>
                                `
                                :''
                        }
                    </td>

                    <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-600">
                        ${AdminUI.escapeHtml(item.category??'—')}
                    </td>

                    <td class="px-4 py-3 text-right  text-xs 2xl:text-sm text-slate-800">
                        ${money(item.purchase_cost)}
                    </td>

                    <td class="px-4 py-3 text-right text-sm text-slate-600">
                        ${money(item.accumulated_depreciation)}
                    </td>

                    <td class="px-4 py-3 text-right  text-xs 2xl:text-sm text-slate-800">
                        ${money(bookValue)}
                    </td>

                    <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-600">
                        ${AdminUI.formatDate(item.purchase_date)}
                    </td>

                    <td class="px-4 py-3">
                        ${AdminUI.statusBadge(item.status)}
                    </td>

                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1">
                            ${assetActionButtons(item)}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

    el.cards.innerHTML=
        assets.map(item=>{
            const bookValue=Math.max(
                Number(item.purchase_cost??0)-
                Number(item.accumulated_depreciation??0),
                0
            );

            return`
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <button
                                type="button"
                                onclick="viewAsset(${item.id})"
                                class="font-mono text-xs 2xl:text-sm font-semibold text-indigo-600 hover:underline">
                                ${AdminUI.escapeHtml(item.asset_code)}
                            </button>

                            <p class="mt-1 truncate text-xs 2xl:text-sm font-semibold text-slate-800">
                                ${AdminUI.escapeHtml(item.name)}
                            </p>

                            ${
                                item.serial_no
                                    ?`
                                        <p class="text-[10px] text-slate-400">
                                            SN: ${AdminUI.escapeHtml(item.serial_no)}
                                        </p>
                                    `
                                    :''
                            }
                        </div>

                        <div class="shrink-0">
                            ${AdminUI.statusBadge(item.status)}
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-x-3 gap-y-2.5 rounded-md bg-slate-50/60 p-3 text-[11px]">
                        <div class="min-w-0">
                            <p class="text-slate-400 text-xs 2xl:text-sm">Category</p>
                            <p class="truncate font-medium text-slate-700">
                                ${AdminUI.escapeHtml(item.category??'—')}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-400 text-xs 2xl:text-sm">Purchase Date</p>
                            <p class="truncate font-medium text-slate-700">
                                ${AdminUI.formatDate(item.purchase_date)}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-400 text-xs 2xl:text-sm">Cost</p>
                            <p class="truncate font-medium text-slate-700">
                                ${money(item.purchase_cost)}
                            </p>
                        </div>

                        <div class="min-w-0">
                            <p class="text-slate-400 text-xs 2xl:text-sm">Book Value</p>
                            <p class="truncate font-semibold text-slate-800">
                                ${money(bookValue)}
                            </p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-1.5 border-t border-slate-100 pt-3">
                        ${assetActionButtons(item,{withLabel:true})}
                    </div>
                </div>
            `;
        }).join('');
}

window.openAssetModal=async function(){
    editingAsset=null;

    AdminUI.resetForm(
        el.form
    );

    AdminUI.clearError(
        'assetError'
    );

    try{
        await loadOptions();

        document.getElementById(
            'assetModalTitle'
        ).textContent=
            'Add Asset';

        [
            'assetAccountId',
            'paymentAccountId',
            'purchaseCost',
            'purchaseDate',
            'usefulLifeMonths',
            'salvageValue'
        ].forEach(id=>{
            document.getElementById(
                id
            ).disabled=false;
        });

        document.getElementById(
            'salvageValue'
        ).value='0';

        AdminUI.openModal(
            'assetModal'
        );

        if(
            typeof window.initDatePickers===
            'function'
        ){
            window.initDatePickers();
        }

        setPickerDate(
            document.getElementById(
                'purchaseDate'
            ),
            localDate()
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

window.closeAssetModal=function(){
    AdminUI.closeModal(
        'assetModal'
    );
};

window.editAsset=async function(id){
    try{
        await loadOptions();

        const response=await api(
            `/api/finance/assets/${id}`
        );

        const item=response.data??{};

        editingAsset=item;

        AdminUI.resetForm(
            el.form
        );

        AdminUI.clearError(
            'assetError'
        );

        document.getElementById(
            'assetModalTitle'
        ).textContent=
            `Edit ${item.asset_code}`;

        document.getElementById(
            'assetName'
        ).value=
            item.name??'';

        document.getElementById(
            'assetCategory'
        ).value=
            item.category??'';

        document.getElementById(
            'assetAccountId'
        ).value=
            item.asset_account_id??'';

        document.getElementById(
            'paymentAccountId'
        ).value=
            item.payment_account_id??'';

        document.getElementById(
            'purchaseCost'
        ).value=
            item.purchase_cost??'';

        document.getElementById(
            'vendor'
        ).value=
            item.vendor??'';

        document.getElementById(
            'reference'
        ).value=
            item.reference??'';

        document.getElementById(
            'location'
        ).value=
            item.location??'';

        document.getElementById(
            'serialNo'
        ).value=
            item.serial_no??'';

        document.getElementById(
            'usefulLifeMonths'
        ).value=
            item.useful_life_months??'';

        document.getElementById(
            'salvageValue'
        ).value=
            item.salvage_value??0;

        document.getElementById(
            'assetDescription'
        ).value=
            item.description??'';

        AdminUI.openModal(
            'assetModal'
        );

        if(
            typeof window.initDatePickers===
            'function'
        ){
            window.initDatePickers();
        }

        setPickerDate(
            document.getElementById(
                'purchaseDate'
            ),
            item.purchase_date
        );

        const posted=
            !!item.finance_transaction_id;

        [
            'assetAccountId',
            'paymentAccountId',
            'purchaseCost',
            'purchaseDate'
        ].forEach(field=>{
            document.getElementById(
                field
            ).disabled=posted;
        });

        const depreciationPosted=
            Number(
                item.accumulated_depreciation??0
            )>0;

        document.getElementById(
            'usefulLifeMonths'
        ).disabled=
            depreciationPosted;

        document.getElementById(
            'salvageValue'
        ).disabled=
            depreciationPosted;

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'assetError'
        );

        AdminUI.clearFieldErrors(
            el.form
        );

        if(
            !AdminUI.validateForm(
                el.form
            )
        ){
            return;
        }

        const purchaseCost=
            Number(
                document.getElementById(
                    'purchaseCost'
                ).value??0
            );

        const salvageValue=
            Number(
                document.getElementById(
                    'salvageValue'
                ).value??0
            );

        if(
            !editingAsset?.finance_transaction_id&&
            purchaseCost<=0
        ){
            AdminUI.showFieldError(
                'purchaseCost',
                'Purchase cost must be greater than zero.'
            );

            return;
        }

        if(
            !document.getElementById(
                'salvageValue'
            ).disabled&&
            salvageValue>purchaseCost
        ){
            AdminUI.showFieldError(
                'salvageValue',
                'Salvage value cannot exceed purchase cost.'
            );

            return;
        }

        const data={
            name:
                document.getElementById(
                    'assetName'
                ).value.trim(),

            category:
                document.getElementById(
                    'assetCategory'
                ).value.trim()||null,

            asset_account_id:
                Number(
                    document.getElementById(
                        'assetAccountId'
                    ).value
                ),

            payment_account_id:
                Number(
                    document.getElementById(
                        'paymentAccountId'
                    ).value
                ),

            purchase_cost:
                purchaseCost,

            purchase_date:
                document.getElementById(
                    'purchaseDate'
                ).value,

            vendor:
                document.getElementById(
                    'vendor'
                ).value.trim()||null,

            reference:
                document.getElementById(
                    'reference'
                ).value.trim()||null,

            location:
                document.getElementById(
                    'location'
                ).value.trim()||null,

            serial_no:
                document.getElementById(
                    'serialNo'
                ).value.trim()||null,

            useful_life_months:
                document.getElementById(
                    'usefulLifeMonths'
                ).value
                    ?Number(
                        document.getElementById(
                            'usefulLifeMonths'
                        ).value
                    )
                    :null,

            salvage_value:
                salvageValue,

            description:
                document.getElementById(
                    'assetDescription'
                ).value.trim()||null
        };

        if(
            editingAsset?.finance_transaction_id
        ){
            delete data.asset_account_id;
            delete data.payment_account_id;
            delete data.purchase_cost;
            delete data.purchase_date;
        }

        if(
            Number(
                editingAsset
                    ?.accumulated_depreciation??0
            )>0
        ){
            delete data.useful_life_months;
            delete data.salvage_value;
        }

        AdminUI.setLoading(
            el.saveButton,
            'Saving...'
        );

        try{
            const response=await api(
                editingAsset
                    ?`/api/finance/assets/${editingAsset.id}`
                    :'/api/finance/assets',
                {
                    method:
                        editingAsset
                            ?'PUT'
                            :'POST',

                    body:
                        JSON.stringify(data)
                }
            );

            closeAssetModal();

            Toast.success(
                response.message||
                'Asset saved successfully.'
            );

            optionsLoaded=false;

            await Promise.all([
                loadAssets(currentPage),
                loadSummary(),
                loadOptions(true)
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    el.form,
                    error,
                    {
                        name:
                            'assetName',

                        category:
                            'assetCategory',

                        asset_account_id:
                            'assetAccountId',

                        payment_account_id:
                            'paymentAccountId',

                        purchase_cost:
                            'purchaseCost',

                        purchase_date:
                            'purchaseDate',

                        useful_life_months:
                            'usefulLifeMonths',

                        salvage_value:
                            'salvageValue'
                    }
                )
            ){
                AdminUI.showError(
                    'assetError',
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

window.viewAsset=async function(id){
    AdminUI.openModal(
        'assetDetailsModal'
    );

    const body=
        document.getElementById(
            'assetDetailsBody'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading details...'
        );

    try{
        const response=await api(
            `/api/finance/assets/${id}`
        );

        const item=
            response.data??{};

        document.getElementById(
            'assetDetailsSubtitle'
        ).textContent=
            `${item.asset_code??''} • ${item.name??''}`;

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                ${detail(
                    'Asset Code',
                    item.asset_code
                )}

                ${detail(
                    'Name',
                    item.name
                )}

                ${detail(
                    'Category',
                    item.category??'—'
                )}

                ${detail(
                    'Purchase Date',
                    AdminUI.formatDate(
                        item.purchase_date
                    )
                )}

                ${detail(
                    'Purchase Cost',
                    money(
                        item.purchase_cost
                    )
                )}

                ${detail(
                    'Book Value',
                    money(
                        item.book_value
                    )
                )}

                ${detail(
                    'Depreciation',
                    money(
                        item.accumulated_depreciation
                    )
                )}

                ${detail(
                    'Salvage Value',
                    money(
                        item.salvage_value
                    )
                )}

                ${detail(
                    'Useful Life',
                    item.useful_life_months
                        ?`${item.useful_life_months} months`
                        :'—'
                )}

                ${detail(
                    'Asset Account',
                    `${item.asset_account?.code??''} ${item.asset_account?.name??''}`.trim()||'—'
                )}

                ${detail(
                    'Payment Account',
                    `${item.payment_account?.code??''} ${item.payment_account?.name??''}`.trim()||'—'
                )}

                ${detail(
                    'Vendor',
                    item.vendor??'—'
                )}

                ${detail(
                    'Reference',
                    item.reference??'—'
                )}

                ${detail(
                    'Location',
                    item.location??'—'
                )}

                ${detail(
                    'Serial No',
                    item.serial_no??'—'
                )}

                ${detail(
                    'Status',
                    AdminUI.titleCase(
                        item.status
                    )
                )}
            </div>

            ${
                item.description
                    ?`
                        <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Description
                            </p>

                            <p class=" text-xs 2xl:text-sm leading-5 text-slate-600">
                                ${AdminUI.escapeHtml(
                                    item.description
                                )}
                            </p>
                        </div>
                    `
                    :''
            }
        `;

    }catch(error){
        body.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

window.openSellModal=async function(id){
    try{
        await loadOptions();

        selectedAsset=
            assets.find(
                row=>
                    Number(row.id)===
                    Number(id)
            );

        if(!selectedAsset){
            return;
        }

        AdminUI.resetForm(
            document.getElementById(
                'sellForm'
            )
        );

        AdminUI.clearError(
            'sellError'
        );

        document.getElementById(
            'sellAssetInfo'
        ).textContent=
            `${selectedAsset.asset_code} • ${selectedAsset.name}`;

        AdminUI.openModal(
            'sellModal'
        );

        if(
            typeof window.initDatePickers===
            'function'
        ){
            window.initDatePickers();
        }

        setPickerDate(
            document.getElementById(
                'saleDate'
            ),
            localDate()
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

document.getElementById(
    'sellForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        if(!selectedAsset)return;

        const form=event.currentTarget;

        AdminUI.clearError(
            'sellError'
        );

        AdminUI.clearFieldErrors(
            form
        );

        if(
            !AdminUI.validateForm(
                form
            )
        ){
            return;
        }

        const button=
            document.getElementById(
                'sellButton'
            );

        AdminUI.setLoading(
            button,
            'Posting...'
        );

        try{
            const response=await api(
                `/api/finance/assets/${selectedAsset.id}/sell`,
                {
                    method:'POST',

                    body:JSON.stringify({
                        amount:
                            Number(
                                document.getElementById(
                                    'saleAmount'
                                ).value
                            ),

                        receive_account_id:
                            Number(
                                document.getElementById(
                                    'saleReceiveAccount'
                                ).value
                            ),

                        disposal_date:
                            document.getElementById(
                                'saleDate'
                            ).value,

                        note:
                            document.getElementById(
                                'saleNote'
                            ).value.trim()||null
                    })
                }
            );

            AdminUI.closeModal(
                'sellModal'
            );

            Toast.success(
                response.message||
                'Asset sold successfully.'
            );

            await Promise.all([
                loadAssets(currentPage),
                loadSummary()
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    form,
                    error,
                    {
                        amount:
                            'saleAmount',

                        receive_account_id:
                            'saleReceiveAccount',

                        disposal_date:
                            'saleDate'
                    }
                )
            ){
                AdminUI.showError(
                    'sellError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.openDisposeModal=function(id){
    selectedAsset=
        assets.find(
            row=>
                Number(row.id)===
                Number(id)
        );

    if(!selectedAsset){
        return;
    }

    const form=
        document.getElementById(
            'disposeForm'
        );

    AdminUI.resetForm(
        form
    );

    AdminUI.clearError(
        'disposeError'
    );

    document.getElementById(
        'disposeAssetInfo'
    ).textContent=
        `${selectedAsset.asset_code} • ${selectedAsset.name}`;

    AdminUI.openModal(
        'disposeModal'
    );

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }

    setPickerDate(
        document.getElementById(
            'disposeDate'
        ),
        localDate()
    );
};

document.getElementById(
    'disposeForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        if(!selectedAsset)return;

        const form=event.currentTarget;

        AdminUI.clearError(
            'disposeError'
        );

        AdminUI.clearFieldErrors(
            form
        );

        if(
            !AdminUI.validateForm(
                form
            )
        ){
            return;
        }

        const button=
            document.getElementById(
                'disposeButton'
            );

        AdminUI.setLoading(
            button,
            'Posting...'
        );

        try{
            const response=await api(
                `/api/finance/assets/${selectedAsset.id}/dispose`,
                {
                    method:'POST',

                    body:JSON.stringify({
                        disposal_date:
                            document.getElementById(
                                'disposeDate'
                            ).value,

                        note:
                            document.getElementById(
                                'disposeNote'
                            ).value.trim()
                    })
                }
            );

            AdminUI.closeModal(
                'disposeModal'
            );

            Toast.success(
                response.message||
                'Asset disposed successfully.'
            );

            await Promise.all([
                loadAssets(currentPage),
                loadSummary()
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    form,
                    error,
                    {
                        disposal_date:
                            'disposeDate',

                        note:
                            'disposeNote'
                    }
                )
            ){
                AdminUI.showError(
                    'disposeError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.openDepreciationModal=
async function(id){
    try{
        const response=await api(
            `/api/finance/assets/${id}/depreciations`
        );

        depreciationAsset=
            response.data?.asset??null;

        if(!depreciationAsset){
            return;
        }

        const form=
            document.getElementById(
                'depreciationForm'
            );

        AdminUI.resetForm(
            form
        );

        AdminUI.clearError(
            'depreciationError'
        );

        document.getElementById(
            'depreciationAssetInfo'
        ).textContent=
            `${depreciationAsset.asset_code} • ${depreciationAsset.name}`;

        document.getElementById(
            'depreciationMonthly'
        ).textContent=
            money(
                depreciationAsset
                    .monthly_depreciation
            );

        document.getElementById(
            'depreciationRemaining'
        ).textContent=
            money(
                depreciationAsset
                    .remaining_depreciable_amount
            );

        AdminUI.openModal(
            'depreciationModal'
        );

        if(
            typeof window.initDatePickers===
            'function'
        ){
            window.initDatePickers();
        }

        setPickerDate(
            document.getElementById(
                'depreciationDate'
            ),
            localDate()
        );

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
};

document.getElementById(
    'depreciationForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        if(!depreciationAsset)return;

        const form=event.currentTarget;

        AdminUI.clearError(
            'depreciationError'
        );

        AdminUI.clearFieldErrors(
            form
        );

        if(
            !AdminUI.validateForm(
                form
            )
        ){
            return;
        }

        const button=
            document.getElementById(
                'depreciationButton'
            );

        AdminUI.setLoading(
            button,
            'Posting...'
        );

        try{
            const response=await api(
                `/api/finance/assets/${depreciationAsset.id}/depreciate`,
                {
                    method:'POST',

                    body:JSON.stringify({
                        depreciation_date:
                            document.getElementById(
                                'depreciationDate'
                            ).value,

                        description:
                            document.getElementById(
                                'depreciationDescription'
                            ).value.trim()||null
                    })
                }
            );

            AdminUI.closeModal(
                'depreciationModal'
            );

            Toast.success(
                response.message||
                'Depreciation posted successfully.'
            );

            await Promise.all([
                loadAssets(currentPage),
                loadSummary()
            ]);

        }catch(error){
            if(
                !AdminUI.showValidationErrors(
                    form,
                    error,
                    {
                        depreciation_date:
                            'depreciationDate'
                    }
                )
            ){
                AdminUI.showError(
                    'depreciationError',
                    AdminUI.extractError(error)
                );
            }

        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.viewDepreciationHistory=
async function(id){
    AdminUI.openModal(
        'depreciationHistoryModal'
    );

    const body=
        document.getElementById(
            'depreciationHistoryContent'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading depreciation history...'
        );

    try{
        const response=await api(
            `/api/finance/assets/${id}/depreciations`
        );

        const asset=
            response.data?.asset??{};

        const rows=
            response.data?.depreciations??[];

        document.getElementById(
            'depreciationHistoryAsset'
        ).textContent=
            `${asset.asset_code??''} • ${asset.name??''}`;

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">

                ${detail(
                    'Purchase Cost',
                    money(asset.purchase_cost)
                )}

                ${detail(
                    'Accumulated Dep.',
                    money(
                        asset.accumulated_depreciation
                    )
                )}

                ${detail(
                    'Book Value',
                    money(asset.book_value)
                )}

                ${detail(
                    'Remaining Dep.',
                    money(
                        asset.remaining_depreciable_amount
                    )
                )}

            </div>

            <div class="mt-5 overflow-x-auto rounded-md border border-slate-200">

                <table class="w-full min-w-[750px] text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2  text-xs 2xl:text-sm text-left font-semibold text-slate-500">
                                Period
                            </th>

                            <th class="px-3 py-2  text-xs 2xl:text-sm text-left font-semibold text-slate-500">
                                Date
                            </th>

                            <th class="px-3 py-2  text-xs 2xl:text-sm text-right font-semibold text-slate-500">
                                Amount
                            </th>

                            <th class="px-3 py-2  text-xs 2xl:text-sm text-right font-semibold text-slate-500">
                                Before
                            </th>

                            <th class="px-3 py-2  text-xs 2xl:text-sm text-right font-semibold text-slate-500">
                                After
                            </th>

                            <th class="px-3 py-2  text-xs 2xl:text-sm text-left font-semibold text-slate-500">
                                Journal
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        ${
                            rows.length
                                ?rows.map(row=>`
                                    <tr class="border-t border-slate-100">

                                        <td class="px-3 py-2 font-mono text-slate-600">
                                            ${AdminUI.escapeHtml(
                                                row.period_key
                                            )}
                                        </td>

                                        <td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">
                                            ${AdminUI.formatDate(
                                                row.depreciation_date
                                            )}
                                        </td>

                                        <td class="px-3 py-2 text-right font-semibold text-slate-700">
                                            ${money(
                                                row.amount
                                            )}
                                        </td>

                                        <td class="px-3 py-2 text-right text-slate-600">
                                            ${money(
                                                row.book_value_before
                                            )}
                                        </td>

                                        <td class="px-3 py-2 text-right text-slate-600">
                                            ${money(
                                                row.book_value_after
                                            )}
                                        </td>

                                        <td class="px-3 py-2  text-xs 2xl:text-sm text-slate-600">
                                            ${AdminUI.escapeHtml(
                                                row.finance_transaction
                                                    ?.transaction_no??'—'
                                            )}
                                        </td>
                                    </tr>
                                `).join('')
                                :`
                                    <tr>
                                        <td colspan="6" class="px-3 py-8 text-center text-slate-400">
                                            No depreciation history found.
                                        </td>
                                    </tr>
                                `
                        }
                    </tbody>
                </table>
            </div>
        `;

    }catch(error){
        body.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

function detail(label,value){
    return`
        <div class="rounded-md border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 break-words text-sm font-medium text-slate-700">
                ${AdminUI.escapeHtml(
                    value??'—'
                )}
            </p>
        </div>
    `;
}

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';
    el.category.value='';

    selectedFrom='';
    selectedTo='';

    if(dateRangePicker){
        dateRangePicker.clear(false);

    }else if(el.dateRange){
        el.dateRange.value='';
    }

    loadAssets(1);
};

async function initAssetPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initAssetPage,
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

    initDateRangePicker();
el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadAssets(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadAssets(1)
    );

    el.category.addEventListener(
        'change',
        ()=>loadAssets(1)
    );

    try{
        await loadOptions();

        await Promise.all([
            loadAssets(),
            loadSummary()
        ]);

    }catch(error){
        Toast.error(
            AdminUI.extractError(error)
        );
    }
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initAssetPage
    );

}else{
    initAssetPage();
}
</script>
@endpush