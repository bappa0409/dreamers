@extends('layouts.member')

@section('title','Meetings')
@section('page_title','Meetings')

@section('content')
<div class="space-y-3">

    <div class="flex flex-col gap-4 rounded-md border border-slate-200 bg-white px-5 py-4 md:flex-row md:items-center md:justify-between">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                <i class="bi bi-people"></i>
            </div>

            <div>
                <h1 class="text-base font-bold text-slate-800">Association Meetings</h1>

                <p class=" text-xs 2xl:text-sm text-slate-500">
                    View meeting schedules, agendas, attendance and decisions.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
            <p class=" text-xs 2xl:text-sm text-slate-500">Total Meetings</p>
            <p id="totalMeetings" class="text-xl font-bold text-slate-800">0</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50/40 px-5 py-2">
            <p class="text-xs 2xl:text-sm text-indigo-600">Scheduled</p>
            <p id="scheduledMeetings" class="text-xl font-bold text-indigo-700">0</p>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4">
            <p class="text-xs 2xl:text-sm text-amber-600">Ongoing</p>
            <p id="ongoingMeetings" class="text-xl font-bold text-amber-700">0</p>
        </div>

        <div class="rounded-md border border-emerald-200 bg-emerald-50/40 px-5 py-2">
            <p class="text-xs 2xl:text-sm text-emerald-600">My Invitations</p>
            <p id="myInvitations" class="text-xl font-bold text-emerald-700">0</p>
        </div>
    </div>

    <div class="rounded-md border border-slate-200 bg-white p-3">
        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-search text-base"></i>
                </div>

                <div>
                    <p class="text-sm font-semibold text-slate-700">Find Meetings</p>
                    <p class="hidden text-[11px] text-slate-500 sm:block">
                        Search by meeting title, number or venue.
                    </p>
                </div>
            </div>

            <div class="grid w-full grid-cols-1 gap-2 sm:grid-cols-2 xl:w-auto xl:grid-cols-[280px_140px_140px_auto] xl:gap-0">
                <div class="relative sm:col-span-2 xl:col-span-1">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-500"></i>

                    <input
                        id="searchInput"
                        type="text"
                        placeholder="Search meeting..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 xl:rounded-r-none">
                </div>

                <select id="typeFilter"
                    class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none xl:rounded-none xl:border-l-0">
                    <option value="">All Types</option>
                    <option value="general">General</option>
                    <option value="annual">Annual</option>
                    <option value="executive">Executive</option>
                    <option value="emergency">Emergency</option>
                    <option value="special">Special</option>
                </select>

                <select id="statusFilter"
                    class="h-9 rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-600 outline-none xl:rounded-none xl:border-l-0">
                    <option value="">All Status</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="ongoing">Ongoing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <button type="button"
                    onclick="clearFilters()"
                    class="inline-flex h-9 items-center justify-center gap-1.5 rounded-md border border-slate-300 bg-slate-50 px-3 text-sm font-semibold text-slate-600 hover:bg-slate-100 xl:rounded-l-none xl:border-l-0">
                    <i class="bi bi-x-lg text-[10px]"></i>
                    Clear
                </button>
            </div>
        </div>
    </div>

    <div class="hidden overflow-hidden rounded-md border border-slate-200 bg-white lg:block">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Meeting</th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Date & Time</th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Venue</th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">My Status</th>
                        <th class="px-4 py-3 text-left text-xs 2xl:text-sm font-semibold text-slate-600">Meeting Status</th>
                        <th class="px-4 py-3 text-right text-xs 2xl:text-sm font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody id="meetingTableBody" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-base text-slate-500">
                            Loading meetings...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="meetingMobileGrid" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:hidden">
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs 2xl:text-sm text-slate-500">
            Loading meetings...
        </div>
    </div>

    <div id="paginationWrap"></div>
</div>

