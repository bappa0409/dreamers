@extends('layouts.admin')

@section('title','Land Management')
@section('page_title','Land Management')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">

        <div class="flex items-start gap-3">

            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-map"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">
                    Land Management
                </h1>

                <p class="text-sm text-slate-500">
                    Track land purchase, valuation and sales.
                </p>
            </div>

        </div>


        @if(auth()->user()->hasPermission('Land.create'))

            <button
                type="button"
                onclick="openLandModal()"
                class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
            >
                <i class="bi bi-plus-lg"></i>
                Add Land
            </button>

        @endif

    </div>


    {{-- =========================================================
    STATISTICS
    ========================================================== --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

        <div class="rounded-md border border-slate-200 bg-white p-4">

            <p class="text-xs text-slate-500">
                Purchase Value
            </p>

            <p
                id="purchaseValue"
                class="mt-2 truncate text-xl font-bold text-slate-800"
            >
                {{ setting('currency_symbol','৳') }}0
            </p>

        </div>


        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">

            <p class="text-xs text-indigo-600">
                Current Value
            </p>

            <p
                id="currentValue"
                class="mt-2 truncate text-xl font-bold text-indigo-700"
            >
                {{ setting('currency_symbol','৳') }}0
            </p>

        </div>


        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">

            <p class="text-xs text-emerald-600">
                Sold
            </p>

            <p
                id="soldCount"
                class="mt-2 text-xl font-bold text-emerald-700"
            >
                0
            </p>

        </div>


        <div class="rounded-md border border-slate-200 bg-white p-4">

            <p class="text-xs text-slate-500">
                Profit / Loss
            </p>

            <p
                id="profitLoss"
                class="mt-2 truncate text-xl font-bold text-slate-800"
            >
                {{ setting('currency_symbol','৳') }}0
            </p>

        </div>

    </div>


    {{-- =========================================================
    SEARCH
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Search Land
                    </p>

                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by code, title, location, khatian or dag
                    </p>
                </div>

            </div>


            <div class="flex w-full items-center lg:w-auto">

                <div class="relative w-full lg:w-80">

                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search land..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                    >

                </div>


                <select
                    id="statusFilter"
                    class="h-9 cursor-pointer border border-slate-300 bg-white px-3 text-xs font-medium text-slate-600 outline-none focus:border-indigo-400"
                >
                    <option value="">All Status</option>
                    <option value="planned">Planned</option>
                    <option value="negotiating">Negotiating</option>
                    <option value="purchased">Purchased</option>
                    <option value="sold">Sold</option>
                    <option value="cancelled">Cancelled</option>
                </select>


                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>

            </div>

        </div>

    </div>


    {{-- =========================================================
    LAND GRID
    ========================================================== --}}
    <div
        id="landGrid"
        class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3"
    >
        <div class="col-span-full">
            <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
                Loading lands...
            </div>
        </div>
    </div>


    {{-- Pagination --}}
    <div id="paginationContainer"></div>

</div>


{{-- =============================================================
LAND MODAL
============================================================= --}}
<div
    id="landModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-map"></i>
                </div>

                <div>
                    <h2
                        id="landModalTitle"
                        class="text-lg font-bold text-slate-800"
                    >
                        Add Land
                    </h2>

                    <p class="text-xs text-slate-500">
                        Land purchase and property information.
                    </p>
                </div>

            </div>


            <button
                type="button"
                onclick="closeLandModal()"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <form
            id="landForm"
            class="flex min-h-0 flex-1 flex-col"
        >

            <div class="space-y-4 overflow-y-auto p-5">

                {{-- Title --}}
                <div>

                    <label class="form-label">
                        Title *
                    </label>

                    <input
                        id="title"
                        maxlength="255"
                        class="app-input"
                        placeholder="Land title"
                    >

                </div>


                {{-- Description --}}
                <div>

                    <label class="form-label">
                        Description
                    </label>

                    <textarea
                        id="description"
                        rows="3"
                        class="app-input resize-none"
                    ></textarea>

                </div>


                {{-- Location --}}
                <div>

                    <div class="mb-3 flex items-center gap-2">
                        <i class="bi bi-geo-alt text-xs text-indigo-500"></i>

                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Location
                        </p>
                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                        <div>
                            <label class="form-label">
                                District
                            </label>

                            <input
                                id="district"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Upazila
                            </label>

                            <input
                                id="upazila"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Mouza
                            </label>

                            <input
                                id="mouza"
                                class="app-input"
                            >
                        </div>

                    </div>

                </div>


                {{-- Property --}}
                <div class="border-t border-slate-100 pt-4">

                    <div class="mb-3 flex items-center gap-2">
                        <i class="bi bi-map text-xs text-indigo-500"></i>

                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Property Information
                        </p>
                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

                        <div>
                            <label class="form-label">
                                Khatian No
                            </label>

                            <input
                                id="khatianNo"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Dag No
                            </label>

                            <input
                                id="dagNo"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Status
                            </label>

                            <select
                                id="status"
                                class="app-input"
                            >
                                <option value="planned">Planned</option>
                                <option value="negotiating">Negotiating</option>
                                <option value="purchased">Purchased</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>


                        <div>
                            <label class="form-label">
                                Land Area
                            </label>

                            <input
                                id="landArea"
                                type="number"
                                min="0"
                                step="0.0001"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Area Unit
                            </label>

                            <select
                                id="areaUnit"
                                class="app-input"
                            >
                                <option value="decimal">Decimal</option>
                                <option value="katha">Katha</option>
                                <option value="bigha">Bigha</option>
                                <option value="acre">Acre</option>
                                <option value="sqft">Sq Ft</option>
                            </select>
                        </div>


                        <div>
                            <label class="form-label">
                                Purchase Date
                            </label>

                            <div class="relative">

                                <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                                <input
                                    id="purchaseDate"
                                    type="text"
                                    class="app-input js-date-picker !pl-9"
                                    placeholder="Select date"
                                    autocomplete="off"
                                >

                            </div>
                        </div>

                    </div>

                </div>


                {{-- Financial --}}
                <div class="border-t border-slate-100 pt-4">

                    <div class="mb-3 flex items-center gap-2">
                        <i class="bi bi-cash-stack text-xs text-indigo-500"></i>

                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Financial Information
                        </p>
                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="form-label">
                                Purchase Price
                            </label>

                            <input
                                id="purchasePrice"
                                type="number"
                                min="0"
                                step="0.01"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Current Value
                            </label>

                            <input
                                id="currentValueInput"
                                type="number"
                                min="0"
                                step="0.01"
                                class="app-input"
                            >
                        </div>

                    </div>

                </div>


                {{-- Seller --}}
                <div class="border-t border-slate-100 pt-4">

                    <div class="mb-3 flex items-center gap-2">
                        <i class="bi bi-person text-xs text-indigo-500"></i>

                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">
                            Seller Information
                        </p>
                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="form-label">
                                Seller Name
                            </label>

                            <input
                                id="sellerName"
                                class="app-input"
                            >
                        </div>


                        <div>
                            <label class="form-label">
                                Seller Phone
                            </label>

                            <input
                                id="sellerPhone"
                                class="app-input"
                            >
                        </div>

                    </div>

                </div>


                {{-- Notes --}}
                <div>

                    <label class="form-label">
                        Notes
                    </label>

                    <textarea
                        id="notes"
                        rows="3"
                        class="app-input resize-none"
                    ></textarea>

                </div>


                <div
                    id="landError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"
                ></div>

            </div>


            {{-- Footer --}}
            <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-white px-5 py-4">

                <button
                    type="button"
                    onclick="closeLandModal()"
                    class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="saveLandButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-indigo-700"
                >
                    Save Land
                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
SELL MODAL
============================================================= --}}
<div
    id="sellModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5"
>

    <div class="app-modal-panel w-full max-w-lg overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex items-start justify-between border-b border-slate-200 px-5 py-3">

            <div class="flex min-w-0 items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="min-w-0">

                    <h2 class="text-lg font-bold text-slate-800">
                        Sell Land
                    </h2>

                    <p
                        id="sellLandInfo"
                        class="mt-1 truncate text-xs text-slate-500"
                    ></p>

                </div>

            </div>


            <button
                type="button"
                onclick="AdminUI.closeModal('sellModal')"
                class="app-modal-close"
            >
                <i class="bi bi-x-lg"></i>
            </button>

        </div>


        <form id="sellForm">

            <div class="space-y-4 p-5">

                <input
                    id="sellLandId"
                    type="hidden"
                >


                <div>

                    <label class="form-label">
                        Sale Price *
                    </label>

                    <input
                        id="salePrice"
                        type="number"
                        min="0.01"
                        step="0.01"
                        class="app-input"
                    >

                </div>


                <div>

                    <label class="form-label">
                        Selling Expense
                    </label>

                    <input
                        id="sellingExpense"
                        type="number"
                        min="0"
                        step="0.01"
                        value="0"
                        class="app-input"
                    >

                </div>


                <div>

                    <label class="form-label">
                        Sale Date *
                    </label>

                    <div class="relative">

                        <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-xs text-slate-400"></i>

                        <input
                            id="saleDate"
                            type="text"
                            class="app-input js-date-picker !pl-9"
                            placeholder="Select date"
                            autocomplete="off"
                        >

                    </div>

                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                    <div>
                        <label class="form-label">
                            Buyer Name
                        </label>

                        <input
                            id="buyerName"
                            class="app-input"
                        >
                    </div>


                    <div>
                        <label class="form-label">
                            Buyer Phone
                        </label>

                        <input
                            id="buyerPhone"
                            class="app-input"
                        >
                    </div>

                </div>


                <div
                    id="sellError"
                    class="hidden rounded-md border border-red-200 bg-red-50 p-3 text-xs text-red-700"
                ></div>

            </div>


            <div class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">

                <button
                    type="button"
                    onclick="AdminUI.closeModal('sellModal')"
                    class="cursor-pointer rounded-md border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Cancel
                </button>

                <button
                    id="sellButton"
                    type="submit"
                    class="cursor-pointer rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700"
                >
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
let currentPage=1;
let lastPage=1;
let total=0;

const canUpdate=@json(
    auth()->user()->hasPermission('Land.update')
);

const canDelete=@json(
    auth()->user()->hasPermission('Land.delete')
);

const currency=@json(
    setting('currency_symbol','৳')
);

const el={
    grid:document.getElementById('landGrid'),
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

    status:document.getElementById('status'),
    notes:document.getElementById('notes'),

    saveButton:document.getElementById('saveLandButton')
};


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function money(value){
    return currency+
        Number(value??0)
            .toLocaleString(
                'en-US',
                {
                    minimumFractionDigits:2,
                    maximumFractionDigits:2
                }
            );
}


/*
|--------------------------------------------------------------------------
| Date Picker Value
|--------------------------------------------------------------------------
*/

function setPickerDate(
    element,
    value
){
    if(!element){
        return;
    }

    const date=value
        ?String(value).substring(0,10)
        :'';

    element.value=date;

    if(element._flatpickr){
        if(date){
            element._flatpickr.setDate(
                date,
                false
            );
        }else{
            element._flatpickr.clear();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Grid States
|--------------------------------------------------------------------------
*/

function loadingState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.loadingState(
                'Loading lands...'
            )}
        </div>
    `;
}


function emptyState(){
    el.grid.innerHTML=`
        <div class="col-span-full">
            ${AdminUI.emptyState(
                'No lands found.'
            )}
        </div>
    `;
}


function errorState(error){
    el.grid.innerHTML=`
        <div class="col-span-full">
            <div class="rounded-md border border-red-200 bg-red-50 p-8 text-center text-sm text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Load Lands
|--------------------------------------------------------------------------
*/

async function loadLands(page=1){
    currentPage=page;

    loadingState();

    const query=AdminUI.query({
        search:el.search.value.trim(),
        status:el.statusFilter.value,
        page
    });

    try{
        const response=
            await api(
                `/api/lands?${query}`
            );

        const paginator=
            response.data??{};

        lands=
            Array.isArray(paginator.data)
                ?paginator.data
                :[];

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

        renderLands();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadLands
        });

    }catch(error){
        errorState(error);

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadLands
        });
    }
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

async function loadStatistics(){
    try{
        const response=
            await api(
                '/api/lands/statistics'
            );

        const stats=
            response.data??{};

        document.getElementById(
            'purchaseValue'
        ).innerText=
            money(
                stats.purchase_value
            );

        document.getElementById(
            'currentValue'
        ).innerText=
            money(
                stats.current_value
            );

        document.getElementById(
            'soldCount'
        ).innerText=
            stats.sold??0;


        const profitElement=
            document.getElementById(
                'profitLoss'
            );

        const profit=
            Number(
                stats.profit_loss??0
            );

        profitElement.innerText=
            money(profit);


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
        console.error(
            'Land statistics load failed:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Render Lands
|--------------------------------------------------------------------------
*/

function renderLands(){
    if(!lands.length){
        emptyState();
        return;
    }


    el.grid.innerHTML=
        lands.map(item=>`

            <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:border-slate-300">

                <div class="p-4">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <p
                                class="truncate text-xs font-bold text-indigo-600"
                                title="${AdminUI.escapeHtml(item.land_code??'')}"
                            >
                                ${AdminUI.escapeHtml(
                                    item.land_code??
                                    '—'
                                )}
                            </p>


                            <h3
                                class="mt-1 truncate text-sm font-bold text-slate-800"
                                title="${AdminUI.escapeHtml(item.title??'')}"
                            >
                                ${AdminUI.escapeHtml(
                                    item.title??
                                    'Untitled'
                                )}
                            </h3>


                            <p
                                class="mt-1 truncate text-[11px] text-slate-400"
                                title="${AdminUI.escapeHtml(locationText(item))}"
                            >
                                <i class="bi bi-geo-alt me-1"></i>

                                ${AdminUI.escapeHtml(
                                    locationText(item)
                                )}
                            </p>

                        </div>


                        <div class="shrink-0">
                            ${AdminUI.statusBadge(
                                item.status
                            )}
                        </div>

                    </div>


                    {{-- Value --}}
                    <div class="mt-4 grid grid-cols-2 gap-2">

                        <div class="min-w-0 rounded-md bg-slate-50 p-3">

                            <p class="text-[10px] text-slate-400">
                                Purchase
                            </p>

                            <p
                                class="mt-1 truncate text-xs font-bold text-slate-700"
                                title="${AdminUI.escapeHtml(money(item.purchase_price))}"
                            >
                                ${money(
                                    item.purchase_price
                                )}
                            </p>

                        </div>


                        <div class="min-w-0 rounded-md bg-indigo-50 p-3">

                            <p class="truncate text-[10px] text-indigo-400">

                                ${
                                    item.status==='sold'
                                        ?'Sale Price'
                                        :'Current Value'
                                }

                            </p>

                            <p
                                class="mt-1 truncate text-xs font-bold text-indigo-700"
                                title="${AdminUI.escapeHtml(
                                    money(
                                        item.status==='sold'
                                            ?item.sale_price
                                            :item.current_value
                                    )
                                )}"
                            >
                                ${money(
                                    item.status==='sold'
                                        ?item.sale_price
                                        :item.current_value
                                )}
                            </p>

                        </div>

                    </div>


                    {{-- Profit/Loss --}}
                    ${
                        item.status==='sold'
                            ?`
                                <div class="mt-2 rounded-md ${
                                    Number(item.profit_loss)>=0
                                        ?'bg-emerald-50 text-emerald-700'
                                        :'bg-red-50 text-red-700'
                                } p-3">

                                    <p class="text-[10px] opacity-70">
                                        Profit / Loss
                                    </p>

                                    <p class="mt-1 truncate text-sm font-bold">
                                        ${money(item.profit_loss)}
                                        (${Number(item.profit_percentage??0).toFixed(2)}%)
                                    </p>

                                </div>
                            `
                            :''
                    }


                    {{-- Meta --}}
                    <div class="mt-3 flex flex-wrap gap-1.5 text-[10px] text-slate-500">

                        ${
                            item.land_area
                                ?`
                                    <span class="rounded-md bg-slate-100 px-2 py-1">
                                        ${AdminUI.escapeHtml(item.land_area)}
                                        ${AdminUI.escapeHtml(item.area_unit??'')}
                                    </span>
                                `
                                :''
                        }


                        ${
                            item.khatian_no
                                ?`
                                    <span class="max-w-full truncate rounded-md bg-slate-100 px-2 py-1">
                                        Khatian:
                                        ${AdminUI.escapeHtml(item.khatian_no)}
                                    </span>
                                `
                                :''
                        }


                        ${
                            item.dag_no
                                ?`
                                    <span class="max-w-full truncate rounded-md bg-slate-100 px-2 py-1">
                                        Dag:
                                        ${AdminUI.escapeHtml(item.dag_no)}
                                    </span>
                                `
                                :''
                        }


                        <span class="rounded-md bg-slate-100 px-2 py-1">
                            ${item.investments_count??0}
                            Investor
                        </span>

                    </div>


                    ${
                        item.purchase_date
                            ?`
                                <p class="mt-3 text-[10px] text-slate-400">
                                    Purchased:
                                    ${AdminUI.formatDate(item.purchase_date)}
                                </p>
                            `
                            :''
                    }


                    ${
                        item.status==='sold'&&
                        item.sale_date
                            ?`
                                <p class="mt-1 text-[10px] text-slate-400">
                                    Sold:
                                    ${AdminUI.formatDate(item.sale_date)}
                                </p>
                            `
                            :''
                    }

                </div>


                {{-- Actions --}}
                <div class="mt-auto flex justify-end gap-1 border-t border-slate-100 bg-slate-50/50 px-4 py-3">

                    ${
                        canUpdate&&
                        item.status==='purchased'
                            ?`
                                <button
                                    type="button"
                                    onclick="openSellModal(${item.id})"
                                    title="Sell Land"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-emerald-50 text-emerald-700 transition hover:bg-emerald-100"
                                >
                                    <i class="bi bi-cash-stack text-xs"></i>
                                </button>
                            `
                            :''
                    }


                    ${
                        canUpdate&&
                        item.status!=='sold'
                            ?`
                                <button
                                    type="button"
                                    onclick="editLand(${item.id})"
                                    title="Edit Land"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-indigo-50 text-indigo-600 transition hover:bg-indigo-100"
                                >
                                    <i class="bi bi-pencil-square text-xs"></i>
                                </button>
                            `
                            :''
                    }


                    ${
                        canDelete&&
                        item.status!=='sold'
                            ?`
                                <button
                                    type="button"
                                    onclick="deleteLand(${item.id})"
                                    title="Delete Land"
                                    class="flex h-8 w-8 cursor-pointer items-center justify-center rounded-md bg-red-50 text-red-600 transition hover:bg-red-100"
                                >
                                    <i class="bi bi-trash text-xs"></i>
                                </button>
                            `
                            :''
                    }

                </div>

            </article>

        `).join('');
}


/*
|--------------------------------------------------------------------------
| Location Text
|--------------------------------------------------------------------------
*/

function locationText(item){
    return [
        item.mouza,
        item.upazila,
        item.district
    ]
        .filter(Boolean)
        .join(', ')
        ||'Location not specified';
}


/*
|--------------------------------------------------------------------------
| Open Land Modal
|--------------------------------------------------------------------------
*/

window.openLandModal=
function(item=null){
    editingLand=item;

    AdminUI.resetForm(
        el.form
    );

    AdminUI.clearError(
        'landError'
    );


    document.getElementById(
        'landModalTitle'
    ).innerText=
        item
            ?'Edit Land'
            :'Add Land';


    if(item){
        el.title.value=
            item.title??'';

        el.description.value=
            item.description??'';

        el.district.value=
            item.district??'';

        el.upazila.value=
            item.upazila??'';

        el.mouza.value=
            item.mouza??'';

        el.khatianNo.value=
            item.khatian_no??'';

        el.dagNo.value=
            item.dag_no??'';

        el.landArea.value=
            item.land_area??'';

        el.areaUnit.value=
            item.area_unit??
            'decimal';

        el.purchasePrice.value=
            item.purchase_price??0;

        el.currentValue.value=
            item.current_value??'';

        el.sellerName.value=
            item.seller_name??'';

        el.sellerPhone.value=
            item.seller_phone??'';

        el.status.value=
            item.status??
            'planned';

        el.notes.value=
            item.notes??'';


        setPickerDate(
            el.purchaseDate,
            item.purchase_date
        );

    }else{
        el.status.value=
            'planned';

        el.areaUnit.value=
            'decimal';

        el.purchasePrice.value='';

        el.currentValue.value='';

        setPickerDate(
            el.purchaseDate,
            null
        );
    }


    AdminUI.openModal(
        'landModal'
    );
};


/*
|--------------------------------------------------------------------------
| Close Land Modal
|--------------------------------------------------------------------------
*/

window.closeLandModal=
function(){
    AdminUI.closeModal(
        'landModal'
    );

    editingLand=null;
};


/*
|--------------------------------------------------------------------------
| Edit
|--------------------------------------------------------------------------
*/

window.editLand=
function(id){
    const item=
        lands.find(
            item=>
                Number(item.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Land record not found.'
        );

        return;
    }


    openLandModal(item);
};


/*
|--------------------------------------------------------------------------
| Save Land
|--------------------------------------------------------------------------
*/

el.form.addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'landError'
        );


        const title=
            el.title.value.trim();


        if(!title){
            AdminUI.showError(
                'landError',
                'Land title is required.'
            );

            return;
        }


        const data={
            title,

            description:
                el.description.value.trim()
                ||null,

            district:
                el.district.value.trim()
                ||null,

            upazila:
                el.upazila.value.trim()
                ||null,

            mouza:
                el.mouza.value.trim()
                ||null,

            khatian_no:
                el.khatianNo.value.trim()
                ||null,

            dag_no:
                el.dagNo.value.trim()
                ||null,

            land_area:
                el.landArea.value
                ||null,

            area_unit:
                el.areaUnit.value,

            purchase_price:
                el.purchasePrice.value
                ||0,

            current_value:
                el.currentValue.value
                ||null,

            purchase_date:
                el.purchaseDate.value
                ||null,

            seller_name:
                el.sellerName.value.trim()
                ||null,

            seller_phone:
                el.sellerPhone.value.trim()
                ||null,

            status:
                el.status.value,

            notes:
                el.notes.value.trim()
                ||null
        };


        AdminUI.setLoading(
            el.saveButton,
            editingLand
                ?'Updating...'
                :'Saving...'
        );


        try{
            const editing=
                Boolean(
                    editingLand
                );

            const pageAfterSave=
                editing
                    ?currentPage
                    :1;


            await api(
                editing
                    ?`/api/lands/${editingLand.id}`
                    :'/api/lands',
                {
                    method:
                        editing
                            ?'PUT'
                            :'POST',

                    body:
                        JSON.stringify(
                            data
                        )
                }
            );


            closeLandModal();


            Toast.success(
                editing
                    ?'Land updated successfully.'
                    :'Land created successfully.'
            );


            await Promise.all([
                loadLands(
                    pageAfterSave
                ),

                loadStatistics()
            ]);

        }catch(error){
            AdminUI.showError(
                'landError',
                AdminUI.extractError(
                    error
                )
            );

        }finally{
            AdminUI.resetLoading(
                el.saveButton
            );
        }
    }
);


