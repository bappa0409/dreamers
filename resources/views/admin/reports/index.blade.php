@extends('layouts.admin')

@section('title','Reports')
@section('page_title','Reports')

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-bar-chart"></i>
            </div>

            <div class="min-w-0">
                <h1 class="text-base font-bold text-slate-800">Reports & Analytics</h1>
                <p class="text-xs text-slate-500">Generate, filter, review and export association reports.</p>
            </div>
        </div>

        <button type="button" onclick="refreshReports()" class="inline-flex w-fit cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    {{-- Filters --}}
    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-file-earmark-bar-graph text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Report Generator</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">Select report type, status and reporting period</p>
                </div>
            </div>

            <div class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto xl:gap-0">
                <select id="moduleFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-600 outline-none focus:border-indigo-400 xl:w-40 xl:rounded-r-none">
                    <option value="members">Members</option>
                    <option value="finance">Finance</option>
                    <option value="investments">Investments</option>
                    <option value="land">Land</option>
                    <option value="projects">Projects</option>
                    <option value="polls">Polls</option>
                    <option value="notices">Notices</option>
                </select>

                <div class="relative min-w-0 sm:flex-1 xl:w-56">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input id="searchInput" type="text" placeholder="Search report..." class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-600 outline-none placeholder:text-slate-400 focus:border-indigo-400 xl:rounded-none xl:border-l-0">
                </div>

                <select id="statusFilter" class="h-9 cursor-pointer rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-600 outline-none focus:border-indigo-400 xl:w-36 xl:rounded-none xl:border-l-0">
                    <option value="">All Status</option>
                </select>

                <div class="relative min-w-0 xl:w-56">
                    <i class="bi bi-calendar3 pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input id="reportDateRange" type="text" class="js-date-range h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-600 outline-none placeholder:text-slate-400 focus:border-indigo-400 xl:rounded-none xl:border-l-0" placeholder="All time" autocomplete="off">
                </div>

                <button type="button" onclick="clearReportFilters()" class="h-9 cursor-pointer whitespace-nowrap rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
                    <i class="bi bi-x-lg mr-1"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    {{-- Summary --}}
    <div id="summaryContainer">
        <div class="rounded-md border border-slate-200 bg-white p-8 text-center text-base text-slate-400">
            Loading summary...
        </div>
    </div>

    {{-- Detailed Report --}}
    <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <h2 id="reportTitle" class="truncate text-base font-bold text-slate-800">Member Report</h2>
                <p id="reportSubtitle" class="mt-0.5 truncate text-[11px] text-slate-400">0 records</p>
            </div>

            {{-- <div class="flex flex-wrap items-center gap-1.5">
                <button type="button" onclick="exportReport('pdf')" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-red-50 px-3 text-[11px] font-semibold text-red-700 transition hover:bg-red-100">
                    <i class="bi bi-file-earmark-pdf"></i>
                    PDF
                </button>

                <button type="button" onclick="exportReport('xlsx')" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-emerald-50 px-3 text-[11px] font-semibold text-emerald-700 transition hover:bg-emerald-100">
                    <i class="bi bi-file-earmark-excel"></i>
                    Excel
                </button>

                <button type="button" onclick="exportReport('csv')" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-sky-50 px-3 text-[11px] font-semibold text-sky-700 transition hover:bg-sky-100">
                    <i class="bi bi-filetype-csv"></i>
                    CSV
                </button>

                <button type="button" onclick="printReport()" class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md bg-slate-100 px-3 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-200">
                    <i class="bi bi-printer"></i>
                    Print
                </button>
            </div> --}}
            <div class="flex flex-wrap items-center gap-2">
    <button
        type="button"
        onclick="exportReport('pdf')"
        class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
    >
        <i class="bi bi-file-earmark-pdf"></i>
        Download PDF
    </button>

    <button
        type="button"
        onclick="exportReport('xlsx')"
        class="inline-flex h-8 cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 bg-white px-3 text-[11px] font-semibold text-slate-700 transition hover:bg-slate-50"
    >
        <i class="bi bi-file-earmark-excel"></i>
        Download Excel
    </button>