<div id="meetingDetailsModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[95vh] w-full max-w-5xl flex-col overflow-hidden rounded-md bg-white">

        <div class="flex shrink-0 items-start justify-between border-b border-slate-200 px-5 py-4">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                    <i class="bi bi-people"></i>
                </div>

                <div class="min-w-0">
                    <h3 id="detailsTitle" class="truncate text-lg font-bold text-slate-800">
                        Meeting Details
                    </h3>

                    <p id="detailsSubtitle" class=" text-xs 2xl:text-sm text-slate-500"></p>
                </div>
            </div>

            <button type="button"
                onclick="closeMeetingDetails()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto">
            <div id="detailsLoading" class="p-5">
                <div class="py-16 text-center text-base text-slate-500">
                    Loading meeting details...
                </div>
            </div>

            <div id="detailsContent" class="hidden space-y-5 p-4 sm:p-5">

                <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-md border border-indigo-200 bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-indigo-500">Date</p>
                        <p id="detailDate" class="mt-1 text-base font-bold text-indigo-700"></p>
                    </div>

                    <div class="rounded-md border border-sky-200 bg-sky-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-sky-500">Time</p>
                        <p id="detailTime" class="mt-1 text-base font-bold text-sky-700"></p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-500">Type</p>
                        <p id="detailType" class="mt-1 text-base font-bold text-slate-700"></p>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-slate-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-slate-500">Status</p>
                        <div id="detailStatus" class="mt-1"></div>
                    </div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="rounded-md border border-slate-200 bg-white">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <h4 class="text-base font-bold text-slate-700">
                                Meeting Information
                            </h4>
                        </div>

                        <div id="meetingInformation"
                            class="grid grid-cols-2 gap-4 p-4 text-sm"></div>

                        <div id="descriptionBlock"
                            class="hidden border-t border-slate-100 p-4">
                            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                Description
                            </p>

                            <p id="meetingDescription"
                                class="mt-2 whitespace-pre-wrap text-base leading-6 text-slate-600"></p>
                        </div>
                    </div>

                    <div class="rounded-md border border-indigo-200 bg-indigo-50/20">
                        <div class="border-b border-indigo-100 px-4 py-3">
                            <h4 class="text-base font-bold text-indigo-700">
                                My Attendance
                            </h4>
                        </div>

                        <div id="myAttendance" class="p-4"></div>
                    </div>
                </div>

                <section>
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-list-check"></i>
                        </div>

                        <h4 class="text-base font-bold text-slate-700">
                            Meeting Agenda
                        </h4>
                    </div>

                    <div id="agendaList"></div>
                </section>

                <section>
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-emerald-50 text-emerald-600">
                            <i class="bi bi-check2-square"></i>
                        </div>

                        <h4 class="text-base font-bold text-slate-700">
                            Decisions
                        </h4>
                    </div>

                    <div id="decisionList"></div>
                </section>

                <section>
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-md bg-sky-50 text-sky-600">
                            <i class="bi bi-journal-text"></i>
                        </div>

                        <h4 class="text-base font-bold text-slate-700">
                            Meeting Minutes
                        </h4>
                    </div>

                    <div class="rounded-md border border-slate-200 bg-white px-5 py-2">
                        <div id="minutesContent"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let meetings=[];
let currentPage=1;

const $=id=>document.getElementById(id);
const esc=value=>AdminUI.escapeHtml(value??'');

function formatLabel(value){
    return String(value??'')
        .replaceAll('_',' ')
        .replace(/\b\w/g,char=>char.toUpperCase());
}

function formatTime(value){
    if(!value){
        return '—';
    }

    const time=String(value).substring(0,5);
    const [hourRaw,minute='00']=time.split(':');

    let hour=Number(hourRaw);

    if(!Number.isFinite(hour)){
        return time;
    }

    const suffix=hour>=12?'PM':'AM';

    hour=hour%12||12;

    return `${hour}:${minute} ${suffix}`;
}

function meetingTime(meeting){
    if(meeting.start_time&&meeting.end_time){
        return `${formatTime(meeting.start_time)} - ${formatTime(meeting.end_time)}`;
    }

    return formatTime(
        meeting.start_time||
        meeting.end_time
    );
}