/*
|--------------------------------------------------------------------------
| Open Sell Modal
|--------------------------------------------------------------------------
*/

window.openSellModal=
function(id){
    const item=
        lands.find(
            item=>
                Number(item.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Land record not found.'
        );

        return;
    }


    const form=
        document.getElementById(
            'sellForm'
        );

    form.reset();

    AdminUI.clearError(
        'sellError'
    );


    document.getElementById(
        'sellLandId'
    ).value=
        item.id;


    document.getElementById(
        'sellLandInfo'
    ).innerText=
        `${item.land_code} • ${item.title}`;


    document.getElementById(
        'sellingExpense'
    ).value='0';


    const saleDate=
        document.getElementById(
            'saleDate'
        );


    setPickerDate(
        saleDate,
        new Date()
            .toISOString()
            .slice(0,10)
    );


    AdminUI.openModal(
        'sellModal'
    );
};


/*
|--------------------------------------------------------------------------
| Sell Land
|--------------------------------------------------------------------------
*/

document.getElementById(
    'sellForm'
).addEventListener(
    'submit',
    async event=>{
        event.preventDefault();

        AdminUI.clearError(
            'sellError'
        );


        const id=
            document.getElementById(
                'sellLandId'
            ).value;


        const button=
            document.getElementById(
                'sellButton'
            );


        const salePrice=
            Number(
                document.getElementById(
                    'salePrice'
                ).value
            );


        const sellingExpense=
            Number(
                document.getElementById(
                    'sellingExpense'
                ).value
                ||0
            );


        const saleDate=
            document.getElementById(
                'saleDate'
            ).value;


        if(
            !salePrice||
            salePrice<=0
        ){
            AdminUI.showError(
                'sellError',
                'Valid sale price is required.'
            );

            return;
        }


        if(sellingExpense<0){
            AdminUI.showError(
                'sellError',
                'Selling expense cannot be negative.'
            );

            return;
        }


        if(!saleDate){
            AdminUI.showError(
                'sellError',
                'Sale date is required.'
            );

            return;
        }


        AdminUI.setLoading(
            button,
            'Selling...'
        );


        try{
            await api(
                `/api/lands/${id}/sell`,
                {
                    method:'POST',

                    body:
                        JSON.stringify({
                            sale_price:
                                salePrice,

                            selling_expense:
                                sellingExpense,

                            sale_date:
                                saleDate,

                            buyer_name:
                                document
                                    .getElementById(
                                        'buyerName'
                                    )
                                    .value
                                    .trim()
                                ||null,

                            buyer_phone:
                                document
                                    .getElementById(
                                        'buyerPhone'
                                    )
                                    .value
                                    .trim()
                                ||null
                        })
                }
            );


            AdminUI.closeModal(
                'sellModal'
            );


            Toast.success(
                'Land sold successfully.'
            );


            await Promise.all([
                loadLands(
                    currentPage
                ),

                loadStatistics()
            ]);

        }catch(error){
            AdminUI.showError(
                'sellError',
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


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

window.deleteLand=
function(id){
    const item=
        lands.find(
            land=>
                Number(land.id)===
                Number(id)
        );


    if(!item){
        Toast.error(
            'Land record not found.'
        );

        return;
    }


    AdminUI.deleteRequest(
        `/api/lands/${id}`,
        {
            message:
                `Delete "${item.land_code} - ${item.title}" permanently? This action cannot be undone.`,

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
                    loadLands(
                        currentPage
                    ),

                    loadStatistics()
                ]);
            }
        }
    );
};


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

window.clearFilters=
function(){
    el.search.value='';
    el.statusFilter.value='';

    loadLands(1);
};


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initLandPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initLandPage,
            50
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Global Flatpickr
    |--------------------------------------------------------------------------
    */

    if(
        typeof window.initDatePickers==='function'
    ){
        window.initDatePickers();
    }


    /*
    |--------------------------------------------------------------------------
    | Global Debounce
    |--------------------------------------------------------------------------
    */

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


    loadingState();


    await Promise.all([
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