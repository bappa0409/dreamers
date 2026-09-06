@extends('layouts.member')

@section('title','Polls')
@section('page_title','Polls')

@section('content')

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex flex-col gap-4 rounded-lg border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-sm">
                <i class="bi bi-ui-checks-grid"></i>
            </div>

            <div>
                <h1 class="text-base font-semibold tracking-tight text-slate-800">
                    Member Polls
                </h1>

                <p class="mt-0.5 text-sm text-slate-500">
                    Participate in association polls and review your voting history.
                </p>
            </div>
        </div>

        <button
            type="button"
            onclick="loadPolls()"
            class="inline-flex h-9 w-fit items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">
            <i class="bi bi-arrow-clockwise"></i>
            Refresh
        </button>
    </div>

    {{-- Information --}}
    <div class="flex gap-3 rounded-lg border border-sky-200 bg-sky-50/60 p-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
            <i class="bi bi-shield-check"></i>
        </div>

        <div>
            <p class="text-sm font-semibold text-sky-800">
                Member Voting
            </p>

            <p class="mt-0.5 text-[11px] leading-5 text-sky-700">
                Each member may submit one vote per active poll. Once submitted, a vote cannot be changed from the member portal.
            </p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                        Active Polls
                    </p>

                    <p id="summaryActive" class="mt-2 text-xl font-bold text-slate-800">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-ui-checks-grid"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-sky-200 bg-sky-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-sky-600">
                        Available to Vote
                    </p>

                    <p id="summaryAvailable" class="mt-2 text-xl font-bold text-sky-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                    <i class="bi bi-pencil-square"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-emerald-200 bg-emerald-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-emerald-600">
                        Voted
                    </p>

                    <p id="summaryVoted" class="mt-2 text-xl font-bold text-emerald-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-violet-200 bg-violet-50/40 p-4">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-violet-600">
                        Voting History
                    </p>

                    <p id="summaryHistory" class="mt-2 text-xl font-bold text-violet-700">
                        0
                    </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-violet-100 text-violet-600">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- Filter / Action Bar --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

            <div class="lg:col-span-6">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Search
                </label>

                <div class="relative">
                    <i class="bi bi-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>

                    <input
                        id="pollSearch"
                        type="text"
                        placeholder="Search poll title or description..."
                        class="h-9 w-full rounded-md border border-slate-300 bg-white pl-9 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                </div>
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                    Voting Status
                </label>

                <select
                    id="pollStatusFilter"
                    class="h-9 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">

                    <option value="">
                        All Polls
                    </option>

                    <option value="available">
                        Available to Vote
                    </option>

                    <option value="voted">
                        Voted
                    </option>

                </select>
            </div>

            <div class="lg:col-span-3">
                <div class="flex gap-2">

                    <button
                        type="button"
                        onclick="clearPollFilters()"
                        class="h-9 flex-1 rounded-md border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Clear
                    </button>

                    <button
                        type="button"
                        onclick="openHistoryModal()"
                        class="inline-flex h-9 flex-1 items-center justify-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-3  text-xs 2xl:text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">

                        <i class="bi bi-clock-history"></i>

                        History
                    </button>

                </div>
            </div>

        </div>
    </div>

    {{-- Poll List --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

        <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">

            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <i class="bi bi-ui-checks-grid"></i>
            </div>

            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Active Polls
                </h2>

                <p class="text-[11px] text-slate-400">
                    Association polls currently available to members
                </p>
            </div>

        </div>

        <div
            id="pollContainer"
            class="grid grid-cols-1 gap-4 p-5 xl:grid-cols-2">

            <div class="col-span-full py-12 text-center">
                <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

                <p class="mt-3 text-sm text-slate-400">
                    Loading polls...
                </p>
            </div>

        </div>
    </div>

</div>

{{-- Vote Modal --}}
<div
    id="voteModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-xl flex-col overflow-hidden rounded-lg bg-white">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <i class="bi bi-check2-square"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Submit Vote
                    </h2>

                    <p class="text-[11px] text-slate-400">
                        Select one option and submit your vote.
                    </p>
                </div>

            </div>

            <button
                type="button"
                onclick="closeVoteModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div class="min-h-0 flex-1 overflow-y-auto p-5">

            <div class="mb-5 rounded-lg border border-slate-200 bg-slate-50/50 p-4">

                <h3
                    id="votePollTitle"
                    class="text-base font-bold text-slate-800">
                </h3>

                <p
                    id="votePollDescription"
                    class="mt-1 whitespace-pre-line text-sm leading-5 text-slate-500">
                </p>

            </div>

            <p class="mb-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                Select Option
            </p>

            <div
                id="voteOptions"
                class="space-y-2">
            </div>

            <div
                id="voteError"
                class="mt-4 hidden rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-600">
            </div>

        </div>

        <div class="flex shrink-0 justify-end gap-2 border-t border-slate-200 bg-slate-50/50 px-5 py-4">

            <button
                type="button"
                onclick="closeVoteModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Cancel
            </button>

            <button
                id="submitVoteButton"
                type="button"
                onclick="submitVote()"
                class="inline-flex h-9 items-center gap-1.5 rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60">

                <i class="bi bi-check2-circle"></i>

                Submit Vote
            </button>

        </div>

    </div>
</div>

{{-- Voting History Modal --}}
<div
    id="historyModal"
    class="app-modal-overlay fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-5">

    <div class="app-modal-panel flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white">

        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-5 py-4">

            <div class="flex items-center gap-3">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>
                    <h2 class="text-base font-bold text-slate-800">
                        Voting History
                    </h2>

                    <p class="text-[11px] text-slate-400">
                        Poll votes previously submitted by you.
                    </p>
                </div>

            </div>

            <button
                type="button"
                onclick="closeHistoryModal()"
                class="app-modal-close">
                <i class="bi bi-x-lg"></i>
            </button>

        </div>

        <div
            id="historyBody"
            class="min-h-0 flex-1 overflow-y-auto p-5">
        </div>

        <div class="flex shrink-0 justify-end border-t border-slate-200 bg-slate-50/50 px-5 py-4">

            <button
                type="button"
                onclick="closeHistoryModal()"
                class="h-9 rounded-md border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Close
            </button>

        </div>

    </div>
</div>

@endsection

@push('scripts')

<script>
let memberPolls=[];
let votingHistory=[];
let selectedPoll=null;
let selectedOptionId=null;
let pollSearchTimer=null;

const pollSearch=
    document.getElementById(
        'pollSearch'
    );

const pollStatusFilter=
    document.getElementById(
        'pollStatusFilter'
    );

async function loadPolls(){
    const container=
        document.getElementById(
            'pollContainer'
        );

    container.innerHTML=`
        <div class="col-span-full py-14 text-center">

            <div class="mx-auto h-7 w-7 animate-spin rounded-full border-4 border-slate-200 border-t-indigo-600"></div>

            <p class="mt-3 text-sm text-slate-400">
                Loading polls...
            </p>

        </div>
    `;

    try{
        const response=
            await api(
                '/api/member/polls'
            );

        const data=
            response.data??{};

        memberPolls=
            Array.isArray(
                data.active_polls
            )
                ?data.active_polls
                :[];

        votingHistory=
            Array.isArray(
                data.voting_history
            )
                ?data.voting_history
                :[];

        updateSummary(
            data.summary??{}
        );

        renderPolls();

    }catch(error){
        container.innerHTML=`
            <div class="col-span-full py-14 text-center">

                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-red-100 text-red-500">
                    <i class="bi bi-exclamation-circle text-lg"></i>
                </div>

                <p class="mt-3 text-base font-semibold text-red-600">
                    Failed to load polls
                </p>

                <p class="mt-1 text-sm text-red-400">
                    ${escapeValue(
                        AdminUI.extractError(
                            error
                        )
                    )}
                </p>

                <button
                    type="button"
                    onclick="loadPolls()"
                    class="mt-4 inline-flex h-9 items-center gap-2 rounded-md border border-red-200 bg-white px-3 text-sm font-semibold text-red-600 transition hover:bg-red-50">

                    <i class="bi bi-arrow-clockwise"></i>

                    Try Again
                </button>

            </div>
        `;
    }
}

function updateSummary(summary){
    const votedCount=
        memberPolls.filter(
            poll=>
                Boolean(
                    poll.has_voted
                )
        ).length;

    const availableCount=
        memberPolls.filter(
            poll=>
                !Boolean(
                    poll.has_voted
                )
        ).length;

    document.getElementById(
        'summaryActive'
    ).textContent=
        Number(
            summary.active_polls??
            memberPolls.length
        );

    document.getElementById(
        'summaryAvailable'
    ).textContent=
        availableCount;

    document.getElementById(
        'summaryVoted'
    ).textContent=
        votedCount;

    document.getElementById(
        'summaryHistory'
    ).textContent=
        Number(
            summary.total_votes??
            votingHistory.length
        );
}

function getFilteredPolls(){
    const search=
        pollSearch.value
            .trim()
            .toLowerCase();

    const status=
        pollStatusFilter.value;

    return memberPolls.filter(
        poll=>{
            if(
                status==='voted'&&
                !Boolean(
                    poll.has_voted
                )
            ){
                return false;
            }

            if(
                status==='available'&&
                Boolean(
                    poll.has_voted
                )
            ){
                return false;
            }

            if(search){
                const haystack=[
                    poll.title,
                    poll.description
                ]
                .map(
                    value=>
                        String(
                            value??''
                        ).toLowerCase()
                )
                .join(' ');

                if(
                    !haystack.includes(
                        search
                    )
                ){
                    return false;
                }
            }

            return true;
        }
    );
}

function renderPolls(){
    const container=
        document.getElementById(
            'pollContainer'
        );

    const polls=
        getFilteredPolls();

    if(!polls.length){
        container.innerHTML=`
            <div class="col-span-full py-16 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                    <i class="bi bi-ui-checks-grid text-xl"></i>
                </div>

                <p class="mt-4 text-base font-semibold text-slate-700">
                    No polls found
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    No active polls match the current filters.
                </p>

            </div>
        `;

        return;
    }

    container.innerHTML=
        polls.map(
            poll=>
                renderPollCard(
                    poll
                )
        ).join('');
}

function renderPollCard(poll){
    const voted=
        Boolean(
            poll.has_voted
        );

    const options=
        Array.isArray(
            poll.options
        )
            ?poll.options
            :[];

    const selectedId=
        Number(
            poll.my_vote_option_id??
            0
        );

    return`
        <article class="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:border-slate-300 hover:shadow-sm">

            <div class="flex-1 p-5">

                <div class="mb-4 flex items-start justify-between gap-4">

                    <div class="flex min-w-0 items-start gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${
                            voted
                                ?'bg-emerald-50 text-emerald-600'
                                :'bg-indigo-50 text-indigo-600'
                        }">

                            <i class="bi ${
                                voted
                                    ?'bi-check-circle'
                                    :'bi-ui-checks-grid'
                            }"></i>

                        </div>

                        <div class="min-w-0">

                            <h3 class="text-base font-bold leading-5 text-slate-800">
                                ${escapeValue(
                                    poll.title??
                                    'Untitled Poll'
                                )}
                            </h3>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] text-slate-400">

                                <span>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    ${dateTime(
                                        poll.start_at
                                    )}
                                </span>

                                <span>
                                    <i class="bi bi-calendar-x me-1"></i>
                                    ${dateTime(
                                        poll.end_at
                                    )}
                                </span>

                            </div>

                        </div>

                    </div>

                    ${
                        voted
                            ?`
                                <span class="inline-flex shrink-0 items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">

                                    <i class="bi bi-check-circle-fill"></i>

                                    Voted
                                </span>
                            `
                            :`
                                <span class="inline-flex shrink-0 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[10px] font-semibold text-indigo-700">
                                    Available
                                </span>
                            `
                    }

                </div>

                ${
                    poll.description
                        ?`
                            <p class="mb-4 whitespace-pre-line text-sm leading-5 text-slate-500">
                                ${escapeValue(
                                    poll.description
                                )}
                            </p>
                        `
                        :''
                }

                <div class="space-y-2">

                    ${
                        options.map(
                            (
                                option,
                                index
                            )=>{
                                const selected=
                                    voted&&
                                    Number(
                                        option.id
                                    )===selectedId;

                                return`
                                    <div class="flex items-center gap-3 rounded-md border ${
                                        selected
                                            ?'border-indigo-200 bg-indigo-50'
                                            :'border-slate-200 bg-slate-50/50'
                                    } px-3 py-2.5">

                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full ${
                                            selected
                                                ?'bg-indigo-600 text-white'
                                                :'bg-white text-slate-400 ring-1 ring-slate-200'
                                        } text-[10px] font-bold">

                                            ${
                                                selected
                                                    ?'<i class="bi bi-check-lg"></i>'
                                                    :index+1
                                            }

                                        </span>

                                        <span class="min-w-0 flex-1 text-sm font-medium ${
                                            selected
                                                ?'text-indigo-700'
                                                :'text-slate-600'
                                        }">

                                            ${escapeValue(
                                                option.option_text??
                                                'Option'
                                            )}

                                        </span>

                                        ${
                                            selected
                                                ?`
                                                    <span class="shrink-0 text-[10px] font-semibold text-indigo-600">
                                                        Your Vote
                                                    </span>
                                                `
                                                :''
                                        }

                                    </div>
                                `;
                            }
                        ).join('')
                    }

                </div>

                ${
                    voted
                        ?`
                            <div class="mt-4 flex items-center gap-2 rounded-md border border-emerald-100 bg-emerald-50 px-3 py-2.5">

                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                    <i class="bi bi-check-lg text-sm"></i>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-emerald-700">
                                        Vote submitted
                                    </p>

                                    <p class="text-[10px] text-emerald-600">
                                        Your response has been recorded.
                                    </p>
                                </div>

                            </div>
                        `
                        :`
                            <div class="mt-4 flex items-center gap-2 rounded-md border border-sky-100 bg-sky-50 px-3 py-2.5">

                                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                                    <i class="bi bi-info-circle text-sm"></i>
                                </div>

                                <div>
                                    <p class="text-[11px] font-semibold text-sky-700">
                                        Waiting for your vote
                                    </p>

                                    <p class="text-[10px] text-sky-600">
                                        Select Vote Now to participate.
                                    </p>
                                </div>

                            </div>
                        `
                }

            </div>

            <div class="flex items-center justify-between gap-2 border-t border-slate-100 bg-slate-50/50 px-5 py-3">

                <button
                    type="button"
                    onclick="openHistoryModal()"
                    class="inline-flex h-8 items-center gap-1.5 rounded-md border border-slate-200 bg-white px-3 text-[11px] font-semibold text-slate-600 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700">

                    <i class="bi bi-clock-history"></i>

                    History
                </button>

                ${
                    !voted
                        ?`
                            <button
                                type="button"
                                onclick="openVoteModal(${Number(poll.id)})"
                                class="inline-flex h-8 items-center gap-1.5 rounded-md bg-indigo-600 px-3 text-[11px] font-semibold text-white transition hover:bg-indigo-700">

                                <i class="bi bi-check2-square"></i>

                                Vote Now
                            </button>
                        `
                        :`
                            <span class="inline-flex h-8 items-center gap-1.5 rounded-md bg-emerald-50 px-3 text-[11px] font-semibold text-emerald-700">

                                <i class="bi bi-check-circle"></i>

                                Completed
                            </span>
                        `
                }

            </div>

        </article>
    `;
}