function myAttendanceStatus(meeting){
    return(
        meeting.my_attendance?.status||
        meeting.attendance_status||
        null
    );
}

function attendanceBadge(status){
    if(!status){
        return`
            <span class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-500">
                Not Invited
            </span>
        `;
    }

    return AdminUI.statusBadge(status);
}

function loadingState(){
    $('meetingTableBody').innerHTML=
        AdminUI.loadingState(
            'Loading meetings...',
            7
        );

    $('meetingMobileGrid').innerHTML=`
        <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center text-xs 2xl:text-sm text-slate-500">
            <div class="flex items-center justify-center gap-2">
                <span class="h-4 w-4 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
                Loading meetings...
            </div>
        </div>
    `;
}

async function loadSummary(){
    try{
        const response=await api(
            '/api/member/meetings/summary'
        );

        const data=response.data??{};

        $('totalMeetings').textContent=data.total??0;
        $('scheduledMeetings').textContent=data.scheduled??0;
        $('ongoingMeetings').textContent=data.ongoing??0;
        $('myInvitations').textContent=
            data.my_invitations??
            data.invited??
            data.my_meetings??
            0;
    }catch(error){
        console.error(error);
    }
}

async function loadMeetings(page=1){
    currentPage=page;

    loadingState();

    const params=new URLSearchParams({
        page,
        per_page:15
    });

    const search=$('searchInput').value.trim();
    const type=$('typeFilter').value;
    const status=$('statusFilter').value;

    if(search){
        params.set('search',search);
    }

    if(type){
        params.set('type',type);
    }

    if(status){
        params.set('status',status);
    }

    try{
        const response=await api(
            `/api/member/meetings?${params.toString()}`
        );

        const paginator=response.data??{};

        meetings=Array.isArray(paginator.data)
            ?paginator.data
            :[];

        renderMeetings();

        $('paginationWrap').innerHTML='';

        AdminUI.renderPagination(
            paginator,
            $('paginationWrap'),
            loadMeetings
        );
    }catch(error){
        const message=AdminUI.extractError(error);

        $('meetingTableBody').innerHTML=
            AdminUI.emptyState(
                message,
                7
            );

        $('meetingMobileGrid').innerHTML=`
            <div class="col-span-full rounded-md border border-red-200 bg-red-50 p-8 text-center text-base text-red-600">
                ${esc(message)}
            </div>
        `;
    }
}

