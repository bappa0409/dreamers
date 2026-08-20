@extends('layouts.admin')

@section('title','Assets')
@section('page_title','Assets')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col gap-3 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <h1 class="text-base font-bold text-slate-800">Assets</h1>
                <p class="text-sm text-slate-500">Manage association fixed assets, purchases and disposals.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('Finance.create'))
        <button
            type="button"
            onclick="openAssetModal()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700">
            <i class="bi bi-plus-lg"></i>
            Add Asset
        </button>
        @endif
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Active Assets</p>
            <p id="summaryCount" class="mt-1 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Purchase Cost</p>
            <p id="summaryCost" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Accumulated Depreciation</p>
            <p id="summaryDepreciation" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Book Value</p>
            <p id="summaryBookValue" class="mt-1 text-xl font-bold text-slate-800">৳0.00</p>
        </div>
    </div>

    {{-- Search / Filter - unchanged structure --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Search Assets</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by code, name, category, serial or vendor.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 lg:w-auto lg:grid-cols-[240px_150px_140px_auto] lg:gap-0">
                <input
                    id="searchInput"
                    type="text"
                    placeholder="Search..."
                    class="h-9 rounded-md border border-slate-300 px-3 text-sm outline-none focus:border-indigo-400 lg:rounded-r-none">

                <select
                    id="categoryFilter"
                    class="h-9 border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Categories</option>
                </select>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 px-3 text-xs outline-none focus:border-indigo-400 lg:border-l-0">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="sold">Sold</option>
                    <option value="disposed">Disposed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="h-9 cursor-pointer rounded-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 lg:rounded-l-none lg:border-l-0">
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1050px] text-sm">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Asset</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Purchase Date</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Account</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Depreciation</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Book Value</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody id="assetTable">
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-slate-400">
                            Loading assets...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>

{{-- Add Asset Modal --}}
<div id="assetModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-building-add"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Add Asset</h2>
                    <p class="text-sm text-slate-500">
                        Register asset and post purchase accounting automatically.
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeAssetModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="assetForm" class="flex min-h-0 flex-1 flex-col">
            <div class="grid grid-cols-1 gap-4 overflow-y-auto p-5 md:grid-cols-2">
                <div>
                    <label class="form-label">
                        Asset Name <span class="text-red-500">*</span>
                    </label>
                    <input id="assetName" type="text" class="app-input" required>
                </div>

                <div>
                    <label class="form-label">Category</label>
                    <input id="assetCategory" type="text" class="app-input">
                </div>

                <div>
                    <label class="form-label">
                        Asset Account <span class="text-red-500">*</span>
                    </label>
                    <select id="assetAccount" class="app-input" required>
                        <option value="">Select Asset Account</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Payment Account <span class="text-red-500">*</span>
                    </label>
                    <select id="paymentAccount" class="app-input" required>
                        <option value="">Select Cash/Bank</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Purchase Cost <span class="text-red-500">*</span>
                    </label>
                    <input id="purchaseCost" type="number" min="0.01" step="0.01" class="app-input" required>
                </div>

                <div>
                    <label class="form-label">
                        Purchase Date <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                        <input
                            id="purchaseDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                            required>
                    </div>
                </div>

                <div>
                    <label class="form-label">Vendor</label>
                    <input id="vendor" type="text" class="app-input">
                </div>

                <div>
                    <label class="form-label">Reference</label>
                    <input id="reference" type="text" class="app-input">
                </div>

                <div>
                    <label class="form-label">Location</label>
                    <input id="location" type="text" class="app-input">
                </div>

                <div>
                    <label class="form-label">Serial Number</label>
                    <input id="serialNo" type="text" class="app-input">
                </div>

                <div>
                    <label class="form-label">Useful Life (Months)</label>
                    <input id="usefulLife" type="number" min="1" class="app-input">
                </div>

                <div>
                    <label class="form-label">Salvage Value</label>
                    <input id="salvageValue" type="number" min="0" step="0.01" value="0" class="app-input">
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Description</label>
                    <textarea id="description" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div id="assetError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600 md:col-span-2"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeAssetModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="saveAssetButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Save Asset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Asset Details Modal --}}
<div id="detailsModal" class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-slate-600">
                    <i class="bi bi-building"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Asset Details</h2>
                    <p class="text-sm text-slate-500">View asset, depreciation and disposal information.</p>
                </div>
            </div>

            <button type="button" onclick="closeDetailsModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="detailsBody" class="min-h-0 flex-1 overflow-y-auto p-5"></div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-white px-5 py-4">
            <button
                type="button"
                onclick="closeDetailsModal()"
                class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>
        </div>
    </div>
</div>

{{-- Sell Asset Modal --}}
<div id="sellModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-lg flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-coin"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-800">Sell Asset</h2>
                    <p class="text-sm text-slate-500">Record asset sale and disposal accounting.</p>
                </div>
            </div>

            <button type="button" onclick="closeSellModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="sellForm" class="flex min-h-0 flex-1 flex-col">
            <input id="sellAssetId" type="hidden">

            <div class="space-y-4 overflow-y-auto p-5">
                <div>
                    <label class="form-label">
                        Sale Amount <span class="text-red-500">*</span>
                    </label>
                    <input id="saleAmount" type="number" min="0" step="0.01" class="app-input" required>
                </div>

                <div>
                    <label class="form-label">
                        Receive Account <span class="text-red-500">*</span>
                    </label>
                    <select id="saleReceiveAccount" class="app-input" required>
                        <option value="">Select Cash/Bank</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">
                        Sale Date <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                        <input
                            id="saleDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                            required>
                    </div>
                </div>

                <div>
                    <label class="form-label">Note</label>
                    <textarea id="saleNote" rows="3" class="app-input resize-none"></textarea>
                </div>

                <div id="sellError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeSellModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    id="sellButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60">
                    Sell Asset
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Dispose / Cancel Asset Modal --}}
<div id="actionModal" class="app-modal-overlay fixed inset-0 z-[60] hidden items-center justify-center p-3 sm:p-5">
    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-md flex-col overflow-hidden rounded-md bg-white">
        <div class="app-modal-header flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
            <div class="flex items-center gap-3">
                <div id="actionIcon" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600">
                    <i class="bi bi-exclamation-circle"></i>
                </div>

                <div>
                    <h2 id="actionTitle" class="text-lg font-bold text-slate-800"></h2>
                    <p id="actionSubtitle" class="text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button" onclick="closeActionModal()" class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <form id="actionForm" class="flex min-h-0 flex-1 flex-col">
            <input id="actionAssetId" type="hidden">
            <input id="actionType" type="hidden">

            <div class="space-y-4 overflow-y-auto p-5">
                <div id="disposalDateWrapper">
                    <label class="form-label">
                        Date <span class="text-red-500">*</span>
                    </label>

                    <div class="relative">
                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                        <input
                            id="actionDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label">
                        Reason / Note <span class="text-red-500">*</span>
                    </label>

                    <textarea
                        id="actionReason"
                        rows="4"
                        maxlength="1000"
                        class="app-input resize-none"
                        required></textarea>
                </div>

                <div id="actionError" class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600"></div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-2 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onclick="closeActionModal()"
                    class="cursor-pointer rounded-md border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                    Back
                </button>

                <button
                    id="actionButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-red-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-700 disabled:opacity-60">
                    Continue
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
let assets=[];
let options=null;
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(auth()->user()->hasPermission('Finance.update'));

