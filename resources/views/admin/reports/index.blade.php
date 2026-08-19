@extends('layouts.admin')

@section('title','Reports')
@section('page_title','Reports')

@section('content')

<div class="space-y-5">

    {{-- =========================================================
    HEADER
    ========================================================== --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bar-chart"></i>
            </div>

            <div>
                <h1 class="text-xl font-bold text-slate-800">
                    Reports & Analytics
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Association-wide operational summary and module reports.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="refreshReports()"
            class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
        >
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>


    {{-- =========================================================
    REPORT PERIOD
    ========================================================== --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-calendar-range text-sm"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Report Period
                    </p>

                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Leave empty to show all-time data
                    </p>
                </div>
            </div>


            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-72">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>

                    <input
                        id="reportDateRange"
                        type="text"
                        class="js-date-range h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-xs text-slate-600 outline-none placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
                        placeholder="Select date range"
                        autocomplete="off"
                    >
                </div>

                <button
                    type="button"
                    onclick="clearReportFilters()"
                    class="inline-flex h-9 shrink-0 cursor-pointer items-center gap-1.5 rounded-r-md border border-slate-300 bg-slate-50 px-3 text-xs font-semibold text-slate-600 transition hover:bg-slate-100"
                >
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>

        </div>
    </div>


    {{-- =========================================================
    REPORT CONTENT
    ========================================================== --}}
    <div id="reportContainer">
        <div class="rounded-md border border-slate-200 bg-white p-10 text-center text-sm text-slate-400">
            Loading reports...
        </div>
    </div>

</div>


<script>
const reportElements={
    container:document.getElementById('reportContainer'),
    dateRange:document.getElementById('reportDateRange')
};

const currencySymbol=@json(setting('currency_symbol','৳'));


/*
|--------------------------------------------------------------------------
| Date Helpers
|--------------------------------------------------------------------------
*/

function formatRawDate(date){
    if(!date){
        return '';
    }

    const year=date.getFullYear();
    const month=String(
        date.getMonth()+1
    ).padStart(2,'0');

    const day=String(
        date.getDate()
    ).padStart(2,'0');

    return `${year}-${month}-${day}`;
}


function getReportDateRange(){
    const element=reportElements.dateRange;

    if(!element){
        return {
            from:'',
            to:''
        };
    }

    if(element._flatpickr){
        const dates=
            element._flatpickr.selectedDates??[];

        return {
            from:dates[0]
                ?formatRawDate(dates[0])
                :'',

            to:dates[1]
                ?formatRawDate(dates[1])
                :''
        };
    }

    const value=element.value.trim();

    if(!value){
        return {
            from:'',
            to:''
        };
    }

    const parts=value.split(' to ');

    return {
        from:parts[0]??'',
        to:parts[1]??''
    };
}


/*
|--------------------------------------------------------------------------
| Load Reports
|--------------------------------------------------------------------------
*/

async function loadReports(){
    reportElements.container.innerHTML=
        AdminUI.loadingState(
            'Loading reports...'
        );

    const range=getReportDateRange();

    const query=AdminUI.query({
        from:range.from,
        to:range.to
    });

    try{
        const response=await api(
            `/api/reports/summary?${query}`
        );

        renderReports(
            response.data??{}
        );

    }catch(error){
        reportElements.container.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-6 text-sm text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| Report Renderer
|--------------------------------------------------------------------------
*/

function renderReports(data){
    reportElements.container.innerHTML=`
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            ${reportCard(
                'Members',
                'bi-people',
                AdminUI.formatNumber(
                    data.members?.total??0
                ),
                [
                    [
                        'Active',
                        data.members?.active??0,
                        'emerald'
                    ],
                    [
                        'Pending',
                        data.members?.pending??0,
                        'amber'
                    ],
                    [
                        'Rejected',
                        data.members?.rejected??0,
                        'red'
                    ],
                    [
                        'Suspended',
                        data.members?.suspended??0,
                        'slate'
                    ]
                ]
            )}

            ${reportCard(
                'Investments',
                'bi-cash-stack',
                AdminUI.formatNumber(
                    data.investments?.total_records??0
                ),
                [
                    [
                        'Records',
                        data.investments?.total_records??0,
                        'indigo'
                    ],
                    [
                        'Total Amount',
                        formatMoney(
                            data.investments?.total_amount??0
                        ),
                        'emerald'
                    ],
                    [
                        'Active',
                        data.investments?.active??0,
                        'indigo'
                    ],
                    [
                        'Completed',
                        data.investments?.completed??0,
                        'emerald'
                    ]
                ]
            )}

            ${reportCard(
                'Land',
                'bi-map',
                AdminUI.formatNumber(
                    data.land?.total??0
                ),
                [
                    [
                        'Purchased',
                        data.land?.purchased??0,
                        'indigo'
                    ],
                    [
                        'Sold',
                        data.land?.sold??0,
                        'emerald'
                    ],
                    [
                        'Purchase Value',
                        formatMoney(
                            data.land?.purchase_value??0
                        ),
                        'slate'
                    ],
                    [
                        'Profit / Loss',
                        formatMoney(
                            data.land?.profit_loss??0
                        ),
                        Number(
                            data.land?.profit_loss??0
                        )>=0
                            ?'emerald'
                            :'red'
                    ]
                ]
            )}

            ${reportCard(
                'Projects',
                'bi-kanban',
                AdminUI.formatNumber(
                    data.projects?.total??0
                ),
                [
                    [
                        'Active',
                        data.projects?.active??0,
                        'indigo'
                    ],
                    [
                        'Completed',
                        data.projects?.completed??0,
                        'emerald'
                    ],
                    [
                        'On Hold',
                        data.projects?.on_hold??0,
                        'amber'
                    ],
                    [
                        'Budget',
                        formatMoney(
                            data.projects?.total_budget??0
                        ),
                        'slate'
                    ]
                ]
            )}

            ${reportCard(
                'Polls',
                'bi-ui-checks',
                AdminUI.formatNumber(
                    data.polls?.total??0
                ),
                [
                    [
                        'Active',
                        data.polls?.active??0,
                        'emerald'
                    ],
                    [
                        'Upcoming',
                        data.polls?.upcoming??0,
                        'indigo'
                    ],
                    [
                        'Ended',
                        data.polls?.ended??0,
                        'slate'
                    ],
                    [
                        'Votes',
                        data.polls?.total_votes??0,
                        'amber'
                    ]
                ]
            )}

            ${reportCard(
                'Approvals',
                'bi-check2-square',
                AdminUI.formatNumber(
                    data.approvals?.total??0
                ),
                [
                    [
                        'Pending',
                        data.approvals?.pending??0,
                        'amber'
                    ],
                    [
                        'Approved',
                        data.approvals?.approved??0,
                        'emerald'
                    ],
                    [
                        'Rejected',
                        data.approvals?.rejected??0,
                        'red'
                    ],
                    [
                        'Cancelled',
                        data.approvals?.cancelled??0,
                        'slate'
                    ]
                ]
            )}

            ${reportCard(
                'Notices',
                'bi-megaphone',
                AdminUI.formatNumber(
                    data.notices?.total??0
                ),
                [
                    [
                        'Published',
                        data.notices?.published??0,
                        'emerald'
                    ],
                    [
                        'Draft',
                        data.notices?.draft??0,
                        'slate'
                    ],
                    [
                        'Urgent',
                        data.notices?.urgent??0,
                        'red'
                    ],
                    [
                        'Expired',
                        data.notices?.expired??0,
                        'amber'
                    ]
                ]
            )}

            ${reportCard(
                'Finance',
                'bi-wallet2',
                formatMoney(
                    data.finance?.net??0
                ),
                [
                    [
                        'Income',
                        formatMoney(
                            data.finance?.income??0
                        ),
                        'emerald'
                    ],
                    [
                        'Expense',
                        formatMoney(
                            data.finance?.expense??0
                        ),
                        'red'
                    ],
                    [
                        'Net',
                        formatMoney(
                            data.finance?.net??0
                        ),
                        Number(
                            data.finance?.net??0
                        )>=0
                            ?'emerald'
                            :'red'
                    ],
                    [
                        'Transactions',
                        data.finance?.transactions??0,
                        'indigo'
                    ]
                ]
            )}

        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Report Card
|--------------------------------------------------------------------------
*/

function reportCard(
    title,
    icon,
    total,
    items
){
    return `
        <section class="rounded-md border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center justify-between gap-4">

                <div class="flex min-w-0 items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi ${icon}"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="truncate text-sm font-bold text-slate-800">
                            ${AdminUI.escapeHtml(title)}
                        </p>

                        <p class="mt-1 text-[11px] text-slate-400">
                            Summary
                        </p>
                    </div>

                </div>

                <div class="min-w-0 text-right">
                    <p class="text-[10px] uppercase tracking-wide text-slate-400">
                        Total
                    </p>

                    <p class="mt-1 truncate text-xl font-bold text-slate-800 sm:text-2xl">
                        ${total}
                    </p>
                </div>

            </div>


            <div class="mt-5 grid grid-cols-2 gap-2 sm:grid-cols-4 xl:grid-cols-2 2xl:grid-cols-4">
                ${items
                    .map(item=>metricBox(...item))
                    .join('')
                }
            </div>

        </section>
    `;
}


/*
|--------------------------------------------------------------------------
| Metric Box
|--------------------------------------------------------------------------
*/

function metricBox(
    label,
    value,
    color='slate'
){
    const map={
        emerald:
            'bg-emerald-50 text-emerald-700',

        amber:
            'bg-amber-50 text-amber-700',

        red:
            'bg-red-50 text-red-700',

        indigo:
            'bg-indigo-50 text-indigo-700',

        slate:
            'bg-slate-50 text-slate-600'
    };

    return `
        <div class="min-w-0 rounded-md ${map[color]??map.slate} p-3">
            <p class="truncate text-[10px] font-medium opacity-70">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 truncate text-sm font-bold" title="${AdminUI.escapeHtml(String(value))}">
                ${value}
            </p>
        </div>
    `;
}


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function formatMoney(value){
    const amount=Number(value??0);

    return `${currencySymbol}${amount.toLocaleString(
        'en-US',
        {
            minimumFractionDigits:2,
            maximumFractionDigits:2
        }
    )}`;
}


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

async function refreshReports(){
    await loadReports();

    if(typeof window.Toast!=='undefined'){
        Toast.success(
            'Reports refreshed successfully.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Clear Filters
|--------------------------------------------------------------------------
*/

function clearReportFilters(){
    const input=
        reportElements.dateRange;

    if(input?._flatpickr){
        input._flatpickr.clear();
    }else if(input){
        input.value='';
    }

    loadReports();
}


/*
|--------------------------------------------------------------------------
| Date Range Events
|--------------------------------------------------------------------------
*/

function registerDateRangeListener(){
    const input=
        reportElements.dateRange;

    if(!input){
        return;
    }

    if(input._flatpickr){
        input._flatpickr.config.onClose.push(
            selectedDates=>{
                if(
                    selectedDates.length===0||
                    selectedDates.length===2
                ){
                    loadReports();
                }
            }
        );

        return;
    }

    input.addEventListener(
        'change',
        loadReports
    );
}


/*
|--------------------------------------------------------------------------
| Initialize
|--------------------------------------------------------------------------
*/

async function initReportsPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initReportsPage,
            50
        );

        return;
    }

    if(
        typeof window.initDatePickers==='function'
    ){
        window.initDatePickers();
    }

    registerDateRangeListener();

    await loadReports();
}


if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initReportsPage
    );
}else{
    initReportsPage();
}
</script>

@endsection