function renderMeetings(){
    const tbody=$('meetingTableBody');
    const grid=$('meetingMobileGrid');

    if(!meetings.length){
        tbody.innerHTML=
            AdminUI.emptyState(
                'No meetings found.',
                7
            );

        grid.innerHTML=`
            <div class="col-span-full rounded-md border border-slate-200 bg-white p-8 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <i class="bi bi-calendar-x"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-slate-600">
                    No meetings found
                </p>
            </div>
        `;

        return;
    }

    tbody.innerHTML=meetings.map(meeting=>`
        <tr class="transition hover:bg-slate-50/70">
            <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                        <i class="bi bi-people"></i>
                    </div>

                    <div class="min-w-0">
                        <p class="font-semibold text-xs 2xl:text-sm text-slate-700">
                            ${esc(meeting.meeting_no)}
                        </p>

                        <p class="mt-0.5 max-w-[220px] truncate text-sm text-slate-500">
                            ${esc(meeting.title)}
                        </p>
                    </div>
                </div>
            </td>

            <td class="px-4 py-3 text-base text-slate-500">
                ${esc(formatLabel(meeting.type))}
            </td>

            <td class="px-4 py-3">
                <p class="text-xs text-slate-600">
                    ${meeting.meeting_date
                        ?AdminUI.formatDate(meeting.meeting_date)
                        :'—'}
                </p>

                <p class="mt-0.5 text-[10px] text-slate-500">
                    ${esc(meetingTime(meeting))}
                </p>
            </td>

            <td class="px-4 py-3 text-base text-slate-500">
                ${esc(meeting.venue||'—')}
            </td>

            <td class="px-4 py-3">
                ${attendanceBadge(
                    myAttendanceStatus(meeting)
                )}
            </td>

            <td class="px-4 py-3">
                ${AdminUI.statusBadge(meeting.status)}
            </td>

            <td class="px-4 py-3 text-right">
                <button
                    type="button"
                    onclick="viewMeeting(${meeting.id})"
                    class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">

                    <i class="bi bi-eye"></i>
                    View
                </button>
            </td>
        </tr>
    `).join('');

    grid.innerHTML=meetings.map(meeting=>`
        <article class="overflow-hidden rounded-md border border-slate-200 bg-white">

            <div class="border-b border-slate-100 px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex min-w-0 gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                            <i class="bi bi-people"></i>
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-base font-bold text-slate-700">
                                ${esc(meeting.title)}
                            </p>

                            <p class="mt-0.5 text-[11px] font-medium text-indigo-600">
                                ${esc(meeting.meeting_no)}
                            </p>
                        </div>
                    </div>

                    ${AdminUI.statusBadge(meeting.status)}
                </div>
            </div>

            <div class="space-y-4 p-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-md bg-indigo-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-indigo-500">
                            Date
                        </p>

                        <p class="mt-1 text-sm font-bold text-indigo-700">
                            ${meeting.meeting_date
                                ?AdminUI.formatDate(meeting.meeting_date)
                                :'—'}
                        </p>
                    </div>

                    <div class="rounded-md bg-sky-50 p-3">
                        <p class="text-[10px] uppercase tracking-wide text-sky-500">
                            Time
                        </p>

                        <p class="mt-1 truncate text-sm font-bold text-sky-700">
                            ${esc(meetingTime(meeting))}
                        </p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] text-slate-500">
                        Venue
                    </p>

                    <p class="mt-1 truncate text-sm font-semibold text-slate-600">
                        ${esc(meeting.venue||'—')}
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                    <div>
                        <p class="text-[10px] text-slate-500">Type</p>
                        <p class="mt-1 text-sm font-semibold text-slate-600">
                            ${esc(formatLabel(meeting.type))}
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-[10px] text-slate-500">My Status</p>
                        <div class="mt-1">
                            ${attendanceBadge(
                                myAttendanceStatus(meeting)
                            )}
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                <button
                    type="button"
                    onclick="viewMeeting(${meeting.id})"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md bg-indigo-50 px-3 py-2 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">

                    <i class="bi bi-eye"></i>
                    View Meeting Details
                </button>
            </div>
        </article>
    `).join('');
}

window.viewMeeting=async function(id){
    const meetingId=Number(id);

    if(
        !Number.isInteger(meetingId)||
        meetingId<=0
    ){
        return;
    }

    $('detailsLoading').innerHTML=`
        <div class="py-16 text-center text-base text-slate-500">
            <span class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-slate-300 border-t-indigo-600"></span>
            <p class="mt-2">Loading meeting details...</p>
        </div>
    `;

    $('detailsLoading').classList.remove('hidden');
    $('detailsContent').classList.add('hidden');

    AdminUI.openModal(
        'meetingDetailsModal'
    );

    setMeetingQuery(
        meetingId
    );

    try{
        const response=await api(
            `/api/member/meetings/${meetingId}`
        );

        renderMeetingDetails(
            response.data??{}
        );

        $('detailsLoading').classList.add('hidden');
        $('detailsContent').classList.remove('hidden');
    }catch(error){
        $('detailsLoading').innerHTML=`
            <div class="py-12 text-center text-base text-red-600">
                ${esc(
                    AdminUI.extractError(error)
                )}
            </div>
        `;
    }
};