window.clearPollFilters=function(){
    pollSearch.value='';
    pollStatusFilter.value='';

    renderPolls();
};

window.openVoteModal=function(pollId){
    const poll=
        memberPolls.find(
            item=>
                Number(item.id)===
                Number(pollId)
        );

    if(!poll){
        Toast.error(
            'Poll not found.'
        );

        return;
    }

    if(poll.has_voted){
        Toast.error(
            'You have already voted in this poll.'
        );

        return;
    }

    selectedPoll=poll;
    selectedOptionId=null;

    document.getElementById(
        'votePollTitle'
    ).textContent=
        poll.title??
        'Poll';

    document.getElementById(
        'votePollDescription'
    ).textContent=
        poll.description??
        '';

    document.getElementById(
        'voteError'
    ).classList.add(
        'hidden'
    );

    document.getElementById(
        'voteError'
    ).textContent='';

    const options=
        Array.isArray(
            poll.options
        )
            ?poll.options
            :[];

    document.getElementById(
        'voteOptions'
    ).innerHTML=
        options.map(
            (
                option,
                index
            )=>`
                <label class="group flex cursor-pointer items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-3 transition hover:border-indigo-200 hover:bg-indigo-50/40">

                    <input
                        type="radio"
                        name="pollOption"
                        value="${Number(option.id)}"
                        onchange="selectPollOption(${Number(option.id)})"
                        class="h-4 w-4 cursor-pointer border-slate-300 text-indigo-600 focus:ring-indigo-500">

                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500 transition group-hover:bg-indigo-100 group-hover:text-indigo-600">
                        ${index+1}
                    </span>

                    <span class="min-w-0 flex-1 text-sm font-medium text-slate-700">
                        ${escapeValue(
                            option.option_text??
                            'Option'
                        )}
                    </span>

                </label>
            `
        ).join('');

    AdminUI.openModal(
        'voteModal'
    );
};

