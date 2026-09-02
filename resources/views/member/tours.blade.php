@extends('layouts.member')

@section('title','Tours')
@section('page_title','Tours')

@section('content')
<div class="space-y-5">

    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-airplane"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Association Tours</h1>
                <p class="text-xs text-slate-500">
                    View upcoming tours, schedules and participation status.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Available Tours</p>
            <p id="totalTours" class="mt-2 text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 p-4">
            <p class="text-sm text-indigo-600">Upcoming</p>
            <p id="upcomingTours" class="mt-2 text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-sm text-amber-600">Ongoing</p>
            <p id="ongoingTours" class="mt-2 text-xl font-bold text-amber-700">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 p-4">
            <p class="text-sm text-emerald-600">My Participations</p>
            <p id="myParticipations" class="mt-2 text-xl font-bold text-emerald-700">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Find Tours</p>
                    <p class="hidden text-[11px] text-slate-400 sm:block">
                        Search by title or destination.
                    </p>
                </div>
            </div>

            <div class="flex w-full items-center lg:w-auto">
                <div class="relative w-full lg:w-72">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search tour..."
                        class="h-9 w-full rounded-l-md border border-r-0 border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>

                <select
                    id="statusFilter"
                    class="h-9 border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none">
                    <option value="">All Status</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button
                    type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 shrink-0 items-center gap-1.5 rounded-r-md border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div id="tourGrid" class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-10 text-center text-base text-slate-400">
            Loading tours...
        </div>
    </div>

    <div id="paginationContainer"></div>
</div>

<div id="tourDetailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[94vh] w-full max-w-4xl flex-col overflow-hidden rounded-md bg-white">

        <div class="app-modal-header flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-3">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-airplane"></i>
                </div>

                <div class="min-w-0">
                    <h2 id="detailsTitle" class="truncate text-lg font-bold text-slate-800">
                        Tour Details
                    </h2>

                    <p id="detailsSubtitle" class="mt-0.5 text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button"
                onclick="closeTourDetails()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">

            <div id="tourDetailsLoading">
                <div class="py-16 text-center text-base text-slate-400">
                    Loading tour details...
                </div>
            </div>

            <div id="tourDetailsContent" class="hidden space-y-5">

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">Destination</p>
                        <p id="detailDestination" class="mt-1 truncate text-base font-bold text-slate-700"></p>
                    </div>

                    <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-indigo-400">Start Date</p>
                        <p id="detailStartDate" class="mt-1 text-base font-bold text-indigo-700"></p>
                    </div>

                    <div class="rounded-md border border-sky-200 bg-sky-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-sky-400">End Date</p>
                        <p id="detailEndDate" class="mt-1 text-base font-bold text-sky-700"></p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">Status</p>
                        <div id="detailStatus" class="mt-1"></div>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h3 class="text-base font-bold text-slate-700">Tour Information</h3>
                    </div>

                    <div class="space-y-4 p-4">
                        <div id="tourInformation"
                            class="grid grid-cols-2 gap-4 text-sm md:grid-cols-3"></div>

                        <div id="tourDescriptionBlock"
                            class="hidden border-t border-slate-100 pt-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Description
                            </p>

                            <p id="tourDescription"
                                class="mt-2 whitespace-pre-wrap text-base leading-6 text-slate-600"></p>
                        </div>

                        <div id="tourNotesBlock"
                            class="hidden border-t border-slate-100 pt-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                Notes
                            </p>

                            <p id="tourNotes"
                                class="mt-2 whitespace-pre-wrap text-base leading-6 text-slate-600"></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-md border border-indigo-200 bg-indigo-50/30">
                    <div class="border-b border-indigo-100 px-4 py-3">
                        <h3 class="text-base font-bold text-indigo-700">
                            My Participation
                        </h3>
                    </div>

                    <div id="myParticipationSection" class="p-4"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let tours=[];
let currentPage=1;

const el={
    grid:document.getElementById('tourGrid'),
    search:document.getElementById('searchInput'),
    status:document.getElementById('statusFilter')
};

function loadingState(){
    el.grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-10 text-center text-base text-slate-400">
            <div class="flex items-center justify-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading tours...
            </div>
        </div>
    `;
}

function emptyState(){
    el.grid.innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-10 text-center">
            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="bi bi-airplane"></i>
            </div>

            <p class="mt-3 text-base font-semibold text-slate-600">
                No tours found
            </p>

            <p class="mt-1 text-sm text-slate-400">
                No tour matches the selected filters.
            </p>
        </div>
    `;
}

function participationBadge(status){
    if(!status){
        return`
            <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">
                Not Registered
            </span>
        `;
    }

    return AdminUI.statusBadge(status);
}