function renderMeetingDetails(meeting){
    $('detailsTitle').textContent=
        meeting.title||
        'Meeting Details';

    $('detailsSubtitle').textContent=
        [
            meeting.meeting_no,
            meeting.venue
        ].filter(Boolean).join(' • ');

    $('detailDate').textContent=
        meeting.meeting_date
            ?AdminUI.formatDate(
                meeting.meeting_date
            )
            :'—';

    $('detailTime').textContent=
        meetingTime(meeting);

    $('detailType').textContent=
        formatLabel(meeting.type);

    $('detailStatus').innerHTML=
        AdminUI.statusBadge(
            meeting.status
        );

    $('meetingInformation').innerHTML=[
        infoItem(
            'Meeting No',
            meeting.meeting_no
        ),
        infoItem(
            'Type',
            formatLabel(meeting.type)
        ),
        infoItem(
            'Meeting Date',
            meeting.meeting_date
                ?AdminUI.formatDate(meeting.meeting_date)
                :null
        ),
        infoItem(
            'Start Time',
            formatTime(meeting.start_time)
        ),
        infoItem(
            'End Time',
            formatTime(meeting.end_time)
        ),
        infoItem(
            'Venue',
            meeting.venue
        )
    ].join('');

    const descriptionBlock=
        $('descriptionBlock');

    if(meeting.description){
        descriptionBlock.classList.remove('hidden');

        $('meetingDescription').textContent=
            meeting.description;
    }else{
        descriptionBlock.classList.add('hidden');
    }

    renderAttendance(
        meeting.my_attendance??
        meeting.attendance??
        null
    );

    renderAgendas(
        meeting.agendas??[]
    );

    renderDecisions(
        meeting.decisions??[]
    );

    renderMinutes(
        meeting.minutes
    );
}

function renderAttendance(attendance){
    const container=$('myAttendance');

    if(!attendance){
        container.innerHTML=`
            <div class="py-4 text-center">
                <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                    <i class="bi bi-person-dash"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-slate-600">
                    No attendance record
                </p>
            </div>
        `;

        return;
    }

    container.innerHTML=`
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] uppercase tracking-wide text-slate-500">
                    Attendance Status
                </p>

                <div class="mt-1">
                    ${AdminUI.statusBadge(
                        attendance.status
                    )}
                </div>
            </div>

            ${
                attendance.notes
                    ?`
                    <div class="max-w-md">
                        <p class="text-[10px] uppercase tracking-wide text-slate-500">
                            Notes
                        </p>

                        <p class="mt-1 text-sm leading-5 text-slate-600">
                            ${esc(attendance.notes)}
                        </p>
                    </div>
                    `
                    :''
            }
        </div>
    `;
}