const el={
    table:document.getElementById('assetTable'),
    search:document.getElementById('searchInput'),
    category:document.getElementById('categoryFilter'),
    status:document.getElementById('statusFilter')
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

async function loadAssets(page=1){
    currentPage=page;

    el.table.innerHTML=
        AdminUI.loadingState(
            'Loading assets...',
            9
        );

    const query=AdminUI.query({
        search:el.search.value.trim(),
        category:el.category.value,
        status:el.status.value,
        page
    });

    try{
        const [listResponse,summaryResponse]=await Promise.all([
            api(`/api/finance/assets?${query}`),
            api('/api/finance/assets/summary')
        ]);

        const paginator=
            listResponse.data??{};

        assets=
            paginator.data??[];

        currentPage=
            Number(
                paginator.current_page??1
            );

        lastPage=
            Number(
                paginator.last_page??1
            );

        total=
            Number(
                paginator.total??0
            );

        renderAssets();
        renderSummary(
            summaryResponse.data??{}
        );

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
                9
            );
    }
}

function renderSummary(data){
    document.getElementById(
        'summaryCount'
    ).textContent=
        Number(
            data.active_assets??0
        );

    document.getElementById(
        'summaryCost'
    ).textContent=
        money(
            data.purchase_cost
        );

    document.getElementById(
        'summaryDepreciation'
    ).textContent=
        money(
            data.accumulated_depreciation
        );

    document.getElementById(
        'summaryBookValue'
    ).textContent=
        money(
            data.book_value
        );
}