</div>
        </div>

        <div class="w-full overflow-hidden">
            <table class="w-full table-fixed text-sm">
                <thead id="reportHead" class="border-b border-slate-200 bg-slate-50"></thead>

                <tbody id="reportBody">
                    <tr>
                        <td class="px-5 py-10 text-center text-slate-400">
                            Loading report...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="paginationContainer" class="border-t border-slate-200 px-4 py-3"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let reportRows=[];
let reportHeadings=[];
let currentPage=1;
let lastPage=1;
let total=0;
let branding={
    name:@json(config('app.name')),
    logo_url:null
};

const currency=@json(setting('currency_symbol','৳'));

const el={
    module:document.getElementById('moduleFilter'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter'),
    range:document.getElementById('reportDateRange'),
    summary:document.getElementById('summaryContainer'),
    head:document.getElementById('reportHead'),
    body:document.getElementById('reportBody'),
    title:document.getElementById('reportTitle'),
    subtitle:document.getElementById('reportSubtitle')
};

const statusOptions={
    members:[
        ['','All Status'],
        ['active','Active'],
        ['pending','Pending'],
        ['inactive','Inactive'],
        ['suspended','Suspended'],
        ['rejected','Rejected']
    ],
    finance:[
        ['','All Status'],
        ['draft','Draft'],
        ['posted','Posted'],
        ['cancelled','Cancelled']
    ],
    investments:[
        ['','All Status'],
        ['pending','Pending'],
        ['active','Active'],
        ['completed','Completed'],
        ['cancelled','Cancelled']
    ],
    land:[
        ['','All Status'],
        ['planned','Planned'],
        ['negotiating','Negotiating'],
        ['purchased','Purchased'],
        ['sold','Sold'],
        ['cancelled','Cancelled']
    ],
    projects:[
        ['','All Status'],
        ['planned','Planned'],
        ['active','Active'],
        ['on_hold','On Hold'],
        ['completed','Completed'],
        ['cancelled','Cancelled']
    ],
    polls:[
        ['','All State'],
        ['active','Active'],
        ['upcoming','Upcoming'],
        ['ended','Ended'],
        ['inactive','Inactive']
    ],
    notices:[
        ['','All Status'],
        ['published','Published'],
        ['draft','Draft'],
        ['urgent','Urgent'],
        ['high','High Priority']
    ]
};

function money(value){
    return currency+Number(value??0).toLocaleString('en-US',{
        minimumFractionDigits:2,
        maximumFractionDigits:2
    });
}

function rawDate(date){
    const year=date.getFullYear();
    const month=String(date.getMonth()+1).padStart(2,'0');
    const day=String(date.getDate()).padStart(2,'0');

    return`${year}-${month}-${day}`;
}

function selectedRange(){
    const dates=el.range?._flatpickr?.selectedDates??[];

    return{
        from:dates[0]?rawDate(dates[0]):'',
        to:dates[1]?rawDate(dates[1]):''
    };
}

function reportQuery(extra={}){
    const range=selectedRange();

    return AdminUI.query({
        search:el.search.value.trim(),
        status:el.status.value,
        from:range.from,
        to:range.to,
        ...extra
    });
}

function updateStatusOptions(){
    const options=statusOptions[el.module.value]??[['','All Status']];

    el.status.innerHTML=options.map(option=>`
        <option value="${option[0]}">
            ${option[1]}
        </option>
    `).join('');
}

async function loadBranding(){
    try{
        const response=await api('/api/reports/branding');

        branding={
            ...branding,
            ...(response.data??{})
        };
    }catch(error){
        console.error('Branding load failed:',error);
    }
}

async function loadSummary(){
    el.summary.innerHTML=AdminUI.loadingState('Loading summary...');

    const range=selectedRange();

    try{
        const response=await api(`/api/reports/summary?${AdminUI.query({
            from:range.from,
            to:range.to
        })}`);

        const data=response.data??{};

        el.summary.innerHTML=`
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4 xl:grid-cols-7">
                ${summaryCard(
                    'Members',
                    data.members?.total??0,
                    'bi-people'
                )}

                ${summaryCard(
                    'Investments',
                    data.investments?.total??0,
                    'bi-graph-up-arrow'
                )}

                ${summaryCard(
                    'Land',
                    data.land?.total??0,
                    'bi-map'
                )}

                ${summaryCard(
                    'Projects',
                    data.projects?.total??0,
                    'bi-kanban'
                )}

                ${summaryCard(
                    'Polls',
                    data.polls?.total??0,
                    'bi-ui-checks'
                )}

                ${summaryCard(
                    'Notices',
                    data.notices?.total??0,
                    'bi-megaphone'
                )}

                ${summaryCard(
                    'Net Finance',
                    money(data.finance?.net??0),
                    'bi-wallet2'
                )}
            </div>
        `;
    }catch(error){
        el.summary.innerHTML=`
            <div class="rounded-md border border-red-200 bg-red-50 p-5 text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
}

function summaryCard(label,value,icon){
    return`
        <div class="min-w-0 rounded-md border border-slate-200 bg-white p-3">
            <div class="flex items-center gap-2">
                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi ${icon} text-sm"></i>
                </div>

                <p class="truncate text-[10px] font-medium text-slate-400">
                    ${AdminUI.escapeHtml(label)}
                </p>
            </div>

            <p class="mt-2 truncate text-base font-bold text-slate-800" title="${AdminUI.escapeHtml(String(value))}">
                ${value}
            </p>
        </div>
    `;
}

async function loadReport(page=1){
    currentPage=page;

    el.body.innerHTML=
        AdminUI.loadingState(
            'Loading report...',
            reportHeadings.length||1
        );

    try{
        const response=await api(
            `/api/reports/${el.module.value}?${reportQuery({
                page,
                per_page:15
            })}`
        );

        const paginator=response.data??{};

        reportRows=
            Array.isArray(paginator.data)
                ?paginator.data
                :[];

        reportHeadings=
            response.meta?.headings??[];

        currentPage=
            Number(paginator.current_page??1);

        lastPage=
            Number(paginator.last_page??1);

        total=
            Number(paginator.total??0);

        el.title.innerText=
            response.meta?.title??'Report';

        el.subtitle.innerText=
            `${total} record${total===1?'':'s'}`;

        renderReport();

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage,
            lastPage,
            total,
            onPageChange:loadReport
        });
    }catch(error){
        el.head.innerHTML='';

        el.body.innerHTML=
            AdminUI.emptyState(
                AdminUI.extractError(error),
                reportHeadings.length||1
            );

        AdminUI.renderPagination({
            container:'paginationContainer',
            currentPage:1,
            lastPage:1,
            total:0,
            onPageChange:loadReport
        });
    }
}

function renderReport(){
    el.head.innerHTML=`
        <tr>
            ${reportHeadings.map(heading=>`
                <th class="px-3 py-3 text-left text-[10px] font-semibold text-slate-600">
                    ${AdminUI.escapeHtml(heading)}
                </th>
            `).join('')}
        </tr>
    `;

    if(!reportRows.length){
        el.body.innerHTML=
            AdminUI.emptyState(
                'No report data found.',
                reportHeadings.length||1
            );

        return;
    }

    const rows=
        reportRows.map(
            item=>reportRow(
                el.module.value,
                item
            )
        );

    el.body.innerHTML=rows.map(row=>`
        <tr class="border-b border-slate-100 last:border-0 hover:bg-slate-50">
            ${row.map(value=>`
                <td class="min-w-0 overflow-hidden px-3 py-3">
                    <p class="truncate text-[11px] text-slate-600" title="${AdminUI.escapeHtml(String(value??''))}">
                        ${AdminUI.escapeHtml(
                            String(value??'—')
                        )}
                    </p>
                </td>
            `).join('')}
        </tr>
    `).join('');
}

function reportRow(module,item){
    if(module==='members'){
        return[
            item.member_code,
            item.user?.name,
            item.user?.email,
            item.phone||item.user?.mobile,
            AdminUI.formatDate(item.joining_date),
            AdminUI.titleCase(item.status),
            (item.user?.roles??[])
                .map(role=>role.display_name||role.name)
                .join(', ')
        ];
    }

    if(module==='finance'){
        const entries=item.entries??[];

        return[
            item.transaction_no,
            AdminUI.formatDate(item.transaction_date),
            AdminUI.titleCase(item.type),
            item.description,
            money(
                entries.reduce(
                    (sum,row)=>
                        sum+Number(row.debit??0),
                    0
                )
            ),
            money(
                entries.reduce(
                    (sum,row)=>
                        sum+Number(row.credit??0),
                    0
                )
            ),
            AdminUI.titleCase(item.status),
            item.creator?.name
        ];
    }

    if(module==='investments'){
        const paid=item.paid_return_sum!==undefined
            ?Number(item.paid_return_sum??0)
            :(item.returns??[])
                .filter(row=>row.status==='paid')
                .reduce(
                    (sum,row)=>
                        sum+Number(row.amount??0),
                    0
                );

        return[
            item.investment_no,
            item.title,
            item.member?.user?.name,
            money(item.amount),
            money(item.expected_return),
            money(paid),
            AdminUI.formatDate(item.investment_date),
            AdminUI.formatDate(item.maturity_date),
            AdminUI.titleCase(item.status)
        ];
    }

    if(module==='land'){
        return[
            item.land_code,
            item.title,
            [
                item.mouza,
                item.upazila,
                item.district
            ]
                .filter(Boolean)
                .join(', '),

            `${item.land_area??''} ${item.area_unit??''}`.trim(),

            money(item.purchase_price),

            money(
                item.status==='sold'
                    ?item.sale_price
                    :item.current_value
            ),

            AdminUI.formatDate(item.purchase_date),
            AdminUI.formatDate(item.sale_date),
            AdminUI.titleCase(item.status),
            money(item.profit_loss)
        ];
    }

    if(module==='projects'){
        return[
            item.project_code,
            item.name,
            item.location,
            money(item.budget),
            money(item.actual_cost),
            `${item.progress??0}%`,
            AdminUI.titleCase(item.status),
            AdminUI.formatDate(item.start_date),
            AdminUI.formatDate(item.expected_end_date),
            item.members_count??0
        ];
    }

    if(module==='polls'){
        return[
            item.title,
            AdminUI.formatDate(
                item.start_at,
                true
            ),
            AdminUI.formatDate(
                item.end_at,
                true
            ),
            item.is_active?'Yes':'No',
            item.votes_count??0
        ];
    }

    return[
        item.title,
        AdminUI.titleCase(item.type),
        AdminUI.titleCase(item.priority),
        item.is_published?'Yes':'No',
        AdminUI.formatDate(
            item.publish_at,
            true
        ),
        AdminUI.formatDate(
            item.expires_at,
            true
        ),
        item.creator?.name
    ];
}

window.exportReport=function(format){
    if(!['pdf','xlsx'].includes(format)){
        return;
    }

    const url=
        `/api/reports/${el.module.value}/export/${format}?${reportQuery()}`;

    window.location.href=url;
};

window.printReport=function(){
    if(!reportRows.length){
        Toast.warning(
            'No report data available to print.'
        );

        return;
    }

    const range=selectedRange();

    const rows=
        reportRows.map(
            item=>reportRow(
                el.module.value,
                item
            )
        );

    const win=window.open(
        '',
        '_blank',
        'width=1200,height=850'
    );

    if(!win){
        Toast.error(
            'Please allow popups to print the report.'
        );

        return;
    }

    win.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">

            <title>
                ${AdminUI.escapeHtml(
                    el.title.innerText
                )}
            </title>

            <style>
                *{box-sizing:border-box}
                body{
                    font-family:Arial,"Noto Sans Bengali",sans-serif;
                    color:#334155;
                    padding:25px;
                }
                .header{
                    text-align:center;
                    border-bottom:1px solid #cbd5e1;
                    padding-bottom:12px;
                    margin-bottom:15px;
                }
                .logo{
                    height:55px;
                    max-width:130px;
                    margin-bottom:5px;
                }
                h1{
                    margin:0;
                    font-size:20px;
                    color:#0f172a;
                }
                h2{
                    margin:5px 0 0;
                    font-size:14px;
                }
                .period{
                    margin-top:5px;
                    font-size:11px;
                    color:#64748b;
                }
                table{
                    width:100%;
                    border-collapse:collapse;
                    font-size:10px;
                }
                th,td{
                    border:1px solid #dbe1ea;
                    padding:6px;
                    text-align:left;
                    vertical-align:top;
                }
                th{
                    background:#eef2ff;
                    color:#3730a3;
                }
            </style>
        </head>

        <body>

            <div class="header">

                ${
                    branding.logo_url
                        ?`
                            <img
                                src="${branding.logo_url}"
                                class="logo"
                            >
                        `
                        :''
                }

                <h1>
                    ${AdminUI.escapeHtml(
                        branding.name??''
                    )}
                </h1>

                <h2>
                    ${AdminUI.escapeHtml(
                        el.title.innerText
                    )}
                </h2>

                <div class="period">

                    ${
                        range.from||range.to
                            ?`
                                Report Period:
                                ${range.from||'Beginning'}
                                -
                                ${range.to||'Present'}
                            `
                            :'All-time Report'
                    }

                </div>

            </div>

            <table>

                <thead>
                    <tr>
                        ${
                            reportHeadings
                                .map(heading=>`
                                    <th>
                                        ${AdminUI.escapeHtml(
                                            heading
                                        )}
                                    </th>
                                `)
                                .join('')
                        }
                    </tr>
                </thead>

                <tbody>

                    ${
                        rows.map(row=>`
                            <tr>
                                ${
                                    row.map(value=>`
                                        <td>
                                            ${AdminUI.escapeHtml(
                                                String(value??'')
                                            )}
                                        </td>
                                    `).join('')
                                }
                            </tr>
                        `).join('')
                    }

                </tbody>

            </table>

        </body>
        </html>
    `);

    win.document.close();

    setTimeout(()=>{
        win.focus();
        win.print();
    },300);
};

window.clearReportFilters=function(){
    el.search.value='';
    el.status.value='';

    if(el.range?._flatpickr){
        el.range._flatpickr.clear();
    }else{
        el.range.value='';
    }

    loadAll();
};

window.refreshReports=async function(){
    await loadAll();

    Toast.success(
        'Reports refreshed successfully.'
    );
};

async function loadAll(){
    await Promise.all([
        loadSummary(),
        loadReport(1)
    ]);
}

function registerDateRange(){
    if(!el.range?._flatpickr){
        return;
    }

    el.range._flatpickr
        .config
        .onClose
        .push(dates=>{
            if(
                dates.length===0||
                dates.length===2
            ){
                loadAll();
            }
        });
}

async function initReportPage(){
    if(
        typeof window.AdminUI==='undefined'||
        typeof window.api==='undefined'
    ){
        setTimeout(
            initReportPage,
            50
        );

        return;
    }

    if(
        typeof window.initDatePickers==='function'
    ){
        window.initDatePickers();
    }

    updateStatusOptions();
    registerDateRange();

    el.module.addEventListener(
        'change',
        ()=>{
            el.search.value='';
            updateStatusOptions();
            loadReport(1);
        }
    );

    el.search.addEventListener(
        'input',
        AdminUI.debounce(
            ()=>loadReport(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadReport(1)
    );

    await Promise.all([
        loadBranding(),
        loadSummary(),
        loadReport()
    ]);
}

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initReportPage
    );
}else{
    initReportPage();
}
</script>
@endpush