window.selectPollOption=function(id){
    selectedOptionId=
        Number(id);
};

window.closeVoteModal=function(){
    selectedPoll=null;
    selectedOptionId=null;

    AdminUI.closeModal(
        'voteModal'
    );
};

window.submitVote=async function(){
    if(!selectedPoll){
        Toast.error(
            'Poll not found.'
        );

        return;
    }

    if(!selectedOptionId){
        showVoteError(
            'Please select an option.'
        );

        return;
    }

    const button=
        document.getElementById(
            'submitVoteButton'
        );

    const original=
        button.innerHTML;

    button.disabled=true;

    button.innerHTML=`
        <i class="bi bi-arrow-repeat animate-spin"></i>
        Submitting...
    `;

    document.getElementById(
        'voteError'
    ).classList.add(
        'hidden'
    );

    try{
        const response=
            await api(
                `/api/polls/${selectedPoll.id}/vote`,
                {
                    method:'POST',
                    body:JSON.stringify({
                        poll_option_id:
                            selectedOptionId
                    })
                }
            );

        closeVoteModal();

        Toast.success(
            response.message??
            'Vote submitted successfully.'
        );

        await loadPolls();

    }catch(error){
        showVoteError(
            AdminUI.extractError(
                error
            )
        );

    }finally{
        button.disabled=false;
        button.innerHTML=original;
    }
};