function renderAgendas(items){
    const container=$('agendaList');

    if(!items.length){
        container.innerHTML=
            emptyBlock(
                'No agenda has been published.'
            );

        return;
    }

    container.innerHTML=`
        <div class="overflow-hidden rounded-md border border-slate-200 bg-white">
            <div class="divide-y divide-slate-100">
                ${items.map(item=>`
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-indigo-50 text-sm font-bold text-indigo-600">
                                ${item.sort_order??1}
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h5 class="text-base font-semibold text-slate-700">
                                        ${esc(item.title)}
                                    </h5>

                                    ${
                                        item.status
                                            ?AdminUI.statusBadge(item.status)
                                            :''
                                    }
                                </div>

                                ${
                                    item.description
                                        ?`
                                        <p class="mt-2 whitespace-pre-wrap text-sm leading-5 text-slate-500">
                                            ${esc(item.description)}
                                        </p>
                                        `
                                        :''
                                }
                            </div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function renderDecisions(items){
    const container=$('decisionList');

    if(!items.length){
        container.innerHTML=
            emptyBlock(
                'No decisions have been published.'
            );

        return;
    }

    container.innerHTML=`
        <div class="space-y-3">
            ${items.map(item=>`
                <article class="rounded-md border border-slate-200 bg-white px-5 py-2">

                    <div class="flex flex-wrap items-center gap-2">
                        ${
                            item.decision_no
                                ?`
                                <span class="text-[10px] font-semibold text-indigo-600">
                                    ${esc(item.decision_no)}
                                </span>
                                `
                                :''
                        }

                        ${
                            item.result
                                ?`
                                <span class="rounded-md bg-slate-100 px-2 py-1 text-[10px] font-semibold text-slate-600">
                                    ${esc(formatLabel(item.result))}
                                </span>
                                `
                                :''
                        }

                        ${
                            item.status
                                ?AdminUI.statusBadge(item.status)
                                :''
                        }
                    </div>

                    <h5 class="mt-2 text-base font-bold text-slate-700">
                        ${esc(item.title)}
                    </h5>

                    <p class="mt-1 whitespace-pre-wrap text-sm leading-5 text-slate-500">
                        ${esc(item.decision)}
                    </p>

                    ${
                        item.agenda?.title||
                        item.due_date
                            ?`
                            <div class="mt-3 grid grid-cols-1 gap-2 border-t border-slate-100 pt-3 text-[11px] sm:grid-cols-2">

                                ${
                                    item.agenda?.title
                                        ?`
                                        <div>
                                            <p class="text-slate-500 text-xs 2xl:text-sm">
                                                Related Agenda
                                            </p>

                                            <p class="mt-0.5 font-medium text-slate-600">
                                                ${esc(item.agenda.title)}
                                            </p>
                                        </div>
                                        `
                                        :''
                                }

                                ${
                                    item.due_date
                                        ?`
                                        <div>
                                            <p class="text-slate-500 text-xs 2xl:text-sm">
                                                Due Date
                                            </p>

                                            <p class="mt-0.5 font-medium text-slate-600">
                                                ${AdminUI.formatDate(item.due_date)}
                                            </p>
                                        </div>
                                        `
                                        :''
                                }
                            </div>
                            `
                            :''
                    }
                </article>
            `).join('')}
        </div>
    `;
}

function renderMinutes(minutes){
    if(!minutes){
        $('minutesContent').innerHTML=`
            <div class="py-5 text-center text-base text-slate-500">
                Meeting minutes have not been published yet.
            </div>
        `;

        return;
    }

    $('minutesContent').innerHTML=`
        <div class="whitespace-pre-wrap text-base leading-6 text-slate-600">
            ${esc(minutes)}
        </div>
    `;
}

function infoItem(label,value){
    return`
        <div class="min-w-0">
            <p class="text-[10px] uppercase tracking-wide text-slate-500">
                ${esc(label)}
            </p>

            <p class="mt-1 break-words font-semibold text-slate-700">
                ${esc(value??'—')}
            </p>
        </div>
    `;
}

function emptyBlock(message){
    return`
        <div class="rounded-md border border-slate-200 bg-white py-8 text-center text-base text-slate-500">
            ${esc(message)}
        </div>
    `;
}

function setMeetingQuery(id){
    const url=new URL(
        window.location.href
    );

    url.searchParams.set(
        'meeting',
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

function removeMeetingQuery(){
    const url=new URL(
        window.location.href
    );

    url.searchParams.delete(
        'meeting'
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

window.closeMeetingDetails=function(){
    AdminUI.closeModal(
        'meetingDetailsModal'
    );

    removeMeetingQuery();
};

window.clearFilters=function(){
    $('searchInput').value='';
    $('typeFilter').value='';
    $('statusFilter').value='';

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

    await Promise.all([
        loadSummary(),
        loadMeetings()
    ]);

    const params=
        new URLSearchParams(
            window.location.search
        );

    const meetingId=Number(
        params.get('meeting')
    );

    if(
        Number.isInteger(meetingId)&&
        meetingId>0
    ){
        await viewMeeting(
            meetingId
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

        const meetingId=Number(
            params.get('meeting')
        );

        if(
            Number.isInteger(meetingId)&&
            meetingId>0
        ){
            viewMeeting(
                meetingId
            );
        }else{
            AdminUI.closeModal(
                'meetingDetailsModal'
            );
        }
    }
);

if(document.readyState==='loading'){
    document.addEventListener(
        'DOMContentLoaded',
        initMeetingPage
    );
}else{
    initMeetingPage();
}
</script>
@endpush