function renderAssets(){
    if(!assets.length){
        el.table.innerHTML=
            AdminUI.emptyState(
                'No assets found.',
                9
            );
        return;
    }

    el.table.innerHTML=assets.map(item=>{
        const book=Math.max(
            Number(item.purchase_cost??0)-
            Number(item.accumulated_depreciation??0),
            0
        );

        return`
            <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
                <td class="px-4 py-3">
                    <p class="text-xs font-semibold text-slate-700">
                        ${AdminUI.escapeHtml(item.name)}
                    </p>

                    <p class="font-mono text-[10px] text-indigo-600">
                        ${AdminUI.escapeHtml(item.asset_code)}
                    </p>
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${AdminUI.escapeHtml(item.category??'—')}
                </td>

                <td class="px-4 py-3 text-xs text-slate-600">
                    ${AdminUI.formatDate(item.purchase_date)}
                </td>

                <td class="px-4 py-3">
                    <p class="text-xs font-medium text-slate-700">
                        ${AdminUI.escapeHtml(
                            item.asset_account?.name??'—'
                        )}
                    </p>

                    <p class="text-[10px] text-slate-400">
                        ${AdminUI.escapeHtml(
                            item.asset_account?.code??''
                        )}
                    </p>
                </td>

                <td class="px-4 py-3 text-right text-xs font-semibold text-slate-800">
                    ${money(item.purchase_cost)}
                </td>

                <td class="px-4 py-3 text-right text-xs text-slate-600">
                    ${money(item.accumulated_depreciation)}
                </td>

                <td class="px-4 py-3 text-right text-xs font-semibold text-slate-800">
                    ${money(book)}
                </td>

                <td class="px-4 py-3">
                    ${statusBadge(item.status)}
                </td>

                <td class="px-4 py-3">
                    <div class="flex justify-end gap-1">
                        <button
                            type="button"
                            onclick="viewAsset(${item.id})"
                            class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-slate-50 text-slate-500 transition hover:bg-slate-100"
                            title="View">
                            <i class="bi bi-eye text-xs"></i>
                        </button>

                        ${
                            canUpdate&&item.status==='active'
                                ?`
                                    <button
                                        type="button"
                                        onclick="openSellModal(${item.id})"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-600 transition hover:bg-emerald-100"
                                        title="Sell">
                                        <i class="bi bi-cash-coin text-xs"></i>
                                    </button>

                                    <button
                                        type="button"
                                        onclick="openActionModal(${item.id},'dispose')"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-amber-50 text-amber-600 transition hover:bg-amber-100"
                                        title="Dispose">
                                        <i class="bi bi-box-arrow-down text-xs"></i>
                                    </button>

                                    <button
                                        type="button"
                                        onclick="openActionModal(${item.id},'cancel')"
                                        class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                        title="Cancel">
                                        <i class="bi bi-x-circle text-xs"></i>
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

async function loadOptions(){
    if(options){
        return options;
    }

    const response=await api(
        '/api/finance/assets/options'
    );

    options=
        response.data??{};

    const assetAccounts=
        document.getElementById(
            'assetAccount'
        );

    const paymentAccount=
        document.getElementById(
            'paymentAccount'
        );

    const saleAccount=
        document.getElementById(
            'saleReceiveAccount'
        );

    assetAccounts.innerHTML=`
        <option value="">Select Asset Account</option>

        ${(options.asset_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    const cashBank=`
        <option value="">Select Cash/Bank</option>

        ${(options.cash_bank_accounts??[]).map(item=>`
            <option value="${item.id}">
                ${AdminUI.escapeHtml(item.code)}
                -
                ${AdminUI.escapeHtml(item.name)}
            </option>
        `).join('')}
    `;

    paymentAccount.innerHTML=
        cashBank;

    saleAccount.innerHTML=
        cashBank;

    document.getElementById(
        'categoryFilter'
    ).innerHTML=`
        <option value="">All Categories</option>

        ${(options.categories??[]).map(value=>`
            <option value="${AdminUI.escapeHtml(value)}">
                ${AdminUI.escapeHtml(value)}
            </option>
        `).join('')}
    `;

    return options;
}

window.openAssetModal=async function(){
    try{
        await loadOptions();

        document.getElementById(
            'assetForm'
        ).reset();

        document.getElementById(
            'salvageValue'
        ).value='0';

        AdminUI.clearError(
            'assetError'
        );

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

document.getElementById(
    'assetForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const button=
            document.getElementById(
                'saveAssetButton'
            );

        AdminUI.clearError(
            'assetError'
        );

        AdminUI.setLoading(
            button,
            'Saving...'
        );

        try{
            await api(
                '/api/finance/assets',
                {
                    method:'POST',
                    body:JSON.stringify({
                        name:
                            document
                                .getElementById(
                                    'assetName'
                                )
                                .value
                                .trim(),

                        category:
                            document
                                .getElementById(
                                    'assetCategory'
                                )
                                .value
                                .trim()||
                            null,

                        asset_account_id:
                            Number(
                                document
                                    .getElementById(
                                        'assetAccount'
                                    )
                                    .value
                            ),

                        payment_account_id:
                            Number(
                                document
                                    .getElementById(
                                        'paymentAccount'
                                    )
                                    .value
                            ),

                        purchase_cost:
                            Number(
                                document
                                    .getElementById(
                                        'purchaseCost'
                                    )
                                    .value
                            ),

                        purchase_date:
                            document
                                .getElementById(
                                    'purchaseDate'
                                )
                                .value,

                        vendor:
                            document
                                .getElementById(
                                    'vendor'
                                )
                                .value
                                .trim()||
                            null,

                        reference:
                            document
                                .getElementById(
                                    'reference'
                                )
                                .value
                                .trim()||
                            null,

                        location:
                            document
                                .getElementById(
                                    'location'
                                )
                                .value
                                .trim()||
                            null,

                        serial_no:
                            document
                                .getElementById(
                                    'serialNo'
                                )
                                .value
                                .trim()||
                            null,

                        useful_life_months:
                            document
                                .getElementById(
                                    'usefulLife'
                                )
                                .value
                                ?Number(
                                    document
                                        .getElementById(
                                            'usefulLife'
                                        )
                                        .value
                                )
                                :null,

                        salvage_value:
                            Number(
                                document
                                    .getElementById(
                                        'salvageValue'
                                    )
                                    .value||
                                0
                            ),

                        description:
                            document
                                .getElementById(
                                    'description'
                                )
                                .value
                                .trim()||
                            null
                    })
                }
            );

            closeAssetModal();

            Toast.success(
                'Asset created successfully.'
            );

            options=null;

            await loadAssets(1);
        }catch(error){
            AdminUI.showError(
                'assetError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.closeDetailsModal=function(){
    AdminUI.closeModal(
        'detailsModal'
    );
};

window.viewAsset=async function(id){
    AdminUI.openModal(
        'detailsModal'
    );

    const body=
        document.getElementById(
            'detailsBody'
        );

    body.innerHTML=
        AdminUI.loadingState(
            'Loading asset...'
        );

    try{
        const response=await api(
            `/api/finance/assets/${id}`
        );

        const item=
            response.data??{};

        body.innerHTML=`
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
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
                    'Status',
                    titleCase(
                        item.status
                    )
                )}

                ${detail(
                    'Purchase Cost',
                    money(
                        item.purchase_cost
                    )
                )}

                ${detail(
                    'Depreciation',
                    money(
                        item.accumulated_depreciation
                    )
                )}

                ${detail(
                    'Book Value',
                    money(
                        Math.max(
                            Number(item.purchase_cost)-
                            Number(item.accumulated_depreciation),
                            0
                        )
                    )
                )}

                ${detail(
                    'Purchase Date',
                    AdminUI.formatDate(
                        item.purchase_date
                    )
                )}

                ${detail(
                    'Vendor',
                    item.vendor??'—'
                )}

                ${detail(
                    'Serial No',
                    item.serial_no??'—'
                )}

                ${detail(
                    'Location',
                    item.location??'—'
                )}

                ${detail(
                    'Reference',
                    item.reference??'—'
                )}
            </div>

            ${
                item.disposal_date
                    ?`
                        <div class="mt-5 rounded-md border border-slate-200 p-4">
                            <p class="mb-3 text-xs font-semibold text-slate-700">
                                Disposal Information
                            </p>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                ${detail(
                                    'Date',
                                    AdminUI.formatDate(
                                        item.disposal_date
                                    )
                                )}

                                ${detail(
                                    'Amount',
                                    money(
                                        item.disposal_amount
                                    )
                                )}

                                ${detail(
                                    'Note',
                                    item.disposal_note??'—'
                                )}
                            </div>
                        </div>
                    `
                    :''
            }

            ${
                item.description
                    ?`
                        <div class="mt-4 rounded-md border border-slate-200 bg-slate-50 p-3">
                            <p class="mb-1 text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Description
                            </p>

                            <p class="text-xs leading-5 text-slate-600">
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
            <div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
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

        document.getElementById(
            'sellForm'
        ).reset();

        document.getElementById(
            'sellAssetId'
        ).value=id;

        AdminUI.clearError(
            'sellError'
        );

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

window.closeSellModal=function(){
    AdminUI.closeModal(
        'sellModal'
    );
};

document.getElementById(
    'sellForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const id=
            document.getElementById(
                'sellAssetId'
            ).value;

        const button=
            document.getElementById(
                'sellButton'
            );

        AdminUI.clearError(
            'sellError'
        );

        AdminUI.setLoading(
            button,
            'Selling...'
        );

        try{
            await api(
                `/api/finance/assets/${id}/sell`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        amount:
                            Number(
                                document
                                    .getElementById(
                                        'saleAmount'
                                    )
                                    .value
                            ),

                        receive_account_id:
                            Number(
                                document
                                    .getElementById(
                                        'saleReceiveAccount'
                                    )
                                    .value
                            ),

                        disposal_date:
                            document
                                .getElementById(
                                    'saleDate'
                                )
                                .value,

                        note:
                            document
                                .getElementById(
                                    'saleNote'
                                )
                                .value
                                .trim()||
                            null
                    })
                }
            );

            closeSellModal();

            Toast.success(
                'Asset sold successfully.'
            );

            await loadAssets(
                currentPage
            );
        }catch(error){
            AdminUI.showError(
                'sellError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

window.openActionModal=function(id,type){
    document.getElementById(
        'actionForm'
    ).reset();

    document.getElementById(
        'actionAssetId'
    ).value=id;

    document.getElementById(
        'actionType'
    ).value=type;

    const dispose=
        type==='dispose';

    document.getElementById(
        'actionTitle'
    ).textContent=
        dispose
            ?'Dispose Asset'
            :'Cancel Asset Purchase';

    document.getElementById(
        'actionSubtitle'
    ).textContent=
        dispose
            ?'Record disposal of this asset.'
            :'Cancel this asset purchase and reverse its accounting.';

    document.getElementById(
        'disposalDateWrapper'
    ).classList.toggle(
        'hidden',
        !dispose
    );

    document.getElementById(
        'actionDate'
    ).required=
        dispose;

    const icon=
        document.getElementById(
            'actionIcon'
        );

    icon.className=
        dispose
            ?'flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-amber-50 text-amber-600'
            :'flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-red-50 text-red-600';

    icon.innerHTML=
        dispose
            ?'<i class="bi bi-box-arrow-down"></i>'
            :'<i class="bi bi-x-circle"></i>';

    AdminUI.clearError(
        'actionError'
    );

    AdminUI.openModal(
        'actionModal'
    );

    if(
        typeof window.initDatePickers===
        'function'
    ){
        window.initDatePickers();
    }

    if(dispose){
        setPickerDate(
            document.getElementById(
                'actionDate'
            ),
            localDate()
        );
    }
};

window.closeActionModal=function(){
    AdminUI.closeModal(
        'actionModal'
    );
};

document.getElementById(
    'actionForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        const id=
            document.getElementById(
                'actionAssetId'
            ).value;

        const type=
            document.getElementById(
                'actionType'
            ).value;

        const reason=
            document
                .getElementById(
                    'actionReason'
                )
                .value
                .trim();

        const button=
            document.getElementById(
                'actionButton'
            );

        AdminUI.clearError(
            'actionError'
        );

        AdminUI.setLoading(
            button,
            type==='dispose'
                ?'Disposing...'
                :'Cancelling...'
        );

        try{
            const payload=
                type==='dispose'
                    ?{
                        disposal_date:
                            document
                                .getElementById(
                                    'actionDate'
                                )
                                .value,

                        note:
                            reason
                    }
                    :{
                        reason
                    };

            await api(
                `/api/finance/assets/${id}/${type}`,
                {
                    method:'POST',
                    body:JSON.stringify(
                        payload
                    )
                }
            );

            closeActionModal();

            Toast.success(
                type==='dispose'
                    ?'Asset disposed successfully.'
                    :'Asset purchase cancelled successfully.'
            );

            await loadAssets(
                currentPage
            );
        }catch(error){
            AdminUI.showError(
                'actionError',
                AdminUI.extractError(error)
            );
        }finally{
            AdminUI.resetLoading(
                button
            );
        }
    }
);

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

function statusBadge(status){
    const map={
        active:
            'bg-emerald-50 text-emerald-700',

        sold:
            'bg-indigo-50 text-indigo-700',

        disposed:
            'bg-amber-50 text-amber-700',

        cancelled:
            'bg-slate-100 text-slate-500'
    };

    return`
        <span class="inline-flex rounded-md px-2 py-1 text-[10px] font-semibold ${map[status]??map.active}">
            ${AdminUI.escapeHtml(
                titleCase(status)
            )}
        </span>
    `;
}

function titleCase(value){
    return String(
        value??''
    )
        .replaceAll(
            '_',
            ' '
        )
        .replace(
            /\b\w/g,
            character=>
                character.toUpperCase()
        );
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

window.clearFilters=function(){
    el.search.value='';
    el.category.value='';
    el.status.value='';

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

    try{
        await loadOptions();
    }catch(error){
        console.error(error);
    }

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadAssets(1)
        )
    );

    el.category.addEventListener(
        'change',
        ()=>loadAssets(1)
    );

    el.status.addEventListener(
        'change',
        ()=>loadAssets(1)
    );

    await loadAssets();
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