function showVoteError(message){
    const error=
        document.getElementById(
            'voteError'
        );

    error.textContent=message;

    error.classList.remove(
        'hidden'
    );
}

window.openHistoryModal=function(){
    const body=
        document.getElementById(
            'historyBody'
        );

    if(!votingHistory.length){
        body.innerHTML=`
            <div class="py-12 text-center">

                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-violet-50 text-violet-500">
                    <i class="bi bi-clock-history text-xl"></i>
                </div>

                <p class="mt-4 text-base font-semibold text-slate-700">
                    No voting history
                </p>

                <p class="mt-1 text-sm text-slate-400">
                    You have not voted in any polls yet.
                </p>

            </div>
        `;

        AdminUI.openModal(
            'historyModal'
        );

        return;
    }

    body.innerHTML=`
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">

            <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">

                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-slate-800">
                        Submitted Votes
                    </h3>

                    <p class="text-[11px] text-slate-400">
                        Your complete voting history
                    </p>
                </div>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full min-w-[700px]">

                    <thead class="border-b border-slate-200 bg-slate-50">
                        <tr>

                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                Poll
                            </th>

                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                Your Vote
                            </th>

                            <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                Voted At
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        ${
                            votingHistory.map(
                                item=>`
                                    <tr class="transition hover:bg-slate-50/70">

                                        <td class="px-4 py-3">

                                            <p class="text-sm font-semibold text-slate-700">
                                                ${escapeValue(
                                                    item.poll_title??
                                                    '—'
                                                )}
                                            </p>

                                        </td>

                                        <td class="px-4 py-3">

                                            <span class="inline-flex rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-[10px] font-semibold text-indigo-700">

                                                <i class="bi bi-check2 me-1"></i>

                                                ${escapeValue(
                                                    item.option_text??
                                                    '—'
                                                )}

                                            </span>

                                        </td>

                                        <td class="px-4 py-3  text-xs 2xl:text-sm text-slate-500">
                                            ${dateTime(
                                                item.created_at
                                            )}
                                        </td>

                                    </tr>
                                `
                            ).join('')
                        }

                    </tbody>

                </table>

            </div>

        </div>
    `;

    AdminUI.openModal(
        'historyModal'
    );
};