async function loadSummary(){
    try{
        const response=await api('/api/member/tours/summary');
        const data=response.data??{};

        document.getElementById('totalTours').textContent=data.total??0;
        document.getElementById('upcomingTours').textContent=data.upcoming??0;
        document.getElementById('ongoingTours').textContent=data.ongoing??0;
        document.getElementById('myParticipations').textContent=
            data.my_participations??
            data.participating??
            data.registered??
            0;
    }catch(error){
        console.error(error);
    }
}

async function loadTours(page=1){
    currentPage=page;

    loadingState();

    const params=new URLSearchParams({
        page,
        per_page:12
    });

    const search=el.search.value.trim();
    const status=el.status.value;

    if(search){
        params.set('search',search);
    }

    if(status){
        params.set('status',status);
    }

    try{
        const response=await api(
            `/api/member/tours?${params.toString()}`
        );

        const paginator=response.data??{};

        tours=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        renderTours();

        const pagination=document.getElementById(
            'paginationContainer'
        );

        pagination.innerHTML='';

        AdminUI.renderPagination(
            paginator,
            pagination,
            loadTours
        );
    }catch(error){
        el.grid.innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
}

function renderTours(){
    if(!tours.length){
        emptyState();
        return;
    }

    el.grid.innerHTML=tours.map(item=>`
        <article class="flex min-w-0 flex-col overflow-hidden rounded-md border border-slate-200 bg-white transition hover:border-slate-300 hover:shadow-sm">

            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-[11px] font-bold text-indigo-600">
                            ${AdminUI.escapeHtml(item.tour_no??'')}
                        </p>

                        <h3 class="mt-1 truncate text-base font-bold text-slate-800">
                            ${AdminUI.escapeHtml(item.title??'Tour')}
                        </h3>

                        <p class="mt-1 truncate text-[11px] text-slate-400">
                            <i class="bi bi-geo-alt me-1"></i>
                            ${AdminUI.escapeHtml(item.destination??'—')}
                        </p>
                    </div>

                    <div class="shrink-0">
                        ${AdminUI.statusBadge(item.status)}
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="rounded-md bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-indigo-400">
                            Start Date
                        </p>

                        <p class="mt-1 text-sm font-bold text-indigo-700">
                            ${item.start_date
                                ?AdminUI.formatDate(item.start_date)
                                :'—'}
                        </p>
                    </div>

                    <div class="rounded-md bg-sky-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-sky-400">
                            End Date
                        </p>

                        <p class="mt-1 text-sm font-bold text-sky-700">
                            ${item.end_date
                                ?AdminUI.formatDate(item.end_date)
                                :'—'}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <div>
                        <p class="text-[10px] text-slate-400">
                            My Status
                        </p>

                        <div class="mt-1">
                            ${participationBadge(
                                item.my_participation?.status??
                                item.participation_status??
                                null
                            )}
                        </div>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] text-slate-400">
                            Participants
                        </p>

                        <p class="mt-1 text-base font-bold text-slate-700">
                            ${item.participants_count??0}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-auto flex items-center justify-between border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                <span class="text-[10px] text-slate-400">
                    Association Tour
                </span>

                <button
                    type="button"
                    onclick="viewTour(${item.id})"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md bg-indigo-50 px-3 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">

                    <i class="bi bi-eye"></i>
                    View Details
                </button>
            </div>
        </article>
    `).join('');
}

window.viewTour=async function(id){
    const tourId=Number(id);

    if(
        !Number.isInteger(tourId)||
        tourId<=0
    ){
        return;
    }

    const loading=document.getElementById(
        'tourDetailsLoading'
    );

    const content=document.getElementById(
        'tourDetailsContent'
    );

    loading.innerHTML=`
        <div class="py-16 text-center text-base text-slate-400">
            <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>

            <p class="mt-2">
                Loading tour details...
            </p>
        </div>
    `;

    loading.classList.remove('hidden');
    content.classList.add('hidden');

    AdminUI.openModal(
        'tourDetailsModal'
    );

    setTourQuery(tourId);

    try{
        const response=await api(
            `/api/member/tours/${tourId}`
        );

        renderTourDetails(
            response.data??{}
        );

        loading.classList.add('hidden');
        content.classList.remove('hidden');
    }catch(error){
        loading.innerHTML=`
            <div class="py-12 text-center text-base text-red-600">
                ${AdminUI.escapeHtml(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

function renderTourDetails(tour){
    document.getElementById(
        'detailsTitle'
    ).textContent=
        tour.title??'Tour Details';

    document.getElementById(
        'detailsSubtitle'
    ).textContent=
        [
            tour.tour_no,
            tour.destination
        ].filter(Boolean).join(' • ');

    document.getElementById(
        'detailDestination'
    ).textContent=
        tour.destination??'—';

    document.getElementById(
        'detailStartDate'
    ).textContent=
        tour.start_date
            ?AdminUI.formatDate(tour.start_date)
            :'—';

    document.getElementById(
        'detailEndDate'
    ).textContent=
        tour.end_date
            ?AdminUI.formatDate(tour.end_date)
            :'—';

    document.getElementById(
        'detailStatus'
    ).innerHTML=
        AdminUI.statusBadge(tour.status);

    document.getElementById(
        'tourInformation'
    ).innerHTML=[
        infoItem('Tour No',tour.tour_no),
        infoItem('Destination',tour.destination),
        infoItem(
            'Start Date',
            tour.start_date
                ?AdminUI.formatDate(tour.start_date)
                :null
        ),
        infoItem(
            'End Date',
            tour.end_date
                ?AdminUI.formatDate(tour.end_date)
                :null
        ),
        infoItem(
            'Status',
            formatLabel(tour.status)
        ),
        infoItem(
            'Approved By',
            tour.approver?.name??null
        )
    ].join('');

    const descriptionBlock=
        document.getElementById(
            'tourDescriptionBlock'
        );

    if(tour.description){
        descriptionBlock.classList.remove('hidden');

        document.getElementById(
            'tourDescription'
        ).textContent=
            tour.description;
    }else{
        descriptionBlock.classList.add('hidden');
    }

    const notesBlock=
        document.getElementById(
            'tourNotesBlock'
        );

    if(tour.notes){
        notesBlock.classList.remove('hidden');

        document.getElementById(
            'tourNotes'
        ).textContent=
            tour.notes;
    }else{
        notesBlock.classList.add('hidden');
    }

    renderMyParticipation(
        tour.my_participation??
        tour.participation??
        null
    );
}

function renderMyParticipation(participation){
    const container=document.getElementById(
        'myParticipationSection'
    );

    if(!participation){
        container.innerHTML=`
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-700">
                        Not registered for this tour
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        No participation record was found for this tour.
                    </p>
                </div>

                <span class="w-fit rounded-md bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500">
                    Not Registered
                </span>
            </div>
        `;

        return;
    }

    container.innerHTML=`
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-wide text-slate-400">
                    Participation Status
                </p>

                <div class="mt-1">
                    ${AdminUI.statusBadge(
                        participation.status
                    )}
                </div>
            </div>

            ${
                participation.notes
                    ?`
                    <div class="max-w-xl">
                        <p class="text-[10px] uppercase tracking-wide text-slate-400">
                            Notes
                        </p>

                        <p class="mt-1 text-sm leading-5 text-slate-600">
                            ${AdminUI.escapeHtml(
                                participation.notes
                            )}
                        </p>
                    </div>
                    `
                    :''
            }
        </div>
    `;
}

function infoItem(label,value){
    return`
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-wide text-slate-400">
                ${AdminUI.escapeHtml(label)}
            </p>

            <p class="mt-1 break-words font-semibold text-slate-700">
                ${AdminUI.escapeHtml(value??'—')}
            </p>
        </div>
    `;
}

function formatLabel(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function setTourQuery(id){
    const url=new URL(
        window.location.href
    );

    url.searchParams.set(
        'tour',
        id
    );

    window.history.replaceState(
        {},
        '',
        url.pathname+
        (
            url.searchParams.toString()
                ?'?'+url.searchParams.toString()
                :''
        )
    );
}

function removeTourQuery(){
    const url=new URL(
        window.location.href
    );

    url.searchParams.delete(
        'tour'
    );

    window.history.replaceState(
        {},
        '',
        url.pathname+
        (
            url.searchParams.toString()
                ?'?'+url.searchParams.toString()
                :''
        )
    );
}

window.closeTourDetails=function(){
    AdminUI.closeModal(
        'tourDetailsModal'
    );

    removeTourQuery();
};

window.clearFilters=function(){
    el.search.value='';
    el.status.value='';

    loadTours(1);
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

    el.search.addEventListener(
        'input',
        debounce(
            ()=>loadTours(1)
        )
    );

    el.status.addEventListener(
        'change',
        ()=>loadTours(1)
    );

    await Promise.all([
        loadSummary(),
        loadTours()
    ]);

    const params=
        new URLSearchParams(
            window.location.search
        );

    const tourId=Number(
        params.get('tour')
    );

    if(
        Number.isInteger(tourId)&&
        tourId>0
    ){
        await viewTour(
            tourId
        );
    }
}

window.addEventListener(
    'popstate',
    ()=>{
        const params=
            new URLSearchParams(
                window.location.search
            );

        const tourId=Number(
            params.get('tour')
        );

        if(
            Number.isInteger(tourId)&&
            tourId>0
        ){
            viewTour(
                tourId
            );
        }else{
            AdminUI.closeModal(
                'tourDetailsModal'
            );
        }
    }
);

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initTourPage
    );
}else{
    initTourPage();
}
</script>
@endpush