window.closeHistoryModal=function(){
    AdminUI.closeModal(
        'historyModal'
    );
};

function dateTime(value){
    if(!value){
        return'—';
    }

    const date=
        new Date(value);

    if(
        Number.isNaN(
            date.getTime()
        )
    ){
        return'—';
    }

    return date.toLocaleString(
        'en-GB',
        {
            day:'2-digit',
            month:'short',
            year:'numeric',
            hour:'2-digit',
            minute:'2-digit'
        }
    );
}

function escapeValue(value){
    return AdminUI.escapeHtml(
        String(
            value??''
        )
    );
}

pollSearch.addEventListener(
    'input',
    ()=>{
        clearTimeout(
            pollSearchTimer
        );

        pollSearchTimer=
            setTimeout(
                renderPolls,
                300
            );
    }
);

pollStatusFilter.addEventListener(
    'change',
    renderPolls
);

async function initPollPage(){
    if(
        typeof window.AdminUI===
            'undefined'||
        typeof window.api===
            'undefined'
    ){
        setTimeout(
            initPollPage,
            50
        );

        return;
    }

    await loadPolls();
}

if(
    document.readyState===
    'loading'
){
    document.addEventListener(
        'DOMContentLoaded',
        initPollPage
    );
}else{
    initPollPage();
}
</script>

@endpush